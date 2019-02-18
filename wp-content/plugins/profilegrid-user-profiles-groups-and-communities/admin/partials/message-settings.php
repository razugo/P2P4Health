<?php
$dbhandler = new PM_DBhandler;
$textdomain = $this->profile_magic;
$pmrequests = new PM_request;
$path =  plugin_dir_url(__FILE__);
$identifier = 'SETTINGS';
if(filter_input(INPUT_POST,'submit_settings'))
{
	$retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
	if (!wp_verify_nonce($retrieved_nonce, 'save_message_settings' ) ) die( __('Failed security check','profile-magic') );
	$exclude = array("_wpnonce","_wp_http_referer","submit_settings");
	if(!isset($_POST['pm_enable_private_messaging'])) $_POST['pm_enable_private_messaging'] = 0;
        if(!isset($_POST['pm_unread_message_notification'])) $_POST['pm_unread_message_notification'] = 0;
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
  <form name="pm_message_settings" id="pm_message_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php _e( 'Messaging Settings','profile-magic' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
    
        <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Enable Private Messaging','profile-magic' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_private_messaging" id="pm_enable_private_messaging" type="checkbox" <?php checked($dbhandler->get_global_option_value('pm_enable_private_messaging','1'),'1'); ?> class="pm_toggle" value="1" style="display:none;" onClick="pm_show_hide(this,'pm_enable_private_messaging_html')" />
          <label for="pm_enable_private_messaging"></label>
        </div>
        <div class="uimnote"><?php _e("Turn on private messaging system for your site users. Registered users can start conversations with each other.",'profile-magic');?></div>
      </div>
      <div class="childfieldsrow" id="pm_enable_private_messaging_html" style=" <?php if($dbhandler->get_global_option_value('pm_enable_private_messaging',1)==1){echo 'display:block;';} else { echo 'display:none;';} ?>">
            <div class="uimrow">
                <div class="uimfield">
                  <?php _e( 'Enable Unread Message Notification','profile-magic' ); ?>
                </div>
                <div class="uiminput">
                   <input name="pm_unread_message_notification" id="pm_unread_message_notification" type="checkbox" <?php checked($dbhandler->get_global_option_value('pm_unread_message_notification'),'1'); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'pm_unread_message_notification_html')" />
                  <label for="pm_unread_message_notification"></label>
                </div>
                <div class="uimnote"><?php _e("User will be notified when there's a new unread private message.",'profile-magic');?></div>
            </div>
            
          <div class="childfieldsrow" id="pm_unread_message_notification_html" style=" <?php if($dbhandler->get_global_option_value('pm_unread_message_notification',0)==1){echo 'display:block;';} else { echo 'display:none;';} ?>">
            
                <div class="uimrow">
                    <div class="uimfield">
                      <?php _e( 'Email Subject','profile-magic' ); ?>
                    </div>
                    <div class="uiminput">
                     <input type="text" name="pm_unread_message_email_subject" id="pm_unread_message_email_subject" value="<?php echo $dbhandler->get_global_option_value('pm_unread_message_email_subject',__('New Private Message from {{sender_name}}','profile-magic'));?>" />
                      
                    </div>
                    <div class="uimnote"><?php _e('Subject of the email sent to the user.','profile-magic');?></div>
                 </div>
                <?php
                $settings = array('wpautop' => true,'media_buttons' => true,
                    'textarea_name' => 'pm_unread_message_email_body',
                    'textarea_rows' => 20,
                    'tabindex' => '',
                    'tabfocus_elements' => ':prev,:next', 
                    'editor_css' => '', 
                    'editor_class' => '',
                    'teeny' => false,
                    'dfw' => false,
                    'tinymce' => true, // <-----
                    'quicktags' => true
                );
                $pm_unread_message_email_body = $dbhandler->get_global_option_value('pm_unread_message_email_body',__('Hi {{display_name}},<br /><br />You just received a new private message from {{sender_name}}. Visit your profile at {{profile_link}} to make sure you are not missing out on the latest updates.','profile-magic'));
                ?>
	    
                <div class="uimrow">
                    <div class="uimfield">
                      <?php _e( 'Email Content','profile-magic' ); ?>
                    </div>
                    <div class="uiminput">
                        <?php wp_editor( $pm_unread_message_email_body, 'pm_unread_message_email_body',$settings);?>
                    </div>
                    <div class="uimnote"><?php _e('Content of the email sent to the user.','profile-magic');?></div>
                 </div>
                
                
            </div> 
            
        </div>
        
   
      <div class="buttonarea"> 
          <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php _e('Cancel','profile-magic');?>
        </div>
        </a>
        <?php wp_nonce_field('save_message_settings'); ?>
        <input type="submit" value="<?php _e('Save','profile-magic');?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
   
  </form>
</div>