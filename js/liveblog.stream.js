(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.liveblogStream = {
    attach: function(context, settings) {
      var self = this
      this.getContainer(context).once('liveblog-stream-initialised').each(function(index, element) {
        var assetHandler = new settings.liveblog.AssetHandler(self.getContainer(context), '')

        var liveblogStream = new LiveblogStream(element, {
          getURL: settings.liveblog.getURL,
          getNextURL: settings.liveblog.getNextURL,
          handleAssets: function (post, context) {
            assetHandler.handleAssets(post, context)
          },
          translator: function(string) {
            return Drupal.t(string)
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
