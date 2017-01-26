<?php

namespace Drupal\liveblog;

use Drupal\Core\Ajax\AddCssCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AjaxResponseAttachmentsProcessor;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Processes attachments of AJAX responses.
 *
 * Adds all the setting commands and libraries necessary for attachments.
 *
 * @see \Drupal\Core\Ajax\AjaxResponseAttachmentsProcessor
 * @see \Drupal\Core\Ajax\AjaxResponse
 * @see \Drupal\Core\Render\MainContent\AjaxRenderer
 */
class LiveblogAjaxResponseAttachmentsProcessor extends AjaxResponseAttachmentsProcessor {

  /**
   * The asset resolver service.
   *
   * @var \Drupal\liveblog\LiveblogAssetResolver
   */
  protected $assetResolver;

  /**
   * All the css, js assets grouped by libraries.
   *
   * @todo Cache this. @see \Drupal\Core\Asset\AssetResolver::getCssAssets().
   *
   * @var array
   */
  protected $libraries;

  /**
   * Constructs a AjaxResponseAttachmentsProcessor object.
   *
   * @param \Drupal\liveblog\LiveblogAssetResolver $asset_resolver
   *   An asset resolver.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $css_collection_renderer
   *   The CSS asset collection renderer.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $js_collection_renderer
   *   The JS asset collection renderer.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(LiveblogAssetResolver $asset_resolver, ConfigFactoryInterface $config_factory, AssetCollectionRendererInterface $css_collection_renderer, AssetCollectionRendererInterface $js_collection_renderer, RequestStack $request_stack, RendererInterface $renderer, ModuleHandlerInterface $module_handler) {
    parent::__construct($asset_resolver, $config_factory, $css_collection_renderer, $js_collection_renderer, $request_stack, $renderer, $module_handler);
  }

  public function getLibraries() {
    return $this->libraries;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildAttachmentsCommands(AjaxResponse $response, Request $request) {
    // @todo Aggregate CSS/JS if necessary, during normal site operation.
    //   We should optimize every file separately, else it will be not possible
    //   to distinguish between them.
    //$optimize_css = !defined('MAINTENANCE_MODE') && $this->config->get('css.preprocess');
    //$optimize_js = !defined('MAINTENANCE_MODE') && $this->config->get('js.preprocess');
    $optimize_css = $optimize_js = FALSE;

    $attachments = $response->getAttachments();

    // Resolve the attached libraries into asset collections.
    $assets = new AttachedAssets();
    // We should not set already loaded libraries here from the ajax page state,
    // as different clients might have different libraries loaded.
    $assets->setLibraries(isset($attachments['library']) ? $attachments['library'] : [])
      ->setSettings(isset($attachments['drupalSettings']) ? $attachments['drupalSettings'] : []);
    $css_assets = $this->assetResolver->getCssAssets($assets, $optimize_css);
    list($js_assets_header, $js_assets_footer) = $this->assetResolver->getJsAssets($assets, $optimize_js);

    $attachments['drupalSettings'] = $assets->getSettings();

    // Render the HTML to load these files, and add AJAX commands to insert this
    // HTML in the page. Settings are handled separately, afterwards.
    $settings = [];
    if (isset($js_assets_header['drupalSettings'])) {
      $settings = $js_assets_header['drupalSettings']['data'];
      unset($js_assets_header['drupalSettings']);
    }
    if (isset($js_assets_footer['drupalSettings'])) {
      $settings = $js_assets_footer['drupalSettings']['data'];
      unset($js_assets_footer['drupalSettings']);
    }

    $libraries = $this->assetResolver->getAllLibrariesToLoad($assets);
    $this->groupAssetsByLibraries($libraries, $css_assets, 'css');
    $this->groupAssetsByLibraries($libraries, $js_assets_header, 'js');
    $this->groupAssetsByLibraries($libraries, $js_assets_footer, 'js');

    // Prepend a command to merge changes and additions to drupalSettings.
    if (!empty($settings)) {
      // During Ajax requests basic path-specific settings are excluded from
      // new drupalSettings values. The original page where this request comes
      // from already has the right values. An Ajax request would update them
      // with values for the Ajax request and incorrectly override the page's
      // values.
      // @see system_js_settings_alter()
      unset($settings['path']);

      // Ajax page state is updated at the frontend side, as page state might be
      // different for every client.
      unset($settings['ajaxPageState']);

      $response->addCommand(new SettingsCommand($settings, TRUE), TRUE);
    }

    $commands = $response->getCommands();
    $this->moduleHandler->alter('ajax_render', $commands);

    return $commands;
  }

  /**
   * Groups all the css, js assets by libraries.
   *
   * @param array $libraries
   *   Libraries array.
   * @param array $assets
   *   Assets array.
   * @param string $type
   *   Assets type(css or js).
   */
  protected function groupAssetsByLibraries($libraries, $assets, $type) {
    foreach ($libraries as $library) {
      list($extension, $name) = explode('/', $library, 2);
      $definition = $this->getLibraryDiscovery()->getLibraryByName($extension, $name);

      if (empty($definition[$type])) {
        continue;
      }

      foreach ($definition[$type] as $options) {
        if (!empty($options['data']) && !empty($assets[$options['data']])) {
          $asset = $assets[$options['data']];

          // Create commands to add the assets.
          if ($type == 'css') {
            $css_render_array = $this->cssCollectionRenderer->render([$asset]);
            $command = new AddCssCommand($this->renderer->renderPlain($css_render_array));
          }
          elseif ($type == 'js') {
            $js_render_array = $this->jsCollectionRenderer->render([$asset]);
            if ($asset['scope'] == 'header') {
              $command = new PrependCommand('head', $this->renderer->renderPlain($js_render_array));
            }
            elseif ($asset['scope'] == 'footer') {
              $command = new AppendCommand('body', $this->renderer->renderPlain($js_render_array));
            }
          }

          if (!empty($command)) {
            $this->libraries[$library][$asset['data']] = $command->render();
          }
        }
      }
    }
  }

  /**
   * Gets library discovery service.
   *
   * @return \Drupal\Core\Asset\LibraryDiscoveryInterface
   *   The library discovery service.
   */
  function getLibraryDiscovery() {
    return \Drupal::service('library.discovery');
  }

}
