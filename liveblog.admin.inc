<?php
/**
 * function liveblog_admin_settings
 * Implementation of admin_settings
 */
function liveblog_admin_settings() {
  $form['liveblog_refresh_rate'] = array(
    '#type' => 'textfield',
    '#size' => 20,
    '#title' => t('Refresh rate'),
    '#default_value' => variable_get('liveblog_refresh_rate', 30),
    '#description' => t('Enter a number (in seconds) for the refresh rate for the client-side liveblog updating'),
  );
  $form['liveblog_display_order'] = array(
    '#type' => 'select',
    '#options' => array(
      'ASC' => t('Ascending'),
      'DESC' => t('Descending'),
    ),
    '#title' => t('Refresh rate'),
    '#default_value' => variable_get('liveblog_display_order', 'ASC'),
    '#description' => t('Enter a number (in seconds) for the refresh rate for the client-side liveblog updating'),
  );

  return system_settings_form($form);
}

/**
 * function liveblog_manage() 
 * Manage liveblogging content
 */
function liveblog_manage($node) {
  drupal_set_title(check_plain($node->title));
  return drupal_get_form('_liveblog_manage_form', $node);
}

function _liveblog_manage_form(&$form_state, $node) {
  if (is_numeric($_GET['comment_id'])) {
    $default_comment = db_fetch_object(db_query("SELECT * from {liveblog_comments} where id = %d", $_GET['comment_id']));
    if ($default_comment->id == $_GET['comment_id']) {
      $default_value = theme('liveblog_comment', $default_comment);
    }    
  }
  if (is_numeric($_GET['post_id'])) {
    $default_post = db_fetch_object(db_query("SELECT * from {liveblog_posts} where id = %d", $_GET['post_id']));
    if ($default_post->id == $_GET['post_id']) {
      $default_value = $default_post->text;
    }
    $form['post_edit'] = array(
      '#type' => 'hidden',
      '#value' => $_GET['post_id'],
    );    
  }
  if (!$default_value) { $default_value = ''; }
  $width = 1010;
  $height = 200;
  $form['post_new'] = array(
    '#type' => 'fieldset',
    '#collapsed' => FALSE,
    '#collapsible' => FALSE,
    '#title' => t('Post to the liveblog'),
    '#prefix' => '<div id="post_new"><a name="post_new"></a>',
    '#suffix' => '</div>',
  );
  $form['post_new']['post_text'] = array(
    '#title' => t('Post text'),
    '#type' => 'textarea',
    '#default_value' => $default_value,
  );
  $form['post_new']['post_new_submit'] = array(
    '#type' => 'submit',
    '#value' => t('Post now!'),
  );
  $form = array_merge($form, _liveblog_manage_twitter_form(&$form_state, $node));
  $form = array_merge($form, _liveblog_manage_posts_form(&$form_state, $node));
  $form = array_merge($form, _liveblog_manage_comments_form(&$form_state, $node));
  return $form;
}

/**
 * _liveblog_manage_form_submit()
 * Handle posting/saving data for a liveblog
 * If post_* data is set, create a new item and update the JSON files
 */
function _liveblog_manage_form_submit($form, &$form_state) {
  $values = $form['#post'];
  $node = $form['#parameters'][2];
  if ($values['post_text']) {
    //Post new item
    $post = array(
      'type' => 'post',
      'text' => $values['post_text'],
    );
    if (is_numeric($values['post_edit'])) {
      $post['id'] = $values['post_edit'];
      liveblog_modify_post($post, $node);
    } else {
      liveblog_create_post($post, $node); 
    }
  }
}

/**
 * function _liveblog_manage_twitter_form()
 * Add twitter subscription stuff - e.g.,
 * users to follow, hashtags, etc
 */
function _liveblog_manage_twitter_form(&$form_state, $node) {
  $form['twitter_configuration'] = array(
    '#type' => 'fieldset',
    '#collapsed' => TRUE,
    '#collapsible' => TRUE,
    '#title' => t('Twitter Configuration'),
    '#prefix' => '<div class="controls">',
  );
  $form['twitter_configuration']['twitter_follow'] = array(
    '#type' => 'textarea',
    '#title' => t('Twitter users to follow'),
    '#description' => t('If you have specific users whose tweets you would like to be automatically added to the liveblog, enter their twitter handles here, one per line.'),
    '#default_value' => $node->liveblog->twitter->twitter_follow,
  );
  $form['twitter_configuration']['twitter_hashtags'] = array(
    '#type' => 'textarea',
    '#title' => t('Twitter hashtags to follow'),
    '#description' => t('Enter any twitter hashtags you would like to follow and have entered into the timeline automatically, one per line.'),
    '#default_value' => $node->liveblog->twitter->twitter_hashtags,
  );
  return $form;
}

/**
 * function _liveblog_manage_comments_form()
 * Manage comments
 */
function _liveblog_manage_comments_form(&$form_state, $node) {
  $form['manage_comments'] = array(
    '#type' => 'markup',
    '#value' => _liveblog_manage_comments($node),
    '#suffix' => '</div>',
  );
  return $form;
}

/**
 * function _liveblog_manage_comments()
 * Pulls a list of all comment for editing.
 */
function _liveblog_manage_comments($node) {
  $output = '<fieldset id="manage_comments" class="collapsible">';
  $output .= '<legend>'.t('Manage comments').'</legend>';
  $output .= '<div class="manage-comments">';
  foreach (_liveblog_get_comments_from_ahah(TRUE) as $comments_html) {
    $output .= $comments_html;
  }
  $output .= '<br />';
  $output .= '</div><div id="load-more" class="comments">Load More</div>';
  $output .= '</fieldset>'; 
  return $output;
}

/**
 * function _liveblog_manage_posts_form()
 * Manage posts
 */
function _liveblog_manage_posts_form(&$form_state, $node) {
  $form['manage_posts'] = array(
    '#type' => 'markup',
    '#value' => _liveblog_manage_posts($node),
  );
  return $form;
}

/**
 * function _liveblog_manage_posts()
 * Pulls a list of all posts for editing.
 */
function _liveblog_manage_posts($node) {
  $output = '<fieldset  id="manage_posts" class="collapsible">';
  $output .= '<legend>'.t('Manage posts').'</legend>';
  $output .= '<div class="manage-posts">';
  foreach (_liveblog_get_posts_from_ahah(TRUE) as $posts_html) {
    $output .= $posts_html;
  }
  $output .= '<br />';
  $output .= '</div><div id="load-more" class="posts">Load More</div>';
  $output .= '</fieldset>'; 
  return $output;
}

