<?php

namespace Drupal\liveblog\NotificationChannel;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Default controller class for service plugins.
 *
 * @ingroup liveblog_notification_channel
 */
abstract class NotificationChannelPluginBase extends PluginBase implements NotificationChannelInterface  {

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
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Nothing to do here by default.
  }

}
