<?php

namespace Drupal\liveblog;

use Drupal\liveblog\Entity\LiveblogPost;

/**
 * Pusher payload renderer interface.
 */
interface PusherPayloadRendererInterface {

  /**
   * Gets a rendered payload from the liveblog post entity.
   *
   * @param \Drupal\liveblog\Entity\LiveblogPost $entity
   *   Liveblog post.
   *
   * @return array
   *   The payload array.
   */
  public function getRenderedPayload(LiveblogPost $entity);

  /**
   * Gets payload from the liveblog post entity.
   *
   * @param \Drupal\liveblog\Entity\LiveblogPost $entity
   *   Liveblog post.
   *
   * @return array
   *   The payload array.
   */
  public function getPayload(LiveblogPost $entity);

  /**
   * If the renderer is marked as default. Last marked default renderer is used.
   *
   * @return bool
   *   If the renderer is default.
   */
  public function isDefault();

}
