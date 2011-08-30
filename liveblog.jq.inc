<?php
// $Id:$

/**
 * Implementation of hook_jq().
 * http://drupal.org/project/jq
 */
function liveblog_jq($op, $plugin = NULL) {
  if ($op == 'info') {
    $path = drupal_get_path('module', 'liveblog');
    return array(
      'scrollTo' => array(
        'name' => t('scrollTo'),
        'description' => t('Implement the scrollTo jQuery plugin.'),
        'version' => '1.4.0',
        'url' => 'http://plugins.jquery.com/project/ScrollTo/',
        'files' => array(
          'js' => array(
            $path .'/scrollto/jquery.scrollTo-1.4.0-min.js',
          ),
        ),
        'invocation' => NULL,
      ),
    );
  }
}
