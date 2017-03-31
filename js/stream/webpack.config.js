const LodashModuleReplacementPlugin = require('lodash-webpack-plugin')

module.exports = {
  entry: './src/liveblogStream.js',
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
  devtool: 'source-map',
  plugins: [
    new LodashModuleReplacementPlugin
  ]
}
