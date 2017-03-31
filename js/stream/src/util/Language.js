class Language {
  static t(string) {
    return this.callback(string)
  }

  static callback(string) {
    return string
  }

  static setCallback(cb) {
    if (typeof cb === 'function') {
      this.callback = cb
    }
  }
}

export default Language
