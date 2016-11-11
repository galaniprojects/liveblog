<?php

namespace Drupal\liveblog\Tests\Rest;

use Drupal\liveblog\Entity\LiveblogPost;
use Drupal\rest\Tests\RESTTestBase;

/**
 * Test of the Description Trait.
 *
 * @group liveblog
 */
class LiveblogRestTest extends RESTTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'link',
    'liveblog',
    'node',
    'options',
    'rest',
    'serialization',
    'simple_gmap',
    'system',
    'taxonomy',
    'text',
    'user',
  ];

  /**
   * Highlight terms.
   *
   * @var \Drupal\taxonomy\Entity\Term[]
   */
  private $terms;

  /**
   * Liveblog nodes.
   *
   * @var \Drupal\node\Entity\Node[]
   */
  private $nodes;

  /**
   * Liveblog posts.
   *
   * @var \Drupal\liveblog\Entity\LiveblogPost[]
   */
  private $posts;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();


    // Create a test higlight terms.
    $term_names = ['Goal', 'Game started', 'Game finished'];
    foreach ($term_names as $term_name) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
        'name' => $term_name,
        'vid' => LiveblogPost::LIVEBLOG_POSTS_HIGHLIGHTS_VID,
      ]);
      $term->save();
      $this->terms[] = $term;
    }

    // Create a test liveblog node.
    $node = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type' => 'liveblog',
      'title' => 'Football game',
      'field_highlights' => $this->terms[0]->id(),
    ]);
    $node->save();
    $this->nodes[] = $node;

    // Create a test liveblog posts.
    foreach ($term_names as $term_name) {
      $post = \Drupal::entityTypeManager()->getStorage('liveblog_post')->create([
        'title' => $term_name,
        'body' => [
          'value' => '<p>Goal!</p>',
          'format' => 'basic_html',
        ],
        'highlight' => LiveblogPost::convertTextToMachineName($term_name),
        'uid' => 1,
        'status' => 1,
      ]);
      $post->save();
      $this->posts[] = $post;
    }
  }

  /**
   * Make sure that the trait finds the template file and renders it.
   */
  public function testLiveblogNode() {
    $client = \Drupal::httpClient();
    $request = $client->get('http://thunder.local/liveblog_post/1?_format=json');
    $response = $request->getBody();
  }

}
