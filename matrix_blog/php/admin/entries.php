<?php
  // page created
  if (isset($_POST['create'])) {
    // fix fields
    if (empty($_POST['post-slug'])) {
      $_POST['post-slug'] = $matrix->str2slug($_POST['post-title']);
    }
    
    // create the page
    $create = $matrix->createRecord(self::TABLE_BLOG, $_POST);
    
    // success message
    if ($create) {
      $entry = $matrix->query('SELECT * FROM '.self::TABLE_BLOG.' ORDER BY id DESC', 'SINGLE', $cache=false); // get latest page (newly created)
      $msg  = str_replace('%s', $entry['slug'], i18n_r(self::FILE.'/PAGES_CREATESUCCESS'));
      $matrix->getAdminError($msg, true);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/PAGES_CREATEERROR'), false);
    }
    
    // refresh the index to reflect the changes
    $matrix->refreshIndex();
  }
  
  // page deleted
  if (isset($_GET['delete'])) {
    $entry = $matrix->query('SELECT * FROM '.self::TABLE_BLOG.' WHERE id = "'.$_GET['delete'].'"', 'SINGLE', $cache=false); // $cache is set to false so that the query below doesn't still contain the deleted record
    // delete the page
    $delete = $matrix->deleteRecord(self::TABLE_BLOG, $_GET['delete']);
    
    // success message
    if ($delete) {
      $msg  = str_replace('%s', $entry['slug'], i18n_r(self::FILE.'/PAGES_DELETESUCCESS'));
      $undo = 'load.php?id='.self::FILE.'&undo='.$_GET['delete'];
      $matrix->getAdminError($msg, true, true, $undo);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/PAGES_DELETEERROR'), false);
    }
    
    // refresh the index to reflect the changes
    $matrix->refreshIndex();
  }
  
  // undo page deletion
  elseif (isset($_GET['undo'])) {
    // undo the page deletion
    $undo = $matrix->undoRecord(self::TABLE_BLOG, $_GET['undo']);
    
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
  
  // get matrix-blog & config
  $blog = $matrix->query('SELECT * FROM '.self::TABLE_BLOG.' ORDER BY credate DESC');
?>

<!--header-->
  <h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE'); ?></h3>
  <div class="edit-nav">
    <a href="load.php?id=<?php echo self::FILE; ?>&config"><?php echo i18n_r(self::FILE.'/LABEL_CONFIG'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>&template=entry"><?php echo i18n_r(self::FILE.'/LABEL_TEMPLATES'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>" class="current"><?php echo i18n_r(self::FILE.'/ENTRIES'); ?></a> 
    <a href="<?php echo $this->config['url']; ?>" target="_blank"><?php echo i18n_r('VIEW'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&compatibility"><?php echo i18n_r(self::FILE.'/COMPATIBILITY'); ?></a> 
    <div class="clear"></div>
  </div>

<!--pages-->
  <table class="pajinate edittable highlight">
    <thead>
      <tr>
        <th colspan="100%" id="order" style="overflow: hidden;">
          <div style="float: left;">
            <?php echo i18n_r(self::FILE.'/LABEL_ORDERBY'); ?>: [
            <a href="#" class="cancel" data-sort="title"><?php echo i18n_r(self::FILE.'/LABEL_TITLE'); ?></a>
            <a href="#" class="cancel" data-sort="slug"><?php echo i18n_r(self::FILE.'/LABEL_SLUG'); ?></a>
            <a href="#" class="cancel" data-sort="language"><?php echo i18n_r(self::FILE.'/LABEL_LANGUAGE'); ?></a>
            <a href="#" class="cancel" data-sort="credate"><?php echo i18n_r(self::FILE.'/LABEL_CREDATE'); ?></a>
            <a href="#" class="cancel" data-sort="pubdate"><?php echo i18n_r(self::FILE.'/LABEL_PUBDATE'); ?></a>
            ]
          </div>
          <form style="float: right;">
            <input class="" style="display: inline; width: 100px;" type="text" id="search_input" placeholder=""/>
          </form>
        </th>
      </tr>
    </thead>
    <tbody class="content">
      <?php
        foreach ($blog as $entry) {
      ?>
      <tr data-title="<?php echo $entry['title']; ?>" data-slug="<?php echo $entry['slug']; ?>" data-language="<?php echo $entry['language']; ?>" data-credate="<?php echo $entry['credate']; ?>" data-pubdate="<?php echo $entry['pubdate']; ?>">
        <td style="text-align: left; width: 58%;">
          <a href="load.php?id=<?php echo self::FILE; ?>&edit=<?php echo $entry['id']; ?>"><?php echo $entry['title']; ?></a>
        </td>
        <td style="text-align: right; width: 30%"><?php echo $entry['pubdate']; ?></td>
        <td style="text-align: right; width: 5%;">[<?php echo $entry['language']; ?>]</td>
        <td style="text-align: right; width: 7%;">
          <a href="<?php echo $this->getEntryURL($entry); ?>?setlang=<?php echo $entry['language']; ?>" target="_blank" class="cancel">#</a> 
          <a href="load.php?id=<?php echo self::FILE; ?>&delete=<?php echo $entry['id']; ?>" class="cancel">&times;</a>
        </td>
      </tr>
      <?php } ?>
      <?php if (empty($blog)) { ?>
      <tr>
        <td><?php echo i18n_r(self::FILE.'/PAGES_NONE'); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div style="overflow: hidden;">
    <div style="float: left;"><i><?php echo str_replace('%s', count($blog), i18n_r(self::FILE.'/TOTAL_ENTRIES')); ?></i></div>
    <div style="float: right; font-size: 10px;">
      <input type="submit" class="submit createpage" style="display:none;" value="<?php echo i18n_r(self::FILE.'/TITLE_CREATEENTRY'); ?>"/>
      <a href="load.php?id=<?php echo self::FILE; ?>&create" class="createpage cancel"><?php echo strtoupper(i18n_r(self::FILE.'/TITLE_CREATEENTRY')); ?></a>
    </div>
  </div>
  
<!--script-->
  <script>
    $(document).ready(function() {
      // create page button
        $('a.createpage').hide();
        $('input.createpage').show();
        $('input.createpage').click(function() {
          window.location.href = '<?php echo $this->siteurl; ?>load.php?id=<?php echo self::FILE; ?>&create';
          return false;
        });
      
      // filter
        $('#search_input').fastLiveFilter('.content');
      
      // table sorting
        $('#order a').toggle(
          function() {
            $('table tbody tr').tsort({attr:'data-' + $(this).data('sort'), order:'asc'});
          },
          function () {
            $('table tbody tr').tsort({attr:'data-' + $(this).data('sort'), order:'desc'});
          }
        ); // toggle
    }); // ready
  </script>