<?php

namespace Drupal\liveblog_pusher\Plugin\LiveblogNotificationChannel;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Renderer;
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
   * @var \Pusher
   */
  protected $client;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

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
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   * @param \Drupal\liveblog_pusher\PusherLoggerInterface $logger
   *   The logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, Renderer $renderer, PusherLoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory, $entity_type_manager);
    $this->renderer = $renderer;
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
      $container->get('renderer'),
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    // Check the required dependency on the Pusher library.
    if (!class_exists('\Pusher')) {
      $form_state->setErrorByName('plugin', t('The "\Pusher" class was not found. Please make sure you have included the <a href="https://github.com/pusher/pusher-http-php">Pusher PHP Library</a>.'));
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return \Pusher
   *   The notification channel client.
   */
  public function getClient() {
    if (!$this->client) {
      $options = [
        'encrypted' => true
      ];
      $this->client = new \Pusher(
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
  function triggerLiveblogPostEvent(LiveblogPost $liveblog_post, $event) {
    $client = $this->getClient();
    $channel = "liveblog-{$liveblog_post->getLiveblog()->id()}";

    // Trigger an event by providing event name and payload.
    $response = $client->trigger($channel, $event, $this->getLiveblogPostPayload($liveblog_post));

    if (!$response) {
      // Log response if there is an error.
      $this->logger->saveLog('error');
    }
  }

  /**
   * Gets payload from the liveblog post entity.
   *
   * @param \Drupal\liveblog\Entity\LiveblogPost $liveblog_post
   *   The target liveblog post.
   *
   * @return array
   *   The payload array.
   */
  public function getLiveblogPostPayload(LiveblogPost $liveblog_post) {
    $rendered_entity = $this->entityTypeManager->getViewBuilder('liveblog_post')->view($liveblog_post);
    $output = $this->renderer->render($rendered_entity);

    $data['id'] = $liveblog_post->id();
    $data['uuid'] = $liveblog_post->uuid();
    $data['title'] = $liveblog_post->get('title')->value;
    $data['liveblog'] = $liveblog_post->getLiveblog()->id();
    $data['body__value'] = $liveblog_post->body->value;
    $data['highlight'] = $liveblog_post->highlight->value;
    $data['location'] = $liveblog_post->location->value;
    $data['source__uri'] = $liveblog_post->source->first() ? $liveblog_post->source->first()->getUrl()->toString() : NULL;
    $data['uid'] = $liveblog_post->getAuthor() ? $liveblog_post->getAuthor()->getAccountName() : NULL;
    $data['changed'] = $liveblog_post->changed->value;
    $data['created'] = $liveblog_post->created->value;
    $data['status'] = $liveblog_post->status->value;
    $data['rendered_entity'] = $output;

    return $data;
  }

}
