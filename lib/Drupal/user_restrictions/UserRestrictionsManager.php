<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\UserRestrictionsManager
 */

namespace Drupal\user_restrictions;

class UserRestrictionsManager implements UserRestrictionsManagerInterface {

  protected $form;
  protected $form_state;

  public function __construct($form, $form_state) {
    $this->form = $form;
    $this->form_state = $form_state;
  }

  public function isRestricted($type, $mask) {
    $args = array(
      ':type' => $type,
      ':mask' => $mask,
      ':now'  => REQUEST_TIME,
    );
    $sql = "SELECT 1 FROM {user_restrictions} WHERE type = :type AND LOWER(:mask) LIKE LOWER(mask) AND status = :status AND (expire > :now OR expire = 0)";
    $denied = (
      db_query_range($sql, 0, 1, $args + array(':status' => 0))->fetchField() &&
      !db_query_range($sql, 0, 1, $args + array(':status' => 1))->fetchField()
    );
    if ($denied) {
      $this->setErrors($type, $mask);
    }
  }


  public function setErrors($name, $mask) {
    \Drupal::formBuilder()->setErrorByName($name, $this->form_state, t('The :type :mask is not allowed', array(':type' => $name, ':mask' => $mask)));
  }

  public static function garbageCollection() {
    return db_delete('user_restrictions')
    ->condition('expire', 0, '<>')
    ->condition('expire', REQUEST_TIME, '<')
    ->execute();
  }

}