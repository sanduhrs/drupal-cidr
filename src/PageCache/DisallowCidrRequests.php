<?php

namespace Drupal\cidr\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cache policy for pages served from cidr.
 *
 * This policy disallows caching of requests that use cidr for security
 * reasons. Otherwise responses for authenticated requests can get into the
 * page cache and could be delivered to unprivileged users.
 */
class DisallowCidrRequests implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    /** @var \Drupal\cidr\CidrService $cidr */
    $cidr = \Drupal::service('cidr.service');
    if ($cidr->isAuthoritative($request->getClientIp())) {
      return self::DENY;
    }
  }

}
