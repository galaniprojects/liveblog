class AssetHandler {
  static handleAssets(post, context) {
    this.callback(post, context)
  }

  static callback(post, context) {}

  static setCallback(cb) {
    if (typeof cb === 'function') {
      this.callback = cb
    }
  }
}

export default AssetHandler
