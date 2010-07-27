<?php
// $Id$

/**
 * @file
 * Administration pages for the user restrictions module.
 */

define('USER_RESTRICTIONS_EMAIL', 0);
define('USER_RESTRICTIONS_USERNAME', 1);

/**
 * Form builder for user restriction delete form.
 *
 * @see user_restrictions_delete_rule_form_submit()
 * @ingroup forms
 */
function user_restrictions_delete_rule_form($form_state, $rule) {
  $access_types = array(
    USER_RESTRICTIONS_USERNAME => t('username'),
    USER_RESTRICTIONS_EMAIL => t('e-mail'),
  );
  $form = array('#urid' => $rule->urid);

  $output = confirm_form($form,
    t(
      'Are you sure you want to delete the @type rule for %mask?',
      array('@type' => $access_types[$rule->type], '%mask' => $rule->mask)
    ),
    'admin/config/people/restrictions',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );

  return $output;
}

/**
 * Form submission handler for user_restrictions_delete_rule_form().
 */
function user_restrictions_delete_rule_form_submit($form, &$form_state) {
  db_delete('user_restrictions')
    ->condition('urid', $form['#urid'])
    ->execute();

  drupal_set_message(t('The access rule has been deleted.'));
  $form_state['redirect'] = 'admin/config/people/restrictions';
}

/**
 * Form generator for the user restriction edit form.
 *
 * @see user_restrictions_edit_rule_form_validate()
 * @see user_restrictions_edit_rule_form_submit()
 * @ingroup forms
 */
function user_restrictions_edit_rule_form($form, &$form_state, $rule = NULL) {
  $type_options = array(
    USER_RESTRICTIONS_USERNAME => t('Username'),
    USER_RESTRICTIONS_EMAIL => t('E-mail'));

  if (!isset($rule)) {
    $rule = (object) array(
      'status' => 0,
      'type' => USER_RESTRICTIONS_USERNAME,
      'mask' => '',
    );
  }
  else {
    $form['#urid'] = $rule->urid;
  }

  $form['status'] = array(
    '#type' => 'radios',
    '#title' => t('Access type'),
    '#default_value' => $rule->status,
    '#options' => array('1' => t('Allow'), '0' => t('Deny')),
  );

  $form['type'] = array(
    '#type' => 'radios',
    '#title' => t('Rule type'),
    '#default_value' => $rule->type,
    '#options' => $type_options,
  );

  $form['mask'] = array(
    '#type' => 'textfield',
    '#title' => t('Mask'),
    '#size' => 30,
    '#maxlength' => 64,
    '#default_value' => $rule->mask,
    '#description' => t('%ampersand matches any number of characters, even zero characters; %underscore matches exactly one character.', array('%ampersand' => '%', '%underscore' => '_')),
    '#required' => TRUE,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save rule'),
  );

  return $form;
}

/**
 * Form validation handler for user_restrictions_edit_rule_form().
 */
function user_restrictions_edit_rule_form_validate($form, &$form_state) {
  if (!empty($form_state['values']['mask'])) {
    if (!trim($form_state['values']['mask'])) {
      form_set_error('mask', t('The mask value contains only spaces, or other not printable characters'));
      return;
    }

    // Verify if there are other rules of the same type with the same mask.
    if (!isset($form['#urid']) && isset($form_state['values']['type'])) {
      db_query_range(
        'SELECT 1 FROM {user_restrictions} WHERE type = :type AND LOWER(:mask) = LOWER(mask)',
        array()
      );
    }
  }
}

/**
 * Form submission handler for user_restrictions_edit_rule_form().
 */
function user_restrictions_edit_rule_form_submit($form, &$form_state) {
  $primary_keys = array();
  $record = (object) $form_state['values'];

  if (!empty($form['#urid'])) {
    $record->urid = $form['#urid'];
    $primary_keys[] = 'urid';
  }

  drupal_write_record('user_restrictions', $record, $primary_keys);
}

/**
 * Form builder for the list of user restrictions.
 *
 * @see user_restrictions_check_email_validate()
 * @see user_restrictions_check_email_submit()
 * @see user_restrictions_check_username_submit()
 * @ingroup forms
 */
function user_restrictions_overview_form($form, &$form_state) {
  $access_types = array(
    USER_RESTRICTIONS_USERNAME => t('username'),
    USER_RESTRICTIONS_EMAIL => t('e-mail'),
  );
  $destination = drupal_get_destination();
  $header = array(
    'status' => array('data' => t('Access type'), 'field' => 'status', 'sort' => 'desc'),
    'type' => array('data' => t('Rule type'), 'field' => 'type'),
    'mask' => array('data' => t('Mask'), 'field' => 'mask'),
    'operations' => array('data' => t('Operations'), 'colspan' => 2)
  );
  $rows = array();

  $rules = db_select('user_restrictions', 'ur')
    ->fields('ur', array('urid', 'type', 'status', 'mask'))
    ->extend('PagerDefault')
    ->limit(50)
    ->extend('TableSort')
    ->orderByHeader($header)
    ->execute();

  foreach ($rules as $rule) {
    $rows[$rule->urid]['status'] = $rule->status ? t('allow') : t('deny');
    $rows[$rule->urid]['type'] = $access_types[$rule->type];
    $rows[$rule->urid]['mask'] = check_plain($rule->mask);
    $rows[$rule->urid]['operations'] = array(
      'data' => array(
        '#theme' => 'links__user_restrictions_rule_operations',
        '#links' => array(
          'edit' => array(
            'title' => t('edit'),
            'href' => 'admin/config/people/restrictions/' . $rule->urid . '/edit',
            'query' => $destination,
            '#attributes' => array('class' => array('links', 'inline')),
          ),
          'delete' => array(
            'title' => t('delete'),
            'href' => 'admin/config/people/restrictions/' . $rule->urid . '/delete',
            'query' => $destination,
            '#attributes' => array('class' => array('links', 'inline')),
          ),
        ),
      ),
    );
  }

  $form['rules'] = array('#theme' => 'table', '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('There are currently no user restrictions.'),
  );

  // Show the fieldset only if there are restriction rules.
  if (count($rows)) {
    $form['check_rules'] = array(
      '#type' => 'fieldset',
      '#title' => t('Check rules'),
      '#attributes' => array('class' => array('container-inline')),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['check_rules']['value'] = array(
      '#type' => 'textfield',
      '#size' => 30,
      '#maxlength' => USERNAME_MAX_LENGTH,
    );

    $form['check_rules']['check_username'] = array(
      '#type' => 'submit',
      '#value' => t('Check username'),
      '#submit' => array('user_restrictions_check_username_submit'),
    );

    $form['check_rules']['check_email'] = array(
      '#type' => 'submit',
      '#value' => t('Check e-mail'),
      '#submit' => array('user_restrictions_check_email_submit'),
      '#validate' => array('user_restrictions_check_email_validate'),
    );
  }

  $form['pager'] = array('#markup' => theme('pager', array('tags' => NULL)));

  return $form;
}

/**
 * Form validation handler for user_restrictions_overview_form().
 *
 * @see user_restrictions_overview_form()
 * @see user_restrictions_check_email_submit()
 */
function user_restrictions_check_email_validate($form, &$form_state) {
  $bool = (
    !empty($form_state['values']['value']) &&
    !valid_email_address($form_state['values']['value'])
  );
  if ($bool) {
    form_set_error(
      'value',
      t('The entered value is not a valid email address.')
    );
  }
}

/**
 * Form submission handler for user_restrictions_overview_form().
 *
 * @see user_restrictions_overview_form()
 * @see user_restrictions_check_email_validate()
 */
function user_restrictions_check_email_submit($form, &$form_state) {
  if (!empty($form_state['values']['value'])) {
    $value = $form_state['values']['value'];
    if (user_restrictions_user_check(USER_RESTRICTIONS_EMAIL, $value)) {
      drupal_set_message(
        t('The e-mail address %mail is not allowed.', array('%mail' => $value))
      );
    }
    else {
      drupal_set_message(
        t('The e-mail address %mail is allowed.', array('%mail' => $value))
      );
    }
  }
}

/**
 * Form submission handler for user_restrictions_overview_form().
 *
 * @see user_restrictions_overview_form()
 */
function user_restrictions_check_username_submit($form, &$form_state) {
  if (!empty($form_state['values']['value'])) {
    $value = $form_state['values']['value'];
    if (user_restrictions_user_check(USER_RESTRICTIONS_USERNAME, $value)) {
      drupal_set_message(
        t('The username %name is not allowed.', array('%name' => $value))
      );
    }
    else {
      drupal_set_message(
        t('The username %name is allowed.', array('%name' => $value))
      );
    }
  }
}
