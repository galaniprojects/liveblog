<?php

namespace Drupal\liveblog\NotificationChannel;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;

/**
 * Provides an Liveblog Notification Channel plugin manager.
 *
 * @see \Drupal\liveblog\NotificationChannel\NotificationChannelInterface
 * @see plugin_api
 *
 * @ingroup liveblog_notification_channel
 */
class NotificationChannelManager extends DefaultPluginManager {

  /**
   * The instance of the current active plugin.
   *
   * @var \Drupal\liveblog\NotificationChannel\NotificationChannelInterface|null
   */
  protected $plugin;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a NotificationChannelManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory) {
    parent::__construct(
      'Plugin/LiveblogNotificationChannel',
      $namespaces,
      $module_handler,
      'Drupal\liveblog\NotificationChannel\NotificationChannelInterface',
      'Drupal\liveblog\Annotation\LiveblogNotificationChannel'
    );
    $this->alterInfo('liveblog_notification_channel_info');
    $this->setCacheBackend($cache_backend, 'liveblog_notification_channel_info_plugins');
    $this->factory = new ContainerFactory($this, '\Drupal\liveblog\NotificationChannel\NotificationChannelInterface');
    $this->config = $config_factory->get('liveblog.settings');
  }

  /**
   * Returns the plugin labels.
   *
   * @return string[]
   *   Array of plugin labels, keyed by the plugin id.
   */
  public function getLabels() {
    $list = [];
    foreach ($this->getDefinitions() as $plugin => $definition) {
      $list[$plugin] = $definition['label'];
    }
    return $list;
  }

  /**
   * Creates an instance of the current active plugin.
   *
   * @return \Drupal\liveblog\NotificationChannel\NotificationChannelInterface|null
   *   The instance of the current active plugin, null if not set.
   */
  public function createActiveInstance() {
    if (empty($this->plugin) || $this->plugin->getPluginId() != $this->config->get('notification_channel')) {
      if ($plugin_id = $this->config->get('notification_channel')) {
        $this->plugin = $this->createInstance($plugin_id);
      }
    }
    return $this->plugin;
  }

}
