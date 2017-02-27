import React, { Component } from 'react'
import Notification from './notification'
import Post from './Post'

import { connect } from 'react-redux'
import { bindActionCreators } from 'redux'
import { fetchInitialPosts, fetchLazyloadingPosts, addNewPost, editPost, showNewPosts } from "../actions"


class Posts extends Component {
  constructor() {
    super()
    this.postNodes = {}
  }

  componentWillMount() {
    this.props.fetchInitialPosts(this.props.getURL)
    addEventListener('scroll', this._lazyload.bind(this))
  }

  componentWillUnmount() {
    removeEventListener('scroll', this._lazyload.bind(this))
  }

  _lazyload() {
    let el = this._getLastElement();
    if (!this.props.isFetchingLazy && !this.props.hasReachedEnd && el && Posts._elementInViewport(el)) {
      let posts = this.props.posts;
      let lastPost = posts[posts.length - 1];
      this.props.fetchLazyloadingPosts(lastPost)
    }
  }
  _getLastElement() {
    return this.postsWrapper.querySelector('div.liveblog-post:last-child')
  }
  static _elementInViewport(el) {
    let rect = el.getBoundingClientRect();

    return (
         rect.top   >= 0
      && rect.left  >= 0
      && rect.top <= (window.innerHeight || document.documentElement.clientHeight)
    )
  }

  _loadNewPosts() {
    let rect = this.postsWrapper.getBoundingClientRect()
    let bodyRect = document.body.getBoundingClientRect()
    jQuery("html, body").animate({
      scrollTop: rect.top - bodyRect.top - 90
    }, () => {
      this.props.showNewPosts(this.props.newPosts)
    })
  }

  render() {
    return (
      <div className="liveblog-posts-wrapper" ref={(wrapper) => this.postsWrapper = wrapper}>
        <Notification newPosts={this.props.newPosts} loadNewPosts={this._loadNewPosts.bind(this)} />
        { this.props.posts.map((post) => {
          return (
            <div className="liveblog-post" key={post.id} ref={(node) => { this.postNodes[post.id] = node }}>
              <Post content={post.content} />
            </div>
          )
        })}
      </div>
    )
  }


}

function mapStateToProps(state) {
  return {
    isFetching: state.fetcher.isFetching,
    isFetchingLazy: state.fetcher.isFetchingLazy,
    hasReachedEnd: state.fetcher.hasReachedEnd,
    posts: state.posts,
    newPosts: state.newPosts
  }
}

function matchDispatchToProps(dispatch) {
  return bindActionCreators({
    fetchInitialPosts,
    fetchLazyloadingPosts,
    addNewPost,
    editPost,
    showNewPosts
  }, dispatch)
}

export default connect(mapStateToProps, matchDispatchToProps)(Posts)