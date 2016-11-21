(function($, Drupal, drupalSettings) {
    Drupal.behaviors.liveblogEditor = {
        attach: function(context, settings) {
            Drupal.behaviors.liveblogStream.getContainer(context)
                .once('liveblog-editor-initialised')
                .each(function(index, element) {
                    var $this = $(element)
                    $this.on('click','.liveblog-post', function (e) {
                        var target = $(e.currentTarget)
                        var postID = target.data('postid')
                        if(postID && typeof postID != "undefined") {
                            var url = settings.liveblog.editFormURL.replace('%d', postID)
                            // TODO: error handling
                            $.getJSON(url, function(data) {
                              target.append(data.form).submit(function(e) {e.preventDefault()})
                            })
                        }
                    })
            })
        }
    }
})(jQuery, Drupal, drupalSettings)