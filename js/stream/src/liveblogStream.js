// import 'babel-polyfill';

import React from 'react'
import ReactDOM from 'react-dom'
import Posts from './components/Posts'

import Language from './util/Language'

class LiveblogStream {
  constructor(element, options = {getURL: '', getNextURL: '', onPostLoad: function() {}}) {
    if (typeof options.onPostLoad !== 'function') options.onPostLoad = function() {}

    const App = (
        <Posts
          getURL={options.getURL}
          getNextURL={options.getNextURL}
          onPostLoad={options.onPostLoad}
          ref={(postsComponent) => this._postComponent = postsComponent}
        />
    )
    ReactDOM.render(App, element)
  }

  // TODO: check, if parameter contains all necessary attributes
  addPost(post) {
    this._postComponent.addPost(post)
  }
  editPost(post) {
    this._postComponent.editPost(post)
  }

  static setTranslatorFunction(cb) {
    Language.setCallback(cb)
  }
}

window.LiveblogStream = LiveblogStream
