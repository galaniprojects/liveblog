<?php

namespace Drupal\liveblog;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a LiveblogPost entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup liveblog_post
 */
interface LiveblogPostInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
