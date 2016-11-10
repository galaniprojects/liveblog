<?php

namespace Drupal\liveblog\NotificationChannel;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
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

}
