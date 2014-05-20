<?php

/**
 * @file
 * Definition of Drupal\user_restrictions\Tests\UserRestrictionsBasicTest.
 */

namespace Drupal\user_restrictions\Tests;


class UserRestrictionsBasicTest extends UserRestrictionsTestBase {

  protected $id;

  protected $name;

  protected $type;

  public static function getInfo() {
    return array(
      'name' => 'User Restrictions Basic test',
      'description' => 'Tests creation and loading of restrictions',
      'group' => 'User Restrictions',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->name = $this->randomName();
    $this->type = 'name';

    $restriction = entity_create('user_restrictions', array(
      'mask' => $this->name,
      'type' => $this->type,
      'status' => 1,
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
    $this->assertEqual($restriction->getType(), $this->type, 'User Restriction type matches');
  }

}
