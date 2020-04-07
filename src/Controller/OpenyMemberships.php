<?php

namespace Drupal\openy_memberships\Controller;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides OpenyMemberships controller.
 */
class OpenyMemberships extends ControllerBase {

  /**
   * The entityTypeManger.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManger;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * Constructs a new Memberships object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, CartProviderInterface $cart_provider) {
    $this->entityTypeManger = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->cartProvider = $cart_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('commerce_cart.cart_provider')
    );
  }

  /**
   * Check discounts (Income, health insurance, etc).
   */
  public function checkDiscounts(Request $request, $income, $members) {
    $data = [];
    $carts = $this->cartProvider->getCarts();
    if (!empty($carts)) {
      foreach ($carts as $cart_id => $cart) {
        $total_price = $cart->getTotalPrice();
        $income = new Price($income, $total_price->getCurrencyCode());
        $cart->set('field_om_income', $income);
        $members = explode('_', $members);
        if ($members) {
          if ($cart->hasField('field_family')) {
            $profiles = $cart->field_family->referencedEntities();
            if (count($members) !== count($profiles)) {
              throw new \InvalidArgumentException('Members arguments count mismatch');
            }
            foreach ($profiles as $key => $profile) {
              $member = explode(',', $members[$key]);
              if ($profile->hasField('field_om_health_insurance')) {
                $profile->set('field_om_health_insurance', $member[0]);
              }
              if ($profile->hasField('field_om_military_service')) {
                $profile->set('field_om_military_service', $member[1]);
              }
              $profile->save();
            }
          }
        }
        $cart->save();
        $promotions = $this->entityTypeManger->getStorage('commerce_promotion')->loadMultiple();
        foreach ($promotions as $promotion) {
          $conditions = $promotion->get('conditions');
          $condition_values = $conditions->getValue();
          foreach ($condition_values as $condition_value) {
            if ($condition_value['target_plugin_id'] == 'openy_memberships_health_insurance') {
              $data['member_promotions']['health_insurance'] = [
                'amount' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['number'],
                'currency' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['currency_code'],
              ];
            }
            if ($condition_value['target_plugin_id'] == 'openy_memberships_military_service') {
              $data['member_promotions']['military_service'] = [
                'amount' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['number'],
                'currency' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['currency_code'],
              ];
            }
          }
        }
        foreach ($cart->getItems() as $order_item) {
          $adjustments = $order_item->getAdjustments();
          foreach ($adjustments as $adjustment) {
            if ($adjustment->getType() == 'promotion') {
              $promotion_id = $adjustment->getSourceId();
              $promotion = $this->entityTypeManger->getStorage('commerce_promotion')->load($promotion_id);
              $conditions = $promotion->get('conditions');
              $condition_values = $conditions->getValue();
              // Sum and group all the discounts (they are split by order items).
              foreach ($condition_values as $condition_value) {
                if ($condition_value['target_plugin_id'] == 'openy_memberships_income') {
                  $data['discounts']['income']['amount'] += $adjustment->getAmount()->getNumber();
                  $data['discounts']['income']['currency'] = $adjustment->getAmount()->getCurrencyCode();
                }
                if ($condition_value['target_plugin_id'] == 'openy_memberships_health_insurance') {
                  $data['discounts']['health_insurance']['amount'] += $adjustment->getAmount()->getNumber();
                  $data['discounts']['health_insurance']['currency'] = $adjustment->getAmount()->getCurrencyCode();
                }
                if ($condition_value['target_plugin_id'] == 'openy_memberships_military_service') {
                  $data['discounts']['military_service']['amount'] += $adjustment->getAmount()->getNumber();
                  $data['discounts']['military_service']['currency'] = $adjustment->getAmount()->getCurrencyCode();
                }
              }
            }
          }
        }
        // Load family members from order and address discounts.
        if ($cart->hasField('field_family')) {
          $profiles = $cart->field_family->referencedEntities();
          $membersHaveHealthInsuranceCount = 0;
          $membersHaveMilitaryServiceCount = 0;
          foreach ($profiles as $profile) {
            if ($profile->hasField('field_om_health_insurance')) {
              $isHealthInsuranceChecked = $profile->field_om_health_insurance->value;
              if ($isHealthInsuranceChecked) {
                $membersHaveHealthInsuranceCount++;
              }
              $data['members'][$profile->field_first_name->value]['health_insurance'] = $isHealthInsuranceChecked;
            }
            if ($profile->hasField('field_om_military_service')) {
              $isMilitaryServiceChecked = $profile->field_om_military_service->value;
              if ($isMilitaryServiceChecked) {
                $membersHaveMilitaryServiceCount++;
              }
              $data['members'][$profile->field_first_name->value]['military_service'] = $isMilitaryServiceChecked;
            }
          }
          if ($data['discounts']['health_insurance']['amount']) {
            $data['discounts']['health_insurance']['amount'] = $data['discounts']['health_insurance']['amount'] / $membersHaveHealthInsuranceCount;
          }
          if ($data['discounts']['military_service']['amount']) {
            $data['discounts']['military_service']['amount'] = $data['discounts']['military_service']['amount'] / $membersHaveMilitaryServiceCount;
          }
        }
      }
    }

    $data['total_price'] = $cart->getTotalPrice()->getNumber();
    $data['subtotal_price'] = $cart->getSubtotalPrice()->getNumber();
    $data['currency'] = $cart->getTotalPrice()->getCurrencyCode();

    return new JsonResponse($data);
  }

  public function getProductsInBranch($branch) {
    $storage = $this->entityTypeManger->getStorage('commerce_product');
    $query = $storage->getQuery();
    $orGroup = $query->orConditionGroup()
      ->condition('field_product_branch', NULL, 'IS NULL');
    if ($branch) {
      $orGroup->condition('field_product_branch', $branch->id());
    }
    $ids = $query->execute();
    $products = [];
    foreach ($ids as $id) {
      $product = $storage->load($id);
      if ($product) {
        $products[$product->uuid()] = [
          'uuid' => $product->uuid(),
          'id' => $product->id(),
          'title' => $product->label(),
          'field_description' => $product->field_description->value,
          'branch' => $product->field_product_branch && $product->field_product_branch->entity ? [
            'uuid' => $product->field_product_branch->entity->uuid(),
            'id' => $product->field_product_branch->entity->id(),
            'title' => $product->field_product_branch->entity->label(),
          ] : NULL,
          'variations' => [],
        ];
        foreach ($product->variations as $variant) {
          $products[$product->uuid()]['variations'][] = [
            'uuid' => $variant->entity->uuid(),
            'id' => $variant->entity->id(),
            'price' => $variant->entity->getPrice()->__toString(), //toArray(),
            'field_best_value' => $variant->entity->field_best_value->value,
            'title' => $variant->entity->label(),
          ];
        }
      }
    }
    return new JsonResponse($products);
  }

}
