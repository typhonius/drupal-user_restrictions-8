<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\Form\UserRestrictionsDeleteForm
 */

namespace Drupal\user_restrictions\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;


class UserRestrictionsDeleteForm extends ContentEntityConfirmFormBase {

  function getQuestion() {
    $restriction = $this->entity;
      return t('Are you sure you want to delete @type restriction @mask?', array('@type' => $restriction->getType(), '@mask' => $restriction->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'user_restrictions.admin_list',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $restriction = $this->entity;
    $restriction->delete();

    watchdog('user_restrictions', '@type: deleted %title.', array('@type' => $restriction->getType(), '%title' => $restriction->label()));
    drupal_set_message(t('@type %title has been deleted.', array('@type' => $restriction->getType(), '%title' => $restriction->label())));

    $form_state['redirect_route']['route_name'] = 'user_restrictions.admin_list';
  }

}