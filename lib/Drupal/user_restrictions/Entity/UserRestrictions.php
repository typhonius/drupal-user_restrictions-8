<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\Entity\UserRestrictions
 */

namespace Drupal\user_restrictions\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
// https://drupal.org/node/1827470
//https://drupal.org/node/2096117

use Drupal\Core\Config\Entity\EntityWithPluginBagInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityFormControllerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Defines a user restrictions content entity.
 *
 * @ContentEntityType(
 *   id = "user_restrictions",
 *   label = @Translation("User Restrictions"),
 *   controllers = {
 *     "form" = {
 *       "add" = "Drupal\user_restrictions\Form\UserRestrictionsAddForm",
 *       "edit" = "Drupal\user_restrictions\Form\UserRestrictionsEditForm",
 *       "delete" = "Drupal\user_restrictions\Form\UserRestrictionsDeleteForm",
 *       "flush" = "Drupal\user_restrictions\Form\UserRestrictionsFlushForm"
 *     },
 *     "list_builder" = "Drupal\user_restrictions\Form\UserRestrictionsListBuilder",
 *   },
 *   base_table = "user_restrictions",
 *   entity_keys = {
 *     "id" = "urid",
 *     "uuid" = "uuid",
 *     "label" = "mask"
 *   },
 *   admin_permission = "administer user restrictions",
 *   links = {
 *     "flush-form" = "user_restrictions.rule_flush",
 *     "edit-form" = "user_restrictions.rule_edit",
 *     "delete-form" = "user_restrictions.rule_delete"
 *   }
 * )
 */
class UserRestrictions extends ContentEntityBase implements ContentEntityInterface {

  /**
   * The user restriction ID.
   */
  public $urid;

  /**
   * The status of the user restriction.
   */
  public $status = '';

  /**
   * The user restriction type.
   */
  public $type = '';

  /**
   * The user restriction mask.
   */
  public $mask = '';

  // This can go when we upgrade to a later core. @TODO
  public function id() {
    return $this->get('urid')->value;
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

    $fields['mask'] = FieldDefinition::create('string')
      ->setLabel(t('Mask'))
      ->setDescription(t('Text mask used for filtering restrictions.'))
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

//    $fields['uid'] = FieldDefinition::create('entity_reference')
//      ->setLabel(t('Author'))
//      ->setDescription(t('The user that is the node author.'))
//      ->setRevisionable(TRUE)
//      ->setSettings(array(
//        'target_type' => 'user',
//        'default_value' => 0,
//      ))
//      ->setTranslatable(TRUE);

    $fields['status'] = FieldDefinition::create('boolean')
      ->setLabel(t('Restriction status'))
      ->setDescription(t('A boolean indicating whether the restriction is enabled.'))
      ->setTranslatable(TRUE);

    return $fields;
  }


//  /**
//   * The user restriction sub-type.
//   */
//  public $subtype = '';
//
//  /**
//   * The user restriction status.
//   */
//  public $status = 0;
//
//  /**
//   * When the user restriction expires, or 0 if it doesn't expire.
//   */
//  public $expire = 0;
//
//  /**
//   * Checks the user input against user restrictions.
//   *
//   * This was handled by drupal_is_denied() in version of Drupal prior to 7.x.
//   *
//   * @param $form_state
//   *   The array passed to form API functions.
//   * @param $form_type
//   *   The form for which the function is invoked.
//   *
//   * @return
//   *   A message error if there are restrictions agains the entered value, or
//   *   an empty string if there are no restrictions that do not allow to use
//   *   the entered value.
//   */
//  public static function check($form_state, $form_type = 'login') {
//    $result = &drupal_static('user_restrictions_check', array());
//    if (isset($result[$form_type])) {
//      return $result[$form_type];
//    }
//
//    $fields = module_invoke_all('user_restrictions_info', 'fields', $form_state, $form_type);
//    foreach ($fields as $type => $mask) {
//      $args = array(
//        ':type' => $type,
//        ':subtype' => $form_type,
//        ':mask' => $mask,
//        ':now' => REQUEST_TIME,
//      );
//      $context = array(
//        'type' => $type,
//        'mask' => $mask,
//        'time' => REQUEST_TIME,
//        'form_state' => $form_state,
//        'form_type' => $form_type,
//      );
//      $sql = "SELECT 1 FROM {user_restrictions} WHERE type = :type AND (subtype = :subtype OR subtype = '') AND LOWER(:mask) LIKE LOWER(mask) AND status = :status AND (expire > :now OR expire = 0)";
//
//      // We deny access if the only matching records in the {user_restrictions}
//      // table have status 0 (deny). If any have status 1 (allow), or if there are
//      // no matching records, we allow access.
//      $denied = (
//        db_query_range($sql, 0, 1, $args + array(':status' => 0))->fetchField() &&
//        !db_query_range($sql, 0, 1, $args + array(':status' => 1))->fetchField()
//      );
//      $error = array(
//        'field' => '',
//        'message' => '',
//      );
//
//      // Allow third-party modules to alter the user restriction rule.
//      drupal_alter('user_restrictions', $denied, $error, $context);
//
//      if ($denied) {
//        $result[$form_type] = $error;
//        // If the rules module is enabled, an event is triggered signifying
//        // that a user has been restricted access.
//        if (module_exists('rules')) {
//          rules_invoke_event('user_restrictions_denied', $type, $mask, $form_type);
//        }
//        break;
//      }
//    }
//
//    return $error;
//  }
//
//  public static function exists($array) {
//    $query = db_select('user_restrictions', 'ur')
//    ->fields('ur', array('urid'))
//    ->where('type = :type AND LOWER(:mask) = LOWER(mask)', array(':type' => $array['type'], ':mask' => $array['mask']));
//
//    if (isset($array['subtype'])) {
//      $query->condition('subtype', $array['subtype']);
//    }
//
//    return (boolean) $query->execute()->fetchField();
//  }
//
//  public static function getInstanceByMask($type, $mask) {
//    $result = db_query('SELECT urid FROM {user_restrictions} WHERE type = :type AND mask = :mask', array(':type' => $type, 'mask' => $mask));
//
//    if ($result) {
//      $record = $result->fetchColumn();
//      $instance = self::getInstance($record);
//    }
//    else {
//      $instance = new UserRestrictions();
//    }
//
//    return empty($instance) ? FALSE : $instance;
//  }
//
//  function delete() {
//    db_delete('user_restrictions')
//    ->condition('urid', $this->urid)
//    ->execute();
//  }
//
//  public static function deleteExpired() {
//    return db_delete('user_restrictions')
//    ->condition(db_and()
//      ->condition('expire', REQUEST_TIME, '<=')
//      ->condition('expire', 0, '>'))
//    ->execute();
//  }
//
//  public static function getInstance($id = '') {
//    if ($id) {
//      $instance = db_query('SELECT * FROM {user_restrictions} WHERE urid = :urid', array(':urid' => $id), array('fetch' => 'UserRestrictions'))
//      ->fetch();
//    }
//    else {
//      $instance = new UserRestrictions();
//    }
//
//    return empty($instance) ? FALSE : $instance;
//  }
//
//  public static function getRestrictionTable($header, $destination) {
//    $access_types = self::getTypeOptions();
//    $access_statuses = self::getStatusOptions();
//    $rules = db_select('user_restrictions', 'ur')
//    ->fields('ur', array('urid', 'type', 'status', 'mask', 'expire'))
//    ->condition(db_or()
//      ->condition('expire', REQUEST_TIME, '>=')
//      ->condition('expire', 0))
//    ->extend('PagerDefault')
//    ->limit(50)
//    ->extend('TableSort')
//    ->orderByHeader($header)
//    ->execute();
//
//    $rows = array();
//    foreach ($rules as $rule) {
//      $rows[$rule->urid]['status'] = $access_statuses[$rule->status];
//      $rows[$rule->urid]['type'] = $access_types[$rule->type];
//      $rows[$rule->urid]['mask'] = check_plain($rule->mask);
//      $rows[$rule->urid]['expire'] = $rule->expire ? format_date($rule->expire) : t('never');
//      $rows[$rule->urid]['operations'] = array(
//        'data' => array(
//          '#theme' => 'links__user_restrictions_ui_rule_operations',
//          '#links' => array(
//            'edit' => array(
//              'title' => t('edit'),
//              'href' => 'admin/config/people/user-restrictions/' . $rule->urid . '/edit',
//              'query' => $destination,
//              '#attributes' => array('class' => array('links', 'inline')),
//            ),
//            'delete' => array(
//              'title' => t('delete'),
//              'href' => 'admin/config/people/user-restrictions/' . $rule->urid . '/delete',
//              'query' => $destination,
//              '#attributes' => array('class' => array('links', 'inline')),
//            ),
//          ),
//        ),
//      );
//    }
//
//    return $rows;
//  }
//
//  public static function getStatusOptions() {
//    $context = 'status options';
//    $info = module_invoke_all('user_restrictions_info', $context);
//    drupal_alter('user_restrictions_info', $info, $context);
//
//    return $info;
//  }
//
//  public static function getTypeOptions() {
//    $context = 'type options';
//    $info = module_invoke_all('user_restrictions_info', $context);
//    drupal_alter('user_restrictions_info', $info, $context);
//
//    return $info;
//  }
//
//  function save() {
//    $primary_keys = array();
//
//    if (!empty($this->urid)) {
//      $primary_keys[] = 'urid';
//    }
//
//    return drupal_write_record('user_restrictions', clone $this, $primary_keys);
//  }
}
