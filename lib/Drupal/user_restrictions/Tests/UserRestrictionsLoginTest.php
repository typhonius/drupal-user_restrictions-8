<?php

/**
 * @file
 * Definition of Drupal\user_restrictions\Tests\UserRestrictionsLoginTest.
 */

namespace Drupal\user_restrictions\Tests;


class UserRestrictionsLoginTest extends UserRestrictionsTestBase {

  public static function getInfo() {
    return array(
      'name' => 'User Restrictions Login test',
      'description' => 'Test the user restrictions rules for approved and denied names and emails.',
      'group' => 'User Restrictions',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create some default User Restrictions
    $this->createRestrictions();

    // Allow registration by site visitors without administrator approval.
    $config = \Drupal::config('user.settings');
    $config->set('register', USER_REGISTER_VISITORS)->save();
  }

  /**
   * Create a user through the administration interface and ensure that it
   * displays in the user list.
   */
  protected function testUserRestrictionsCheckNameBlacklist() {
    $this->drupalGet('user/register');

    $name = 'lol' . $this->randomName();
    $edit = array();
    $edit['name'] = $name;
    $edit['mail'] = $this->randomName() . '@example.com';
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('The name @name is not allowed', array('@name' => $name)), 'User "name" restricted.');
  }

  /**
   * Create a user through the administration interface and ensure that it
   * displays in the user list.
   */
  protected function testUserRestrictionsCheckNameWhitelist() {
    $this->drupalGet('user/register');

    $name = 'lolcats';
    $edit = array();
    $edit['name'] = $name;
    $edit['mail'] = $this->randomName() . '@example.com';
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('A welcome message with further instructions has been sent to your e-mail address.'), 'User registered successfully.');
  }

  /**
   * Create a user through the administration interface and ensure that it
   * displays in the user list.
   */
  protected function testUserRestrictionsCheckMailBlacklist() {
    $this->drupalGet('user/register');

    $email = $this->randomName() . '@' . $this->randomName() . '.ru';
    $edit = array();
    $edit['name'] = $this->randomName();
    $edit['mail'] = $email;
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('The mail @email is not allowed', array('@email' => $email)), 'User "email" restricted.');
  }

  /**
   * Create a user through the administration interface and ensure that it
   * displays in the user list.
   */
  protected function testUserRestrictionsCheckMailWhitelist() {
    $this->drupalGet('user/register');

    $email = 'typhonius@mail.ru';
    $edit = array();
    $edit['name'] = $this->randomName();
    $edit['mail'] = $email;
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('A welcome message with further instructions has been sent to your e-mail address.'), 'User registered successfully.');
  }

}
