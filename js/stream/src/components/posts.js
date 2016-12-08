import React, { Component } from 'react'
import ScrollPosition from '../helper/ScrollPosition'
import Notification from './notification'

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
  }

  componentWillMount() {
    // TODO Handle errors
    jQuery.getJSON(this.props.getURL, (posts) => {
      if (Array.isArray(posts.content)) {
        this.setState({
          posts: posts.content
        })
        this._handleAssets(posts.libraries, posts.commands, document.body)
      }
      else {
        // TODO Handle empty
      }
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
      if (lazyPosts && Array.isArray(lazyPosts.content)) {
        if (lazyPosts.content.length != 0) {
          this.setState({
            posts: [
              ...this.state.posts,
              ...lazyPosts.content
            ]
          })
          this._handleAssets(lazyPosts.libraries, lazyPosts.commands, document.body)
        }
        else {
          this.hasReachedEnd = true
        }
      }

      this.isloading = false
    })
  }

  _handleAssets(libraries, commands, context) {
    this.props.assetHandler.loadLibraries(libraries)
    this.props.assetHandler.executeCommands(commands)
    this.props.assetHandler.afterLoading(context)
  }

  addPost(post) {
    let rect = this.postsWrapper.getBoundingClientRect()

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
    var found = false
    var posts = this.state.posts.map((post) => {
      if (post.id == editedPost.id) {
        found = true
        return editedPost
      }
      else {
        return post;
      }
    })

    if (found) {
      var scrollPosition = new ScrollPosition(document.body, this.postNodes[editedPost.id])
      scrollPosition.prepareFor('up')

      this.setState({
        posts: posts
      })

      this._handleAssets(editedPost.libraries, editedPost.commands, document.body)
      scrollPosition.restore()
    }
  }

  _loadNewPosts() {
    let rect = this.postsWrapper.getBoundingClientRect()
    let bodyRect = document.body.getBoundingClientRect()
    jQuery("html, body").animate({
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
      let newPost = posts[i]
      this._handleAssets(newPost.libraries, newPost.commands, document.body)
    }
  }

  render() {
    return (
      <div className="liveblog-posts-wrapper" ref={(wrapper) => this.postsWrapper = wrapper}>
        <Notification newPosts={this.state.newPosts} loadNewPosts={this._loadNewPosts.bind(this)} />
        { this.state.posts.map((post) => {
          return (
            <div className="liveblog-post" key={post.id} ref={(node) => { this.postNodes[post.id] = node }}>
              <div dangerouslySetInnerHTML={{ __html: post.content }} />
            </div>
          )
        })}
      </div>
    )
  }


}