<?php

namespace Drupal\liveblog_pusher\Plugin\LiveblogNotificationChannel;

use Pusher\Pusher;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\liveblog\Utility\Payload;
use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\liveblog\NotificationChannel\NotificationChannelPluginBase;
use Drupal\liveblog_pusher\PusherLoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Pusher.com notification channel.
 *
 * @LiveblogNotificationChannel(
 *   id = "liveblog_pusher",
 *   label = @Translation("Pusher.com"),
 *   description = @Translation("Pusher.com notification channel."),
 * )
 */
class PusherNotificationChannel extends NotificationChannelPluginBase {

  /**
   * The pusher client.
   *
   * @var \Pusher\Pusher
   */
  protected $client;

  /**
   * The entity type manager.
   *
   * @var \Drupal\liveblog_pusher\PusherLoggerInterface
   */
  protected $logger;

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
   * @param \Drupal\liveblog_pusher\PusherLoggerInterface $logger
   *   The logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, PusherLoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory, $entity_type_manager);
    $this->logger = $logger;
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
      $container->get('entity_type.manager'),
      $container->get('liveblog_pusher.notification_channel.log')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => t('App ID'),
      '#required' => TRUE,
      '#default_value' => !empty($this->configuration['app_id']) ? $this->configuration['app_id'] : '',
      '#description' => t('Please enter your Pusher App ID.'),
    ];
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => t('Key'),
      '#required' => TRUE,
      '#default_value' => !empty($this->configuration['key']) ? $this->configuration['key']: '',
      '#description' => t('Please enter your Pusher key.'),
    ];
    $form['secret'] = [
      '#type' => 'textfield',
      '#title' => t('Secret'),
      '#required' => TRUE,
      '#default_value' => !empty($this->configuration['secret']) ? $this->configuration['secret'] : '',
      '#description' => t('Please enter your Pusher secret.'),
    ];
    $form['cluster'] = [
      '#type' => 'textfield',
      '#title' => t('Cluster'),
      '#required' => FALSE,
      '#default_value' => !empty($this->configuration['cluster']) ? $this->configuration['cluster'] : '',
      '#description' => t('The cluster name to connect to. Leave emty for the default cluster: mt1 (US east coast)'),
    ];

    return $form;
  }

  /**
   * Try to load Pusher library, if it wasn't autoloaded.
   */
  private function loadPusherLibrary() {
    if (!class_exists('\Pusher\Pusher') && function_exists('libraries_get_path')) {
      include_once (DRUPAL_ROOT.'/'.libraries_get_path('pusher') . '/src/Pusher.php');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);

    $this->loadPusherLibrary();

    // Check the required dependency on the Pusher library.
    if (!class_exists('\Pusher\Pusher')) {
      $form_state->setErrorByName('plugin', t('The "\Pusher\Pusher" class was not found. Please make sure you have included the <a href="https://github.com/pusher/pusher-http-php">Pusher PHP Library</a>.'));
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return \Pusher\Pusher
   *   The notification channel client.
   */
  public function getClient() {
    if (!$this->client) {

      $this->loadPusherLibrary();

      $options = [
        'encrypted' => TRUE,
      ];

      $cluster = $this->getConfigurationValue('cluster');
      if (!empty($cluster)) {
        $options['cluster'] = $cluster;
      }

      $this->client = new Pusher(
        $this->getConfigurationValue('key'),
        $this->getConfigurationValue('secret'),
        $this->getConfigurationValue('app_id'),
        $options
      );
      $this->client->set_logger($this->logger);
    }
    return $this->client;
  }

  /**
   * {@inheritdoc}
   */
  public function triggerLiveblogPostEvent(LiveblogPost $liveblog_post, $event) {
    $client = $this->getClient();
    $channel_prefix = \Drupal::config('liveblog.settings')->get('channel_prefix');
    $channel = "$channel_prefix-{$liveblog_post->getLiveblog()->id()}";

    // Trigger an event by providing event name and payload.
    $response = $client->trigger($channel, $event, Payload::create($liveblog_post)->getRenderedPayload(), null, true);
    if ($response['status'] !== 200) {
      // Log response if there is an error.
      $this->logger->saveLog('error');
      // Throw error.
      throw new \Exception($response['body']);
    }
  }

}
