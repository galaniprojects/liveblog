// import 'babel-polyfill';

import React from 'react'
import ReactDOM from 'react-dom'
import Posts from './components/Posts'

import AssetHandler from './util/AssetHandler'
import Language from './util/Language'

class LiveblogStream {
  constructor(element, options = {getURL: '', getNextURL: '', handleAssets: null, translator: null}) {
    AssetHandler.setCallback(options.handleAssets)
    Language.setCallback(options.translator)

    const App = (
        <Posts
          getURL={options.getURL}
          getNextURL={options.getNextURL}
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
}

window.LiveblogStream = LiveblogStream
