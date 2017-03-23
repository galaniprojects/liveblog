import React, {Component} from 'react'

export default class Post extends Component {

  componentWillMount() {
    Post.executeScripts(this.props.content)
  }

  /**
   * Code taken and modified from jQuery domManip function in /src/manipulation.js
   *
   * Alternative implementations:
   *  http://stackoverflow.com/questions/35614809/react-script-tag-not-working-when-inserted-using-dangerouslysetinnerhtml
   *  http://stackoverflow.com/questions/37803559/react-js-how-to-get-script-inside-dangerouslysetinnerhtml-executed
   *
   * @param html - The html as string which includes scripts to be executed
   */
  static executeScripts(html) {
    const scripts = jQuery('<div>' + html + '</div>').find('script')

    let node
    if (scripts.length) {
      for (let i = 0; i < scripts.length; i++) {
        node = scripts[i]
        if (Post.rscriptType().test(node.type || '')) {
          if (node.src) {

            // Optional AJAX dependency, but won't run scripts if not present
            if (jQuery._evalUrl) {
              jQuery._evalUrl(node.src)
            }
          } else {
            Post.DOMEval(node.textContent.replace(Post.rcleanScript(), ''))
          }
        }
      }
    }
  }

  static rscriptType() {
    return ( /^$|\/(?:java|ecma)script/i )
  }

  static rcleanScript() {
    return /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g
  }

  static DOMEval(code, doc) {
    doc = doc || document

    const script = doc.createElement('script')
    script.text = code
    doc.head.appendChild(script).parentNode.removeChild(script)
  }

  render() {
    return (
      <div>
        <div dangerouslySetInnerHTML={{__html: this.props.content}}></div>
      </div>
    )
  }


}
