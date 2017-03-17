// import 'babel-polyfill';

import React from 'react'
import ReactDOM from 'react-dom'
import Posts from './components/posts'

class LiveblogStream {
  constructor(element, assetHandler, urls = {getURL: '', getNextURL: ''}) {
    const App = (
        <Posts
          getURL={urls.getURL}
          getNextURL={urls.getNextURL}
          assetHandler={assetHandler}
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