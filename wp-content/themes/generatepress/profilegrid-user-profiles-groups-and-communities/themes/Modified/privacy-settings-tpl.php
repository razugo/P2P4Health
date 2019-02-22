<div class="pmagic">   
<!-----Form Starts----->
<form class="pmagic-form pm-dbfl" name="pm_privacy_form" id="pm_privacy_form" method="post">
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pm_profile_privacy"><?php _e('Profile Privacy','profile-magic');?></label>
            </div>
            <div class="pm-field-input pm_required">
                <select name="pm_profile_privacy" id="pm_profile_privacy">
                    <option value="1" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),1);?>><?php _e('Everyone','profile-magic');?></option>
                    <option value="2" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),2);?>><?php _e('Friends','profile-magic');?></option>
                    <option value="3" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),3);?>><?php _e('Group Members','profile-magic');?></option>
                    <option value="4" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),4);?>><?php _e('Friends & Group Members','profile-magic');?></option>
                    <option value="5" <?php selected($pmrequests->profile_magic_get_user_field_value($uid,'pm_profile_privacy'),5);?>><?php _e('Only Me','profile-magic');?></option>
                </select>
            </div>
        </div>
    </div>
    <?php if($dbhandler->get_global_option_value('pm_allow_user_to_hide_their_profile','0')==1):?>
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pm_hide_my_profile"><?php _e('Hide My Profile From Groups, Directories and Search Results','profile-magic');?></label>
            </div>
            <div class="pm-field-input pm_required">
                <div class="pmradio">
                   <div class="pm-radio-option">
                       <input type="radio" class="pg-hide-privacy-profile" name="pm_hide_my_profile" value="0" <?php if($pmrequests->profile_magic_get_user_field_value($uid,'pm_hide_my_profile')==0 || $pmrequests->profile_magic_get_user_field_value($uid,'pm_hide_my_profile')=='')echo 'checked';?>> 
                       <label class="pg-hide-my-profile"><?php  _e('No','profile-magic'); ?></label>
                   </div>
                    <div class="pm-radio-option">
                       <input type="radio" class="pg-hide-privacy-profile" name="pm_hide_my_profile" value="1" <?php checked($pmrequests->profile_magic_get_user_field_value($uid,'pm_hide_my_profile'),1);?>> 
                       <label class="pg-hide-my-profile"> <?php _e('Yes','profile-magic'); ?></label>
                   </div>
                            
                </div>
            </div>
        </div>
    </div>  
    <?php endif;?>
    <div class="buttonarea pm-full-width-container">
        <div id="pm_reset_passerror" style="display:none;"></div>
        <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($uid); ?>" />
      <input type="submit" value="<?php _e('Submit','profile-magic');?>" name="pg_privacy_submit">
    </div>
  </form>
</div>