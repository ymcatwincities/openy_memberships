<?php

namespace Drupal\openy_memberships_front\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Render Activities/Strategies pages.
 */
class Memberships extends ControllerBase {

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
   * Constructs a new Memberships object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityFormBuilder = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Activity route method for proxying client request.
   */
  public function page($filepath) {
    $attachments['library'][] = 'openy_memberships_front/openy_memberships_front';
    $config = array_map(function ($component) {
      return trim($component);
    }, explode(PHP_EOL, $this->configFactory->getEditable('openy_memberships_front.settings')->get('steps')));
    $attachments['drupalSettings']['openy_memberships_front']['steps'] = $config;
    return [
      '#id' => 'app',
      '#theme' => 'openy_memberships_front',
      '#attached' => $attachments,
    ];
  }

}
