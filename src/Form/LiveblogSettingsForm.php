<?php

namespace Drupal\liveblog\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\liveblog\NotificationChannel\NotificationChannelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for notification channel.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class LiveblogSettingsForm extends ConfigFormBase  {

  /**
   * The notification channel manager.
   *
   * @var \Drupal\liveblog\NotificationChannel\NotificationChannelManager
   */
  protected $notificationChannelManager;

  /**
   * Constructs an EntityForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\liveblog\NotificationChannel\NotificationChannelManager $notification_channel_manager
   *   The notification channel service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, NotificationChannelManager $notification_channel_manager) {
    parent::__construct($config_factory);
    $this->notificationChannelManager = $notification_channel_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.liveblog.notification_channel')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'liveblog_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'liveblog.settings',
    ];
  }

  /**
   * Gets notification channel config.
   *
   * @return \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   *   Notification channel config.
   */
  protected function getConfig() {
    return $this->config('liveblog.settings');
  }

  /**
   * Sets notification channel config.
   *
   * @param string $name
   *   The config variable name.
   * @param string $value
   *   The config variable value.
   */
  protected function setConfig($name, $value) {
    $config = $this->configFactory()->getEditable('liveblog.settings');
    $config->set($name, $value)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfig();

    $form['plugin_wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div id="liveblog-plugin-wrapper">',
      '#suffix' => '</div>',
    ];

    if ($available = $this->notificationChannelManager->getLabels()) {
      // If the plugin is not set, pick the first available as the default.
      $plugin_id = $config->get('plugin') ?: key($available);
      $definition = $this->notificationChannelManager->getDefinition($plugin_id);

      $form['plugin_wrapper']['notification_channel'] = [
        '#type' => 'select',
        '#title' => t('Notification channel plugin'),
        '#limit_validation_errors' => [['plugin']],
        '#executes_submit_callback' => TRUE,
        '#description' => isset($definition['description']) ? Xss::filter($definition['description']) : '',
        '#options' => $available,
        '#default_value' => $plugin_id,
        '#required' => TRUE,
        '#ajax' => [
          'callback' => [$this, 'ajaxPluginSelect'],
          'wrapper' => 'liveblog-plugin-wrapper',
        ],
      ];

      $form['plugin_wrapper']['plugin_settings'] = [
        '#type' => 'details',
        '#title' => t('@plugin plugin settings', ['@plugin' => $definition['label']]),
        '#tree' => TRUE,
        '#open' => TRUE,
      ];

      $plugin = $this->notificationChannelManager->createInstance($plugin_id);
      if ($plugin_form = $plugin->buildConfigurationForm($form['plugin_wrapper']['plugin_settings'], $form_state)) {
        $form['plugin_wrapper']['plugin_settings'] += $plugin_form;
      }
      else {
        $form['plugin_wrapper']['plugin_settings']['no_settings'] = [
          '#type' => 'item',
          '#markup' => t('The plugin does not provide any settings.'),
        ];
      }
    }
    else {
      $form['plugin_wrapper']['no_plugins'] = [
        '#type' => 'markup',
        '#markup' => t('There are no liveblog notification plugins to choose from. Please enable a respective module providing a plugin.'),
      ];
    }

    $form['channel_prefix'] = [
      '#type' => 'textfield',
      '#title' => t('Channel prefix'),
      '#description' => t('This can be useful, if you run multiple drupal instances on the same notification channel plugin instance.'),
      '#default_value' => !empty($config->get('channel_prefix')) ? $config->get('channel_prefix') : 'liveblog',
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if (!$plugin = $form_state->getValue('notification_channel')) {
      $form_state->setErrorByName('notification_channel', $this->t('You have to select a liveblog notification channel plugin.'));
    }
    $plugin = $this->notificationChannelManager->createInstance($plugin);
    $plugin->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->setConfig('notification_channel', $form_state->getValue('notification_channel'));
    $this->setConfig('channel_prefix', $form_state->getValue('channel_prefix'));

    $plugin = $form_state->getValue('notification_channel');
    $plugin = $this->notificationChannelManager->createInstance($plugin);
    $plugin->submitConfigurationForm($form, $form_state);

    drupal_set_message(t('Liveblog settings have been updated.'));
  }

  /**
   * Ajax callback for loading the plugin settings form for the selected one.
   */
  public static function ajaxPluginSelect(array $form, FormStateInterface $form_state) {
    return $form['plugin_wrapper'];
  }

}
