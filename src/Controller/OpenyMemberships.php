<?php

namespace Drupal\openy_memberships\Controller;

use Drupal\commerce_price\Price;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\openy_memberships\OmPDFGenerator;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_promotion\Entity\Promotion;
use Drupal\node\Entity\Node;
use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Url;

/**
 * Provides OpenyMemberships controller.
 */
class OpenyMemberships extends ControllerBase {


  /**
   * The entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $siteConfig;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * @var \Drupal\openy_memberships\OmPDFGenerator
   */
  protected $pdfGenerator;

  /**
   * Constructs a new Memberships object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   * @param \Drupal\openy_memberships\OmPDFGenerator $pdf_generator
   * @param \Drupal\Component\Utility\EmailValidator $email_validator
   */
  public function __construct(
      EntityTypeManagerInterface $entity_type_manager,
      ConfigFactoryInterface $config_factory,
      CartProviderInterface $cart_provider,
      AccountProxyInterface $current_user,
      RendererInterface $renderer,
      MailManagerInterface $mail_manager,
      OmPDFGenerator $pdf_generator,
      EmailValidator $email_validator
    ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->cartProvider = $cart_provider;
    $this->currentUser = $current_user;
    $this->siteConfig = $this->configFactory->get('system.site');
    $this->renderer = $renderer;
    $this->mailManager = $mail_manager;
    $this->pdfGenerator = $pdf_generator;
    $this->emailValidator = $email_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('current_user'),
      $container->get('renderer'),
      $container->get('plugin.manager.mail'),
      $container->get('openy_memberships_pdf_generator'),
      $container->get('email.validator')
    );
  }

  /**
   * Get Ages Groups and related products.
   */
  public function getAgesGroupsInfo(Request $request) {
    $data = [];
    $tids = $this->entityTypeManager->getStorage('taxonomy_term')
      ->getQuery()
      ->condition('vid', 'memberships_ages_groups')
      ->condition('status', 1)
      ->sort('weight', 'ASC')
      ->execute();
    $terms = Term::loadMultiple($tids);
    foreach ($terms as $tid => $term) {
      $data[$tid] = [
        'title' => $term->getName(),
        'uuid' => $term->uuid(),
        'id' => $tid,
      ];
    }
    return new JsonResponse($data);
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
        $promotions = $this->entityTypeManager->getStorage('commerce_promotion')->loadMultiple();
        foreach ($promotions as $promotion) {
          $conditions = $promotion->get('conditions');
          $condition_values = $conditions->getValue();
          foreach ($condition_values as $condition_value) {
            if ($condition_value['target_plugin_id'] == 'openy_memberships_health_insurance') {
              $data['member_promotions']['health_insurance'] = [
                'amount' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['number'],
                'currency' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['currency_code'],
                'label' => $promotion->label(),
                'description' => $promotion->description->value,
              ];
            }
            if ($condition_value['target_plugin_id'] == 'openy_memberships_military_service') {
              $data['member_promotions']['military_service'] = [
                'amount' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['number'],
                'currency' => $promotion->offer->getValue()[0]['target_plugin_configuration']['amount']['currency_code'],
                'label' => $promotion->label(),
                'description' => $promotion->description->value,
              ];
            }
          }
        }
        foreach ($cart->getItems() as $order_item) {
          $adjustments = $order_item->getAdjustments();
          foreach ($adjustments as $adjustment) {
            if ($adjustment->getType() == 'promotion') {
              $promotion_id = $adjustment->getSourceId();
              $promotion = $this->entityTypeManager->getStorage('commerce_promotion')->load($promotion_id);
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

  /**
   * Return products.
   */
  public function getProductsInBranch($branch, $data) {
    if ($data) {
      foreach (explode(';', $data) as $agesGroup) {
        if (!$agesGroup) {
          continue;
        }
        $group = explode('_', $agesGroup);
        if ($group[1] > 0) {
          $agesGroups[$group[0]] = $group[1];
        }
      }
    }
    $agesGroupType = count($agesGroups) == 1 ? 'individual' : 'family';
    $storage = $this->entityTypeManager->getStorage('commerce_product');
    $query = $storage->getQuery();
    if (!empty($agesGroups)) {
      // Filter products by provided Ages Groups.
      $query->condition('field_om_total_available.target_id', array_keys($agesGroups), 'IN');
    }
    // Filter products by branch.
    if ($branch && $branch !== 'all') {
      $orGroup = $query->orConditionGroup()
        ->condition('field_product_branch', NULL, 'IS NULL');
      $orGroup->condition('field_product_branch', $branch);
      $query->condition($orGroup);
    }
    $ids = $query->execute();
    $products = [];
    foreach ($ids as $id) {
      $product = $storage->load($id);
      // Remove product if it doesn't have variations.
      if (!$product->hasVariations()) {
        continue;
      }
      if ($product) {
        $filter_product = FALSE;
        $field_om_total_available = $product->field_om_total_available->getValue();
        if (!empty($field_om_total_available)) {
          // Filter product if type is not the same.
          $productType = count($field_om_total_available) == 1 ? 'individual' : 'family';
          // Filter product by type.
          if ($productType != $agesGroupType) {
            $filter_product = TRUE;
          }
          $productAgeGroups = [];
          foreach ($field_om_total_available as $value) {
            $productAgeGroups[$value['target_id']] = $value['quantity'];
          }
          // Filter product if it contains Age Groups that are not requested.
          foreach (array_keys($agesGroups) as $agesGroup) {
            if (!isset($productAgeGroups[$agesGroup])) {
              $filter_product = TRUE;
            }
          }
          // Filter product if it contains more Age Groups than requested.
          if (count($productAgeGroups) > count($agesGroups)) {
            $filter_product = TRUE;
          }
          foreach ($field_om_total_available as $value) {
            if (!isset($agesGroups[$value['target_id']])) {
              continue;
            }
            $requested_quantity = $agesGroups[$value['target_id']];
            $total_available = $value['quantity'];
            // Filter product if total available of any Age Group is more than requested count per group.
            if ($total_available < $requested_quantity) {
              $filter_product = TRUE;
            }
          }
        }
        if (!$filter_product) {
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
            if ($variant->entity instanceof ProductVariationInterface) {
              $products[$product->uuid()]['variations'][] = [
                'uuid' => $variant->entity->uuid(),
                'id' => $variant->entity->id(),
                'price' => $variant->entity->getPrice()->toArray()['number'],
                'field_best_value' => $variant->entity->field_best_value->value,
                'title' => $variant->entity->label(),
              ];
            }
          }
        }
      }
    }
    return new JsonResponse($products);
  }

  /**
   * Set Billing Profile (Customer Name, email, etc).
   */
  public function setBillingInfo(Request $request, $order) {
    $storage = $this->entityTypeManager->getStorage('profile');
    $postData = json_decode($request->getContent(), TRUE);
    $carts = $this->cartProvider->getCarts();
    $profileId = NULL;
    $profileUuid = NULL;
    if (!empty($carts)) {
      foreach ($carts as $cart_id => $cart) {
        if ($order->id() == $cart_id) {
          $profileEntity = $storage->create([
            'type' => 'customer',
            'field_email' => $postData['field_email'],
            'field_phone' => $postData['field_phone'],
            'address' => [
              'country_code' => 'US',
              'address_line1' => '',
              'locality' => '',
              'administrative_area' => '',
              'postal_code' => '',
              'given_name' => $postData['address']['given_name'],
              'family_name' => $postData['address']['family_name'],
            ],
          ]);
          $profileEntity->save();
          $profileId = $profileEntity->id();
          $profileUuid = $profileEntity->uuid();
          $order->set('billing_profile', $profileEntity);
          $order->save();

        }
      }
    }

    return new JsonResponse([
      'order_uuid' => $order->uuid(),
      'order_id' => $order->id(),
      'billing_profile' => [
        'billing_id' => $profileId,
        'billing_uuid' => $profileUuid,
      ],
    ]);
  }

  /**
   * Returns PDF for specific parameters.
   */
  public function getSummaryPdf(Request $request, $order_uuid) {
    $settings = [
      'body' => [
        '#content' => [
          'logo_url' => drupal_get_path('module', 'openy_repeat') . '/img/ymca_logo_black.png',
          'site_name' => $this->siteConfig->get('name'),
          'result' => $this->getSummaryData($order_uuid),
        ],
        '#theme' => 'openy_memberships__pdf__summary',
        '#cache' => [
          'max-age' => 0
        ],
      ],
      'title' => $this->t('Your Membership'),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    $this->pdfGenerator->generatePDF($settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $response = $this->getSummaryData();
    return new JsonResponse($response);
  }

  /**
   * Provides Summary data.
   */
  public function getSummaryData($order_uuid = null) {
    $response = [];
    $carts = $this->cartProvider->getCarts();
    if ($order_uuid && !$carts) {
      $carts = $this->entityTypeManager->getStorage('commerce_order')->loadByProperties(['uuid' => $order_uuid]);
    }
    if (!empty($carts)) {
      foreach ($carts as $cart_id => $cart) {
        foreach ($cart->getItems() as $order_item) {
          if ($order_item->bundle() == 'membership_order_item') {
            $purchasedEntity = $order_item->getPurchasedEntity();
            $product = $purchasedEntity->getProduct();
            $product_description = $product->field_description->view();
            $product_branch = null;
            if ($product_branch_id = $product->field_product_branch->target_id) {
              $product_branch = Node::load($product_branch_id)->getTitle();
            }
            $response['products'][] = [
              'type' => $order_item->bundle(),
              'order_item_id' => $order_item->id(),
              'uuid' => $order_item->uuid(),
              'title' => $order_item->getTitle(),
              'quantity' => $order_item->getQuantity(),
              'amount' => $order_item->getTotalPrice()->getNumber(),
              'currency' => $order_item->getTotalPrice()->getCurrencyCode(),
              'field_best_value' => $purchasedEntity->field_best_value->value,
              'product_title' => $product->getTitle(),
              'product_description' => render($product_description),
              'product_branch' => $product_branch,
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
              $promotion = Promotion::load($promotion_id);
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
    if (isset($cart) && $cart->hasField('field_family')) {
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

    return $response;
  }


  /**
   * Sends emails to store and customer with summary data.
   */
  public function sendSummaryEmail(Request $request, $order_uuid) {
    if (!$order_uuid) {
      return new JsonResponse();
    }
    $order = $this->entityTypeManager->getStorage('commerce_order')->loadByProperties(['uuid' => $order_uuid]);
    $order = reset($order);
    $store = $this->entityTypeManager->getStorage('commerce_store')->loadDefault();
    $user_email = $order->get('mail')->getValue()[0]['value'];
    $store_email = $store->getEmail();

    if ($user_email && $this->emailValidator->isValid($user_email) && $store_email && $this->emailValidator->isValid($store_email)) {
      $to = implode(', ', [$store_email, $user_email]);
    }
    elseif ($store_email && $this->emailValidator->isValid($store_email)) {
      $to = $store->getEmail();
    }
    else {
      // You need to setup store email at least. Using site email as a backup.
      $to = \Drupal::config('system.site')->get('mail');
    }

    $from = $this->siteConfig->get('mail');
    $langcode = $this->currentUser->getPreferredLangcode();
    $subject = $this->t('Your Membership!');

    $body = [
      '#content' => [
        'logo_url' => Url::fromUri('internal:/' . drupal_get_path('module', 'openy_repeat') . '/img/ymca_logo_black.png', ['absolute' => TRUE]),
        'site_name' => $this->siteConfig->get('name'),
        'result' => $this->getSummaryData($order_uuid),
      ],
      '#theme' => 'openy_memberships__pdf__summary',
    ];
    $body = $this->renderer->renderRoot($body);

    $params = array(
      'subject' => $subject,
      'message' => $body,
    );

    $result = $this->mailManager->mail('openy_memberships', 'openy_memberships_summary_email', $to, $langcode, $params, $from, TRUE);

    return new JsonResponse(['status' => $result['result']]);
  }
}
