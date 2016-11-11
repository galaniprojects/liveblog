<?php

namespace Drupal\liveblog\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
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
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack
   *   The current request.
   */
  public function __construct(EntityManagerInterface $entity_manager, RequestStack $request_stack) {
    parent::__construct($entity_manager);
    $this->request = $request_stack->getCurrentRequest();
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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\liveblog\Entity\LiveblogPost */
    $entity = $this->entity;
    if ($node = $this->getCurrentLiveblogNode()) {
      // Pre-populate liveblog reference if we are at the liveblog page.
      $entity->setLiveblog($node);
    }

    $form = parent::buildForm($form, $form_state);

    if ($node) {
      // Hide author and liveblog fields, as they are already pre-populated and
      // should not be changed.
      $form['uid']['#access'] = FALSE;
      $form['liveblog']['#access'] = FALSE;
    }

    if ($entity->isNew()) {
      $form['actions']['submit']['#value'] = t('Create');
    }
    else {
      $form['actions']['submit']['#value'] = t('Update');
    }

    return $form;
  }

  /**
   * Gets liveblog node from the current request.
   *
   * Gets the liveblog node from the request if we are at the liveblog page.
   *
   * @return \Drupal\node\Entity\Node|null
   *   The liveblog node, null if not found.
   */
  protected function getCurrentLiveblogNode() {
    /* @var \Drupal\node\Entity\Node $node */
    $node = $this->request->attributes->get('node');
    if ($node && $node->getType() == 'liveblog') {
      return $node;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->save();
    $url = $entity->toUrl();

    if (!$this->getCurrentLiveblogNode()) {
      // Redirect to the post's full page if we are not at the liveblog page.
      $form_state->setRedirect($url->getRouteName(), $url->getRouteParameters());
    }
  }

}
