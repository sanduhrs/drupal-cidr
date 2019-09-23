<?php

namespace Drupal\cidr\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cidr\CidrInterface;

/**
 * Defines the cidr entity type.
 *
 * @ConfigEntityType(
 *   id = "cidr",
 *   label = @Translation("CIDR"),
 *   label_collection = @Translation("CIDRs"),
 *   label_singular = @Translation("cidr"),
 *   label_plural = @Translation("cidrs"),
 *   label_count = @PluralTranslation(
 *     singular = "@count cidr",
 *     plural = "@count cidrs",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cidr\CidrListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cidr\Form\CidrForm",
 *       "edit" = "Drupal\cidr\Form\CidrForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "cidr",
 *   admin_permission = "administer cidr",
 *   links = {
 *     "collection" = "/admin/structure/cidr",
 *     "add-form" = "/admin/structure/cidr/add",
 *     "edit-form" = "/admin/structure/cidr/{cidr}",
 *     "delete-form" = "/admin/structure/cidr/{cidr}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "ip_dotted",
 *     "suffix",
 *     "range_start",
 *     "range_end",
 *     "uid",
 *     "status",
 *   }
 * )
 */
class Cidr extends ConfigEntityBase implements CidrInterface {

  /**
   * The entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The label.
   *
   * @var string
   */
  protected $label;

  /**
   * The publication status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The IP in dotted notation.
   *
   * @var string
   */
  protected $ip_dotted;

  /**
   * The cidr suffix.
   *
   * @var int
   */
  protected $suffix;

  /**
   * The start IP from a range.
   *
   * @var int
   */
  protected $range_start;

  /**
   * The end IP from a range.
   *
   * @var int
   */
  protected $range_end;

  /**
   * The user id.
   *
   * @var int
   */
  protected $uid;

}
