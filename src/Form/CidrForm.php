<?php

namespace Drupal\cidr\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * CIDR form.
 *
 * @property \Drupal\cidr\CidrInterface $entity
 */
class CidrForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('Label for the cidr.'),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\cidr\Entity\Cidr::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];
    $form['ip_dotted'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IP'),
      '#default_value' => $this->entity->get('ip_dotted'),
      '#description' => $this->t('String representation of an IP address, in dotted notation.'),
    ];
    $form['suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Suffix'),
      '#default_value' => $this->entity->get('suffix'),
      '#description' => $this->t('The ip suffix, representing the netmask.'),
    ];
    $form['range_start'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Range start'),
      '#default_value' => $this->entity->get('range_start'),
    ];
    $form['range_end'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Range end'),
      '#default_value' => $this->entity->get('range_end'),
    ];
    $form['uid'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('User'),
      '#default_value' => User::load($this->entity->get('uid')),
    ];
    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\cidr\CidrService $cidr */
    $cidr = \Drupal::service('cidr.service');

    $ip = $form_state->getValue('ip_dotted');
    $suffix = $form_state->getValue('suffix');
    if (!inet_pton($ip)) {
      $form_state->setErrorByName('ip_dotted', $this->t('Please enter a valid IP address.'));
    }
    if (!preg_match('/^\d+$/', $suffix)) {
      $form_state->setErrorByName('suffix', $this->t('Please enter a valid CIDR Notation suffix.'));
    }

    $cidr->setIp($ip)->setSuffix($suffix);
    $form_state->setValue('range_start', $cidr->getRangeStartIp('numeric'));
    $form_state->setValue('range_end', $cidr->getRangeEndIp('numeric'));
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new cidr %label.', $message_args)
      : $this->t('Updated cidr %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));

    /** @var \Drupal\Core\Cache\CacheBackendInterface $cache */
    $cache = \Drupal::service('cache.dynamic_page_cache');
    $cache->deleteAll();

    return $result;
  }

}
