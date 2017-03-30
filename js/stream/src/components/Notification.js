import React, { Component } from 'react'

export default class Notification extends Component {

  componentWillMount() {
    addEventListener('scroll', this._stickyNotification.bind(this))
  }

  componentWillUnmount() {
    removeEventListener('scroll', this._stickyNotification.bind(this))
  }

  _stickyNotification() {
    const rect = this.notificationWrapper.getBoundingClientRect()

    if (rect.top < 0) {
      this.notification.classList.add('liveblog-posts--notification--sticky')
    }
    else if (rect.top >= 0) {
      this.notification.classList.remove('liveblog-posts--notification--sticky')
    }
  }

  render() {
    let newPostText = ''
    if (this.props.newPosts.length == 1) {
      newPostText = '1 new post. Click here to load it.'
    }
    else if (this.props.newPosts.length > 1) {
      newPostText = this.props.newPosts.length + ' new posts. Click here to load them.'
    }

    const newPostButton = (<span><button className="link" onClick={this.props.loadNewPosts}>{newPostText}</button></span>)

    return (
      <div className="liveblog-notification-wrapper" ref={(wrapper) => {this.notificationWrapper = wrapper}}>
        <div ref={(notifications) => this.notification = notifications}>
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
