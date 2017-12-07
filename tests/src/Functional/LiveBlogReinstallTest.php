<?php

namespace Drupal\Tests\liveblog\Functional;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for the liveblog module.
 *
 * @group Liveblog
 */
class LiveBlogReinstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dblog', 'liveblog'];

  /**
   * Tests re-installing the live blog module and the config after install.
   */
  public function testReinstall() {
    $this->drupalLogin($this->createUser(['administer modules']));
    $query = db_select('watchdog', 'w');
    $condition = $query->orConditionGroup()
      ->condition('severity', RfcLogLevel::ERROR)
      ->condition('severity', RfcLogLevel::CRITICAL);
    $count = $query
      ->condition($condition)
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(0, $count, 'There are no errors before uninstalling Liveblog');

    // Uninstall and re-install via the UI like a real user.
    $edit = [];
    $edit['uninstall[liveblog]'] = TRUE;
    $this->drupalPostForm('admin/modules/uninstall', $edit, t('Uninstall'));
    $this->drupalPostForm(NULL, [], t('Uninstall'));
    $this->rebuildContainer();

    $this->drupalPostForm('admin/modules', ['modules[liveblog][enable]' => "1"], t('Install'));
    $this->rebuildContainer();

    $count = db_select('watchdog', 'w')
      ->condition($condition)
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(0, $count, 'There are no errors after uninstalling and re-installing Liveblog');
  }

}
