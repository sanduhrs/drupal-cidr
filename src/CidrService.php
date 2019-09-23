<?php

namespace Drupal\cidr;

use Drupal\cidr\Entity\Cidr;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * CidrService service.
 */
class CidrService {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The ip address in dotted notation.
   *
   * @var string
   */
  protected $ip;

  /**
   * The range suffix.
   *
   * @var int
   */
  protected $suffix = 32;

  /**
   * Constructs a CidrService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * The ip in dotted notation.
   *
   * @param string $notation
   *   The notation type to return the ip in.
   *
   * @return string|int|false
   *   The ip address at the start of the range, boolean false for failure.
   */
  public function getIp($notation = 'dotted') {
    return $this->getRangeStartIp($notation);
  }

  /**
   * The ip in dotted notation.
   *
   * @param string $ip
   *   The ip address.
   *
   * @return $this
   */
  public function setIp($ip) {
    $this->ip = $ip;
    return $this;
  }

  /**
   * Get the suffix.
   *
   * @return int
   *   The cidr mask suffix.
   */
  public function getSuffix() {
    return $this->suffix;
  }

  /**
   * The suffix.
   *
   * @param int $suffix
   *   The cidr mask suffix.
   *
   * @return $this
   */
  public function setSuffix($suffix) {
    $this->suffix = $suffix;
    return $this;
  }

  /**
   * Get ip address and range in cidr notation.
   *
   * @return string
   *   The ip and range in cidr notation.
   */
  public function getCidr() {
    return "{$this->ip}/{$this->suffix}";
  }

  /**
   * Get ip at the start of the range.
   *
   * @param string $notation
   *   'dotted': The ip in dotted notation, e.g. 192.0.34.166.
   *   'numeric': The ip in numeric notation, e.g. 3221234342.
   *
   * @return string|int|false
   *   The ip address at the start of the range, boolean false for failure.
   */
  public function getRangeStartIp($notation = 'dotted') {
    switch ($notation) {
      case 'dotted':
        return $this->ip;

      case 'numeric':
        return sprintf("%u", ip2long($this->ip));
    }
    return FALSE;
  }

  /**
   * Get ip at the end of the range.
   *
   * @param string $notation
   *   'dotted': The ip in dotted notation, e.g. 192.0.34.166.
   *   'numeric': The ip in numeric notation, e.g. 3221234342.
   *
   * @return string|int|false
   *   The ip address at the end of the range, boolean false for failure.
   */
  public function getRangeEndIp($notation = 'dotted') {
    $exp = 32 - $this->suffix;
    $numeric_ip = sprintf("%u", (ip2long($this->ip) + ($exp > 0 ? pow(2, $exp) : 0)));

    switch ($notation) {
      case 'dotted':
        return long2ip($numeric_ip);

      case 'numeric':
        return $numeric_ip;
    }
    return FALSE;
  }

  /**
   * Check if cidr is authoritative for a given ip.
   *
   * @return bool
   *   Return true if cidr is authoritative, false otherwise.
   */
  public function isAuthoritative($ip) {
    return (bool) $this->getAuthoritativeRanges($ip);
  }

  /**
   * Get authoritative ranges.
   *
   * @return array
   *   An array of valid cidr ranges.
   */
  public function getAuthoritativeRanges($ip) {
    $numeric_ip = sprintf("%u", ip2long($ip));
    $query = \Drupal::entityQuery('cidr');
    $query
      ->condition('status', 1)
      ->condition('range_start', $numeric_ip, '<=')
      ->condition('range_end', $numeric_ip, '>=');
    $entity_ids = $query->execute();
    return Cidr::loadMultiple($entity_ids);
  }

}
