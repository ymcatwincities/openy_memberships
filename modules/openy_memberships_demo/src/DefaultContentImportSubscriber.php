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
  }

  public function fixBrokenBlocks() {
    $tables = ['paragraph__field_prgf_block', 'paragraph_revision__field_prgf_block'];

    foreach ($tables as $table) {
      // Select all paragraphs that have "broken" as plugin_id.
      $query = \Drupal::database()->select($table, 'ptable');
      $query->fields('ptable');
      $query->condition('ptable.field_prgf_block_plugin_id', 'broken');
      $broken_paragraphs = $query->execute()->fetchAll();
      $str = print_r($broken_paragraphs, true);

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
        $res = $query->execute();
      }
    }
  }

}
