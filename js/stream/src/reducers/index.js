import { combineReducers } from 'redux'

import {
  INITIAL_LOAD, RECEIVE_INITIAL_POSTS,
  LAZYLOAD_ITEMS, RECEIVE_LAZYLOAD_POSTS,
  HAS_REACHED_END,
  ADD_NEW_POST,
  RECEIVE_NEW_POSTS,
  CLEAR_NEW_POSTS,
  REPLACE_POSTS
} from '../actions'


function fetcher(state = { isFetching: false, isFetchingLazy: false, hasReachedEnd: false}, action) {
  switch (action.type) {
    case INITIAL_LOAD:
      return {
        ...state,
        isFetching: true
      }
    case RECEIVE_INITIAL_POSTS:
      return {
        ...state,
        isFetching: false
      }
    case LAZYLOAD_ITEMS:
      return {
        ...state,
        isFetchingLazy: true
      }
    case RECEIVE_LAZYLOAD_POSTS:
      return {
        ...state,
        isFetchingLazy: false
      }
    case HAS_REACHED_END:
      return {
        ...state,
        hasReacheEnd: true,
        isFetchingLazy: false
      }
    default:
      return state
  }
}

function posts(state = [], action) {
  switch (action.type) {
    case RECEIVE_INITIAL_POSTS:
    case REPLACE_POSTS:
      return [
        ...action.payload
      ]
    case RECEIVE_LAZYLOAD_POSTS:
      return [
        ...state,
        ...action.payload
      ]
    case RECEIVE_NEW_POSTS:
      return [
        ...action.payload,
        ...state
      ]
    default:
      return state
  }
}

function newPosts(state = [], action) {
  switch (action.type) {
    case ADD_NEW_POST:
      return [
        ...state,
        action.payload
      ]
    case CLEAR_NEW_POSTS:
      return []
    default:
      return state
  }
}

export default combineReducers({
  posts,
  newPosts,
  fetcher
})
