<?php

namespace Drupal\Tests\liveblog\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for the liveblog list service.
 *
 * @group Liveblog
 */
class LiveBlogListTest extends BrowserTestBase  {

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->node = Node::create([
      'title' => 'Test blog',
      'type' => 'liveblog'
    ]);
    $this->node->save();

    LiveblogPost::create([
      'liveblog' => $this->node->id(),
      'title' => 'Title post 1',
      'body' => 'Body post 1',
    ])->save();
  }

  /**
   * Tests using the liveblog-list route.
   */
  public function testLiveBlogListRoute() {
    $content = $this->drupalGet("liveblog/{$this->node->id()}/posts", ['query' => ['_format' => 'json']]);
    $this->assertSession()->statusCodeEquals(200);

    $this->assertJson($content, 'Response is json');
    $result = Json::decode($content);

    $this->assertArrayHasKey('commands', $result);
    $this->assertArrayHasKey('content', $result);
    $this->assertArrayHasKey('content', $result);
    $this->assertArrayHasKey('libraries', $result);

    $this->assertEquals('Title post 1', $result['content'][0]['title']);
    $this->assertContains('Title post 1', $result['content'][0]['content']);
    $this->assertContains('Body post 1', $result['content'][0]['content']);
  }

}
