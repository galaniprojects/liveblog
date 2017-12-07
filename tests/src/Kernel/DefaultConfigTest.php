<?php

namespace Drupal\Tests\liveblog\Kernel;

use Drupal\KernelTests\Config\DefaultConfigTest as CoreDefaultConfigTest;

/**
 * Tests that the installed config matches the default config.
 *
 * @group Liveblog
 */
class DefaultConfigTest extends CoreDefaultConfigTest {

  /**
   * Tests if installed config is equal to the exported config.
   */
  public function testModuleConfig($module = NULL) {
    parent::testModuleConfig('liveblog');
  }

}
