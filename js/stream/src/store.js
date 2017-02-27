import { createStore, applyMiddleware, compose } from 'redux';
import thunkMiddleware from 'redux-thunk'
import allReducers from './reducers'

const store = createStore(
  allReducers,
  compose(
    applyMiddleware(thunkMiddleware),
    // TODO: remove devToolsExtension
    window.devToolsExtension && window.devToolsExtension()
  )
);

export default store;