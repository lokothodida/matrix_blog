<h3><?php echo i18n_r(self::FILE.'/MISSING_DEPENDENCIES'); ?></h3>
<p><?php echo i18n_r(self::FILE.'/PLEASE_INSTALL'); ?>:</p>
<table class="highlight edittable">
  <tbody>
    <?php foreach ($dependencies as $dependency) { ?>
    <tr>
      <td><a href="<?php echo $dependency['url']; ?>"><?php echo $dependency['name']; ?></a></td>
    </tr>
    <?php } ?>
  </tbody>
</table>