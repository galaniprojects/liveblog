<?php

namespace Drupal\liveblog\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\liveblog\Utility\Payload;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller providing the resource for the liveblog posts list.
 */
class LiveblogListController extends ControllerBase {

  /**
   * Returns a liveblog post form wrapped in a json response.
   *
   * @todo Add render caching.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The liveblog node.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response containing the liveblog post form.
   */
  public function getList(Node $node) {
    if ($node->bundle() != 'liveblog') {
      throw new NotFoundHttpException();
    }

    $request = $this->getRequest();
    $items_per_page = (int) $request->get('items_per_page') ?: 10;
    $created_op = $request->get('created_op') == '<' ? $request->get('created_op') : '>';
    $sort_order = $request->get('sort_order') == 'DESC' ? ' DESC' : 'ASC';
    $timestamp = (int) $request->get('created') ?: 0;

    $query = $this->getEntityQuery('liveblog_post');
    $query->condition('status', 1);
    $query->condition('created', $timestamp, $created_op);
    $query->condition('liveblog.entity.nid', $node->id());
    $query->sort('created', $sort_order);
    $query->range(0, $items_per_page);
    $ids = $query->execute();

    $storage = $this->getEntityTypeManager()->getStorage('liveblog_post');
    $entities = $storage->loadMultiple($ids);

    if (!$entities) {
      return new JsonResponse([]);
    }

    $content = $render_array = [];
    /* @var \Drupal\liveblog\Entity\LiveblogPost[] $entities */
    foreach ($entities as $entity) {
      $result = Payload::create($entity)->getPayload();

      // Collect all the render arrays. Will be used later to get the libraries
      // and commands needed for the frontend.
      $render_array[] = $result['content'];

      // Render each post separately to prepare a list item content.
      $result['content'] = $this->getRenderer()->render($result['content']);
      $content[] = $result;
    }

    /** @var \Drupal\liveblog\LiveblogRendererInterface $renderer */
    $renderer = \Drupal::service('liveblog.renderer');
    // Render all the posts together to get the libraries and commands.
    $result = $renderer->render($render_array);

    $result['content'] = $content;

    return new JsonResponse($result);
  }

  /**
   * Returns current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The currently active request object.
   */
  protected function getRequest() {
    return \Drupal::request();
  }

  /**
   * Returns query service.
   *
   * @param string $type
   *   The entity type.
   * @return \Drupal\Core\Entity\Query\Sql\Query
   *   Entity query.
   */
  protected function getEntityQuery($type) {
    return \Drupal::service('entity.query')->get($type);
  }

  /**
   * GEts the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function getEntityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  /**
   * Returns the render API renderer.
   *
   * @return \Drupal\liveblog\LiveblogRenderer
   *   The render API renderer.
   */
  protected function getRenderer() {
    return \Drupal::service('renderer');
  }

}
