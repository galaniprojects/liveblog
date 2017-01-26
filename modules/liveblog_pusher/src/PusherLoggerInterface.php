<?php

namespace Drupal\liveblog_pusher;

interface PusherLoggerInterface {

  /**
   * Logs a message.
   *
   * @param string $message
   *   The message text.
   */
  public function log($message);

  /**
   * Returns collected log messages.
   *
   * @return string[]
   *   The collected log messages.
   */
  public function getMessages();

  /**
   * Saves the log.
   *
   * @param string $type
   *   The log type. E.g.: 'info', 'notice', 'error', 'warning'.
   */
  public function saveLog($type = 'notice');

}
