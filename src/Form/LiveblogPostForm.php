<?php

namespace Drupal\liveblog\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\liveblog\NotificationChannel\NotificationChannelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form controller for the liveblog_post entity edit forms.
 *
 * @ingroup liveblog_post
 */
class LiveblogPostForm extends ContentEntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The notification channel manager.
   *
   * @var \Drupal\liveblog\NotificationChannel\NotificationChannelManager
   */
  protected $notificationChannelManager;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack
   *   The current request.
   * @param \Drupal\liveblog\NotificationChannel\NotificationChannelManager $notification_channel_manager
   *   The notification channel service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, RequestStack $request_stack, NotificationChannelManager $notification_channel_manager) {
    parent::__construct($entity_manager);
    $this->request = $request_stack->getCurrentRequest();
    $this->notificationChannelManager = $notification_channel_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('request_stack'),
      $container->get('plugin.manager.liveblog.notification_channel')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    parent::prepareEntity();

    // Pre-populate liveblog reference if we are at the liveblog page.
    if (!$this->entity->liveblog->entity && $node = $this->request->attributes->get('node')) {
      if ($node->getType() == 'liveblog') {
        $this->entity->setLiveblog($node);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $rebuild_html_id = "{$this->getFormId()}-wrapper";
    $form['#prefix'] = "<div id=\"$rebuild_html_id\">";
    $form['#suffix'] = '</div>';

    // Hide author and liveblog fields, as they are already pre-populated and
    // should not be changed.
    $form['uid']['#access'] = FALSE;
    $form['liveblog']['#access'] = FALSE;

    // On the node view page, enable ajax for submitting the form.
    if ($this->request->attributes->get('node')) {
      $form['#attached']['library'][] = 'liveblog/form_improvements';

      $form['actions']['submit']['#ajax'] = [
        'wrapper' => $rebuild_html_id,
        'callback' => array($this, 'ajaxRebuildCallback'),
        'effect' => 'fade',
      ];
    }

    // Hide status, source, location fields in a collapsible wrapper.
    $form['additional'] = array(
      '#type' => 'details',
      '#title' => t('Additional'),
      '#weight' => 20,
    );
    foreach (['status', 'source', 'location'] as $key) {
      $form['additional'][$key] = $form[$key];
      unset($form[$key]);
    }

    // Show preview div if enabled.
    if ($this->config('liveblog.liveblog_post.settings')->get('preview')) {
      $form['preview'] = [
        '#type' => 'container',
        '#attributes' => ['id' => "{$this->getFormId()}-preview"],
        '#weight' => 100,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if ($this->config('liveblog.liveblog_post.settings')->get('preview')) {
      $actions['preview'] = array(
        '#type' => 'submit',
        '#value' => t('Preview'),
        '#submit' => array('::submitForm', '::preview'),
        '#ajax' => [
          'wrapper' => "{$this->getFormId()}-preview",
          'callback' => array($this, 'ajaxPreviewCallback'),
          'effect' => 'fade',
        ],
      );
    }

    // Show a cancel button on the node page.
    if ($this->request->attributes->get('node')) {
      $actions['cancel'] = array(
        '#type' => 'button',
        '#value' => t('Cancel'),
        '#ajax' => [
          'wrapper' => "{$this->getFormId()}-wrapper",
          'callback' => array($this, 'ajaxCancelCallback'),
          'effect' => 'fade',
        ],
      );
    }

    $actions['submit']['#value'] = $this->entity->isNew() ? t('Create') : t('Update');
    return $actions;
  }

  /**
   * Callback for ajax form submission.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render element to replace the form with.
   */
  public function ajaxRebuildCallback(array $form, FormStateInterface $form_state) {
    // Hide the form after editing on the node page.
    if ($this->getOperation() == 'edit' && $this->request->attributes->get('node')) {
      $html_id = "{$this->getFormId()}-wrapper";
      $element = ['#markup' => "<div id=\"$html_id\"></div>"];
      return $element;
    }
    return $form;
  }

  /**
   * Callback for ajax form submission preview.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The rebuilt form.
   */
  public function ajaxPreviewCallback(array $form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $preview = $this->entityTypeManager->getViewBuilder('liveblog_post')->view($this->entity);
      $form['preview']['content'] = $preview;
    }
    return $form['preview'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();

    if ($this->getOperation() == 'edit') {
      drupal_set_message(t('Liveblog post was successfully updated.'));
    }
    elseif ($this->getOperation() == 'add') {
      drupal_set_message(t('Liveblog post was successfully created.'));
    }

    // Trigger an notification channel message.
    if ($plugin = $this->notificationChannelManager->createActiveInstance()) {
      $plugin->triggerLiveblogPostEvent($this->entity, $this->getOperation());
    }

    // Redirect to the post's full page if we are not at the liveblog page.
    if (!$this->request->attributes->get('node')) {
      $url = $this->entity->toUrl();
      $form_state->setRedirect($url->getRouteName(), $url->getRouteParameters());
    }
    else if ($this->getOperation() == 'add') {
      // Clear form input fields for the add form, as we stay on the same page.
      $this->clearFormInput($form, $form_state);
    }
  }

  /**
   * Form submission handler for the 'preview' action.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function preview(array $form, FormStateInterface $form_state) {
    // Rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Clears form input.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function clearFormInput(array $form, FormStateInterface $form_state) {
    // Rebuild the form.
    $form_state->setRebuild();
    // Replace the form entity with an empty instance.
    $this->entity = $this->entityTypeManager->getStorage('liveblog_post')->create([
      'liveblog' => $this->entity->liveblog->target_id,
    ]);

    // Clear user input.
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';
    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys) && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }
    $form_state->setUserInput($input);
  }

}
