# Liveblog

This repository is the base for a new liveblogging module for the Drupal distribution Thunder.  
This module concentrates on providing the best User Experience for the editor as possible.

## Overall Structure
The Liveblog will consist of following structure:
- A lead article at the top
- Followed by the liveblog, which consists of
  - individual entries/items,
  - with different content types (Text/Image/Twitter...),
  - which updates automatically

## Components
It will be splitted into different components.

1. A UI component
2. A Drupal module

These components should talk with each other over a defined API.  
The API and the UI component should be as flexible as possible, so that new content-types can be added easily later.

### UI component
The UI should consist of a JavaScript library, which also consists of two components:

The feed display, which handles 
- updating the feed
- displaying of new blogging items
- updating modified items

and the editorial view, where editors can
- publish different content
- edit content
- see a live preview of their content

### Drupal Module
The drupal module handles
- persisting items in the database
- providing previews for embedded data
- caching


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

### Installation instructions

It is recommended to apply a patch for the [Inline Entity Form](https://www.drupal.org/project/inline_entity_form) module:
- Issue: [Entities are not updated during buildEntity() phase](https://www.drupal.org/node/2830829)
- Patch https://www.drupal.org/files/issues/ief_building.patch
