<?php

namespace Drupal\openy_memberships\Plugin\Field\FieldType;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataReferenceDefinition;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines a multi item field type.
 *
 * @FieldType(
 *   id = "openy_memberships_multi_item",
 *   label = @Translation("OpenY Memberships Multi Item"),
 *   description = @Translation("An entity field containing an two entity references and quatity field."),
 *   category = @Translation("Reference"),
 *   default_widget = "openy_memberships_multi_item_autocomplete",
 *   default_formatter = "openy_memberships_multi_item_label",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class OpenyMembershipsMultiItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'ar_target_type' => \Drupal::moduleHandler()->moduleExists('node') ? 'node' : 'user',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = parent::defaultFieldSettings();

    $settings['pr_label'] = '';
    $settings['added_reference']['ar_label'] = '';
    $settings['added_reference']['ar_bundles'] = [];
    $settings['added_reference']['ar_weight'] = -50;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $quantity_definition = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Quantity'))
      ->setRequired(TRUE);
    $properties['quantity'] = $quantity_definition;

    $settings = $field_definition->getSettings();

    $ar_target_type_info = \Drupal::entityManager()->getDefinition($settings['ar_target_type']);

    $ar_target_id_data_type = 'string';
    if ($ar_target_type_info->entityClassImplements(FieldableEntityInterface::class)) {
      $id_definition = \Drupal::entityManager()->getBaseFieldDefinitions($settings['ar_target_type'])[$ar_target_type_info->getKey('id')];
      if ($id_definition->getType() === 'integer') {
        $ar_target_id_data_type = 'integer';
      }
    }

    if ($ar_target_id_data_type === 'integer') {
      $ar_target_id_definition = DataReferenceTargetDefinition::create('integer')
        ->setLabel(new TranslatableMarkup('AR: @label ID', ['@label' => $ar_target_type_info->getLabel()]))
        ->setSetting('unsigned', TRUE);
    }
    else {
      $ar_target_id_definition = DataReferenceTargetDefinition::create('string')
        ->setLabel(new TranslatableMarkup('AR: @label ID', ['@label' => $ar_target_type_info->getLabel()]));
    }
    $ar_target_id_definition->setRequired(TRUE);
    $properties['ar_target_id'] = $ar_target_id_definition;

    $properties['ar_entity'] = DataReferenceDefinition::create('entity')
      ->setLabel($ar_target_type_info->getLabel())
      ->setDescription(new TranslatableMarkup('The added reference referenced entity'))
      ->setComputed(TRUE)
      ->setReadOnly(FALSE)
      ->setTargetDefinition(EntityDataDefinition::create($settings['ar_target_type']))
      ->addConstraint('EntityType', $settings['ar_target_type']);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $ar_target_type = $field_definition->getSetting('ar_target_type');
    $ar_target_type_info = \Drupal::entityManager()->getDefinition($ar_target_type);
    $properties = static::propertyDefinitions($field_definition)['ar_target_id'];

    $schema['columns']['quantity'] = array(
      'type' => 'int',
      'size' => 'tiny',
      'unsigned' => TRUE,
    );

    if ($ar_target_type_info->entityClassImplements(FieldableEntityInterface::class) && $properties->getDataType() === 'integer') {
      $columns = [
        'ar_target_id' => [
          'description' => 'AR: The ID of the target entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ];
    }
    else {
      $columns = [
        'ar_target_id' => [
          'description' => 'AR: The ID of the target entity.',
          'type' => 'varchar_ascii',
          'length' => $ar_target_type_info->getBundleOf() ? EntityTypeInterface::BUNDLE_MAX_LENGTH : 255,
        ],
      ];
    }

    $schema['columns']['ar_target_id'] = $columns['ar_target_id'];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = parent::storageSettingsForm($form, $form_state, $has_data);

    $element['ar_target_type'] = [
      '#type' => 'select',
      '#title' => t('Added reference: Type of item to reference'),
      '#options' => \Drupal::service('entity_type.repository')->getEntityTypeLabels(TRUE),
      '#default_value' => $this->getSetting('ar_target_type'),
      '#required' => TRUE,
      '#disabled' => $has_data,
      '#size' => 1,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);
    $settings = $this->getSettings();

    // Get the reference target entity type from the storage settings.
    $ar_target_type = $this->getSetting('ar_target_type');

    $form['pr_label'] = [
      '#type' => 'textfield',
      '#title' => t('Primary reference: Field label'),
      '#default_value' => $settings['pr_label'],
      '#description' => t('Label for the primary reference field.'),
    ];

    $form['added_reference'] = [
      '#type' => 'fieldset',
      '#title' => t('Added reference settings'),
    ];

    $options = [];
    $bundle_options = \Drupal::service('entity_type.bundle.info')->getBundleInfo($ar_target_type);
    foreach ($bundle_options as $key => $option) {
      $options[$key] = $option['label'];
    }
    $form['added_reference']['ar_bundles'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => t('Added reference: Bundles'),
      '#default_value' => $settings['added_reference']['ar_bundles'],
      '#description' => t('Bundles for the added reference field to reference.'),
      '#required' => TRUE,
    ];

    $form['added_reference']['ar_label'] = [
      '#type' => 'textfield',
      '#title' => t('Added reference: Label'),
      '#default_value' => $settings['added_reference']['ar_label'],
      '#description' => t('Label for the added reference field.'),
    ];

    $form['added_reference']['ar_weight'] = [
      '#type' => 'textfield',
      '#title' => t('Added reference: Weight'),
      '#default_value' => $settings['added_reference']['ar_weight'],
      '#description' => t('Set a negative number to have the added reference come first or positive to have it come second.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    // Don't let the parent function run as that will double up all the
    // entity references.
    return [];
  }

}
