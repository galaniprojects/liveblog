/**
 * Holds the scroll position, while inserting elements to the page.
 *
 * Basis taken from http://kirbysayshi.com/2013/08/19/maintaining-scroll-position-knockoutjs-list.html
 *
 * @author Andrew Petersen <senofpeter@gmail.com>
 * @param node - The DOM element, in which the scrolling happens
 * @param element - The DOM element, which needs to be at or over the top of the viewport
 * @constructor
 */

function ScrollPosition(node, element) {
  this.node = node
  this.previousScrollHeightMinusTop = 0
  this.readyFor = 'up'

  const rect = element.getBoundingClientRect()
  if (rect.top >= 0) {
    this.restore = function(){}
    this.prepareFor = function(){}
  }
}

ScrollPosition.prototype.restore = function () {
  if (this.readyFor === 'up') {
    this.node.scrollTop = this.node.scrollHeight - this.previousScrollHeightMinusTop
  }

    // 'down' doesn't need to be special cased unless the
    // content was flowing upwards, which would only happen
    // if the container is position: absolute, bottom: 0 for
    // a Facebook messages effect
}

ScrollPosition.prototype.prepareFor = function (direction) {
  this.readyFor = direction || 'up'
  this.previousScrollHeightMinusTop = this.node.scrollHeight - this.node.scrollTop
}

export default ScrollPosition
