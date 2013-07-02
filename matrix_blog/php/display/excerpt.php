<div class="entry">
  <div class="date">  
    <span class="day"><?php mblog_get_field_date($entry, 'credate', 'd'); ?></span>
    <span class="month"><?php mblog_get_field_date($entry, 'credate', 'm'); ?></span>
    <span class="year"><?php mblog_get_field_date($entry, 'credate', 'Y'); ?></span>
  </div>
  <h3><a href="<?php mblog_get_entry_url($entry); ?>"><?php mblog_get_field($entry, 'title'); ?></a></h3>
  <?php if (!empty($entry['image'])) { ?>
    <a href="<?php mblog_get_img_url($entry); ?>" target="_blank">
      <img class="thumb" src="<?php echo mblog_get_thumb_url($entry); ?>">
    </a>
  <?php } ?>
  <h4><?php mblog_get_field($entry, 'subtitle'); ?></h4>
  <h5>
    Posted by <a href="<?php mblog_get_author_url($entry); ?>"><?php mblog_get_author_name($entry); ?></a> under 
    <a href="<?php mblog_get_category_url($entry); ?>">
      <?php mblog_get_category($entry); ?>
    </a>
  </h5>
  <p><?php mblog_get_field($entry, 'content'); ?></p>
  
  <div class="clear"></div>
  <!-- AddThis Button BEGIN -->
    <div class="addthis_toolbox addthis_default_style addthis_32x32_style" style="overflow: hidden;"
        addthis:url="<?php echo $this->getEntryURL($entry); ?>"
        addthis:title="<?php mblog_get_field($entry, 'title'); ?>">
    <a class="addthis_button_preferred_1"></a>
    <a class="addthis_button_preferred_2"></a>
    <a class="addthis_button_preferred_3"></a>
    <a class="addthis_button_preferred_4"></a>
    <a class="addthis_button_compact"></a>
    <a class="addthis_counter addthis_bubble_style"></a>
    <span class="commentsTotal" style="float: right;">Comments: <a href="<?php echo $this->getEntryURL($entry); ?>#comments"><?php echo $comments['total']; ?></a></span>
    </div>
    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-51cefa386e2eab2a"></script>
  <!-- AddThis Button END -->
</div>