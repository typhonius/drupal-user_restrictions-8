<?php
// $Id$

/**
 * @file
 * Hooks provided by the OpenSearch feed module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allows other modules to change the default settings used by the module.
 *
 * @param $defaults
 *   The array containing the default module settings.
 */
function hook_opensearch_default_settings_alter(&$defaults) {
}

/**
 * Allows other modules to change the OpenSearch RSS item.
 *
 * @param $denied
 *   The value of the restriction; TRUE when the restriction is enabled.
 * @param $error
 *   The message to return to the user when the restriction is enabled.
 * @param $context
 *   An array containing more information about the restriction being checked.
 */
function hook_user_restrictions_alter(&$denied, &$error, &$context) {
}

/**
 * Allows modules to give information about the implemented restrictions.
 *
 * @param $context
 *   An array containing the ID of the required information.
 */
function hook_user_restrictions_info($context) {
}

/**
 * Allows to alter the restriction information returned by other modules.
 *
 * @param $info
 *   An array containing information about the restrictions implemented by
 *   modules.
 * @param $context
 *   An array containing the ID of the required information.
 */
function hook_user_restrictions_info_alter(&$info, &$context) {
}

/**
 * @} End of "addtogroup hooks".
 */

