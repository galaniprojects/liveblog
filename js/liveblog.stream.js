(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.liveblogStream = {
    attach: function(context, settings) {
      var self = this
      this.getContainer(context).once('liveblog-stream-initialised').each(function(index, element) {
        var assetHandler = new settings.liveblog.AssetHandler(self.getContainer(context).get(0))

        LiveblogStream.setTranslatorFunction(function(string) {
          return Drupal.t(string)
        })

        var liveblogStream = new LiveblogStream(element, {
          getURL: settings.liveblog.getURL,
          getNextURL: settings.liveblog.getNextURL,
          onPostLoad: function (post, context) {
            assetHandler.handleAssets(post, context)
          }
        })

        $.data(element, 'liveblog-stream', liveblogStream)
      })
    },
    getContainer: function(context) {
      return $('.liveblog-posts-container', context)
    },
    getInstance: function(context) {
      return this.getContainer(context).data('liveblog-stream')
    }
  }
})(jQuery, Drupal, drupalSettings);
