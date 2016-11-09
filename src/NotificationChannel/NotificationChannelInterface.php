<?php

namespace Drupal\liveblog\NotificationChannel;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Interface for service plugin controllers.
 *
 * @ingroup liveblog_notification_channel
 */
interface NotificationChannelInterface extends PluginInspectionInterface, PluginFormInterface, ConfigurablePluginInterface, ContainerFactoryPluginInterface {

}
