<?php

namespace Drupal\liveblog\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an Liveblog Notification Channel annotation object.
 *
 * Plugin Namespace: Plugin\LiveblogNotificationChannel.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class LiveblogNotificationChannel extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
