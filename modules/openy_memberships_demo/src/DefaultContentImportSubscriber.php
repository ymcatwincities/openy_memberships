<?php

namespace Drupal\openy_memberships_demo;

use Drupal\default_content\Event\DefaultContentEvents;
use Drupal\default_content\Event\ImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reacts to configuration events for the Default Content module.
 */
class DefaultContentImportSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [DefaultContentEvents::IMPORT => 'onModuleImport'];
  }

  /**
   * Fixes broken blocks after default content import.
   *
   * @param ImportEvent $event
   *   The config importer event.
   */
  public function onModuleImport(ImportEvent $event) {
    if ($event->getModule() != 'openy_memberships_demo') {
      return;
    }

    $this->fixBrokenBlocks();
    $this->updateProducts();
  }

  public function fixBrokenBlocks() {
    $tables = ['paragraph__field_prgf_block', 'paragraph_revision__field_prgf_block'];

    foreach ($tables as $table) {
      // Select all paragraphs that have "broken" as plugin_id.
      $query = \Drupal::database()->select($table, 'ptable');
      $query->fields('ptable');
      $query->condition('ptable.field_prgf_block_plugin_id', 'broken');
      $broken_paragraphs = $query->execute()->fetchAll();

      // Update to correct plugin_id based on data array.
      foreach ($broken_paragraphs as $paragraph) {
        $data = unserialize($paragraph->field_prgf_block_plugin_configuration);
        $query = \Drupal::database()->update($table);
        $query->fields([
          'field_prgf_block_plugin_id' => $data['id'],
        ]);
        $query->condition('bundle', $paragraph->bundle);
        $query->condition('entity_id', $paragraph->entity_id);
        $query->condition('revision_id', $paragraph->revision_id);
        $query->condition('langcode', $paragraph->langcode);
        $query->execute();
      }
    }
  }

  public function updateProducts() {
    $products = \Drupal::service('entity_type.manager')->getStorage('commerce_product')->loadMultiple();
    $data = [
      'Family II' => [
        0 => [
          'target_id' => 132,
          'quantity' => 2,
          'ar_quantity' => 1,
          'ar_target_id' => 2
        ],
        1 => [
          'target_id' => 130,
          'quantity' => 1,
          'ar_quantity' => 1,
          'ar_target_id' => 3
        ],
      ],
      'Family I' => [
        0 => [
          'target_id' => 132,
          'quantity' => 4,
          'ar_quantity' => 4,
          'ar_target_id' => 2
        ],
        1 => [
          'target_id' => 130,
          'quantity' => 2,
          'ar_quantity' => 2,
          'ar_target_id' => 3
        ],
      ],
      'Adult' => [
        0 => [
          'target_id' => 132,
          'quantity' => 2,
          'ar_quantity' => 2,
          'ar_target_id' => 2
        ]
      ]
    ];
    foreach ($products as $product) {
      if (isset($data[$product->getTitle()])) {
        $product->set('field_om_total_available', $data[$product->getTitle()]);
        $product->save();
      }
    }
  }

}
