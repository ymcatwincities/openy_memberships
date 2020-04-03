<?php

namespace Drupal\openy_memberships\Plugin\rest\resource;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a Summary for Memberships.
 *
 * @RestResource(
 *   id = "openy_memberships_summary",
 *   label = @Translation("Memberships Summary"),
 *   uri_paths = {
 *     "canonical" = "/om-model/data/summary"
 *   }
 * )
 */
class Summary extends ResourceBase {

  /**
   * The entityTypeManger.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManger;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * Summary constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param array $serializer_formats
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    CartProviderInterface $cart_provider
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->entityTypeManger = $entity_type_manager;
    $this->cartProvider = $cart_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    $response = [];
    $carts = $this->cartProvider->getCarts();
    if (!empty($carts)) {
      foreach ($carts as $cart_id => $cart) {
        foreach ($cart->getItems() as $order_item) {
          if ($order_item->bundle() == 'membership_order_item') {
            $response['products'][] = [
              'type' => $order_item->bundle(),
              'order_item_id' => $order_item->id(),
              'uuid' => $order_item->uuid(),
              'title' => $order_item->getTitle(),
              'quantity' => $order_item->getQuantity(),
              'amount' => $order_item->getTotalPrice()->getNumber(),
              'currency' => $order_item->getTotalPrice()->getCurrencyCode(),
            ];
          }
          if ($order_item->bundle() == 'addon') {
            $response['addons'][] = [
              'order_item_id' => $order_item->id(),
              'uuid' => $order_item->uuid(),
              'title' => $order_item->getTitle(),
              'type' => $order_item->bundle(),
              'amount' => $order_item->getTotalPrice()->getNumber(),
              'currency' => $order_item->getTotalPrice()->getCurrencyCode(),
              'frequency' => $order_item->getPurchasedEntity()->field_om_frequency->value,
            ];
          }
          $adjustments = $order_item->getAdjustments();
          foreach ($adjustments as $adjustment) {
            if ($adjustment->getType() == 'promotion') {
              $promotion_id = $adjustment->getSourceId();
              $promotion = $this->entityTypeManger->getStorage('commerce_promotion')->load($promotion_id);
              $discounts[$promotion->get('conditions')[0]->getValue()['target_plugin_id']] = [
                'id' => $promotion->id(),
                'uuid' => $promotion->uuid(),
                'title' => $promotion->getName(),
                'amount' => $promotion->get('offer')[0]->getValue()['target_plugin_configuration']['amount']['number'],
                'currency' => $adjustment->getAmount()->getCurrencyCode(),
              ];
            }
          }
        }
      }
    }

    $response['discounts'][] = [
      'title' => $discounts['openy_memberships_income']['title'],
      'amount' => $discounts['openy_memberships_income']['amount'],
    ];

    // Load family members from order and address discounts.
    if ($cart->hasField('field_family')) {
      $profiles = $cart->field_family->referencedEntities();
      foreach ($profiles as $profile) {
        if ($profile->hasField('field_om_health_insurance')) {
          if ($profile->field_om_health_insurance->value && isset($discounts['openy_memberships_health_insurance'])) {
            $response['discounts'][] = [
              'title' => $discounts['openy_memberships_health_insurance']['title'],
              'member_name' => $profile->field_first_name->value,
              'age' => $profile->field_age->value,
              'amount' => $discounts['openy_memberships_health_insurance']['amount'],
            ];
          }
        }
        if ($profile->hasField('field_om_military_service')) {
          if ($profile->field_om_military_service->value && isset($discounts['openy_memberships_military_service'])) {
            $response['discounts'][] = [
              'title' => $discounts['openy_memberships_military_service']['title'],
              'member_name' => $profile->field_first_name->value,
              'age' => $profile->field_age->value,
              'amount' => $discounts['openy_memberships_military_service']['amount'],
            ];
          }
        }
      }
    }

    $response['total_price'] = $cart->getTotalPrice()->getNumber();
    $response['currency'] = $cart->getTotalPrice()->getCurrencyCode();

    return new ResourceResponse($response);
  }

}
