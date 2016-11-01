<?php

namespace Drupal\liveblog\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the liveblog_post entity edit forms.
 *
 * @ingroup liveblog_post
 */
class LiveblogPostForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\liveblog\Entity\LiveblogPost */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    // @todo Implement ajax logic here.

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->save();
    $url = $entity->toUrl();

    // Redirect to the post's full page.
    $form_state->setRedirect($url->getRouteName(), $url->getRouteParameters());
  }

}
