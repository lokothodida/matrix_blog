<!--header-->
  <h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE_SIDEBAR'); ?></h3>
  <div class="edit-nav">
    <a href="load.php?id=<?php echo self::FILE; ?>"><?php echo i18n_r(self::FILE.'/BACK'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>&template=sidebar" class="current"><?php echo i18n_r(self::FILE.'/LABEL_SIDEBAR'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&template=author"><?php echo i18n_r(self::FILE.'/AUTHOR'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&template=comments"><?php echo i18n_r(self::FILE.'/COMMENTS'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>&template=excerpt"><?php echo i18n_r(self::FILE.'/LABEL_EXCERPT'); ?></a>  
    <a href="load.php?id=<?php echo self::FILE; ?>&template=entry"><?php echo i18n_r(self::FILE.'/ENTRY'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&template=header"><?php echo i18n_r(self::FILE.'/LABEL_HEADER'); ?></a> 
    <div class="clear"></div>
  </div>
  
<!--entry template-->
  <form method="post">
    <textarea name="edit-template" class="codeeditor DM_codeeditor text" id="post-edit-template"><?php echo $template; ?></textarea>
    <?php
      // get codemirror script
      $matrix->initialiseCodeMirror();
      $matrix->instantiateCodeMirror('edit-template');
    ?>
    <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>
  </form>