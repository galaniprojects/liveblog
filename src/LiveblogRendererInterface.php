<?php

namespace Drupal\liveblog;

/**
 * Renders content into an array containing ajax commands and html.
 */
interface LiveblogRendererInterface {

  /**
   * Renders a render array into html and ajax commands.
   *
   * @return array
   *   An array with the following keys and values:
   *   - html: The rendered html.
   *   - commands: An array of ajax commands.
   */
  public function render(array $content);

}
