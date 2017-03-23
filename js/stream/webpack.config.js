const webpack = require('webpack')

const isProd = (process.env.NODE_ENV === 'production')

module.exports = {
  entry: "./src/liveblogStream.js",
  output: {
    path: './dist',
    filename: 'bundle.js'
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader'
      },
      {
        test: /\.json$/,
        exclude: /node_modules/,
        loader: 'json'
      }
    ]
  },
  devtool: isProd ? false : 'source-map',
  plugins: []
}
