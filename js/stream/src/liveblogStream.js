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
    element.addEventListener('post:added', (event) => {
      this._postComponent.addPost(event.detail)
    })
    element.addEventListener('post:edited', (event) => {
      this._postComponent.editPost(event.detail)
    })
  }

}

window.LiveblogStream = LiveblogStream