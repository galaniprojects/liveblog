<?php

namespace Drupal\liveblog\Utility;

use Drupal\liveblog\Entity\LiveblogPost;

/**
 * Utility for liveblog post payload.
 *
 * @todo: Convert to a trait or service.
 */
class Payload {

  /**
   * The liveblog post entity.
   *
   * @var \Drupal\liveblog\Entity\LiveblogPost $entity
   */
  protected $entity;

  /**
   * Constructors an instance.
   *
   * @param \Drupal\liveblog\Entity\LiveblogPost $entity
   *   The liveblog post entity.
   */
  protected function __construct(LiveblogPost $entity) {
    $this->entity = $entity;
  }

  /**
   * Constructors an instance.
   *
   * @param \Drupal\liveblog\Entity\LiveblogPost $entity
   *   The liveblog post entity.
   *
   * @return self
   *   Instance of the class.
   */
  public static function create(LiveblogPost $entity) {
    return new static($entity);
  }

  /**
   * Gets a rendered payload from the liveblog post entity.
   *
   * @return array
   *   The payload array.
   */
  public function getRenderedPayload() {
    $entity = $this->entity;

    $rendered_entity = $this->entityTypeManager()->getViewBuilder('liveblog_post')->view($entity);
    $output = $this->getRenderer()->render($rendered_entity);

    $data['id'] = $entity->id();
    $data['uuid'] = $entity->uuid();
    $data['title'] = $entity->get('title')->value;
    $data['liveblog'] = $entity->getLiveblog()->id();
    $data['body__value'] = $entity->body->value;
    $data['highlight'] = $entity->highlight->value;
    $data['location'] = $entity->location->value;
    $data['source__uri'] = ($entity->source->first() && $entity->source->first()->uri) ? $entity->source->first()->getUrl()->toString() : NULL;
    $data['uid'] = $entity->getAuthor() ? $entity->getAuthor()->getAccountName() : NULL;
    $data['changed'] = $entity->changed->value;
    $data['created'] = $entity->created->value;
    $data['status'] = $entity->status->value;
    $data += $output;

    return $data;
  }

  /**
   * Gets payload from the liveblog post entity.
   *
   * @return array
   *   The payload array.
   */
  public function getPayload() {
    $entity = $this->entity;

    $rendered_entity = $this->entityTypeManager()->getViewBuilder('liveblog_post')->view($entity);

    $data['id'] = $entity->id();
    $data['uuid'] = $entity->uuid();
    $data['title'] = $entity->get('title')->value;
    $data['liveblog'] = $entity->getLiveblog()->id();
    $data['body__value'] = $entity->body->value;
    $data['highlight'] = $entity->highlight->value;
    $data['location'] = $entity->location->value;
    $data['source__uri'] = ($entity->source->first() && $entity->source->first()->uri) ? $entity->source->first()->getUrl()->toString() : NULL;
    $data['uid'] = $entity->getAuthor() ? $entity->getAuthor()->getAccountName() : NULL;
    $data['changed'] = $entity->changed->value;
    $data['created'] = $entity->created->value;
    $data['status'] = $entity->status->value;
    $data['content'] = $rendered_entity;

    return $data;
  }

  /**
   * Returns the render API renderer.
   *
   * @return \Drupal\liveblog\LiveblogRenderer
   */
  protected function getRenderer() {
    return \Drupal::service('liveblog.renderer');
  }

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected function entityTypeManager() {
    return \Drupal::entityTypeManager();
  }

}
