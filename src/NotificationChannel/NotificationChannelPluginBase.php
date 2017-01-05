<?php

namespace Drupal\liveblog\NotificationChannel;

use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default controller class for service plugins.
 *
 * @ingroup liveblog_notification_channel
 */
abstract class NotificationChannelPluginBase extends PluginBase implements NotificationChannelInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an EntityForm object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Gets name of the config setting.
   *
   * @return string
   *   Name of the config setting.
   */
  protected function getConfigName() {
    return 'liveblog.notification_channel.' . $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = $this->configFactory->get($this->getConfigName());
    return $config->getRawData();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Gets configuration value of a single variable.
   *
   * @param string $name
   *   The variable name.
   * @return mixed
   *   The variable value, null if not set.
   */
  public function getConfigurationValue($name) {
    return isset($this->configuration[$name]) ? $this->configuration[$name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Saves the notification channel configuration.
   */
  protected function saveConfiguration() {
    $config = $this->configFactory->getEditable($this->getConfigName());
    foreach ($this->getConfiguration() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function validateLiveblogPostForm(array &$form, FormStateInterface $form_state, LiveblogPost $liveblog_post) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration = $form_state->getValue('plugin_settings');
    $this->saveConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}
