<?php

namespace Drupal\liveblog;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\NodeInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a LiveblogPost entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup liveblog_post
 */
interface LiveblogPostInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Returns the created datetime timestamp of the liveblog post.
   *
   * @return int
   *   The created datetime timestamp of the liveblog post.
   */
  public function getCreatedTime();

  /**
   * Returns the changed datetime timestamp of the liveblog post.
   *
   * @return int
   *   The changed datetime timestamp of the liveblog post.
   */
  public function getChangedTime();

  /**
   * Returns the liveblog post author(owner).
   *
   * @return \Drupal\user\UserInterface
   *   The liveblog post author(owner)
   */
  public function getOwner();

  /**
   * Returns the ID of liveblog post author(owner).
   *
   * @return int
   *   The ID of liveblog post author(owner)
   */
  public function getOwnerId();

  /**
   * Sets the ID of liveblog post author(owner).
   *
   * @param int
   *   The related liveblog node.
   *
   * @return $this
   */
  public function setOwnerId($uid);

  /**
   * Sets the liveblog post author(owner).
   *
   * @param \Drupal\user\UserInterface
   *   The liveblog post author(owner).
   *
   * @return $this
   */
  public function setOwner(UserInterface $account);

  /**
   * Returns the related liveblog node.
   *
   * @return \Drupal\node\NodeInterface
   *   The related liveblog node.
   */
  public function getTitle();

  /**
   * Returns the related liveblog node.
   *
   * @return \Drupal\node\NodeInterface
   *   The related liveblog node.
   */
  public function getLiveblog();

  /**
   * Sets the related liveblog node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The related liveblog node.
   *
   * @return $this
   */
  public function setLiveblog(NodeInterface $node);

  /**
   * Returns the related liveblog author.
   *
   * @return \Drupal\user\UserInterface
   *   The related liveblog author.
   */
  public function getAuthor();

  /**
   * Sets the related liveblog author.
   *
   * @param \Drupal\user\UserInterface $user
   *   The related liveblog author.
   *
   * @return $this
   */
  public function setAuthor(UserInterface $user);

  /**
   * Returns the related liveblog node ID.
   *
   * @return int
   *   The related liveblog node ID.
   */
  public function getLiveblogId();

}
