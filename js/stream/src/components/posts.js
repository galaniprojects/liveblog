import React, { Component } from 'react'

export default class Posts extends Component {
  constructor() {
    super()
    this.state = {
      posts: []
    }
    this.isloading = false
    this.hasReachedEnd = false
  }

  componentWillMount() {
    jQuery.getJSON(this.props.getURL, (posts) => {
      this.setState({
        posts: posts
      })
    })

    addEventListener('scroll', this._lazyload.bind(this))
  }

  componentWillUnmount() {
    removeEventListener('scroll', this._lazyload.bind(this))
  }

  _lazyload() {
    var el = this._getLastElement()
    if (!this.isloading && !this.hasReachedEnd && el && this._elementInViewport(el)) {
      this.isloading = true
      this._loadNextPosts()
    }
  }
  _getLastElement() {
    return this.postsWrapper.querySelector('div.liveblog-post:last-child')
  }
  _elementInViewport(el) {
    var rect = el.getBoundingClientRect()

    return (
         rect.top   >= 0
      && rect.left  >= 0
      && rect.top <= (window.innerHeight || document.documentElement.clientHeight)
    )
  }
  _loadNextPosts() {
    var posts = this.state.posts
    var lastPost = posts[posts.length-1]
    var url = this.props.getNextURL.replace('%s', lastPost.created)
    // TODO: error handling
    jQuery.getJSON(url, (lazyPosts) => {
      if (lazyPosts && Array.isArray(lazyPosts)) {
        if (lazyPosts.length != 0) {
          this.setState({
            posts: [
              ...this.state.posts,
              ...lazyPosts
            ]
          })
        }
        else {
          this.hasReachedEnd = true
        }
      }

      this.isloading = false
    })
  }

  render() {
    return (
      <div className="liveblog-posts-wrapper" ref={(wrapper) => this.postsWrapper = wrapper}>
        { this.state.posts.map((post) => {
          return (
            <div className="liveblog-post" key={post.id}>
              <div dangerouslySetInnerHTML={{ __html: post.rendered_entity }} />
            </div>
          )
        })}
      </div>
    )
  }


}