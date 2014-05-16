<?php

namespace Drupal\user_restrictions;

interface UserRestrictionsManagerInterface {

  public function isRestricted($type, $mask);

}