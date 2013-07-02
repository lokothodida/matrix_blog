<!--header-->
<h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE_CREATEENTRY'); ?></h3>
<div class="edit-nav">
  <a href="load.php?id=<?php echo self::FILE; ?>"><?php echo i18n_r(self::FILE.'/BACK'); ?></a> 
  <a href="#" id="metadata_toggle"><?php echo i18n_r(self::FILE.'/LABEL_ENTRYOPTIONS'); ?></a>
  <div class="clear"></div>
</div>

<!--content-->
<form method="post" action="load.php?id=<?php echo self::FILE; ?>" enctype="multipart/form-data">
  <?php $matrix->displayForm(self::TABLE_BLOG); ?>
  <input type="submit" class="submit" name="create" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>
</form>

<!--scripts-->
<script>
  $(document).ready(function(){
    $('#metadata_window').hide();
  }); // ready
</script>