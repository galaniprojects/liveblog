<?php

namespace Drupal\liveblog\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\liveblog\LiveblogPostInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\link\LinkItemInterface;

/**
 * Defines the Liveblog Post entity.
 *
 * @ContentEntityType(
 *   id = "liveblog_post",
 *   label = @Translation("Liveblog post"),
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
 *     "storage_schema" = "Drupal\liveblog\LiveblogPostStorageSchema",
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
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

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
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getLiveblog() {
    return $this->get('liveblog')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setLiveblog(NodeInterface $node) {
    $this->set('liveblog', $node->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthor() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthor(UserInterface $user) {
    $this->set('uid', $user->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLiveblogId() {
    return $this->get('liveblog')->target_id;
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
      ->setDescription(t('The ID of the liveblog post.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the liveblog post.'))
      ->setReadOnly(TRUE);

    // Name field for the liveblog_post.
    // We set display options for the view as well as the form.
    // Users with correct privileges can change the view and edit configuration.
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
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
      ->setDescription(t('Adds the possibility to mark the liveblog post as a highlight.'))
      // We can not make this callback as a static method of the LiveblogPost
      // class to support older PHP versions.
      ->setSetting('allowed_values_function','liveblog_post_get_highlight_options')
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
      ->setDescription(t('The source of the liveblog post.'))
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
      ->setDescription(t('Location address string related to the liveblog post.'))
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
        'type' => 'hidden',
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
        'type' => 'hidden',
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
      ->setDescription(t('Whether liveblog post is published.'))
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
      ->setDescription(t('The time that the liveblog post was created.'))
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
      ->setDescription(t('The time that the liveblog post was last edited.'))
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

  /**
   * {@inheritdoc}
   *
   * Triggers an notification channel event for the liveblog post.
   */
  function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if ($plugin = $this->getNotificationChannelManager()->createActiveInstance()) {
      $event_name = $update ? 'edit' : 'add';
      $plugin->triggerLiveblogPostEvent($this, $event_name);
    }
  }

  /**
   * Gets the notification channel plugin manager.
   *
   * @return \Drupal\liveblog\NotificationChannel\NotificationChannelManager
   *   Notification channel plugin manager.
   */
  protected function getNotificationChannelManager() {
    return \Drupal::service('plugin.manager.liveblog.notification_channel');
  }

}
