<?php

namespace Drupal\openy_memberships\Plugin\Field\FieldType;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 *  Extends commerce_product.membership bundle with learn_more field
 *  which is used to render a ajax dialog link to commerce_product route
 *
 * @package Drupal\openy_memberships\Plugin\Field\FieldType
 */
class LearnMoreExtraField {

  use StringTranslationTrait;

  /**
   * Populate $build with LearnMoreExtraField render array.
   *
   * @param array $build
   *   Build array.
   * @param \Drupal\commerce_product\Entity\ProductInterface
   *   Store object.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   Dislpay interface.
   */
  public function fieldAttach(array &$build, ProductInterface $entity, EntityViewDisplayInterface $display) {
    $active_theme = \Drupal::service('theme.manager')->getActiveTheme()->getName();
    $active_theme = str_replace('_', '-', $active_theme);

    $build['learn_more'] = [
      'link' => [
        '#type' => 'link',
        '#url' => Url::fromRoute('entity.commerce_product.canonical',
          ['commerce_product' => $entity->id()]
        ),
        '#title' => $this->t('Learn more'),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'learn_more',
          ],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => json_encode([
            'dialogClass' => 'learn_more ' . "$active_theme-dialog-theme",
            'width' => 'auto',
            'height' => 'auto',
          ]),
        ],
        '#attached' => [
          'library' => ['core/drupal.ajax'],
        ],
      ],
      '#weight' => 5
    ];
  }

  /**
   * Hook extra fields implementation.
   *
   * @param array $extra
   *   Extra field info.
   */
  public function addExtraField(array &$extra) {
    $extra['commerce_product']['membership']['display']['learn_more'] = [
      'label' => $this->t('Learn more button'),
      'weight' => 0,
      'visible' => TRUE,
    ];
  }

}
