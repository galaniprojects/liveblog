<?php

namespace Drupal\liveblog\Tests;

use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\examples\Tests\ExamplesTestBase;

/**
 * Tests the basic functions of the Content Entity Example module.
 *
 * @package Drupal\liveblog\Tests
 *
 * @ingroup liveblog_post
 *
 * @group liveblog_post
 * @group examples
 */
class ContentEntityExampleTest extends ExamplesTestBase {

  public static $modules = array('liveblog_post', 'block', 'field_ui');

  /**
   * Basic tests for Content Entity Example.
   */
  public function testContentEntityExample() {
    $web_user = $this->drupalCreateUser(array(
      'add liveblog_post entity',
      'edit liveblog_post entity',
      'view liveblog_post entity',
      'delete liveblog_post entity',
      'administer liveblog_post entity',
      'administer liveblog_post display',
      'administer liveblog_post fields',
      'administer liveblog_post form display',
    ));

    // Anonymous User should not see the link to the listing.
    $this->assertNoText(t('Content Entity Example: LiveblogPosts Listing'));

    $this->drupalLogin($web_user);

    // Web_user user has the right to view listing.
    $this->assertLink(t('Content Entity Example: LiveblogPosts Listing'));

    $this->clickLink(t('Content Entity Example: LiveblogPosts Listing'));

    // WebUser can add entity content.
    $this->assertLink(t('Add Liveblog Post'));

    $this->clickLink(t('Add Liveblog Post'));

    $this->assertFieldByName('title[0][value]', '', 'Name Field, empty');
    $this->assertFieldByName('title[0][value]', '', 'First Name Field, empty');
    $this->assertFieldByName('title[0][value]', '', 'Gender Field, empty');

    $user_ref = $web_user->name->value . ' (' . $web_user->id() . ')';
    $this->assertFieldByName('user_id[0][target_id]', $user_ref, 'User ID reference field points to web_user');

    // Post content, save an instance. Go back to list after saving.
    $edit = array(
      'title[0][value]' => 'test title',
      'first_name[0][value]' => 'test first name',
      'gender' => 'male',
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Entity listed.
    $this->assertLink(t('Edit'));
    $this->assertLink(t('Delete'));

    $this->clickLink('test title');

    // Entity shown.
    $this->assertText(t('test title'));
    $this->assertText(t('test first name'));
    $this->assertText(t('male'));
    $this->assertLink(t('Add Liveblog Post'));
    $this->assertLink(t('Edit'));
    $this->assertLink(t('Delete'));

    // Delete the entity.
    $this->clickLink('Delete');

    // Confirm deletion.
    $this->assertLink(t('Cancel'));
    $this->drupalPostForm(NULL, array(), 'Delete');

    // Back to list, must be empty.
    $this->assertNoText('test title');

    // Settings page.
    $this->drupalGet('admin/structure/liveblog_post_settings');
    $this->assertText(t('Liveblog Post Settings'));

    // Make sure the field manipulation links are available.
    $this->assertLink(t('Settings'));
    $this->assertLink(t('Manage fields'));
    $this->assertLink(t('Manage form display'));
    $this->assertLink(t('Manage display'));
  }

  /**
   * Test all paths exposed by the module, by permission.
   */
  public function testPaths() {
    // Generate a liveblog_post so that we can test the paths against it.
    $liveblog_post = LiveblogPost::create(
      array(
        'title' => 'somename',
        'first_name' => 'Joe',
        'gender' => 'female',
      )
    );
    $liveblog_post->save();

    // Gather the test data.
    $data = $this->providerTestPaths($liveblog_post->id());

    // Run the tests.
    foreach ($data as $datum) {
      // drupalCreateUser() doesn't know what to do with an empty permission
      // array, so we help it out.
      if ($datum[2]) {
        $user = $this->drupalCreateUser(array($datum[2]));
        $this->drupalLogin($user);
      }
      else {
        $user = $this->drupalCreateUser();
        $this->drupalLogin($user);
      }
      $this->drupalGet($datum[1]);
      $this->assertResponse($datum[0]);
    }
  }

  /**
   * Data provider for testPaths.
   *
   * @param int $liveblog_post_id
   *   The id of an existing LiveblogPost entity.
   *
   * @return array
   *   Nested array of testing data. Arranged like this:
   *   - Expected response code.
   *   - Path to request.
   *   - Permission for the user.
   */
  protected function providerTestPaths($liveblog_post_id) {
    return array(
      array(
        200,
        '/liveblog_post/' . $liveblog_post_id,
        'view liveblog_post entity',
      ),
      array(
        403,
        '/liveblog_post/' . $liveblog_post_id,
        '',
      ),
      array(
        200,
        '/liveblog_post/list',
        'view liveblog_post entity',
      ),
      array(
        403,
        '/liveblog_post/list',
        '',
      ),
      array(
        200,
        '/liveblog_post/add',
        'add liveblog_post entity',
      ),
      array(
        403,
        '/liveblog_post/add',
        '',
      ),
      array(
        200,
        '/liveblog_post/' . $liveblog_post_id . '/edit',
        'edit liveblog_post entity',
      ),
      array(
        403,
        '/liveblog_post/' . $liveblog_post_id . '/edit',
        '',
      ),
      array(
        200,
        '/liveblog_post/' . $liveblog_post_id . '/delete',
        'delete liveblog_post entity',
      ),
      array(
        403,
        '/liveblog_post/' . $liveblog_post_id . '/delete',
        '',
      ),
      array(
        200,
        'admin/structure/liveblog_post_settings',
        'administer liveblog_post entity',
      ),
      array(
        403,
        'admin/structure/liveblog_post_settings',
        '',
      ),
    );
  }

  /**
   * Test add new fields to the liveblog_post entity.
   */
  public function testAddFields() {
    $web_user = $this->drupalCreateUser(array(
      'administer liveblog_post entity',
      'administer liveblog_post display',
      'administer liveblog_post fields',
      'administer liveblog_post form display',
    ));

    $this->drupalLogin($web_user);
    $entity_name = 'liveblog_post';
    $add_field_url = 'admin/structure/' . $entity_name . '_settings/fields/add-field';
    $this->drupalGet($add_field_url);
    $field_name = 'test_name';
    $edit = array(
      'new_storage_type' => 'list_string',
      'label' => 'test name',
      'field_name' => $field_name,
    );

    $this->drupalPostForm(NULL, $edit, t('Save and continue'));
    $expected_path = $this->buildUrl('admin/structure/' . $entity_name . '_settings/fields/' . $entity_name . '.' . $entity_name . '.field_' . $field_name . '/storage');

    // Fetch url without query parameters.
    $current_path = strtok($this->getUrl(), '?');
    $this->assertEqual($expected_path, $current_path, 'It should redirect to field storage settings page.');

  }

}
