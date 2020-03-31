<?php

namespace Drupal\openy_memberships\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Health Insurance condition for orders.
 *
 * @CommerceCondition(
 *   id = "openy_memberships_military_service",
 *   label = @Translation("Military Service"),
 *   category = @Translation("OpenY"),
 *   entity_type = "commerce_order",
 *   weight = -1,
 * )
 */
class OpenyMembershipsMilitaryService extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'military_service' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValues();
    $this->configuration['military_service'] = $values['conditions']['form']['openy']['openy_memberships_military_service']['enable'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $isMilitaryServiceChecked = FALSE;
    // Check in order if at least one member has checked.
    if ($order->hasField('field_family')) {
      $profiles = $order->field_family->referencedEntities();
      foreach ($profiles as $profile) {
        if ($profile->hasField('field_om_military_service')) {
          $isMilitaryServiceChecked = $profile->field_om_military_service->value;
          if ($isMilitaryServiceChecked){
            return $isMilitaryServiceChecked;
          }
        }
      }
    }
    return $isMilitaryServiceChecked;
  }

}
