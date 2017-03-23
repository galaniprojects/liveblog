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
      newPostText = (<span>1 new post. <button className="link" onClick={this.props.loadNewPosts}>Click here</button> to load it.</span>)
    }
    else if (this.props.newPosts.length > 1) {
      newPostText = (
        <span>{ this.props.newPosts.length } new posts.&nbsp;
          <button className="link" onClick={this.props.loadNewPosts}>Click here</button> to load them.
          </span>
      )
    }

    return (
      <div className="liveblog-notification-wrapper" ref={wrapper => this.notificationWrapper = wrapper}>
        <div ref={(notifications) => this.notification = notifications}>
          { newPostText &&
            <div className="liveblog-posts-new">
              { newPostText }
            </div>
          }
        </div>
      </div>
    )
  }
}
