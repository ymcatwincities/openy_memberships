<?php

namespace Drupal\openy_memberships\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;

/**
 * Provides an autocomplete widget for the openy_memberships_multi_item field type.
 *
 * @FieldWidget(
 *   id = "openy_memberships_multi_item_autocomplete",
 *   label = @Translation("OpenY Memberships multi item autocomplete"),
 *   description = @Translation("Autocomplete text fields for each reference."),
 *   field_types = {
 *     "openy_memberships_multi_item"
 *   }
 * )
 */
class OpenyMembershipsAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $widget = parent::formElement($items, $delta, $element, $form, $form_state);

    $widget['quantity'] = array(
      '#title' => $this->t('Quantity'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]) ? $items[$delta]->quantity : 1,
      '#min' => 1,
      '#weight' => 10,
    );

    // Get the reference target entity type from the storage settings.
    $ar_target_type = $this->getFieldSetting('ar_target_type');

    // Get the field settings.
    $settings = $this->fieldDefinition->getSettings();

    // Set the label on the primary reference field, if one is in settings.
    if (!empty($settings['pr_label'])) {
      $widget['target_id']['#title'] = $settings['pr_label'];
      $widget['target_id']['#title_display'] = 'before';
    }

    // Get the settings for the added reference field.
    $ar_bundles = $settings['added_reference']['ar_bundles'];
    $ar_label = !empty($settings['added_reference']['ar_label']) ? $settings['added_reference']['ar_label'] : '';

    // Get the existing value, if any, for the added reference field.
    $default = isset($items[$delta]) ? $items[$delta]->ar_target_id : NULL;
    if (!empty($default)) {
      $default = \Drupal::entityTypeManager()->getStorage($ar_target_type)->load($default);
    }

    // Build the added reference form field.
    $widget['ar_target_id'] = [
      '#type' => 'entity_autocomplete',
      '#selection_handler' => 'default:' . $ar_target_type,
      '#default_value' => $default,
      '#target_type' => $ar_target_type,
      '#weight' => -50,
      '#selection_settings' => [
        'target_bundles' => $ar_bundles,
      ],
    ];

    // Set the label on the added reference field, if one is in settings.
    if (!empty($ar_label)) {
      $widget['ar_target_id']['#title'] = $ar_label;
      $widget['ar_target_id']['#title_display'] = 'before';
    }

    return $widget;
  }

}
