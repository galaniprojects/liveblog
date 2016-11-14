(function(Drupal, drupalSettings) {
  Drupal.behaviors.liveblogPusher = {
    attach: function(context) {
      // Enable pusher logging - don't include this in production
      Pusher.logToConsole = true;

      var pusher = new Pusher(drupalSettings.liveblog_pusher.key, {
        encrypted: true
      });

      var channel = pusher.subscribe(drupalSettings.liveblog_pusher.channel);
      channel.bind('created', function(data) {
        Drupal.behaviors.liveblogStream.trigger('created', data, context)
      });
      channel.bind('updated', function(data) {
        Drupal.behaviors.liveblogStream.trigger('updated', data, context)
      })
    }
  }

})(Drupal, drupalSettings)