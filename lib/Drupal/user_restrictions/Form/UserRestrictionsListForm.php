<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\Form\UserRestrictionsListForm.
 */

namespace Drupal\user_restrictions\Form;

use Drupal\Core\Form\FormBase;



/**
 * Provides a simple example form.
 */
class UserRestrictionsListForm extends FormBase {

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'user_restrictions_list_form';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
//    $form['user_restrictions_help'] = array(
//      '#type' => 'markup',
//      '#markup' => t("Set up rules for allowable usernames and e-mail address. A rule may either explicitly allow access or deny access based on the rule's Access type, Rule type, and Mask. If the username or e-mail address of an existing account or new registration matches a deny rule, but not an allow rule, then the account will not be created (for new registrations) or able to log in (for existing accounts)."),
//    );

    // Use the Form API to define form elements.
    //return parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, array &$form_state) {
    //return parent::validateForm($form, $form_state);
    // Validate the form values.
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    //parent::submitForm($form, $form_state);
    //ubmitForm($form, $form_state);
    // Do something useful.
  }

}
