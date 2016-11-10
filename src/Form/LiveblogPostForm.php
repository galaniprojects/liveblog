<?php

namespace Drupal\liveblog\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\liveblog\NotificationChannel\NotificationChannelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the liveblog_post entity edit forms.
 *
 * @ingroup liveblog_post
 */
class LiveblogPostForm extends ContentEntityForm {

  /**
   * The notification channel manager.
   *
   * @var \Drupal\liveblog\NotificationChannel\NotificationChannelManager
   */
  protected $notificationChannelManager;

  /**
   * Constructs an EntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\liveblog\NotificationChannel\NotificationChannelManager $notification_channel_manager
   *   The notification channel service.
   */
  public function __construct(EntityManagerInterface $entity_manager, NotificationChannelManager $notification_channel_manager) {
    parent::__construct($entity_manager);
    $this->notificationChannelManager = $notification_channel_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.liveblog.notification_channel')
    );
  }

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

    $event = $entity->isNew() ? 'created' : 'updated';

    $entity->save();
    $url = $entity->toUrl();

    // Redirect to the post's full page.
    $form_state->setRedirect($url->getRouteName(), $url->getRouteParameters());

    // Trigger an notification channel message.
    if ($plugin = $this->notificationChannelManager->createActiveInstance()) {
      $plugin->triggerLiveblogPostEvent($entity, $event);
    }
  }

}
