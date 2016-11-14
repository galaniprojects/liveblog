# Liveblog Stream

## Bundling
To use this library, you first have to bundle it up.
To do that, you will need:
- nodejs with npm
    - Download via [http://nodejs.org]
    - or use [nvm](https://github.com/creationix/nvm)
- [webpack](https://webpack.github.io/docs/)
    - [install](https://webpack.github.io/docs/installation.html) with 
    `npm install webpack -g`

If you have everything we need, go the root directory of this library and 
install all dependencies with: `npm install`.  
After that you can start the bundling by typing: `webpack`. 
This will bundle the library for developing (including source-map).

If you want to have a minified version, set the node environment to production. 
E.g. in bash: `NODE_ENV=production webpack` 
or in fish: `env NODE_ENV=production webpack`

If you want to have the library bundled on every change you make to the files, 
use the included file-watcher, with: `webpack -w`