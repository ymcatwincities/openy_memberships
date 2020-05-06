<?php

namespace Drupal\openy_memberships\Plugin\Field\FieldFormatter;

use Drupal\commerce_price\Plugin\Field\FieldFormatter\PriceDefaultFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Provides a formatter for the openy_memberships_multi_item field.
 *
 * @FieldFormatter(
 *   id = "openy_memberships_price_formatter",
 *   label = @Translation("Starting at @price/mo."),
 *   description = @Translation("F.e. Starting at $85/mo."),
 *   field_types = {
 *     "commerce_price"
 *   }
 * )
 */
class StartingAtPriceFormatter extends PriceDefaultFormatter {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $item) {
      $price = $elements[$delta]['#markup'];
      $markup = t('Starting at <div>@price/mo.</div>', ['@price' => $price]);
      $elements[$delta]['#markup'] = $markup;
    }
    return $elements;
  }

}
