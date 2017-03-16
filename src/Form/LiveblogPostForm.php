<?php

namespace Drupal\liveblog\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack
   *   The current request.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, RequestStack $request_stack) {
    parent::__construct($entity_manager);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * Determines whether we are on the liveblog node page.
   *
   * @return bool
   *   Whether we are on the liveblog page.
   */
  public function isLiveBlogNodePage() {
    return $this->requestStack->getCurrentRequest()->attributes->get('node');
  }

  /**
   * Determines whether the JSON edit-form was requested.
   *
   * @return bool
   *   Whether the JSON edit-form was requested.
   */
  public function isJSONEditForm() {
    return $this->requestStack->getCurrentRequest()->attributes->get('_route') == 'entity.liveblog_post.edit_form_json';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    parent::prepareEntity();

    // Pre-populate liveblog reference if we are at the liveblog page.
    if (!$this->entity->liveblog->entity && $node = $this->isLiveBlogNodePage()) {
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

    $form['#prefix'] = "<div id=\"{$this->getFormRebuildWrapperId()}\">";
    $form['#suffix'] = '</div>';

    // On the node view page, enable ajax for submitting the form.
    if ($this->isLiveBlogNodePage() || $this->isJSONEditForm()) {
      $form['#attached']['library'][] = 'liveblog/form_improvements';

      $form['actions']['submit']['#ajax'] = [
        'wrapper' => $this->getFormRebuildWrapperId(),
        'callback' => '::ajaxRebuildCallback',
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
        '#attributes' => ['id' => $this->getFormPreviewWrapperId()],
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
          'wrapper' => $this->getFormPreviewWrapperId(),
          'callback' => '::ajaxPreviewCallback',
          'effect' => 'fade',
        ],
      );
    }

    // Show a cancel button on the node page.
    if ($this->isJSONEditForm() && $this->getOperation() == 'edit') {
      $actions['cancel'] = array(
        '#type' => 'button',
        '#value' => t('Cancel'),
        '#ajax' => [
          'wrapper' => $this->getFormRebuildWrapperId(),
          'callback' => '::ajaxRebuildCallback',
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
    if ($this->getOperation() == 'edit' && $this->isJSONEditForm()) {
      return ['#markup' => ''];
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
      // There is a bug which triggers nested entity values (embeded tweet urls)
      // are lost, during serialization of the form cache in
      // \Drupal\Core\Form\FormCache::setCache(). To work-a-round this bug, we
      // have to build a recent entity again.
      // @todo: Link core issue here.
      $this->entity = $this->buildEntity($form, $form_state);
      $preview = $this->entityTypeManager->getViewBuilder('liveblog_post')->view($this->entity);
      $form['preview']['content'] = $preview;
    }
    return $form['preview'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    try {
      $this->entity->save();
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      $form_state->disableRedirect();
      $this->clearEntity();
      return;
    }

    if ($this->getOperation() == 'edit') {
      drupal_set_message(t('Liveblog post was successfully updated.'));
    }
    elseif ($this->getOperation() == 'add') {
      drupal_set_message(t('Liveblog post was successfully created.'));
    }

    // Redirect to the post's full page if we are not at the liveblog page.
    if (!($this->isLiveBlogNodePage() || $this->isJSONEditForm())) {
      $url = $this->entity->toUrl();
      $form_state->setRedirect($url->getRouteName(), $url->getRouteParameters());
    }
    else if ($this->getOperation() == 'add') {
      // Clear form input fields for the add form, as we stay on the same page.
      $this->clearFormInput($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\liveblog\NotificationChannel\NotificationChannelPluginBase $plugin */
    if ($plugin = $this->getNotificationChannelManager()->createActiveInstance()) {
      $plugin->validateLiveblogPostForm($form, $form_state, $this->buildEntity($form, $form_state));
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

    $this->clearEntity();

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
    $form_state->setStorage([]);
  }

  /**
   * Replaces the form entity with an empty instance.
   */
  protected function clearEntity() {
    $this->entity = $this->entityTypeManager->getStorage('liveblog_post')->create([
      'liveblog' => $this->entity->liveblog->target_id,
    ]);
  }

  /**
   * Gets wrapper id for the rebuild form ajax callback.
   *
   * @return string
   *   Wrapper id.
   */
  protected function getFormRebuildWrapperId() {
    $wrapper = [];
    $wrapper[] = $this->getFormId();
    if ($id = $this->getEntity()->id()) {
      $wrapper[] = "id-{$id}";
    }
    $wrapper[] = 'wrapper';
    $result = implode('-', $wrapper);

    return $result;
  }

  /**
   * Gets wrapper id for the preview form ajax callback.
   *
   * @return string
   *   Wrapper id.
   */
  protected function getFormPreviewWrapperId() {
    $wrapper = [];
    $wrapper[] = $this->getFormId();
    if ($id = $this->getEntity()->id()) {
      $wrapper[] = "id-{$id}";
    }
    $wrapper[] = 'preview';
    $result = implode('-', $wrapper);

    return $result;
  }

  /**
   * Gets the notification channel plugin manager.
   *
   * @return \Drupal\liveblog\NotificationChannel\NotificationChannelManager
   *   Notification channel plugin manager.
   */
  protected function getNotificationChannelManager() {
    return \Drupal::service('plugin.manager.liveblog.notification_channel');
  }

}
