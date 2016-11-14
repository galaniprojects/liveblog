(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.liveblogStream = {
    attach: function(context, settings) {
      new LiveblogStream(jQuery('.liveblog-posts-container', context)[0], {
        getURL: settings.liveblog.getURL,
        getNextURL: settings.liveblog.getNextURL
      })
    }
  }
})(jQuery, Drupal, drupalSettings);