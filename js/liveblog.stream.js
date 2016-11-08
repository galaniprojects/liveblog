(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.liveblogStream = {
    attach: function(context, settings) {
      console.log(settings)
      new LiveblogStream(jQuery('.posts-container', context)[0], {
        getURL: settings.liveblog.getURL,
        getNextURL: settings.liveblog.getNextURL
      })
    }
  }
})(jQuery, Drupal, drupalSettings);