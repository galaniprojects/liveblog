import React, { Component } from 'react'
import ScrollPosition from '../helper/ScrollPosition'
import Notification from './Notification'
import Post from './Post'

import lodash from 'lodash'

export default class Posts extends Component {
  constructor() {
    super()
    this.state = {
      posts: [],
      newPosts: []
    }
    this.isloading = false
    this.hasReachedEnd = false
    this.postNodes = {}

    this.lazyloadListener = lodash.throttle(this._lazyload.bind(this), 100)
  }

  componentWillMount() {
    // TODO Handle errors
    jQuery.getJSON(this.props.getURL, (posts) => {
      if (Array.isArray(posts.content)) {
        this.setState({
          posts: posts.content
        })
        this.props.onPostLoad(posts, document.body)
      }
      else {
        // TODO Handle empty
      }
    })

    addEventListener('scroll', this.lazyloadListener)
  }

  componentWillUnmount() {
    removeEventListener('scroll', this.lazyloadListener)
  }

  _lazyload() {
    const el = this._getLastElement()
    if (!this.isloading && !this.hasReachedEnd && el && this._elementInViewport(el)) {
      this.isloading = true
      this._loadNextPosts()
    }
  }
  _getLastElement() {
    return this.postsWrapper.querySelector('div.liveblog-post:last-child')
  }
  _elementInViewport(el) {
    const rect = el.getBoundingClientRect()

    return (
         rect.top   >= 0
      && rect.left  >= 0
      && rect.top <= (window.innerHeight || document.documentElement.clientHeight)
    )
  }
  _loadNextPosts() {
    const posts = this.state.posts
    const lastPost = posts[posts.length - 1]
    const url = this.props.getNextURL.replace('%s', lastPost.created)
    // TODO: error handling
    jQuery.getJSON(url, (lazyPosts) => {
      if (lazyPosts && Array.isArray(lazyPosts.content)) {
        if (lazyPosts.content.length != 0) {
          this.setState({
            posts: [
              ...this.state.posts,
              ...lazyPosts.content
            ]
          })
          this.props.onPostLoad(lazyPosts, document.body)
        }
        else {
          this.hasReachedEnd = true
        }
      }

      this.isloading = false
    })
  }

  addPost(post) {
    const rect = this.postsWrapper.getBoundingClientRect()

    if (rect.top < 0 || this.state.newPosts.length > 0) {
      this.setState({
        newPosts: [
          post,
          ...this.state.newPosts
        ]
      })
    }
    else {
      this._loadPosts([post])
    }
  }

  editPost(editedPost) {
    let found = false
    const posts = this.state.posts.map((post) => {
      if (post.id == editedPost.id) {
        found = true
        return editedPost
      }
      else {
        return post
      }
    })

    if (found) {
      const scrollPosition = new ScrollPosition(document.body, this.postNodes[editedPost.id])
      scrollPosition.prepareFor('up')

      this.setState({
        posts: posts
      })

      this.props.onPostLoad(editedPost, document.body)
      scrollPosition.restore()
    }
  }

  _loadNewPosts() {
    const rect = this.postsWrapper.getBoundingClientRect()
    const bodyRect = document.body.getBoundingClientRect()
    jQuery('html, body').animate({
      scrollTop: rect.top - bodyRect.top - 90
    }, () => {
      this._loadPosts(this.state.newPosts)
    })
  }

  _loadPosts(posts) {
    this.setState({
      posts: [
        ...posts,
        ...this.state.posts
      ],
      newPosts: []
    })

    for (let i=0; i<posts.length; i++) {
      const newPost = posts[i]
      this.props.onPostLoad(newPost, document.body)
    }
  }

  render() {
    return (
      <div className="liveblog-posts-wrapper" ref={(wrapper) => {this.postsWrapper = wrapper}}>
        <Notification newPosts={this.state.newPosts} loadNewPosts={this._loadNewPosts.bind(this)} />
        { this.state.posts.map((post) => (
            <div className="liveblog-post" key={post.id} ref={(node) => { this.postNodes[post.id] = node }}>
              <Post content={post.content} />
            </div>
          ))}
      </div>
    )
  }


}
