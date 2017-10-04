# Liveblog

This is a new liveblogging module for the Drupal distribution Thunder.  
This module concentrates on providing the best User Experience for the editor as possible.

## Overall Structure
The Liveblog consists of following structure:
- A lead article at the top
- Followed by the liveblog, which consists of
  - individual entries/items,
  - with different content types (Text/Image/Twitter...),
  - which updates automatically
  
## Installation instructions

### Preparation
#### Inline Entity Form
It is recommended to apply a patch for the [Inline Entity Form](https://www.drupal.org/project/inline_entity_form) module:
- Issue: [Entities are not updated during buildEntity() phase](https://www.drupal.org/node/2830829)
- Patch https://www.drupal.org/files/issues/ief_building.patch

#### Pusher
The default method to push new posts to your users is via the _Pusher_ service.
You have to register an account for it here: https://pusher.com/. Please note down 
the App ID, Key and Secret.  
You also need the pusher library. See here for instructions: https://github.com/pusher/pusher-http-php

If you are not using a composer workflow, you can download the library here: https://github.com/pusher/pusher-http-php/releases.
Use pusher version 3.0.0 or greater.  
Extract it into your libraries folder (`libraries` or `sites/<domain>/libraries`) and rename it to `pusher`.
The library should contain the path `src/Pusher.php`

### Installation
1. Activate the _Liveblog_ and the _Liveblog Pusher_ modules.
    - optionally activate _Liveblog Paragraphs_ for a preconfigured 
     paragraphs integration
2. Navigate to http://example.com/admin/config/content/liveblog
    - configure the pusher service with the information given by Pusher
     
## Usage
### General
1. Create a new Liveblog content type. 
    1. Select which default highlights the editors can choose
    2. Set how much posts should be loaded on initial load and on lazyloading.
    3. Click on save.
2. When you are logged in and view the liveblog, you will see a form and below that,
the stream.
3. Fill out the form
    1. Preview it
    2. Save it
4. The post appears in the stream immediately

You can view all posts on your site by navigating to http://example.com/admin/content/liveblog_posts

### Configuration
You can manage the fields of the liveblog posts at 
http://example.com/admin/structure/liveblog_post_settings/fields 
(in menu: Structure -> Liveblog posts)

### Highlights
Highlights add a class to the post, according to the selected one, e.g. when you
select _Breaking News_, the class 
`liveblog-post--highlight-breaking-news` will be added to the post in the DOM.  
They can then be styled via css in your theme.
 
You can add your own highlights to the _Highlight_ taxonomy. 

## Development

### Running tests

Make sure to use your DB connection details for the SIMPLETEST_DB and the URL to
your local Drupal installation for SIMPLETEST_BASE_URL.

    cd /path/to/drupal-8/core
    export SIMPLETEST_DB=mysql://drupal-8:password@localhost/drupal-8
    export SIMPLETEST_BASE_URL=http://drupal-8.localhost
    ../vendor/bin/phpunit ../modules/liveblog
   
See https://www.drupal.org/docs/8/phpunit/running-phpunit-tests (Section:
Run kernel test and browser tests) for more details.


