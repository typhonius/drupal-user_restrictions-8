<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\Entity\UserRestrictions
 */

namespace Drupal\user_restrictions\Entity;

use \Drupal\Core\Entity\ContentEntityBase;
use \Drupal\Core\Entity\ContentEntityInterface;
use \Drupal\Core\Entity\EntityTypeInterface;
use \Drupal\Core\Field\FieldDefinition;

/**
 * Defines a user restrictions content entity.
 *
 * @ContentEntityType(
 *   id = "user_restrictions",
 *   label = @Translation("User Restrictions"),
 *   controllers = {
 *     "list_builder" = "Drupal\user_restrictions\UserRestrictionsListBuilder",
 *     "form" = {
 *       "add" = "Drupal\user_restrictions\UserRestrictionsController",
 *       "edit" = "Drupal\user_restrictions\UserRestrictionsController",
 *       "delete" = "Drupal\user_restrictions\Form\UserRestrictionsDeleteForm",
 *     },
 *   },
 *   base_table = "user_restrictions",
 *   entity_keys = {
 *     "id" = "urid",
 *     "uuid" = "uuid",
 *     "label" = "mask"
 *   },
 *   admin_permission = "administer user restrictions",
 *   links = {
 *     "edit-form" = "user_restrictions.edit_form",
 *     "delete-form" = "user_restrictions.delete_form",
 *   }
 * )
 */
class UserRestrictions extends ContentEntityBase implements ContentEntityInterface {

  /**
   * Indicates that the restriction should never expire unless explicitly deleted.
   */
  const RESTRICT_PERMANENT = 0;

  /**
   * Indicates that the mask is specifically allowed.
   */
  const ACCESS_TYPE_ALLOWED = 1;

  /**
   * Indicates that the mask is specifically denied.
   */
  const ACCESS_TYPE_DENIED = 0;

  // This can go when we upgrade to a later core. @TODO
  public function id() {
    return $this->get('urid')->value;
  }

  public function label() {
    return $this->get('mask')->value;
  }

  public function getStatus() {
    return (bool) $this->get('status')->value;
  }

  public function getExpireTime() {
    return $this->get('expire')->value;
  }

  public function getType() {
    return $this->get('type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpireTime($expire) {
    // Only add the expiry if it's not the forever expiry.
    if ($expire != self::RESTRICT_PERMANENT) {
      $expire += REQUEST_TIME;
    }
    $this->set('expire', $expire);
    return $this;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['urid'] = FieldDefinition::create('integer')
      ->setLabel(t('User Restrictions ID'))
      ->setDescription(t('The User Restrictions ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The User Restrictions UUID.'))
      ->setReadOnly(TRUE);

    $fields['type'] = FieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The restriction type.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ));

    $fields['mask'] = FieldDefinition::create('string')
      ->setLabel(t('Mask'))
      ->setDescription(t('Text mask used for filtering restrictions.
      %: Matches any number of characters, even zero characters.
      _: Matches exactly one character.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = FieldDefinition::create('boolean')
      ->setLabel(t('Restriction status'))
      ->setDescription(t('A boolean indicating whether the restriction is enabled.'))
      ->setSetting('default_value', 1)
      ->setDisplayConfigurable('form', TRUE);

    $fields['expire'] = FieldDefinition::create('timestamp')
      ->setLabel(t('Restriction Expiry'))
      ->setDescription(t('Unix timestamp providing the expiry of restriction.'))
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
