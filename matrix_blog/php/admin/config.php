<?php
  // save changes
  if ($_SERVER['REQUEST_METHOD']=='POST') {
    // update the record
    $update = $matrix->updateRecord(self::TABLE_CONFIG, 0, $_POST);
    
    // success message
    if ($update) {
      $undo = 'load.php?id='.self::FILE.'&config&undo';
      $matrix->getAdminError(i18n_r(self::FILE.'/CONFIG_UPDATESUCCESS'), true, true, $undo);
      
      // fix categories schema and image upload settings
      $this->schema['fields']['category']['options'] = $update['new']['category']['@cdata'];
      
      // fix format for image upload
      $imageconfig = $matrix->explodeTrim("\n", $update['new']['imageconfig']);
      $imageconfig[0] = (int)$imageconfig[0]*10*10*10*1024; // mb conversion
      $imageconfig = $matrix->implodeTrim("\n", $imageconfig);
      $this->schema['fields']['image']['options'] = $imageconfig;
      
      // fix languages
      $this->schema['fields']['language']['options'] = $update['new']['language']['@cdata'];
      
      // fix comments-based schema
      $commentsconfigint = $matrix->explodeTrim("\n", $update['new']['commentsconfigint']);
      $commentsconfigcheck = $matrix->explodeTrim("\n", $update['new']['commentsconfigcheck']);
      $minmax = '.{'.$commentsconfigint[1].', '.$commentsconfigint[2].'}';
      $this->commentsSchema['fields']['content']['maxlength'] = $commentsconfigint[2];
      $this->commentsSchema['fields']['content']['validation'] = $minmax;
      
      
      // change 'required' fields settings
      if (in_array(i18n_r(self::FILE.'/OPTION_REQUIRENAME'), $commentsconfigcheck)) {
        $this->commentsSchema['fields']['name']['required'] = 'required';
      }
      else {
        $this->commentsSchema['fields']['name']['required'] = '';
      }
      if (in_array(i18n_r(self::FILE.'/OPTION_REQUIREEMAIL'), $commentsconfigcheck)) {
        $this->commentsSchema['fields']['email']['required'] = 'required';
      }
      else {
        $this->commentsSchema['fields']['email']['required'] = '';
      }
      if (in_array(i18n_r(self::FILE.'/OPTION_REQUIREURL'), $commentsconfigcheck)) {
        $this->commentsSchema['fields']['url']['required'] = 'required';
      }
      else {
        $this->commentsSchema['fields']['url']['required'] = '';
      }
      
      // modify the schemas
      $matrix->modSchema(self::TABLE_BLOG, $this->schema);
      $matrix->modSchema(self::TABLE_COMMENTS, $this->commentsSchema);
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
    $undo = $matrix->undoRecord(self::TABLE_CONFIG, 0);
    
    // success message
    if ($undo) {
      $matrix->getAdminError(i18n_r(self::FILE.'/CONFIG_UNDOSUCCESS'), true);
      
      // fix categories schema
      $record = $matrix->recordExists(self::TABLE_CONFIG, 0);
      $this->schema['fields']['category']['options'] = $record['category'];
      
      // fix languages
      $this->schema['fields']['language']['options'] = $record['language'];
      
      // fix format for image upload
      $imageconfig = $matrix->explodeTrim("\n", $record['imageconfig']);
      $imageconfig[0] = (int)$imageconfig[0]*10*10*10*1024; // mb conversion
      $imageconfig = $matrix->implodeTrim("\n", $imageconfig);
      $this->schema['fields']['image']['options'] = $imageconfig;
      
      // modify the schema
      $matrix->modSchema(self::TABLE_BLOG, $this->schema);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/CONFIG_UNDOERROR'), false);
    }
    // refresh the index to reflect the changes
    $matrix->refreshIndex();
  }
?>

<!--header-->
  <h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE_CONFIG'); ?></h3>
  <div class="edit-nav">
    <a href="load.php?id=<?php echo self::FILE; ?>&config" class="current"><?php echo i18n_r(self::FILE.'/LABEL_CONFIG'); ?></a>
    <a href="load.php?id=matrix&table=<?php echo self::TABLE_BLOG; ?>&fields" target="_blank"><?php echo i18n_r(self::FILE.'/LABEL_FIELDS'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>&template=entry"><?php echo i18n_r(self::FILE.'/LABEL_TEMPLATES'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>"><?php echo i18n_r(self::FILE.'/ENTRIES'); ?></a> 
    <div class="clear"></div>
  </div>
  
<!--config-->
  <form method="post" >
    <?php $matrix->displayForm(self::TABLE_CONFIG, 0); ?>
    <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>
  </form>