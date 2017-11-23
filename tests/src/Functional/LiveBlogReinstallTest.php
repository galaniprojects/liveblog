<?php

namespace Drupal\Tests\liveblog\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for the liveblog module.
 *
 * @group Liveblog
 */
class LiveBlogReinstallTest extends BrowserTestBase  {

  /**
   * Ignore some config schema errors of modules used.
   *
   * @var array
   */
  protected static $configSchemaCheckerExclusions = [
    'core.entity_view_display.liveblog_post.liveblog_post.default',
  ];

  /**
   * Tests re-installing the live blog module and the config after install.
   */
  public function testReinstall() {
    $this->container->get('module_installer')->install(['liveblog']);
    $this->container->get('module_installer')->uninstall(['liveblog']);
    $this->container->get('module_installer')->install(['liveblog']);
  }

}
