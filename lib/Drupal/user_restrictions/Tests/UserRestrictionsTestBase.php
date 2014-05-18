<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\Tests\UserRestrictionsTestBase.
 */

namespace Drupal\user_restrictions\Tests;

use Drupal\simpletest\WebTestBase;

class UserRestrictionsTestBase extends WebTestBase{

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('user_restrictions');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Create some user restrictions
   */
  protected function createRestrictions() {

    // Block any user with a name starting 'lol'
    $restrictions[] = array(
      'mask' => 'lol%',
      'type' => 'name',
      'status' => 0,
    );

    // Allow the user named 'lolcats'
    $restrictions[] = array(
      'mask' => 'lolcats',
      'type' => 'name',
      'status' => 1,
    );

    // Block any user with a .ru email address
    $restrictions[] = array(
      'mask' => '%@%.ru',
      'type' => 'mail',
      'status' => 0,
    );

    // Specically allow typhonius@mail.ru
    $restrictions[] = array(
      'mask' => 'typhonius@mail.ru',
      'type' => 'mail',
      'status' => 1,
    );

    foreach ($restrictions as $restriction) {
      $entity = entity_create('user_restrictions', $restriction);
      $entity->save();
    }
  }

}
