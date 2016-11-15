// import 'babel-polyfill';

import React from 'react'
import ReactDOM from 'react-dom'
import Posts from './components/posts'

class LiveblogStream {
  constructor(element, urls = {getURL, getNextURL}) {
    const App = () => (
      <div>
        <Posts
          getURL={urls.getURL}
          getNextURL={urls.getNextURL}
          ref={(postsComponent) => this._postComponent = postsComponent}
        />
      </div>
    )
    ReactDOM.render(<App />, element)

    this._attachEventListeners(element)
  }

  // TODO: maybe refactor it to the posts component
  _attachEventListeners(element) {
    element.addEventListener('post:created', (event) => {
      this.addPost(event.detail)
    })
    element.addEventListener('post:updated', (event) => {
      this.editPost(event.detail)
    })
  }

  // TODO; This could lead to an issue, when App hasn't finished mounting
  addPost(post) {
    this._postComponent.addPost(post)
  }
  editPost(post) {
    this._postComponent.editPost(post)
  }

}

window.LiveblogStream = LiveblogStream