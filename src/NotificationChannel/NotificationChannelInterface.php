<?php

namespace Drupal\liveblog\NotificationChannel;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\liveblog\Entity\LiveblogPost;

/**
 * Interface for service plugin controllers.
 *
 * @ingroup liveblog_notification_channel
 */
interface NotificationChannelInterface extends PluginInspectionInterface, PluginFormInterface, ConfigurablePluginInterface, ContainerFactoryPluginInterface {

  /**
   * Gets the notification channel client.
   *
   * @return mixed
   *   The notification channel client.
   */
  public function getClient();

  /**
   * Triggers event notification connected to the liveblog post.
   *
   * @param \Drupal\liveblog\Entity\LiveblogPost $liveblog_post
   *   The target liveblog post.
   * @param string $event
   *   The event name.
   */
  public function triggerLiveblogPostEvent(LiveblogPost $liveblog_post, $event);

  /**
   * Custom validation of the liveblog post.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param FormStateInterface $form_state
   *   The current state of the form. Calling code should pass on a subform
   *   state created through
   *   \Drupal\Core\Form\SubformState::createForSubform().
   * @param LiveblogPost $liveblog_post
   *   The liveblog post entity.
   */
  public function validateLiveblogPostForm(array &$form, FormStateInterface $form_state, LiveblogPost $liveblog_post);

}
