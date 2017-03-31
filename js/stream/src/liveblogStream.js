// import 'babel-polyfill';

import React from 'react'
import ReactDOM from 'react-dom'
import Posts from './components/Posts'

import helperFuncs from './helper/functions'

class LiveblogStream {
  constructor(element, helperFunctions = {assetHandler: null, t: null}, urls = {getURL: '', getNextURL: ''}) {
    if (typeof helperFunctions.assetHandler === 'function') this.setAssetHandler(helperFunctions.assetHandler)
    if (typeof helperFunctions.t === 'function') this.setTranslator(helperFunctions.t)

    const App = (
        <Posts
          getURL={urls.getURL}
          getNextURL={urls.getNextURL}
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

  setAssetHandler(assetHandler) {
    helperFuncs.assetHandler = assetHandler
  }

  setTranslator(t) {
    helperFuncs.t = t
  }

}

window.LiveblogStream = LiveblogStream
