<?php

/**
 * @file
 * Contains \Drupal\user_restrictions\UserRestrictionsListBuilder.
 */

namespace Drupal\user_restrictions;

use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;

class UserRestrictionsListBuilder extends EntityListBuilder {

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'user_restrictions_admin_form';
  }
  /**
   * Constructs a new UserRestrictionsListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Mask');
    $header['type'] = t('Rule Type');
    $header['status'] = t('Access Type');
    $header['expire'] = t('Expires');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['type'] = $entity->getType();
    $row['status'] = $entity->getStatus() ? t('Allowed') : t('Denied');
    $row['expire'] = $entity->getExpireTime() ? format_date($entity->getExpireTime()) : t('Never');
    return $row + parent::buildRow($entity);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
    $form['user_restrictions_help'] = array(
      '#type' => 'markup',
      '#markup' => t("Set up rules for allowable usernames and e-mail address. A rule may either explicitly allow access or deny access based on the rule's Access type, Rule type, and Mask. If the username or e-mail address of an existing account or new registration matches a deny rule, but not an allow rule, then the account will not be created (for new registrations) or able to log in (for existing accounts)."),
    );

    return $form;
  }
}
