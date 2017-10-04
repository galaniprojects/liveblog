<?php

namespace Drupal\liveblog;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\liveblog\Entity\LiveblogPost;

/**
 * Default Pusher payload renderer.
 */
class PusherPayloadRendererDefault implements PusherPayloadRendererInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Renderer to render Liveblog entity.
   *
   * @var \Drupal\liveblog\LiveblogRenderer
   */
  private $liveblogRenderer;

  /**
   * Constructors an instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\liveblog\LiveblogRenderer $liveblogRenderer
   *   Liveblog entity renderer.
   */
  public function __construct(EntityTypeManager $entityTypeManager, LiveblogRenderer $liveblogRenderer) {
    $this->entityTypeManager = $entityTypeManager;
    $this->liveblogRenderer = $liveblogRenderer;
  }

  /**
   * @inheritdoc
   */
  public function isDefault() {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function getRenderedPayload(LiveblogPost $entity) {
    $rendered_entity = $this->entityTypeManager->getViewBuilder('liveblog_post')->view($entity);
    $output = $this->liveblogRenderer->render($rendered_entity);

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
   * @inheritdoc
   */
  public function getPayload(LiveblogPost $entity) {
    $rendered_entity = $this->entityTypeManager->getViewBuilder('liveblog_post')->view($entity);

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

}
