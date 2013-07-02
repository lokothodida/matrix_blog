<!--content-->
  <div id="entry">
    <div class="date">  
      <span class="day"><?php mblog_get_field_date($entry, 'credate', 'd'); ?></span>
      <span class="month"><?php mblog_get_field_date($entry, 'credate', 'm'); ?></span>
      <span class="year"><?php mblog_get_field_date($entry, 'credate', 'Y'); ?></span>
    </div>
    <h3><?php mblog_get_field($entry, 'subtitle'); ?></h3>
    <div id="entrycontent" class="entry-<?php mblog_get_field($entry, 'slug'); ?>">
      <?php mblog_get_entry_langs($entry); ?>
      <h4>
        Posted by <a href="<?php mblog_get_author_url($entry); ?>"><?php mblog_get_author_name($entry); ?></a> under 
        <a href="<?php mblog_get_category_url($entry); ?>">
          <?php mblog_get_category($entry); ?>
        </a>
      </h4>
      <img src="<?php mblog_get_img_url($entry); ?>"/>
      <?php mblog_get_field($entry, 'content'); ?>
      <div class="tags">
        <?php mblog_get_tags($entry, ''); ?>
      </div>
    </div>
  </div>
  <a href="<?php echo mblog_config('url'); ?>">← Go Back</a>
  <hr />
  
<!--addthis-->
  <div class="addthis_toolbox addthis_default_style addthis_32x32_style" style="float: right;">
    <div>
      <a class="addthis_button_preferred_1"></a>
      <a class="addthis_button_preferred_2"></a>
      <a class="addthis_button_preferred_3"></a>
      <a class="addthis_button_preferred_4"></a>
      <a class="addthis_button_compact"></a>
      <a class="addthis_counter addthis_bubble_style"></a>
    </div>
  </div>
  <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-51cde3af27238b94"></script>
  
<!--comments-->
  <?php mblog_get_entry_comments($entry); ?>
  <div class="clear" style="height:10px;"></div>