<?php

namespace Drupal\liveblog\Entity;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\liveblog\LiveblogPostInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\link\LinkItemInterface;

/**
 * Defines the Liveblog Post entity.
 *
 * @ContentEntityType(
 *   id = "liveblog_post",
 *   label = @Translation("Liveblog Post"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\liveblog\Entity\Controller\LiveblogPostListBuilder",
 *     "form" = {
 *       "add" = "Drupal\liveblog\Form\LiveblogPostForm",
 *       "edit" = "Drupal\liveblog\Form\LiveblogPostForm",
 *       "delete" = "Drupal\liveblog\Form\LiveblogPostDeleteForm",
 *     },
 *     "access" = "Drupal\liveblog\LiveblogPostAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "liveblog_post",
 *   admin_permission = "administer liveblog_post entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/liveblog_post/{liveblog_post}",
 *     "edit-form" = "/liveblog_post/{liveblog_post}/edit",
 *     "delete-form" = "/liveblog_post/{liveblog_post}/delete",
 *   },
 *   field_ui_base_route = "liveblog_post.liveblog_post_settings",
 * )
 */
class LiveblogPost extends ContentEntityBase implements LiveblogPostInterface {

  use EntityChangedTrait;

  /**
   * Liveblog posts highlights taxonomy vocabulary id.
   */
  const LIVEBLOG_POSTS_HIGHLIGHTS_VID = 'highlights';

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += ['user_id' => \Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * Returns the related liveblog node.
   *
   * @return \Drupal\node\NodeInterface
   *   The related liveblog node.
   */
  public function getLiveblog() {
    return $this->get('liveblog')->entity;
  }

  /**
   * Returns the related liveblog node ID.
   *
   * @return int
   *   The related liveblog node ID.
   */
  public function getLiveblogId() {
    return $this->get('liveblog')->target_id;
  }

  /**
   * Sets the related liveblog node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The related liveblog node.
   *
   * @return $this
   */
  public function setLiveblog(NodeInterface $node) {
    $this->set('liveblog', $node->id());
    return $this;
  }

  /**
   * Gets highlight options from the liveblog.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $definition
   *   The field storage definition.
   * @param \Drupal\Core\Entity\FieldableEntityInterface|NULL $entity
   *   The entity.
   * @param null $cacheable
   *   If $cacheable is FALSE, then the allowed values are not statically
   *   cached. See options_test_dynamic_values_callback() for an example of
   *   generating dynamic and uncached values.
   *
   * @return string[]
   *   Highlight options.
   *
   * @see options_allowed_values()
   */
  public static function getHighlightOptions(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, &$cacheable = NULL) {
    $options = [];

    // @todo: get terms from liveblog. hook_entity_prepare_form
    $ids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', self::LIVEBLOG_POSTS_HIGHLIGHTS_VID)
      ->execute();
    if (!empty($ids)) {
      $terms = Term::loadMultiple($ids);
      foreach ($terms as $term) {
        $name = $term->name->value;
        // Convert term name to a machine name, which will be used as a CSS
        // class in templates.
        $key = self::convertTextToMachineName($name);
        $options[$key] = $name;
      }
    }

    return $options;
  }

  /**
   * Converts text to a machine name.
   *
   * @param string $text
   *   The target text.
   *
   * @return string
   *   Machine name.
   */
  public static function convertTextToMachineName($text) {
    return strtolower(Html::cleanCssIdentifier($text));
  }

  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the LiveblogPost entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the LiveblogPost entity.'))
      ->setReadOnly(TRUE);

    // Name field for the liveblog_post.
    // We set display options for the view as well as the form.
    // Users with correct privileges can change the view and edit configuration.
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the liveblog post.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Body'))
      ->setDescription(t('Body text for the liveblog post.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 2,
        'settings' => [
          'rows' => 3,
        ],
      ])
      ->setDisplayOptions('view', [
        'type' => 'string',
        'weight' => 5,
        'label' => 'hidden',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['highlight'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Highlight'))
      ->setDescription(t('Adds the possibility to mark a post as a highlight.'))
      ->setSetting('allowed_values_function', __CLASS__ . '::getHighlightOptions')
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'select',
        'weight' => 3,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['source'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Source'))
      ->setDescription(t('The first name of the LiveblogPost entity.'))
      ->setSettings([
        'title' => DRUPAL_REQUIRED,
        'link_type' => LinkItemInterface::LINK_GENERIC,
      ])
      ->setDisplayOptions('form', [
        'type' => 'link',
        'weight' => 4,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['location'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Location'))
      ->setDescription(t('Location address string related to the post.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayOptions('view', [
        'type' => 'simple_gmap',
        'weight' => 7,
        'label' => 'hidden',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Entityreference to Liveblog.
    $fields['liveblog'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Liveblog'))
      ->setRequired(TRUE)
      ->setSettings([
        'target_type' => 'node',
        'handler_settings' => [
          'target_bundles' => ['liveblog' => 'liveblog'],
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 8,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 7,
        'type' => 'entity_reference_label',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 6,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether post is published.'))
      ->setDefaultValue(FALSE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'settings' => [
          'display_label' => TRUE
        ],
        'weight' => 8,
      ])
     ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 3,
        'settings' => [
          'date_format' => 'medium',
          'custom_date_format' => '',
          'timezone' => '',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 4,
        'settings' => [
          'date_format' => 'medium',
          'custom_date_format' => '',
          'timezone' => '',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
