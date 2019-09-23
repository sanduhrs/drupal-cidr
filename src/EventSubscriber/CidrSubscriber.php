<?php

namespace Drupal\cidr\EventSubscriber;

use Drupal\cidr\CidrService;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * CIDR Auth event subscriber.
 */
class CidrSubscriber implements EventSubscriberInterface {

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  private $session;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The CIDR service.
   *
   * @var \Drupal\cidr\CidrService
   */
  protected $cidr;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs event subscriber.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\cidr\CidrService $cidr
   *   The cidr service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
      SessionInterface $session,
      RequestStack $request_stack,
      MessengerInterface $messenger,
      CidrService $cidr,
      EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->session = $session;
    $this->requestStack = $request_stack;
    $this->messenger = $messenger;
    $this->cidr = $cidr;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest'],
    ];
  }

  /**
   * Kernel request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onKernelRequest(GetResponseEvent $event) {
    // Initialize cidr service with the current ip address.
    $this->cidr->setIp($this->requestStack->getCurrentRequest()->getClientIp());
    $current_ip = $this->requestStack->getCurrentRequest()->getClientIp();

    // Check if user has been logged in automatically by cidr and if the ip
    // range is still valid.
    if (!\Drupal::currentUser()->isAnonymous()
      && $this->session->get('cidr')) {
      // Check if cidr is authoritative for current ip.
      if (!$this->cidr->isAuthoritative($current_ip)) {
        // Otherwise log the user out.
        user_logout();
        return;
      }
    }

    // Try to log in any anonymous user if cidr authoritative for current ip.
    if (\Drupal::currentUser()->isAnonymous()
      && $this->cidr->isAuthoritative($current_ip)) {

      /** @var \Drupal\cidr\Entity\Cidr[] $ranges */
      $ranges = $this->cidr->getAuthoritativeRanges($current_ip);
      if (!count($ranges)) {
        return;
      }

      $range = reset($ranges);
      $accounts = $this->entityTypeManager->getStorage('user')
        ->loadByProperties(
        [
          'uid' => $range->get('uid'),
          'status' => 1,
        ]
      );

      if (!count($accounts)) {
        return;
      }

      // Found a valid user.
      $account = reset($accounts);
      // Log the user in.
      user_login_finalize($account);

      // Mark session with indicator for cidr.
      $this->session->set('cidr', TRUE);
    }
  }

}
