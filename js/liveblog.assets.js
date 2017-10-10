(function(Drupal, drupalSettings) {
    function AssetHandler(target, url) {
        // url can't be a falsy value.
        url = url ? url : ' '
        this.ajaxObject = new Drupal.Ajax(null, target, {url: url, wrapper: 'page-wrapper'})
        this.ajaxCommands = new Drupal.AjaxCommands()
    }
    AssetHandler.prototype.loadLibraries = function(libraries) {
        var ajaxLibraries = drupalSettings.liveblog.libraries

        if (libraries) {
            for (var libraryName in libraries) {
                // Check if library exists in ajaxPageState
                if (ajaxLibraries.indexOf(libraryName) === -1) {
                    var library = libraries[libraryName]
                    for (var assetName in library) {
                        var asset = library[assetName]
                        this.ajaxCommands[asset.command](this.ajaxObject, asset, 200)
                    }
                    ajaxLibraries.push(libraryName)
                }
            }
            drupalSettings.liveblog.libraries = ajaxLibraries
        }
    }

    AssetHandler.prototype.executeCommands = function(commands) {
        for (var i=0; i<commands.length; i++) {
            if (commands[i].command && this.ajaxCommands[commands[i].command]) {
                this.ajaxCommands[commands[i].command](this.ajaxObject, commands[i], 200);
            }
        }
    }
    AssetHandler.prototype.afterLoading = function(context) {
        Drupal.attachBehaviors(context, drupalSettings)
    }

    AssetHandler.prototype.handleAssets = function(post, context) {
      this.loadLibraries(post.libraries)
      this.executeCommands(post.commands)
      this.afterLoading(context)
    }

    drupalSettings.liveblog.AssetHandler = AssetHandler
})(Drupal, drupalSettings)
