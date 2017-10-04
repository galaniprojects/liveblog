<?php

namespace Drupal\liveblog;

/**
 * Pusher payload renderer provider.
 */
class PusherPayloadRendererProvider {

  /**
   * Default renderer to be used to render Pusher payload.
   *
   * @var \Drupal\liveblog\PusherPayloadRendererInterface
   */
  protected $defaulRenderer;

  /**
   * List of registered Pusher payload renderers.
   *
   * @var \Drupal\liveblog\PusherPayloadRendererInterface[]
   */
  protected $payloadRenderers;

  /**
   * Registers Pusher payload renderer.
   *
   * @param \Drupal\liveblog\PusherPayloadRendererInterface $payloadRenderer
   *   Payload renderer to register.
   */
  public function addPayloadRenderer(PusherPayloadRendererInterface $payloadRenderer) {
    $this->payloadRenderers[] = $payloadRenderer;
    if ($payloadRenderer->isDefault()) {
      $this->defaulRenderer = $payloadRenderer;
    }
  }

  /**
   * Gets Pusher payload renderer.
   *
   * @param mixed $key
   *   Key of the payload renderer to return.
   *
   * @throws \Exception
   *   Could not provide payload renderer.
   *
   * @return \Drupal\liveblog\PusherPayloadRendererInterface
   *   Payload renderer.
   */
  public function getPayloadRenderer($key = NULL) {
    // First return payload renderer under specific key in the list.
    if (isset($this->payloadRenderers[$key])) {
      $this->payloadRenderers[$key];
    }
    // Otherwise return the default renderer.
    elseif (isset($this->defaulRenderer)) {
      return $this->defaulRenderer;
    }

    throw new \Exception('Could not provide payload renderer.');
  }

}
