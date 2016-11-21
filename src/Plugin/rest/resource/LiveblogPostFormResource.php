<?php

namespace Drupal\liveblog\Plugin\rest\resource;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AjaxResponseAttachmentsProcessor;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource for liveblog post form.
 *
 * @RestResource(
 *   id = "liveblog_post_form",
 *   label = @Translation("Liveblog post form"),
 *   uri_paths = {
 *     "canonical" = "/liveblog_post/{id}/form"
 *   }
 * )
 */
class LiveblogPostFormResource extends ResourceBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns a liveblog post form specified ID.
   *
   * @param int $id
   *   ID of the liveblog post entity.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response containing the liveblog post form.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($id = NULL) {
    if ($id) {
      if ($entity = $this->entityTypeManager->getStorage('liveblog_post')->load($id)) {
        $form_object = $this->entityTypeManager
          ->getFormObject('liveblog_post', 'edit')
          ->setEntity($entity);
        $render = \Drupal::formBuilder()->getForm($form_object);

        // Render the form and convert attachments into ajax commands.
        $html = \Drupal::service('renderer')->renderRoot($render);

        $response = (new AjaxResponse())
          ->setAttachments($render['#attached']);

        // Take care of adding messages if something generated some.
        $status_messages = array('#type' => 'status_messages');
        $output = \Drupal::service('renderer')->renderRoot($status_messages);
        if (!empty($output)) {
          $response->addCommand(new PrependCommand(NULL, $output));
        }

        /** @var AjaxResponseAttachmentsProcessor $processor */
        $processor = \Drupal::service('ajax_response.attachments_processor');
        $processor->processAttachments($response);

        $result = array(
          'commands' => $response->getCommands(),
          'form' => $html
        );
        return new JsonResponse($result);
      }

      throw new NotFoundHttpException(t('Entity with ID @id was not found', array('@id' => $id)));
    }

    throw new BadRequestHttpException(t('No entity ID was provided'));
  }

}
