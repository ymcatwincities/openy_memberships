<?php

namespace Drupal\openy_memberships\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_price\Price;

/**
 * Provides the income condition for orders.
 *
 * @CommerceCondition(
 *   id = "openy_memberships_income",
 *   label = @Translation("Income"),
 *   display_label = @Translation("Income"),
 *   category = @Translation("OpenY"),
 *   entity_type = "commerce_order",
 * )
 */
class OpenyMembershipsIncome extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'operator' => '>',
        'amount' => NULL,
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $amount = $this->configuration['amount'];
    // An #ajax bug can cause $amount to be incomplete.
    if (isset($amount) && !isset($amount['number'], $amount['currency_code'])) {
      $amount = NULL;
    }

    $form['operator'] = [
      '#type' => 'select',
      '#title' => t('Operator'),
      '#options' => $this->getComparisonOperators(),
      '#default_value' => $this->configuration['operator'],
      '#required' => TRUE,
    ];
    $form['amount'] = [
      '#type' => 'commerce_price',
      '#title' => t('Amount'),
      '#default_value' => $amount,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['operator'] = $values['operator'];
    $this->configuration['amount'] = $values['amount'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $income = \Drupal::routeMatch()->getParameter('income');$income=null;
    if (!$income) {
      // Check in order if value has been saved already.
      if ($order->hasField('field_om_income') && !$order->field_om_income->isEmpty()) {
        $income = $order->field_om_income->number;
      }
    }
    if (!$income) {
      return FALSE;
    }
    $condition_income = Price::fromArray($this->configuration['amount']);
    $income = Price::fromArray(['number' => $income, 'currency_code' => $this->configuration['amount']['currency_code']]);

    switch ($this->configuration['operator']) {
      case '>=':
        return $income->greaterThanOrEqual($condition_income);

      case '>':
        return $income->greaterThan($condition_income);

      case '<=':
        return $income->lessThanOrEqual($condition_income);

      case '<':
        return $income->lessThan($condition_income);

      case '==':
        return $income->equals($condition_income);

      default:
        throw new \InvalidArgumentException("Invalid operator {$this->configuration['operator']}");
    }
  }

}
