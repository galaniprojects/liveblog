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
        />
      </div>
    )
    ReactDOM.render(<App />, element)
  }
}

window.LiveblogStream = LiveblogStream