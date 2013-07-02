<?php
  // delete comment
  if (isset($_GET['delete'])) {
    $delete = $matrix->deleteRecord(self::TABLE_COMMENTS, $_GET['delete']);
    
    // success message
    if ($delete) {
      $undo = 'load.php?id='.self::FILE.'&comments='.$_GET['comments'].'&undo='.$_GET['delete'];
      $matrix->getAdminError(i18n_r(self::FILE.'/COMMENT_DELETESUCCESS'), true, true, $undo);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/COMMENT_DELETEERROR'), false);
    }
  }
  // undo comment deletion
  elseif (isset($_GET['undo'])) {
    $undo = $matrix->undoRecord(self::TABLE_COMMENTS, $_GET['undo']);
    
    // success message
    if ($undo) {
      $matrix->getAdminError(i18n_r(self::FILE.'/COMMENT_UNDOSUCCESS'), true);
    }
    // error message
    else {
      $matrix->getAdminError(i18n_r(self::FILE.'/COMMENT_UNDOERROR'), false);
    }
  }
  
  // get comments
  $entry = $matrix->query('SELECT * FROM '.self::TABLE_BLOG.' WHERE id = '.$_GET['comments'], 'SINGLE');
  $comments = $matrix->query('SELECT * FROM '.self::TABLE_COMMENTS.' WHERE entry = '.$_GET['comments'].' ORDER BY date DESC');
?>


<!--header-->
  <h3 class="floated"><?php echo i18n_r(self::FILE.'/TITLE_COMMENTS'); ?></h3>
  <div class="edit-nav">
    <a href="load.php?id=<?php echo self::FILE; ?>&edit=<?php echo $entry['id']; ?>"><?php echo i18n_r(self::FILE.'/BACK'); ?></a> 
    <a href="<?php echo $this->getEntryURL($entry); ?>" target="_blank"><?php echo i18n_r('VIEW'); ?></a> 
    <a href="load.php?id=<?php echo self::FILE; ?>&comments=<?php echo $_GET['comments'];?>" class="current"><?php echo i18n_r(self::FILE.'/COMMENTS'); ?></a> 
    <div class="clear"></div>
  </div>

<!--pages-->
  <table class="pajinate edittable highlight">
    <thead>
      <tr>
        <th colspan="100%" id="order" style="overflow: hidden;">
          <div style="float: left;">
            <?php echo i18n_r(self::FILE.'/LABEL_ORDERBY'); ?>: [
            <a href="#" class="cancel" data-sort="name"><?php echo i18n_r(self::FILE.'/LABEL_AUTHOR'); ?></a>
            <a href="#" class="cancel" data-sort="date"><?php echo i18n_r(self::FILE.'/LABEL_DATE'); ?></a>
            ]
          </div>
          <form style="float: right;">
            <input class="" style="display: inline; width: 100px;" type="text" id="search_input" placeholder="<?php echo i18n_r(self::FILE.'/FILTER'); ?>"/>
          </form>
        </th>
      </tr>
    </thead>
    <tbody class="content">
      <?php
        $i = count($comments);
        foreach ($comments as $key => $comment) {
          if (!empty($comment['username'])) {
            $comment['name'] = $this->getAuthorName($comment['username']);
            $comment['email'] = $this->getAuthorField($comment['username'], 'EMAIL');
          }
      ?>
      <tr class="comment" data-name="<?php echo $comment['name']; ?>" data-date="<?php echo $comment['date']; ?>">
        <td style="width: 1%;"><b>#</b><?php echo $i; ?></td>
        <td style="width: 77%;">
          <a href="mailto:<?php echo $comment['email']; ?>"><?php echo $comment['name']; ?></a> @ <?php echo date('r', $comment['date']); ?><br />
          <?php echo $this->parser->bbcode($comment['content']); ?>
        </td>
        <td style="width: 10%;"><?php echo $comment['ip']; ?></td>
        <td style="width: 7%; text-align: right;">
          <a href="#" data-id="<?php echo $comment['id']; ?>" class="cancel delete">&times;</a></td>
      </tr>
      <?php
          $i--;
        }
        if (empty($comments)) { ?>
      <tr>
        <td><?php echo i18n_r(self::FILE.'/COMMENTS_NONE'); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div><i><?php echo str_replace('%s', count($comments), i18n_r(self::FILE.'/TOTAL_COMMENTS')); ?></i></div>
  
<!--script-->
  <script>
    $(document).ready(function() {
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
  $('.comment .delete').bind('click', function(e) {
    var recordID = $(this).data('id');
      e.preventDefault();
      $.Zebra_Dialog(<?php echo json_encode(i18n_r(self::FILE.'/COMMENT_AREYOUSURE')); ?>, {
          'type':     'question',
          'title':    <?php echo json_encode(i18n_r(self::FILE.'/COMMENT_DELETING')); ?>,
          'buttons':  [
                {caption: <?php echo json_encode(i18n_r(self::FILE.'/OPTION_NO')); ?>, },
                {caption: <?php echo json_encode(i18n_r(self::FILE.'/OPTION_YES')); ?>, callback: function() { window.location = 'load.php?id=<?php echo self::FILE; ?>&comments=<?php echo $_GET['comments']; ?>&delete=' + recordID }},
            ]
      });
  }); // bind
</script>