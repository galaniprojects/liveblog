import React, { Component } from 'react'

export default class Posts extends Component {
  constructor() {
    super()
    this.state = {
      posts: []
    }
  }

  componentWillMount() {
    jQuery.getJSON(this.props.getURL, (posts) => {
      this.setState({
        posts: posts
      })
    })
  }

  render() {
    return (
      <div>
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