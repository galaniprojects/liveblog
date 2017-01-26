<?php

namespace Drupal\Tests\liveblog\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for the liveblog post edit form service.
 *
 * @group Liveblog
 */
class LiveBlogEditFormTest extends BrowserTestBase  {

  /**
   * Ignore some config schema errors of modules used.
   *
   * @var array
   */
  protected static $configSchemaCheckerExclusions = [
    'core.entity_view_display.liveblog_post.liveblog_post.default',
  ];

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'liveblog',
  ];

  /**
   * The liveblog node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * A liveblog post.
   *
   * @var \Drupal\liveblog\LiveblogPostInterface
   */
  protected $post;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->node = Node::create([
      'title' => 'Test blog',
      'type' => 'liveblog'
    ]);
    $this->node->save();

    $this->post = LiveblogPost::create([
      'liveblog' => $this->node->id(),
      'title' => 'Title post 1',
      'body' => 'Body post 1',
    ]);
    $this->post->save();
  }

  /**
   * Tests using the liveblog-edit-form route.
   */
  public function testLiveBlogEditFormRoute() {
    // Make sure things do not work as anonymous user.
    $this->drupalGet("liveblog_post/{$this->post->id()}/edit", ['query' => ['_format' => 'json']]);
    $this->assertSession()->statusCodeEquals(403);

    // Login an make sure things work.
    $this->account = $this->drupalCreateUser([
      'bypass node access',
      'edit liveblog_post entity',
    ]);
    $this->drupalLogin($this->account);

    $content = $this->drupalGet("liveblog_post/{$this->post->id()}/edit", ['query' => ['_format' => 'json']]);
    $this->assertSession()->statusCodeEquals(200);

    $this->assertJson($content, 'Response is json');
    $result = Json::decode($content);

    $this->assertArrayHasKey('commands', $result);
    $this->assertArrayHasKey('content', $result);
    $this->assertArrayHasKey('content', $result);
    $this->assertArrayHasKey('libraries', $result);

    $this->assertContains('</form>', $result['content']);
    $this->assertContains('Title post 1', $result['content']);
    $this->assertContains('Body post 1', $result['content']);
  }

}
