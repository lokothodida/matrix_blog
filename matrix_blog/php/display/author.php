<h3>Email</h3>
  <p><a href="mailto:<?php mblog_get_author_email($author); ?>"><?php mblog_get_author_email($author); ?></a></p>

<h3>About</h3>
  <?php mblog_get_author_bio($author); ?>

<h3>Recent Posts</h3>
  <?php mblog_get_author_latest($author, $max=5); ?>
  
<p><a href="<?php echo mblog_config('authorurl'); ?>">&larr; Back to Authors</a></p>