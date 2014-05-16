<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\UserRestrictionsManagerInterface
 */

namespace Drupal\user_restrictions;

interface UserRestrictionsManagerInterface {

  public function isRestricted($type, $mask);

}