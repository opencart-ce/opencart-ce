<?php echo $header; ?>
<h1>Step 2 - Pre-Installation</h1>
<div id="column-right">
  <ul>
    <li>License</li>
    <li><b>Pre-Installation</b></li>
    <li>Configuration</li>
    <li>Finished</li>
  </ul>
</div>
<div id="content">
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
    <p>1. Please configure your PHP settings to match requirements listed below.</p>
    <fieldset>
      <table>
        <tr>
          <th width="35%" align="left"><b>PHP Settings</b></th>
          <th width="25%" align="left"><b>Current Settings</b></th>
          <th width="25%" align="left"><b>Required Settings</b></th>
          <th width="15%" align="center"><b>Status</b></th>
        </tr>
        <tr>
          <td>PHP Version:</td>
          <td><?php echo phpversion(); ?></td>
          <td>5.5+</td>
          <td align="center"><?php echo (version_compare(phpversion(), '5.5', '>=')) ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <td>Register Globals:</td>
          <td><?php echo (ini_get('register_globals')) ? 'On' : 'Off'; ?></td>
          <td>Off</td>
          <td align="center"><?php echo (!ini_get('register_globals')) ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <td>Magic Quotes GPC:</td>
          <td><?php echo (ini_get('magic_quotes_gpc')) ? 'On' : 'Off'; ?></td>
          <td>Off</td>
          <td align="center"><?php echo (!ini_get('magic_quotes_gpc')) ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <td>File Uploads:</td>
          <td><?php echo (ini_get('file_uploads')) ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo (ini_get('file_uploads')) ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <td>Session Auto Start:</td>
          <td><?php echo (ini_get('session_auto_start')) ? 'On' : 'Off'; ?></td>
          <td>Off</td>
          <td align="center"><?php echo (!ini_get('session_auto_start')) ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
      </table>
    </fieldset>
    <p>2. Please make sure the PHP extensions listed below are installed.</p>
    <fieldset>
      <table>
        <tr>
          <th width="35%" align="left"><b>Extension</b></th>
          <th width="25%" align="left"><b>Current Settings</b></th>
          <th width="25%" align="left"><b>Required Settings</b></th>
          <th width="15%" align="center"><b>Status</b></th>
        </tr>
        <tr>
          <td>MySQL:</td>
          <td><?php echo extension_loaded('mysql') || extension_loaded('mysqli') ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo extension_loaded('mysql') || extension_loaded('mysqli') ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <td>GD:</td>
          <td><?php echo extension_loaded('gd') ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo extension_loaded('gd') ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <td>cURL:</td>
          <td><?php echo extension_loaded('curl') ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo extension_loaded('curl') ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <?php if (version_compare(PHP_VERSION, '7.1', '>=')) { ?>
          <td>OpenSSL:</td>
          <td><?php echo function_exists('openssl_encrypt') ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo function_exists('openssl_encrypt') ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
          <?php } else { ?>
          <td>mCrypt:</td>
          <td><?php echo function_exists('mcrypt_encrypt') ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo function_exists('mcrypt_encrypt') ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
          <?php } ?>
        </tr>
        <tr>
          <td>ZIP:</td>
          <td><?php echo extension_loaded('zlib') ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo extension_loaded('zlib') ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
        <tr>
          <td>JSON:</td>
          <td><?php echo extension_loaded('json') ? 'On' : 'Off'; ?></td>
          <td>On</td>
          <td align="center"><?php echo extension_loaded('json') ? '<img src="view/image/good.png" alt="Good" />' : '<img src="view/image/bad.png" alt="Bad" />'; ?></td>
        </tr>
      </table>
    </fieldset>
    <p>3. Please make sure you have set the correct permissions on the files list below.</p>
    <fieldset>
      <table>
        <tr>
          <th align="left"><b>Files</b></th>
          <th align="left"><b>Status</b></th>
        </tr>
        <tr>
          <td><?php echo $config_catalog; ?></td>
          <td><?php if (!file_exists($config_catalog)) { ?>
            <span class="bad">Missing</span>
            <?php } elseif (!is_writable($config_catalog)) { ?>
            <span class="bad">Unwritable</span>
          <?php } else { ?>
          <span class="good">Writable</span>
          <?php } ?>
            </td>
        </tr>
        <tr>
          <td><?php echo $config_admin; ?></td>
          <td><?php if (!file_exists($config_admin)) { ?>
            <span class="bad">Missing</span>
            <?php } elseif (!is_writable($config_admin)) { ?>
            <span class="bad">Unwritable</span>
          <?php } else { ?>
          <span class="good">Writable</span>
          <?php } ?>
             </td>
        </tr>
      </table>
    </fieldset>
    <p>4. Please make sure you have set the correct permissions on the directories list below.</p>
    <fieldset>
      <table>
        <tr>
          <th align="left"><b>Directories</b></th>
          <th align="left"><b>Status</b></th>
        </tr>
        <tr>
          <td><?php echo $cache . '/'; ?></td>
          <td><?php echo is_writable($cache) ? '<span class="good">Writable</span>' : '<span class="bad">Unwritable</span>'; ?></td>
        </tr>
        <tr>
          <td><?php echo $logs . '/'; ?></td>
          <td><?php echo is_writable($logs) ? '<span class="good">Writable</span>' : '<span class="bad">Unwritable</span>'; ?></td>
        </tr>
        <tr>
          <td><?php echo $image . '/'; ?></td>
          <td><?php echo is_writable($image) ? '<span class="good">Writable</span>' : '<span class="bad">Unwritable</span>'; ?></td>
        </tr>
        <tr>
          <td><?php echo $image_cache . '/'; ?></td>
          <td><?php echo is_writable($image_cache) ? '<span class="good">Writable</span>' : '<span class="bad">Unwritable</span>'; ?></td>
        </tr>
        <tr>
          <td><?php echo $image_data . '/'; ?></td>
          <td><?php echo is_writable($image_data) ? '<span class="good">Writable</span>' : '<span class="bad">Unwritable</span>'; ?></td>
        </tr>
        <tr>
          <td><?php echo $download . '/'; ?></td>
          <td><?php echo is_writable($download) ? '<span class="good">Writable</span>' : '<span class="bad">Unwritable</span>'; ?></td>
        </tr>
      </table>
    </fieldset>
    <div class="buttons">
      <div class="left"><a href="<?php echo $back; ?>" class="button">Back</a></div>
      <div class="right">
        <input type="submit" value="Continue" class="button" />
      </div>
    </div>
  </form>
</div>
<?php echo $footer; ?>
