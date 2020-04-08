<?php

namespace Drupal\openy_memberships_front\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Memberships' block.
 *
 * @Block(
 *   id = "memberships_block",
 *   admin_label = @Translation("Memberships Block"),
 *   category = @Translation("Paragraph Blocks")
 * )
 */
class MembershipsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $attachments['library'][] = 'openy_memberships_front/openy_memberships_front';
    $config = array_map(function ($component) {
      return trim($component);
    }, explode(PHP_EOL, $this->configFactory->getEditable('openy_memberships_front.settings')->get('steps')));
    $attachments['drupalSettings']['openy_memberships_front']['steps'] = $config;
    return [
      '#id' => 'app',
      '#theme' => 'openy_memberships_front',
      '#attached' => $attachments,
      '#cache' => [
        'contexts' => $this->getCacheContexts(),
        'max-age' => $this->getCacheMaxAge(),
      ],
    ];
  }

}
