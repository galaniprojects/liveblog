import React, { Component } from 'react'
import Language from '../util/Language'

export default class Notification extends Component {

  render() {
    let newPostText = ''
    if (this.props.newPosts.length == 1) {
      newPostText = Language.t('1 new post. Click here to load it.')
    }
    else if (this.props.newPosts.length > 1) {
      newPostText = this.props.newPosts.length + Language.t(' new posts. Click here to load them.')
    }

    const newPostButton = (<span><button className="link" onClick={this.props.loadNewPosts}>{newPostText}</button></span>)

    return (
      <div className="liveblog-notification-wrapper">
        <div className="liveblog-posts--notification--sticky">
          { this.props.newPosts.length >= 1 &&
            <div className="liveblog-posts-new">
              { newPostButton }
            </div>
          }
        </div>
      </div>
    )
  }
}
