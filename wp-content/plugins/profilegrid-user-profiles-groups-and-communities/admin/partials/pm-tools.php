<?php
global $wpdb;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
?>

<div class="uimagic">
  <div class="content pm_settings_option">
    <div class="uimheader">
      <?php _e( 'Tools','profile-magic' ); ?>
    </div>
    <div class="uimsubheader"> </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_export_users">
      <div class="pm_setting_image"> 
      	<img src="<?php echo $path;?>images/export-users.png" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php _e( 'Export Users','profile-magic' ); ?></span> 
          <span class="pm-setting-description"><?php _e( 'Exporting made super simple!','profile-magic' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_import_users">
      <div class="pm_setting_image"> 
      	<img src="<?php echo $path;?>images/import-users.png" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php _e( 'Import Users','profile-magic' ); ?></span> 
          <span class="pm-setting-description"><?php _e( 'Different options to add users to your site from CSV file','profile-magic' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_export_options">
      <div class="pm_setting_image"> 
      	<img src="<?php echo $path;?>images/export-options.png" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php _e( 'Save Configuration','profile-magic' ); ?></span> 
          <span class="pm-setting-description"><?php _e( 'Download plugin settings file.','profile-magic' ); ?></span> 
      </div>
    </a> 
    </div>
    
    <div class="uimrow"> 
    <a href="admin.php?page=pm_import_options">
      <div class="pm_setting_image"> 
      	<img src="<?php echo $path;?>images/import-options.png" class="options" alt="options"> 
      </div>
      <div class="pm-setting-heading"> 
          <span class="pm-setting-icon-title"><?php _e( 'Load Configuration','profile-magic' ); ?></span> 
          <span class="pm-setting-description"><?php _e( 'Upload plugin settings file.','profile-magic' ); ?></span> 
      </div>
    </a> 
    </div>
      
      <div class="buttonarea">
          <a href="admin.php?page=pm_settings">
              <div class="cancel">&#8592; &nbsp;
                  <?php _e('Back','profile-magic');?>
              </div>
          </a>
      </div>
      
  </div>
</div>