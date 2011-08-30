<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language; ?>" lang="<?php print $language->language; ?>" dir="<?php print $language->dir; ?>" <?php print $html_tag_attributes; ?>>
<head>
  <title><?php print $node->title; ?></title>
  <script type="text/javascript">
<?php
$aef_slh_display_order = variable_get('aef_slh_display_order', 'ASC');
?>
<?php print $js; ?>
/**
 * jQuery.ScrollTo - Easy element scrolling using jQuery.
 * Copyright (c) 2007-2008 Ariel Flesler - aflesler(at)gmail(dot)com | http://flesler.blogspot.com
 * Dual licensed under MIT and GPL.
 * Date: 9/11/2008
 * @author Ariel Flesler
 * @version 1.4
 *
 * http://flesler.blogspot.com/2007/10/jqueryscrollto.html
 */
;(function(h){var m=h.scrollTo=function(b,c,g){h(window).scrollTo(b,c,g)};m.defaults={axis:'y',duration:1};m.window=function(b){return h(window).scrollable()};h.fn.scrollable=function(){return this.map(function(){var b=this.parentWindow||this.defaultView,c=this.nodeName=='#document'?b.frameElement||b:this,g=c.contentDocument||(c.contentWindow||c).document,i=c.setInterval;return c.nodeName=='IFRAME'||i&&h.browser.safari?g.body:i?g.documentElement:this})};h.fn.scrollTo=function(r,j,a){if(typeof j=='object'){a=j;j=0}if(typeof a=='function')a={onAfter:a};a=h.extend({},m.defaults,a);j=j||a.speed||a.duration;a.queue=a.queue&&a.axis.length>1;if(a.queue)j/=2;a.offset=n(a.offset);a.over=n(a.over);return this.scrollable().each(function(){var k=this,o=h(k),d=r,l,e={},p=o.is('html,body');switch(typeof d){case'number':case'string':if(/^([+-]=)?\d+(px)?$/.test(d)){d=n(d);break}d=h(d,this);case'object':if(d.is||d.style)l=(d=h(d)).offset()}h.each(a.axis.split(''),function(b,c){var g=c=='x'?'Left':'Top',i=g.toLowerCase(),f='scroll'+g,s=k[f],t=c=='x'?'Width':'Height',v=t.toLowerCase();if(l){e[f]=l[i]+(p?0:s-o.offset()[i]);if(a.margin){e[f]-=parseInt(d.css('margin'+g))||0;e[f]-=parseInt(d.css('border'+g+'Width'))||0}e[f]+=a.offset[i]||0;if(a.over[i])e[f]+=d[v]()*a.over[i]}else e[f]=d[i];if(/^\d+$/.test(e[f]))e[f]=e[f]<=0?0:Math.min(e[f],u(t));if(!b&&a.queue){if(s!=e[f])q(a.onAfterFirst);delete e[f]}});q(a.onAfter);function q(b){o.animate(e,j,a.easing,b&&function(){b.call(this,r,a)})};function u(b){var c='scroll'+b,g=k.ownerDocument;return p?Math.max(g.documentElement[c],g.body[c]):k[c]}}).end()};function n(b){return typeof b=='object'?b:{top:b,left:b}}})(jQuery);
/**
 * Keeping our JS separate to avoid bugs
 */
/**
 * Variables available:
 * liveblog_start_time
 * liveblog_end_time
 * liveblog_refresh_rate
 * liveblog_active
 * liveblog_path
 **/

function _aef_slh_calculate_integer(time1, time2, adjust_result) {
  if (time1 == 0) {
    var d = new Date();
    time1 = (d.getTime())/1000;
  }
  var difference = time2 - time1;
  var rounded = Math.round(difference/liveblog_refresh_rate);
  var result = rounded * liveblog_refresh_rate;
  //This is all well and good, but we need a bit of room for 
  //different processing/cron times, etc. Remove one unit of
  //refresh rate from the result (as long as it's non-zero)
  //so that we're return the update just before this one.
  var adjusted_result = result - 30;
  if (adjusted_result > 0 && adjust_result == 1) {
    return adjusted_result;
  } else {
    return result;
  }
}

function _aef_slh_theme_post(post) {
  if (post['published'] == 1) {
    var html = "<div class='post' id='" + post['id'] + "'><div class='body'>" + post['text'] + "</div>";
    //Did it exist previously?
    if ($('#'+post['id']).length > 0) {//Yes, replace it.
      $('#'+post['id']).replaceWith(html);
    } else {//No, add new HTML!
      return html;
    }
  } else {
    //We delete posts by having published = 0 and then doing a .hide() on the element.
    $('#'+post['id']).replaceWith("");
  }
}

$(document).ready(function() { 
  //For updating, we should base the integer on the start_time and refresh_rate
  //UNLESS end_time is non-zero. In which case, find the diff between start and
  //end and compute based on that.
  
  //Let's get the server time! (header value is called "Date")
  //Base filename is event.html
  var cur_server_time = 0;
  if (liveblog_active && liveblog_active == 1) {
    var xhr = $.ajax({
      type: "GET",
      url: liveblog_path + "/event.html",
      success: function(output, status) {
        var d=new Date(xhr.getResponseHeader("Date"));
        var cur_server_time = (d.getTime()) / 1000;
        /*
         * Proceed with querying to the files
         */
        
        //Calculate the difference
        var cur_integer = parseInt(_aef_slh_calculate_integer(liveblog_start_time, cur_server_time, 1));
        $.getJSON(liveblog_path + "/master-" + cur_integer + ".json", function(data) {
          $.each(data, function(key, post) {
            var post_html = _aef_slh_theme_post(post);
            if (liveblog_refresh_order == 'DESC') {
              $("#container").append(post_html);
            } else if (liveblog_refresh_order == 'ASC') {
              $("#container").prepend(post_html);
            }
          });
          if (liveblog_refresh_order == 'DESC') {
            $.scrollTo($('.closure'));            
          } else if (liveblog_refresh_order == 'ASC') {
            //Nothing for now.
          }
        });
        //Now we process everything after this.
        
        var cur_integer_n = cur_integer;
        var cur_integer_s = cur_integer_n
        setInterval(function(){
          cur_integer_n = cur_integer_n + 30;
          var cur_integer_s = cur_integer_n;
          $.getJSON(liveblog_path + "/update-" + cur_integer_s + ".json", function(update) {
            $.each(update, function(key, post) {
              var post_html = _aef_slh_theme_post(post);
              if (liveblog_refresh_order == 'DESC') {
                $(post_html).appendTo("#container")
                .hide()
                .slideDown(250);
              } else if (liveblog_refresh_order == 'ASC') {
                $(post_html).prependTo("#container")
                .hide()
                .slideDown(250);
              }
            });
            if (liveblog_refresh_order == 'DESC') {
              $.scrollTo($('.closure'));            
            } else if (liveblog_refresh_order == 'ASC') {
              //Nothing for now.
            }
          });
        },(liveblog_refresh_rate * 1000));
      },
      error: function(output) {
        //@TODO
      }
    });
  } else {
    if (liveblog_end_time > 0) {
      //We don't care about refreshing if it's over.
      var cur_integer = _aef_slh_calculate_integer(liveblog_start_time, liveblog_end_time);
      $.getJSON(liveblog_path + "/master-" + cur_integer + ".json", function(data) {
        $.each(data, function(key, post) {
            var post_html = _aef_slh_theme_post(post);
            if (liveblog_refresh_order == 'DESC') {
              $("#container").append(post_html);
            } else if (liveblog_refresh_order == 'ASC') {
              $("#container").prepend(post_html);
            }
        });
        if (liveblog_refresh_order == 'DESC') {
            $.scrollTo($('.closure'));            
        } else if (liveblog_refresh_order == 'ASC') {
          //Nothing for now.
        }
      });
    }
  }
  
  $('form#post-comment').submit(function(event) {
    /* Stop form from submitting normally */
    event.preventDefault();
    $('input.submit').attr('disabled', 'disabled');
    /* get some values from elements on the page: */
    var $form = $(this);
    var user = $('input:text.username').val();
    var text = $('input:textarea.comment').val();
    var url = $form.attr('action');
    $.post(url, { username: user, comment: text, nid: liveblog_nid },
      function(data) {
        result = data;
        if (result['error']) {
          $('input.submit').removeAttr('disabled');
          $('<div class="error" style="display:none;">' + result['error'] + '</div>').appendTo('#comment-form').hide();
          $('.error').fadeIn(1000, function() {//Fade in
            window.setTimeout( function() {
              $('.error').fadeOut(1000, function() {//Remove the error after a few seconds
                $('.error').remove();
              });
            }, 5000);
          });
        } else if (result['response']) {//Same stuff as above but for the response code.
          $('<div class="notice" style="display:none;">' + result['response'] + '</div>').appendTo('#comment-form').hide();
          $('input.username').val('');
          $('textarea.comment').val('');
          $('.notice').fadeIn(1000, function() {
            window.setTimeout( function() {
              $('.notice').fadeOut(1000, function() {
                $('.notice').remove();
                $('input.submit').removeAttr('disabled');
              });
            }, 5000);
          });
        }
      }, "json"
    );
  });  
});
  /* setting some variables here */
  var liveblog_nid = <?php print $node->nid; ?>;
  var liveblog_start_time = <?php print $node->liveblog_master_data->start_timestamp; ?>;
  var liveblog_end_time = <?php print $node->liveblog_master_data->end_timestamp; ?>;
  var liveblog_refresh_rate = parseInt(<?php print $node->liveblog_master_data->refresh_rate; ?>);
  var liveblog_active = <?php print $node->liveblog_master_data->active; ?>;
  var liveblog_path = "<?php print $node->liveblog_path; ?>";
  var liveblog_refresh_order = "<?php print $aef_slh_display_order; ?>";
  </script>
  <style type="text/css">



body {
  font-family:helvetica;
  color: #323232;
}
.wrapper {
  width:637px;

  background: none repeat scroll 0 0;
  border-color: #C3C2C2;
  border-style: solid;
  border-width: 1px;
  margin: 0;
  padding:5px;
  overflow: hidden;
}
.title_bar {
  background: url("") no-repeat scroll 0 0 #E8E8E8;
  height: 25px;
  font-size:95%;
  font-weight: bold;
  padding:10px;
  margin-bottom:10px;
  border:1px solid #CFCECE;
}
#container {
  background: none repeat scroll 0 0 #F6F6F6;
  border: 1px solid #CFCECE;
  height:600px;
  overflow:auto;
  clear:both;
}
.post {
  border-bottom: 1px dotted #CFCECE;
  vertical-align: top;
  background-color:#FFF;
  padding:3px 10px;
}
.post .title, .post .title a {
  font-weight:bold;
  font-size:16px;
  text-decoration:none;
  color:#6F6F6F;
}
.post .body {
  font-size:12px;
  margin:5px 0px;
}
#comment-form {
  border: 1px solid #CFCECE;
  padding:3px 10px;
}
#comment-form.top {
  margin-top:0;
  margin-bottom:10px;
}
#comment-form.bottom {
  margin-top:10px;
  margin-bottom:0px;
}

#comment-form input, #comment-form textarea {
  border:1px solid #CFCFCE;
}
#comment-form textarea {
  width:615px;
  height:80px;
}
#comment-form .submit {
  margin:5px 0px;
} 
  </style>
</head>
<body class="<?php print $body_classes; ?>">
<div class="wrapper">
<div class="title_bar"><?php print $node->title; ?></div>
<?php if ($aef_slh_display_order == 'ASC') { ?>
  <div id="comment-form" class="top">
    <form action="<?php print base_path(); ?>tech/liveblog/post-comment" id="post-comment" method="POST">
      <label for="username"><?php print t('Username'); ?>:</label><br /><input type="text" class="username" name="username" /><br />
      <label for="comment"><?php print t('Your comment'); ?>:</label><br /><textarea class="comment" name="comment"></textarea><br />
      <input type="submit" class="submit" value="<?php print t('Submit'); ?>" />
    </form>
  </div>
  <div id="container">
  </div>
<?php } else if ($aef_slh_display_order == 'DESC') { ?>
  <div id="container">
  </div>
  <div id="comment-form" class="bottom">
    <form action="<?php print base_path(); ?>tech/liveblog/post-comment" id="post-comment" method="POST">
      <label for="username"><?php print t('Username'); ?>:</label><br /><input type="text" class="username" name="username" /><br />
      <label for="comment"><?php print t('Your comment'); ?>:</label><br /><textarea class="comment" name="comment"></textarea><br />
      <input type="submit" class="submit" value="<?php print t('Submit'); ?>" />
    </form>
  </div>
<?php } ?>
</div>
<div class="closure"></div>
</body>
</html>
