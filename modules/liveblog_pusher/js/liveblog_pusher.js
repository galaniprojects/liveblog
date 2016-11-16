(function(Drupal, drupalSettings) {
  Drupal.behaviors.liveblogPusher = {
    attach: function(context) {
      Drupal.behaviors.liveblogStream.getContainer(context)
        .once('liveblog-pusher-initialised')
        .each(function(i, element) {
          // Enable pusher logging - don't include this in production
          Pusher.logToConsole = true;

          var pusher = new Pusher(drupalSettings.liveblog_pusher.key, {
            encrypted: true
          });

          var channel = pusher.subscribe(drupalSettings.liveblog_pusher.channel);
          channel.bind('add', function(data) {
            Drupal.behaviors.liveblogStream.trigger('added', data, context)
          });
          channel.bind('edit', function(data) {
            Drupal.behaviors.liveblogStream.trigger('edited', data, context)
          })
        })
    }
  }

})(Drupal, drupalSettings)