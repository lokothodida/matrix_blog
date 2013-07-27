<?php
  // save changes
  if ($_SERVER['REQUEST_METHOD']=='POST') {
    // update the search template
    $update = file_put_contents($this->templates['search'], $_POST['edit-search']);
    
        // success message
    if ($update) {
      $matrix->getAdminError(i18n_r(self::FILE.'/SEARCH_UPDATESUCCESS'), true);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/SEARCH_UPDATEERROR'), false);
    }
  }
?>

<!--header-->
  <h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE_SEARCH'); ?></h3>
  <div class="edit-nav">
    <a href="load.php?id=<?php echo self::FILE; ?>"><?php echo i18n_r(self::FILE.'/BACK'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>&template=sidebar"><?php echo i18n_r(self::FILE.'/LABEL_SIDEBAR'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&template=search" class="current"><?php echo i18n_r(self::FILE.'/LABEL_SEARCH'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&template=excerpt"><?php echo i18n_r(self::FILE.'/LABEL_EXCERPT'); ?></a>  
    <a href="load.php?id=<?php echo self::FILE; ?>&template"><?php echo i18n_r(self::FILE.'/ENTRY'); ?></a> 
    <div class="clear"></div>
  </div>
  
<!--search template-->
  <form method="post">
    <textarea name="edit-search" class="codeeditor DM_codeeditor text" id="post-edit-search"><?php echo file_get_contents($this->templates['search']); ?></textarea>
    <?php
      // get codemirror script
      $matrix->initialiseCodeMirror();
      $matrix->instantiateCodeMirror('edit-search');
    ?>
    <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>
  </form>