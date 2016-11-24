<?php

namespace Drupal\liveblog;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Render\RendererInterface;

/**
 * Renders content in a special way.
 */
class LiveblogRenderer implements LiveblogRendererInterface {

  /**
   * The ajax attachment processor.
   *
   * @var \Drupal\Core\Ajax\AjaxResponseAttachmentsProcessor
   */
  protected $ajaxResponseAttachmentsProcessor;

  /**
   * The renderer to use.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs the object.
   */
  public function __construct(LiveblogAjaxResponseAttachmentsProcessor $attachmentsProcessor, RendererInterface $renderer) {
    $this->ajaxResponseAttachmentsProcessor = $attachmentsProcessor;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc]
   */
  public function render(array &$content) {
    // Render the form and convert attachments into ajax commands.
    $rendered = $this->renderer->renderRoot($content);

    return [
      'commands' => isset($content['#attached']) ? $this->getCommandsForAttachments($content['#attached']) : [],
      'content' => $rendered,
      'libraries' => $this->ajaxResponseAttachmentsProcessor->getLibraries(),
    ];
  }

  /**
   * Turns render attachments into ajax commands.
   *
   * @param array $attachments
   *   The attachments array, i.e. #attached in a render array.
   *
   * @return \Drupal\Core\Ajax\CommandInterface[]
   *   The array of commands.
   */
  protected function getCommandsForAttachments(array $attachments) {
    $response = (new AjaxResponse())
      ->setAttachments($attachments);

    // Take care of adding messages if something generated some.
    $status_messages = array('#type' => 'status_messages');
    $output = $this->renderer->renderRoot($status_messages);
    if (!empty($output)) {
      $response->addCommand(new PrependCommand(NULL, $output));
    }

    $this->ajaxResponseAttachmentsProcessor->processAttachments($response);
    return $response->getCommands();
  }

}
