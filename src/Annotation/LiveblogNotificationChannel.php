<?php

namespace Drupal\liveblog\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an Liveblog Notification Channel annotation object.
 *
 * Plugin Namespace: Plugin\LiveblogNotificationChannel
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
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

}
