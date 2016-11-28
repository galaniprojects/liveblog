(function($, Drupal, drupalSettings) {
    Drupal.behaviors.liveblogEditor = {
        attach: function(context, settings) {
            var self = this
            Drupal.behaviors.liveblogStream.getContainer(context)
                .once('liveblog-editor-initialised')
                .each(function(index, element) {
                    var $this = $(element)
                    $this.on('click','.liveblog-post--edit-button', function (e) {
                        var target = $(e.currentTarget)
                        if (!target.hasClass('is-editing')) {
                            target.addClass('is-editing')
                            var postID = target.parent().data('postid')
                            if(postID && typeof postID != "undefined") {
                                var url = settings.liveblog.editFormURL.replace('%d', postID)
                                // TODO: error handling
                                $.getJSON(url, function(data) {
                              target.append(data.content)
                                })
                            }
                        }
                        else {
                            // TODO remove editor
                        }
                    })
                })
        },
    }
})(jQuery, Drupal, drupalSettings)

