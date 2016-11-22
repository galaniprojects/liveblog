<?php

namespace Drupal\liveblog\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\liveblog\Entity\LiveblogPost;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller providing the resource for the form.
 */
class LiveblogController extends ControllerBase {

  /**
   * Returns a liveblog post form wrapped in a json response.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response containing the liveblog post form.
   */
  public function getFormAsJson(LiveblogPost $liveblog_post) {
    $form_object = $this->entityTypeManager()
      ->getFormObject('liveblog_post', 'edit')
      ->setEntity($liveblog_post);
    $content = $this->formBuilder()->getForm($form_object);

    /** @var \Drupal\liveblog\LiveblogRendererInterface $renderer */
    $renderer = \Drupal::service('liveblog.renderer');
    return new JsonResponse($renderer->render($content));
  }

}
