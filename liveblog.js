$(document).ready(function() { 
  var num_displayed_comments = 5;
  var num_displayed_posts = 5;
  //Unpublish or Publish a post
  $('a#post-modify').bind('click', function(e) {
    e.preventDefault();
    //Sent the AJAX call to unpublish the post.
    $(this).hide();
    $(this).siblings('.throbber').fadeIn('fast');
    var button = $(this);
    var actiontotake = $(this).attr('action');
    var parent_nid = $(this).attr('parent_nid');
    sendPublishData(this, actiontotake, button, parent_nid);
  });
  if ($('.manage-comments').length > 0) {//Go for comments populating
    //Load more comments onclick
    var loadMoreComments = $('#load-more.comments');
    loadMoreComments.bind('click', function(e) {
      e.preventDefault();
		  loadMoreComments.text('Loading...');
		  //begin the ajax attempt
      $.post(Drupal.settings.basePath + 'tech/liveblog/get-new-admin-comments', {nid: Drupal.settings.aefSlh['nid'], start: num_displayed_comments, limit: "5"}, function(data) {
        if (!data['html']) {
				  loadMoreComments.text('No more results remaining.');
        } else {
				  loadMoreComments.text('Load More');
          $.each(data['html'], function(post, posthtml) {
            $(posthtml).appendTo(".manage-comments")
            .hide()
					  .slideDown(250,function() {
							  $.scrollTo($('#load-more.comments'));
					  });
            num_displayed_comments++;
          });
        }
      }, "json");
    });
    //Check every few seconds for new comments, etc. APPEND to manage-comments or manage-posts.
    setInterval(function(){
      //First get the latest comment post ID #. This is the first result from the .manage-comments div.
      var mostRecentComment = $('.manage-comments div:first-child').attr('comment_id');
      //Get updated comments
      $.post(Drupal.settings.basePath + 'tech/liveblog/get-new-admin-comments', {nid: Drupal.settings.aefSlh['nid'], newest: mostRecentComment, start: num_displayed_comments, limit: "5"}, function(data) {
        if (data['html']) {
          $.each(data['html'], function(post, posthtml) {
            $(".manage-comments").prepend(posthtml);
            num_displayed_comments++;
          });
        }
      }, "json");
    },(15000));//Update every 15 seconds.
  }
  if ($('.manage-posts').length > 0) {//Go for comments populating
    //Load more posts onclick
    var loadMorePosts = $('#load-more.posts');
    loadMorePosts.bind('click', function(e) {
      e.preventDefault();
		  loadMorePosts.text('Loading...');
		  //begin the ajax attempt
      $.post(Drupal.settings.basePath + 'tech/liveblog/get-new-admin-posts', {nid: Drupal.settings.aefSlh['nid'], start: num_displayed_posts, limit: "5"}, function(data) {
        if (!data['html']) {
				  loadMorePosts.text('No more results remaining.');
        } else {
				  loadMorePosts.text('Load More');
          $.each(data['html'], function(post, posthtml) {
            $("<div id='" + num_displayed_posts + "'>" + posthtml + "</div>").appendTo(".manage-posts")
            .hide()
					  .slideDown(250,function() {
							  $.scrollTo($('#load-more.posts'));
					  });
            num_displayed_posts++;
          });
        }
      }, "json");
    });
    //Check every few seconds for new posts, etc. APPEND to manage-posts.
    setInterval(function(){
      //First get the latest comment post ID #. This is the first result from the .manage-comments div.
      var mostRecentPost = $('.manage-posts div:first-child').attr('post_id');
      //Get updated posts
      $.post(Drupal.settings.basePath + 'tech/liveblog/get-new-admin-posts', {nid: Drupal.settings.aefSlh['nid'], newest: mostRecentPost, start: num_displayed_posts, limit: "5"}, function(data) {
        if (data['html']) {
          $.each(data['html'], function(post, posthtml) {
            $(".manage-posts").prepend(posthtml);
            num_displayed_posts++;
          });
        }
      }, "json");
    },(15000));//Update every 15 seconds.
  }
});

function sendPublishData(target, actiontotake, button, parent_nid) {
  $.post($(target).attr('href'), { action: actiontotake, id: $(target).attr('post_id'), nid: parent_nid },
    function(data) {
      var result = data;
        if (result['error']) {
          button.siblings('.throbber').hide();
          button.fadeIn('fast');
        } else {
          button.siblings('.throbber').hide();
          button.text(result['button_text']).replace;
          button.attr('action', result['button_action']);
          button.fadeIn('fast');          
        }
    }, "json"
  );
}
