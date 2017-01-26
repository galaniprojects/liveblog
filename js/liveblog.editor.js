(function($, Drupal, drupalSettings) {
    Drupal.behaviors.liveblogEditor = {
        attach: function(context, settings) {
            var self = this
            Drupal.behaviors.liveblogStream.getContainer(context)
                .once('liveblog-editor-initialised')
                .each(function(index, element) {
                    var $this = $(element)
                    var isLoading = false
                    $this.on('click','.liveblog-post--edit-button', function (e) {
                        var target = $(e.currentTarget)
                        if (target.has('form.liveblog-post-form').length == 0 && !isLoading) {
                            isLoading = true
                            var postID = target.parent().data('postid')
                            if(postID && typeof postID != "undefined") {
                                var url = settings.liveblog.editFormURL.replace('%d', postID)
                                // TODO: error handling
                                $.getJSON(url, function(data) {
                                    target.append(data.content)

                                    var assetHandler = new drupalSettings.liveblog.AssetHandler(target, url)
                                    assetHandler.loadLibraries(data.libraries)
                                    assetHandler.executeCommands(data.commands)
                                    assetHandler.afterLoading(target[0])

                                    isLoading = false
                                })
                            }
                        }
                        else {
                            // TODO remove editor
                        }
                    })
                })
            // Show edit buttons on new/lazyloaded posts
            Drupal.behaviors.liveblogStream.getContainer(context).find('.liveblog-post--edit-button').show()
        },
    }
})(jQuery, Drupal, drupalSettings)

