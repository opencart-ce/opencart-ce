<?php echo $header; ?>
<div id="content">
<div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
  <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
</div>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<div class="box">
  <div class="heading">
    <h1><img src="view/image/module.png" alt="<?php echo $heading_title; ?>" /><?php echo $heading_title; ?></h1>
    <div class="buttons"><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
  </div>
  <div class="content">
    <?php if ($vqmod_is_installed == true) { ?>
    <div id="tabs" class="htabs"><a href="#tab-scripts"><?php echo $tab_scripts; ?></a><a href="#tab-settings"><?php echo $tab_settings; ?></a><a href="#tab-error"><?php echo $tab_error_log; ?></a><a href="#tab-about"><?php echo $tab_about; ?></a></div>
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
    <div id="tab-scripts">
      <table class="vqmm-list">
        <thead>
          <tr>
            <th class="left"><?php echo $column_file_name; ?><hr /><i><?php echo $column_id; ?></i></th>
            <th class="center"><?php echo $column_version; ?></th>
            <th class="center"><?php echo $column_vqmver; ?></th>
            <th class="center"><?php echo $column_author; ?></th>
            <th class="center"><?php echo $column_status; ?></th>
            <th class="center"><?php echo $column_action; ?></th>
            <th class="center"><?php echo $column_delete; ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if ($vqmods) { ?>
          <?php $class = 'row-odd'; ?>
          <?php foreach ($vqmods as $vqmod) { ?>
          <?php $class = ($class == 'row-even' ? 'row-odd' : 'row-even'); ?>
          <tr class="<?php echo $class; ?>">
            <td class="left"><strong><?php echo $vqmod['file_name']; ?></strong><br /><div class="description"><?php echo $vqmod['id']; ?><br /><?php echo $vqmod['invalid_xml']; ?></div></td>
            <td class="center"><?php echo $vqmod['version']; ?></td>
            <td class="center"><?php echo $vqmod['vqmver']; ?></td>
            <td class="center"><?php echo $vqmod['author']; ?></td>
            <td class="center"><?php echo $vqmod['status'] ?></td>
            <td class="action"><?php foreach ($vqmod['action'] as $action) { ?>
              [ <a class="action-link" href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
              <?php } ?></td>
            <td class="action">
              [ <a class="action-link" href="<?php echo $vqmod['delete'] ?>"><?php echo $text_delete; ?></a> ]</td>
          </tr>
          <?php } ?>
          <?php } else { ?>
          <tr>
            <td class="center" colspan="7"><?php echo $text_no_results; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <table class="form">
        <tr>
          <td><?php echo $entry_upload; ?>&nbsp;<input type="file" name="vqmod_file" /><input type="submit" name="upload" value="<?php echo $text_upload; ?>" /></td>
        </tr>
      </table>
    </div>
    <div id="tab-settings">
      <table class="form">
         <tr>
           <td><?php echo $entry_vqcache; ?><br /><span class="help"><?php echo $text_vqcache_help; ?></span></td>
           <td>
             <select multiple="multiple" size="7" id="vqcache">
               <?php foreach ($vqcache as $vqcache_file) { ?>
                <option><?php echo $vqcache_file; ?></option>
               <?php } ?>
             </select><br />
             <a href="<?php echo $clear_vqcache; ?>" class="button"><span><?php echo $button_clear; ?></span></a>
             <?php if ($ziparchive) { ?>
             <a href="<?php echo $download_vqcache; ?>" class="button"><span><?php echo $button_vqcache_dump; ?></span></a>
             <?php } ?>
           </td>
         </tr>
         <tr>
           <td><?php echo $entry_backup; ?></td>
           <?php if ($ziparchive) { ?>
           <td><a href="<?php echo $download_scripts; ?>" class="button"><span><?php echo $button_backup; ?></span></a></td>
           <?php } else { ?>
           <td><?php echo $error_ziparchive; ?></td>
           <?php } ?>
         </tr>
         <tr>
           <td><?php echo $entry_vqmod_path; ?></td>
           <td><?php echo $vqmod_path; ?></td>
         </tr>
         <?php if ($vqmod_vars) { ?>
         <?php foreach ($vqmod_vars as $vqmod_var) { ?>
         <tr>
           <td><?php echo $vqmod_var['setting']; ?></td>
           <td><?php echo $vqmod_var['value']; ?></td>
         </tr>
         <?php } ?>
         <?php } ?>
      </table>
    </div>
    <div id="tab-error">
      <table class="form">
        <tr>
          <td><textarea rows="20" cols="160" id="error-log"><?php echo $log; ?></textarea>
          <div class="right"><?php if ($ziparchive) { ?><a href="<?php echo $download_log; ?>" class="button"><span><?php echo $button_download_log; ?></span></a><?php } ?> <a href="<?php echo $clear_log; ?>" class="button"><span><?php echo $button_clear; ?></span></a></div></td>
        </tr>
      </table>
    </div>
    <div id="tab-about">
      <table class="form">
        <tr>
          <td><?php echo $entry_ext_version; ?></td>
          <td><?php echo $vqmod_manager_version; ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_author; ?></td>
          <td><?php echo $vqmod_manager_author; ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_website; ?></td>
          <td><a class="about" href="http://opencarthelp.com" target="_blank">http://opencarthelp.com</a></td>
        </tr>
        <tr>
          <td><?php echo $entry_ext_store; ?></td>
          <td><a class="about" href="http://www.opencart.com/index.php?route=extension/extension/info&amp;extension_id=2969&amp;filter_username=rph" target="_blank">http://www.opencart.com/index.php?route=extension/extension/info&amp;extension_id=2969</a></td>
        </tr>
        <tr>
          <td><?php echo $entry_forum; ?></td>
          <td><a class="about" href="http://forum.opencart.com/viewtopic.php?t=36235" target="_blank">http://forum.opencart.com/viewtopic.php?t=36235</a></td>
        </tr>
        <tr>
          <td><?php echo $entry_license; ?></td>
          <td><a class="about" href="http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode" target="_blank"><?php echo $vqmod_manager_license; ?></a></td>
        </tr>
      </table>
    </div>
    </form>
  <?php } else { ?>
    <span><?php echo $vqmod_installation_error; ?></span>
  <?php } ?>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	// Confirm Delete
	$('a').click(function(){
		if ($(this).attr('href') != null && $(this).attr('href').indexOf('delete',1) != -1) {
			if (!confirm ('<?php echo $warning_vqmod_delete; ?>')) {
				return false;
			}
		}
	});

	// Confirm vqmod_opencart.xml Uninstall
	$('a').click(function(){
		if ($(this).attr('href') != null && $(this).attr('href').indexOf('vqmod_opencart',1) != -1 && $(this).attr('href').indexOf('uninstall',1) != -1) {
			if (!confirm ('<?php echo $warning_required_uninstall; ?>')) {
				return false;
			}
		}
	});

	// Confirm vqmod_opencart.xml Delete
	$('a').click(function(){
		if ($(this).attr('href') != null && $(this).attr('href').indexOf('vqmod_opencart',1) != -1 && $(this).attr('href').indexOf('delete',1) != -1) {
			if (!confirm ('<?php echo $warning_required_delete; ?>')) {
				return false;
			}
		}
	});
});
</script>
<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script>
<?php echo $footer; ?>