<?php

namespace Drupal\liveblog;

use Drupal\Core\Asset\AssetResolver;
use Drupal\Core\Asset\AttachedAssetsInterface;

/**
 * Liveblog asset resolver. Needed to get all the libraries necessary to load.
 *
 * @see LiveblogAjaxResponseAttachmentsProcessor::buildAttachmentsCommands()
 */
class LiveblogAssetResolver extends AssetResolver {

  /**
   * Returns the libraries that need to be loaded.
   *
   * For example, with core/a depending on core/c and core/b on core/d:
   * @code
   * $assets = new AttachedAssets();
   * $assets->setLibraries(['core/a', 'core/b', 'core/c']);
   * $assets->setAlreadyLoadedLibraries(['core/c']);
   * $resolver->getLibrariesToLoad($assets) === ['core/a', 'core/b', 'core/d']
   * @endcode
   *
   * @param \Drupal\Core\Asset\AttachedAssetsInterface $assets
   *   The assets attached to the current response.
   *
   * @return string[]
   *   A list of libraries and their dependencies, in the order they should be
   *   loaded, excluding any libraries that have already been loaded.
   */
  public function getAllLibrariesToLoad(AttachedAssetsInterface $assets) {
    return $this->getLibrariesToLoad($assets);
  }

}
