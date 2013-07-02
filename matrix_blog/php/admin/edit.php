<?php
  // save changes
  if ($_SERVER['REQUEST_METHOD']=='POST') {
    // fix fields
    $_POST['post-pubdate'] = time();
    if (empty($_POST['post-slug'])) {
      $_POST['post-slug'] = $matrix->str2slug($_POST['post-title']);
    }
    
    // update the record
    $update = $matrix->updateRecord(self::TABLE_BLOG, $_GET['edit'], $_POST);
    
    // success message
    if ($update) {
      $msg  = str_replace('%s', $update['new']['slug'], i18n_r(self::FILE.'/PAGES_UPDATESUCCESS'));
      $undo = 'load.php?id='.self::FILE.'&edit='.$_GET['edit'].'&undo';
      $matrix->getAdminError($msg, true, true, $undo);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/PAGES_UPDATEERROR'), false);
    }
    
    // refresh the index to reflect the changes
    $matrix->refreshIndex();
  }
  
  // undo changes
  elseif (isset($_GET['undo'])) {
    // undo the record update
    $undo = $matrix->undoRecord(self::TABLE_BLOG, $_GET['edit']);
    
    // success message
    if ($undo) {
      $matrix->getAdminError(i18n_r(self::FILE.'/PAGES_UNDOSUCCESS'), true);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/PAGES_UNDOERROR'), false);
    }
    
    // refresh the index to reflect the changes
    $matrix->refreshIndex();
  }
  
  // get page information
  $entry = $matrix->query('SELECT * FROM '.self::TABLE_BLOG.' WHERE id = '.$_GET['edit'], 'SINGLE');
?>

<!--header-->
  <h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE_EDITENTRY'); ?></h3>
  <div class="edit-nav">
    <a href="load.php?id=<?php echo self::FILE; ?>"><?php echo i18n_r(self::FILE.'/BACK'); ?></a> 
    <a href="<?php echo $this->getEntryURL($entry); ?>" target="_blank"><?php echo i18n_r('VIEW'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&comments=9"><?php echo i18n_r(self::FILE.'/COMMENTS'); ?></a> 
    <a href="#" id="metadata_toggle"><?php echo i18n_r(self::FILE.'/LABEL_ENTRYOPTIONS'); ?></a>
    <div class="clear"></div>
  </div>

<!--content-->
  <form method="post" enctype="multipart/form-data">
    <?php $matrix->displayForm(self::TABLE_BLOG, $entry['id']); ?>
    <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>
  </form>

<!--scripts-->
  <script>
    $(document).ready(function(){
      $('#metadata_window').hide();
    }); // ready
  </script>