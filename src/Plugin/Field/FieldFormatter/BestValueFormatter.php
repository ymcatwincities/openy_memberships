<?php

namespace Drupal\openy_memberships\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Provides a formatter for the boolean field.
 *
 * @FieldFormatter(
 *   id = "openy_memberships_best_value_formatter",
 *   label = @Translation("Best Value Badge"),
 *   field_types = {
 *     "boolean"
 *   }
 * )
 */
class BestValueFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      if ($item->value) {
        $elements[$delta]['#markup'] = '<div>' . $this->t('Best value!') . '</div>';
      }
    }
    return $elements;
  }

}
