<?php

namespace Drupal\liveblog\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\liveblog\LiveblogPostInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\link\LinkItemInterface;

/**
 * Defines the Liveblog Post entity.
 *
 * @ContentEntityType(
 *   id = "liveblog_post",
 *   label = @Translation("Liveblog Post entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\liveblog\Entity\Controller\LiveblogPostListBuilder",
 *     "form" = {
 *       "add" = "Drupal\liveblog\Form\LiveblogPostForm",
 *       "edit" = "Drupal\liveblog\Form\LiveblogPostForm",
 *       "delete" = "Drupal\liveblog\Form\LiveblogPostDeleteForm",
 *     },
 *     "access" = "Drupal\liveblog\LiveblogPostAccessControlHandler",
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
 *     "collection" = "/liveblog_post/list"
 *   },
 *   field_ui_base_route = "liveblog_post.liveblog_post_settings",
 * )
 */
class LiveblogPost extends ContentEntityBase implements LiveblogPostInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
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
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
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

    // @todo: get terms from liveblog.
    $options = [1,2,3];

    return $options;
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
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -6,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['body'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Body'))
      ->setDescription(t('Body text for the liveblog post.'))
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 7,
        'settings' => array(
          'rows' => 3,
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => 7,
        'label' => 'above',
      ))
      ->setDisplayConfigurable('view', TRUE);

    // @todo: Location.

    $fields['source'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Source'))
      ->setDescription(t('The first name of the LiveblogPost entity.'))
      ->setSettings(array(
        'title' => DRUPAL_REQUIRED,
        'link_type' => LinkItemInterface::LINK_GENERIC,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'link',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['highlight'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Highlight'))
      ->setDescription(t('Adds the possibility to mark a post as a highlight.'))

      ->setRequired(TRUE)
      ->setSetting('allowed_values_function', __CLASS__ . '::getHighlightOptions')
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'select',
        'weight' => 2,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'weight' => 2,
      ))
      ->setDisplayConfigurable('view', TRUE);

    // Entityreference to Liveblog.
    $fields['liveblog'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Liveblog'))
      ->setSettings(array(
        'target_type' => 'node',
        'target_bundles' => ['liveblog'],
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'weight' => 5,
        'type' => 'entity_reference_label',
      ))
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether post is published.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'settings' => [
          'display_label' => TRUE
        ],
        'weight' => 7,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // @todo should we support multilingual posts?
    /*$fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Liveblog Post entity.'));*/
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
