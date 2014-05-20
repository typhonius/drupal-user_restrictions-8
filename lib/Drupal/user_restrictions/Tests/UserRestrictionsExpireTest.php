<?php

/**
 * @file
 * Definition of Drupal\user_restrictions\Tests\UserRestrictionsExpireTest.
 */

namespace Drupal\user_restrictions\Tests;


class UserRestrictionsExpireTest extends UserRestrictionsTestBase {

  protected $id;

  protected $name;

  protected $mail;

  public static function getInfo() {
    return array(
      'name' => 'User Restrictions Expire test',
      'description' => 'Tests expired user restrictions rules do not take effect and are deleted on cron.',
      'group' => 'User Restrictions',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a restriction with an expiration date in the past.
    $this->name = $this->randomName();

    $restriction = entity_create('user_restrictions', array(
      'mask' => $this->name,
      'type' => $this->mail,
      'status' => 1,
      'expire' => 1000,
    ));
    $restriction->save();

    $this->id = $restriction->id();
  }

  /**
   * Ensure the restriction exists in the database
   */
  protected function testUserRestrictionsRecordExists() {
    $restriction = user_restrictions_load($this->id, TRUE);
    $this->assertTrue($restriction, 'User Restriction exists in the database');
    $this->assertEqual($restriction->label(), $this->name, 'User Restriction name matches');
  }

  /**
   * Ensure an expired user may now log in.
   */
  protected function testUserRestrictionsExpiredLogin() {
    $account = $this->drupalCreateUser(array(), $this->name);
    $this->drupalLogin($account);
  }

  /**
   * Ensure an expired restriction gets deleted on cron
   */
  protected function testUserRestrictionsExpiredCron() {
    \Drupal::service('cron')->run();
    $this->assertFalse(user_restrictions_load($this->id, TRUE), 'User Restriction was removed from the database.');
  }

}
