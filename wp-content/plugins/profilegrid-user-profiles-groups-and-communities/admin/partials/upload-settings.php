<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$identifier = 'SETTINGS';
if(filter_input(INPUT_POST,'submit_settings'))
{
	$retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
	if (!wp_verify_nonce($retrieved_nonce, 'save_upload_settings' ) ) die( __('Failed security check','profile-magic') );
	$exclude = array("_wpnonce","_wp_http_referer","submit_settings");
	$post = $pmrequests->sanitize_request($_POST,$identifier,$exclude);
	if($post!=false)
	{
            foreach($post as $key=>$value)
            {
                    $dbhandler->update_global_option_value($key,$value);
            }
	}
	wp_redirect( esc_url_raw('admin.php?page=pm_settings') );exit;
}
?>

<div class="uimagic">
  <form name="pm_upload_settings" id="pm_upload_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php _e( 'Uploads','profile-magic' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Profile Image Maximum File Size (in bytes)','profile-magic' ); ?>
        </div>
        <div class="uiminput">
         <input type="text" name="pg_profile_image_max_file_size" id="pg_profile_image_max_file_size" value="<?php echo $dbhandler->get_global_option_value('pg_profile_image_max_file_size','');?>" />

        </div>
        <div class="uimnote"><?php printf(__('Set an upper limit to the size of profile images uploaded by users. Sizes are in Bytes. For example, for 2MB limit use 2097152. You can also use an <a href="%s" target="_blank">online convertor</a> for finding out exact values.','profile-magic'),'http://whatsabyte.com/P1/byteconverter.htm');?></div>
      </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Cover Image Maximum File Size (in bytes)','profile-magic' ); ?>
        </div>
        <div class="uiminput">
         <input type="text" name="pg_cover_image_max_file_size" id="pg_cover_image_max_file_size" value="<?php echo $dbhandler->get_global_option_value('pg_cover_image_max_file_size','');?>" />

        </div>
        <div class="uimnote"><?php printf(__('Set an upper limit to the size of cover images uploaded by users. Sizes are in Bytes. For example, for 2MB limit use 2097152. You can also use an <a href="%s" target="_blank">online convertor</a> for finding out exact values.','profile-magic'),'http://whatsabyte.com/P1/byteconverter.htm');?></div>
      </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Image Quality','profile-magic' ); ?>
        </div>
        <div class="uiminput">
         <input type="text" name="pg_image_quality" id="pg_image_quality" value="<?php echo $dbhandler->get_global_option_value('pg_image_quality','90');?>" />

        </div>
        <div class="uimnote"><?php _e('Set quality of images being rendered inside ProfileGrid. A lower quality can improve load times. Values vary between 1 to 100.','profile-magic');?></div>
      </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Profile Image Minimum Width','profile-magic' ); ?>
        </div>
        <div class="uiminput">
         <input type="text" name="pg_profile_photo_minimum_width" id="pg_profile_photo_minimum_width" value="<?php echo $dbhandler->get_global_option_value('pg_profile_photo_minimum_width','DEFAULT');?>" />

        </div>
        <div class="uimnote"><?php _e('Set a minimum width (in pixels) for the photos users upload as their profile image.','profile-magic');?></div>
      </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Cover Image Minimum Width','profile-magic' ); ?>
        </div>
        <div class="uiminput">
         <input type="text" name="pg_cover_photo_minimum_width" id="pg_cover_photo_minimum_width" value="<?php echo $dbhandler->get_global_option_value('pg_cover_photo_minimum_width','DEFAULT');?>" />

        </div>
        <div class="uimnote"><?php _e('Set a minimum width (in pixels) for the photos users upload as their cover image. While ProfileGrid automatically informs users a recommended width based on your site theme, disallowing smaller sizes will reduce possibility of blurry or stretched our cover images.','profile-magic');?></div>
      </div>
    <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php _e('Cancel','profile-magic');?>
        </div>
        </a>
        <?php wp_nonce_field('save_upload_settings'); ?>
        <input type="submit" value="<?php _e('Save','profile-magic');?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>