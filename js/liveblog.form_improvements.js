(function ($) {

  Drupal.behaviors.liveblogFormImprovements = {
    attach: function (context, settings) {
      var successMessagesSelector = 'div.messages__wrapper';
      var successMessagesTimeout = 7000;

      // Hide success messages after timeout to clean-up the form for editors.
      setTimeout(function() {
        $('.liveblog-posts').find(successMessagesSelector).once('liveblog').slideUp();
      }, successMessagesTimeout);
    }
  };

})(jQuery);
