<?php

namespace Drupal\liveblog\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentEntityExampleSettingsForm.
 *
 * @package Drupal\liveblog\Form
 *
 * @ingroup liveblog_post
 */
class LiveblogPostSettingsForm extends ConfigFormBase {
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'liveblog_post_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'liveblog.liveblog_post.settings',
    ];
  }

  /**
   * Gets notification channel config.
   *
   * @return \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   *   Notification channel config.
   */
  protected function getConfig() {
    return $this->config('liveblog.liveblog_post.settings');
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
    $config = $this->configFactory()->getEditable('liveblog.liveblog_post.settings');
    $config->set($name, $value)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfig();
    $form['settings'] = [
      '#type' => 'details',
      '#title' => t('Liveblog post settings'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];
    $form['settings']['preview'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable preview'),
      '#default_value' => $config->get('preview'),
      '#description' => t('Enables preview of liveblog posts before publishing.'),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('settings') as $key => $value) {
      $this->setConfig($key, $value);
    }
    drupal_set_message(t('Liveblog post settings have been updated.'));
  }

}
