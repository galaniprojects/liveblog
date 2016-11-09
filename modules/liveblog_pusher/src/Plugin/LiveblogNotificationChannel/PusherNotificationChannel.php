<?php

namespace Drupal\liveblog_pusher\Plugin\LiveblogNotificationChannel;

use Drupal\Core\Form\FormStateInterface;
use Drupal\liveblog\NotificationChannel\NotificationChannelPluginBase;

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
      '#title' => t('Username'),
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

}
