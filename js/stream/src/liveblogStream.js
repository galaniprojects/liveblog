// import 'babel-polyfill';

import React from 'react'
import ReactDOM from 'react-dom'
import Posts from './components/posts'

import settings from './settings.json'

import store from './store'
import { Provider } from 'react-redux'

import { addNewPost, showNewPosts, editPost } from './actions/index'

class LiveblogStream {
  constructor(element, assetHandler, urls = {getURL, getNextURL}) {
    this.setURLs(urls.getURL, urls.getNextURL)
    this.setAssetHandler(assetHandler)
    this.element = element
    const App = (
      <Provider store={store} >
        <Posts
          ref={(postsComponent) => this._postComponent = postsComponent}
        />
      </Provider>
    )
    ReactDOM.render(App, element)
  }

  // TODO: check, if parameter contains all necessary attributes
  addPost(post) {
    let rect = this.element.getBoundingClientRect()

    if (rect.top < 0 || store.getState().newPosts.length > 0) {
      store.dispatch(addNewPost(post))
    }
    else {
      store.dispatch(showNewPosts([post]))
    }
  }
  editPost(post) {
    store.dispatch(editPost(post))
  }

  setURLs(getURL, getNextURL) {
    settings.getURL = getURL
    settings.getNextURL = getNextURL
  }

  setAssetHandler(assetHandler) {
    settings.assetHandler = assetHandler
  }
}

window.LiveblogStream = LiveblogStream