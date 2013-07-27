<?php
  // save order
  if ($_SERVER['REQUEST_METHOD']=='POST') {
    $reorder = array();
    foreach ($_POST['order'] as $order => $entry) {
      $reorder[] = $matrix->updateRecord('matrix-blog', $entry, array('order' => $order));
    }
    
    $matrix->getAdminError(i18n_r(self::FILE.'/MENU_UPDATESUCCESS'), true);
  }
  
  // load pages
  $blog  = $matrix->query('SELECT * FROM matrix-blogORDER BY order ASC');
?>

<!--header-->
  <h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE_MENU'); ?></h3>
  <div class="edit-nav">
    <a href="load.php?id=<?php echo self::FILE; ?>&menu" class="current"><?php echo i18n_r(self::FILE.'/LABEL_MENU'); ?></a>
    <a href="load.php?id=matrix&table=<?php echo self::TABLE_BLOG; ?>&fields" target="_blank"><?php echo i18n_r(self::FILE.'/LABEL_FIELDS'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>&search"><?php echo i18n_r(self::FILE.'/LABEL_SEARCH'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>&template"><?php echo i18n_r(self::FILE.'/LABEL_TEMPLATE'); ?></a>
    <a href="load.php?id=<?php echo self::FILE; ?>"><?php echo i18n_r('VIEW'); ?></a> 
    <div class="clear"></div>
  </div>
  
<!--menu-->
  <form method="post">
    <table class="pajinate edittable highlight">
      <thead>
        <tr>
          <th colspan="100%"></th>
        </tr>
      </thead>
      
      <tbody class="sortable">
        <?php foreach ($blog as $entry) { ?>
        <tr>
          <td <?php if ($entry['menu']==i18n_r(self::FILE.'/OPTION_NO')) echo 'style="color:grey;"'; ?>><input type="hidden" name="order[]" value="<?php echo $entry['id']?>"><?php echo $entry['title']; ?></a></td>
        </tr>
        <?php } ?>
        <?php if (empty($blog)) { ?>
        <tr>
          <td><?php echo i18n_r(self::FILE.'/PAGES_NONE'); ?></td>
        </tr>
        <?php } ?>
      </tbody>
      
    </table>
    <?php if (!empty($blog)) { ?>
    <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>
    <?php } ?>
  </form>
  
<script>
  $(document).ready(function() {
    $('.sortable').sortable();
  }); // ready
</script>