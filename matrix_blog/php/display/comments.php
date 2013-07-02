<!--comments-->
  <div class="comment <?php mblog_get_comment_field($comment, 'isadmin'); ?> <?php mblog_get_comment_field($comment, 'isauthor'); ?>">
    <a name="comment<?php mblog_get_comment_field($comment, 'number'); ?>"></a>
    <img class="gravatar" src="<?php mblog_get_comment_gravatar($comment); ?>" alt="" />
    <div class="details">
      Posted by <a href="mailto:<?php mblog_get_comment_field($comment, 'email'); ?>"><?php mblog_get_comment_field($comment, 'name'); ?></a> 
      @ <?php echo date('r', $comment['date']); ?>
    </div>
    <div class="commentcontent"><?php mblog_get_comment_field($comment, 'content'); ?></div>
    <!--raw bbcode-->
    <div class="rawcontent" style="display: none;"><?php mblog_get_comment_field($comment, 'rawcontent'); ?></div>
    <div class="options">
      <a href="#" class="quote">Quote</a>
      <a href="#postreply" class="reply">Reply</a>
    </div>
  </div>