import settings from '../settings.json'
import store from '../store'

import Post from '../components/post'

export const INITIAL_LOAD = 'INITIAL_LOAD'
export const RECEIVE_INITIAL_POSTS = 'RECEIVE_INITIAL_POSTS'
export const LAZYLOAD_ITEMS = 'LAZYLOAD_ITEMS'
export const RECEIVE_LAZYLOAD_POSTS = 'RECEIVE_LAZYLOAD_POSTS'
export const HAS_REACHED_END = 'HAS_REACHED_END'
export const ADD_NEW_POST = 'ADD_NEW_POST'
export const RECEIVE_NEW_POSTS = 'RECEIVE_NEW_POSTS'
export const CLEAR_NEW_POSTS = 'CLEAR_NEW_POSTS'
export const REPLACE_POSTS = 'REPLACE_POSTS'

export function requestInitialItems() {
  return {
    type: INITIAL_LOAD
  }
}

export function receiveInitialItems(posts) {
  return {
    type: RECEIVE_INITIAL_POSTS,
    payload: posts
  }
}

export function requestLazyloadPosts() {
  return {
    type: LAZYLOAD_ITEMS
  }
}

export function receiveLazyloadPosts(posts) {
  return {
    type: RECEIVE_LAZYLOAD_POSTS,
    payload: posts
  }
}

export function hasReachedEnd() {
  return {
    type: HAS_REACHED_END
  }
}

export function addNewPost(post) {
  return {
    type: ADD_NEW_POST,
    payload: post
  }
}

export function receiveNewPosts(posts) {
  return {
    type: RECEIVE_NEW_POSTS,
    payload: posts
  }
}

export function clearNewPosts() {
  return {
    type: CLEAR_NEW_POSTS
  }
}

export function replacePosts(posts) {
  return {
    type: REPLACE_POSTS,
    payload: posts
  }
}

// Thunks
export function fetchInitialPosts() {
  return (dispatch) => {

    dispatch(requestInitialItems())

    // TODO Handle errors
    jQuery.getJSON(settings.getURL, (posts) => {
      if (Array.isArray(posts.content)) {
        dispatch(receiveInitialItems(posts.content))
        _handleAssets(posts.libraries, posts.commands, document.body)
      }
      else {
        // TODO Handle empty
      }
    })

  }
}

export function fetchLazyloadingPosts(lastPost) {
  return (dispatch) => {

    dispatch(requestLazyloadPosts())

    // var posts = this.state.posts
    // var lastPost = posts[posts.length - 1]
    var url = settings.getNextURL.replace('%s', lastPost.created)
    // TODO: error handling
    jQuery.getJSON(url, (lazyPosts) => {
      if (lazyPosts && Array.isArray(lazyPosts.content)) {
        if (lazyPosts.content.length != 0) {
          dispatch(receiveLazyloadPosts(lazyPosts.content))
          _handleAssets(lazyPosts.libraries, lazyPosts.commands, document.body)
        }
        else {
          dispatch(hasReachedEnd())
        }
      }
      else {
        dispatch(hasReachedEnd())
      }
    })
  }
}

export function showNewPosts(posts) {

  return (dispatch) => {
    dispatch(receiveNewPosts(posts))
    dispatch(clearNewPosts())

    for (let i=0; i<posts.length; i++) {
      let newPost = posts[i]
      _handleAssets(newPost.libraries, newPost.commands, document.body)
    }
  }
}

// TODO: try to keep scrollPosition
export function editPost(editedPost) {
  return (dispatch) => {
    let found = false;
    let posts = store.getState().posts.map((post) => {
      if (post.id == editedPost.id) {
        found = true
        return editedPost
      }
      else {
        return post;
      }
    });

    if (found) {
      dispatch(replacePosts(posts))

      _handleAssets(editedPost.libraries, editedPost.commands, document.body)

      Post.executeScripts(editedPost.content)
    }
  }
}

function _handleAssets(libraries, commands, context) {
  settings.assetHandler.loadLibraries(libraries)
  settings.assetHandler.executeCommands(commands)
  settings.assetHandler.afterLoading(context)
}