<?php

namespace Drupal\liveblog_pusher;

/**
 * Logger class for Pusher notifications channel.
 */
class PusherLogger implements PusherLoggerInterface {

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The collected log messages.
   *
   * @var string[]
   */
  protected $messages;

  /**
   * PusherLogger constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger_factory
   *   The logger factory.
   */
  public function __construct($logger_factory) {
    $this->logger = $logger_factory->get('liveblog_pusher');
  }

  /**
   * {@inheritdoc}
   */
  public function log($message) {
    $this->messages[] = $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessages() {
    return $this->messages;
  }

  /**
   * {@inheritdoc}
   */
  public function saveLog($type = 'notice') {
    $log = implode('<br>', $this->messages);
    switch ($type) {
      case 'info':
        $log = 'Pusher request log:<br>' . $log;
        $this->logger->info($log);
        break;
      case 'warning':
        $log = 'Pusher request log:<br>' . $log;
        $this->logger->warning($log);
        break;
      case 'error':
        $log = 'Failed to send a message to Pusher. See the request log:<br>' . $log;
        $this->logger->error($log);
        break;
      case 'notice':
      default:
        $log = 'Pusher request log:<br>' . $log;
        $this->logger->notice($log);
        break;
    }
  }

}
