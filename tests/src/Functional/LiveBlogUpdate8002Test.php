<?php

namespace Drupal\Tests\liveblog\Functional;

use Drupal\Core\Config\Entity\ConfigEntityDependency;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for adding the 'revision_translation_affected' field.
 *
 * @group liveblog
 */
class LiveBlogUpdate8002Test extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../fixtures/drupal-8.4.0-with-liveblog-installed.php.gz',
    ];
  }

  /**
   * Tests fixing dependencies.
   *
   * @see liveblog_update_8002()
   */
  public function testFixingDependencies() {
    $fixed_config = [
      'field.storage.node.field_highlights',
      'field.storage.node.field_posts_load_limit',
      'field.storage.node.field_posts_number_initial',
      'field.storage.node.field_status',
      'node.type.liveblog',
      'taxonomy.vocabulary.highlights',
    ];
    // Check state before running updates.
    foreach ($fixed_config as $name) {
      $this->assertNull($this->config($name)->get('dependencies.enforced.module'), '$name does not have a forced dependency on liveblog');
    }

    $this->runUpdates();

    // Check state after running updates.
    $dependents = \Drupal::service('config.manager')->findConfigEntityDependents('module', ['liveblog']);
    $dependent_names = array_map(function (ConfigEntityDependency $entity) {
      return $entity->getConfigDependencyName();
    }, $dependents);
    foreach ($fixed_config as $name) {
      $this->assertTrue(in_array('liveblog', $this->config($name)->get('dependencies.enforced.module'), TRUE), '$name has a forced dependency on liveblog');
      $this->assertTrue(in_array($name, $dependent_names, TRUE), "$name is dependent on the liveblog module");
    }
  }

}
