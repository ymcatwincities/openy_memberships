<?php

namespace Drupal\openy_memberships\Plugin\Commerce\PromotionOffer;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\OrderPromotionOfferBase;
use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\FixedAmountOffTrait;

/**
 * Provides the fixed amount off offer for orders.
 *
 * The discount is split between order items, to simplify VAT taxes and refunds.
 *
 * @CommercePromotionOffer(
 *   id = "openy_memberships_order_household_off",
 *   label = @Translation("Fixed amount off each household"),
 *   entity_type = "commerce_order",
 * )
 */
class OpenYOrderHouseholdOff extends OrderPromotionOfferBase {

  use FixedAmountOffTrait;

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, PromotionInterface $promotion) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $subtotal_price = $order->getSubTotalPrice();
    $amount = $this->getAmount();
    if ($subtotal_price->getCurrencyCode() != $amount->getCurrencyCode()) {
      return;
    }
    // The promotion amount can't be larger than the subtotal, to avoid
    // potentially having a negative order total.
    if ($amount->greaterThan($subtotal_price)) {
      $amount = $subtotal_price;
    }
    $conditions = $promotion->getConditions();

    $membersHaveHealthInsuranceCount = 0;
    $membersHaveMilitaryServiceCount = 0;
    if ($order->hasField('field_family')) {
      $profiles = $order->field_family->referencedEntities();
      foreach ($profiles as $key => $profile) {
        if (isset($conditions[0]) && $conditions[0]->getPluginId() == 'openy_memberships_health_insurance') {
          if ($profile->hasField('field_om_health_insurance')) {
            if ($profile->field_om_health_insurance->value) {
              $membersHaveHealthInsuranceCount++;
            }
          }
        }
        if (isset($conditions[0]) && $conditions[0]->getPluginId() == 'openy_memberships_military_service') {
          if ($profile->hasField('field_om_military_service')) {
            if ($profile->field_om_military_service->value) {
              $membersHaveMilitaryServiceCount++;
            }
          }
        }
      }
    }

    if ($membersHaveHealthInsuranceCount > 0) {
      $amount = $amount->multiply($membersHaveHealthInsuranceCount);
    }
    if ($membersHaveMilitaryServiceCount > 0) {
      $amount = $amount->multiply($membersHaveMilitaryServiceCount);
    }

    // Split the amount between order items.
    $amounts = $this->splitter->split($order, $amount);

    foreach ($order->getItems() as $order_item) {
      if (isset($amounts[$order_item->id()])) {
        $order_item->addAdjustment(new Adjustment([
          'type' => 'promotion',
          // @todo Change to label from UI when added in #2770731.
          'label' => t('Discount'),
          'amount' => $amounts[$order_item->id()]->multiply('-1'),
          'source_id' => $promotion->id(),
        ]));
      }
    }
  }

}
