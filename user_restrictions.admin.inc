<?php
// $Id$

/**
 * @file
 * Admin page callback file for the user restrictions module.
 */

/**
 * Menu callback: list all access rules.
 */
function user_restrictions_check() {
  $output = drupal_get_form('user_restrictions_check_user');
  $output .= drupal_get_form('user_restrictions_check_mail');
  return $output;
}

/**
 * Menu callback: add an access rule.
 */
function user_restrictions_add($mask = NULL, $type = NULL) {
  if ($edit = $_POST) {
    if (!$edit['mask']) {
      form_set_error('mask', t('You must enter a mask.'));
    }
    else {
      db_query("INSERT INTO {user_restrictions} (mask, type, status) VALUES ('%s', '%s', %d)", $edit['mask'], $edit['type'], $edit['status']);
      $urid = db_last_insert_id('user_restrictions', 'urid');
      drupal_set_message(t('The restriction has been added.'));
      drupal_goto('admin/user/restrictions');
    }
  }
  else {
    $edit['mask'] = $mask;
    $edit['type'] = $type;
  }
  return drupal_get_form('user_restrictions_add_form', $edit, t('Add rule'));
}

/**
 * Menu callback: edit an access rule.
 */
function user_restrictions_edit($urid = 0) {
  if ($edit = $_POST) {
    if (!$edit['mask']) {
      form_set_error('mask', t('You must enter a mask.'));
    }
    else {
      db_query("UPDATE {user_restrictions} SET mask = '%s', type = '%s', status = '%s' WHERE urid = %d", $edit['mask'], $edit['type'], $edit['status'], $urid);
      drupal_set_message(t('The restriction has been saved.'));
      drupal_goto('admin/user/restrictions');
    }
  }
  else {
    $edit = db_fetch_array(db_query('SELECT urid, type, status, mask FROM {user_restrictions} WHERE urid = %d', $urid));
  }
  return drupal_get_form('user_restrictions_edit_form', $edit, t('Save rule'));
}

/**
 * Form builder; Configure access rules.
 *
 * @ingroup forms
 */
function user_restrictions_form(&$form_state, $edit, $submit) {
  $form['status'] = array(
    '#type' => 'radios',
    '#title' => t('Access type'),
    '#default_value' => isset($edit['status']) ? $edit['status'] : 0,
    '#options' => array('1' => t('Allow'), '0' => t('Deny')),
  );
  $type_options = array('user' => t('Username'), 'mail' => t('E-mail'));
  $form['type'] = array(
    '#type' => 'radios',
    '#title' => t('Rule type'),
    '#default_value' => (isset($type_options[$edit['type']]) ? $edit['type'] : 'user'),
    '#options' => $type_options,
  );
  $form['mask'] = array(
    '#type' => 'textfield',
    '#title' => t('Mask'),
    '#size' => 30,
    '#maxlength' => 64,
    '#default_value' => $edit['mask'],
    '#description' => '%: '. t('Matches any number of characters, even zero characters') .'.<br />_: '. t('Matches exactly one character.'),
    '#required' => TRUE,
  );
  $form['submit'] = array('#type' => 'submit', '#value' => $submit);

  return $form;
}

function user_restrictions_check_validate($form, &$form_state) {
  if (empty($form_state['values']['test'])) {
    form_set_error($form_state['values']['type'], t('No value entered. Please enter a test string and try again.'));
  }
}

function user_restrictions_check_user() {
  $form['user'] = array('#type' => 'fieldset', '#title' => t('Username'));
  $form['user']['test'] = array('#type' => 'textfield', '#title' => '', '#description' => t('Enter a username to check if it will be denied or allowed.'), '#size' => 30, '#maxlength' => USERNAME_MAX_LENGTH);
  $form['user']['type'] = array('#type' => 'hidden', '#value' => 'user');
  $form['user']['submit'] = array('#type' => 'submit', '#value' => t('Check username'));
  $form['#submit'][] = 'user_restrictions_check_submit';
  $form['#validate'][] = 'user_restrictions_check_validate';
  $form['#theme'] = 'user_restrictions_check';
  return $form;
}

function user_restrictions_check_mail() {
  $form['mail'] = array('#type' => 'fieldset', '#title' => t('E-mail'));
  $form['mail']['test'] = array('#type' => 'textfield', '#title' => '', '#description' => t('Enter an e-mail address to check if it will be denied or allowed.'), '#size' => 30, '#maxlength' => EMAIL_MAX_LENGTH);
  $form['mail']['type'] = array('#type' => 'hidden', '#value' => 'mail');
  $form['mail']['submit'] = array('#type' => 'submit', '#value' => t('Check e-mail'));
  $form['#submit'][] = 'user_restrictions_check_submit';
  $form['#validate'][] = 'user_restrictions_check_validate';
  $form['#theme'] = 'user_restrictions_check';
  return $form;
}

function user_restrictions_check_submit($form, &$form_state) {
  switch ($form_state['values']['type']) {
    case 'user':
      if (user_restrictions_is_denied('user', $form_state['values']['test'])) {
        drupal_set_message(t('The username %name is not allowed.', array('%name' => $form_state['values']['test'])));
      }
      else {
        drupal_set_message(t('The username %name is allowed.', array('%name' => $form_state['values']['test'])));
      }
      break;
    case 'mail':
      if (user_restrictions_is_denied('mail', $form_state['values']['test'])) {
        drupal_set_message(t('The e-mail address %mail is not allowed.', array('%mail' => $form_state['values']['test'])));
      }
      else {
        drupal_set_message(t('The e-mail address %mail is allowed.', array('%mail' => $form_state['values']['test'])));
      }
      break;
    default:
      break;
  }
}

/**
 * Menu callback: delete a user restriction.
 *
 * @ingroup forms
 * @see user_restrictions_delete_confirm_submit()
 */
function user_restrictions_delete_confirm($form_state, $urid = 0) {
  $access_types = array('user' => t('username'), 'mail' => t('e-mail'));
  $edit = db_fetch_object(db_query('SELECT urid, type, status, mask FROM {user_restrictions} WHERE urid = %d', $urid));

  $form = array();
  $form['urid'] = array('#type' => 'hidden', '#value' => $urid);
  $output = confirm_form($form,
                  t('Are you sure you want to delete the @type rule for %rule?', array('@type' => $access_types[$edit->type], '%rule' => $edit->mask)),
                  'admin/user/restrictions',
                  t('This action cannot be undone.'),
                  t('Delete'),
                  t('Cancel'));
  return $output;
}

function user_restrictions_delete_confirm_submit($form, &$form_state) {
  db_query('DELETE FROM {user_restrictions} WHERE urid = %d', $form_state['values']['urid']);
  drupal_set_message(t('The access rule has been deleted.'));
  $form_state['redirect'] = 'admin/user/restrictions';
  return;
}

/**
 * Menu callback: list all access rules.
 */
function user_restrictions_list() {
  $header = array(array('data' => t('Access type'), 'field' => 'status'), array('data' => t('Rule type'), 'field' => 'type'), array('data' => t('Mask'), 'field' => 'mask'), array('data' => t('Operations'), 'colspan' => 2));
  $result = db_query("SELECT urid, type, status, mask FROM {user_restrictions}". tablesort_sql($header));
  $access_types = array('user' => t('username'), 'mail' => t('e-mail'));
  $rows = array();
  while ($rule = db_fetch_object($result)) {
    $rows[] = array($rule->status ? t('allow') : t('deny'), $access_types[$rule->type], $rule->mask, l(t('edit'), 'admin/user/restrictions/edit/'. $rule->urid), l(t('delete'), 'admin/user/restrictions/delete/'. $rule->urid));
  }
  if (empty($rows)) {
    $rows[] = array(array('data' => '<em>'. t('There are currently no user restrictions.') .'</em>', 'colspan' => 5));
  }
  return theme('table', $header, $rows);
}
