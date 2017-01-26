<?php

namespace Drupal\liveblog\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a liveblog_post entity.
 *
 * @ingroup liveblog_post
 */
class LiveblogPostDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the liveblog post "%title"?', ['%title' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   *
   * If the delete command is canceled, return to the liveblog_post page.
   */
  public function getCancelUrl() {
    return $this->getEntity()->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * Delete the entity and log the event. logger() replaces the watchdog.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->delete();

    $this->logger('liveblog_post')->notice('@type: deleted %title.', [
      '@type' => $this->entity->bundle(),
      '%title' => $this->entity->label(),
    ]);

    // Redirect to the posts list.
    $form_state->setRedirect('view.liveblog_posts.liveblog_posts_admin');

    drupal_set_message(t('Liveblog post was successfully deleted.'));
  }

}
