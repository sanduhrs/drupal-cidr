<?php

namespace Drupal\cidr;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of cidrs.
 */
class CidrListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['status'] = $this->t('Status');
    $header['range_start'] = $this->t('Range start');
    $header['range_end'] = $this->t('Range end');
    $header['suffix'] = $this->t('Suffix');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\cidr\CidrService $cidr */
    $cidr = \Drupal::service('cidr.service');
    $cidr->setIp($entity->get('ip_dotted'));
    $cidr->setSuffix($entity->get('suffix'));

    /** @var \Drupal\cidr\CidrInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    $row['range_start'] = $cidr->getRangeStartIp();
    $row['range_end'] = $cidr->getRangeEndIp();
    $row['suffix'] = $cidr->getSuffix();
    return $row + parent::buildRow($entity);
  }

}
