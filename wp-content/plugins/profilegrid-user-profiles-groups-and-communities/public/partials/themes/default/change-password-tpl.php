<div class="pmagic">   
<!-----Form Starts----->
<form class="pmagic-form pm-dbfl" name="resetpassform" id="resetpassform" method="post" autocomplete="off" onsubmit="return pm_frontend_change_password(this)">
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pass1"><?php _e('New password','profile-magic');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="password" size="20" value="" autocomplete="off" id="pass1" name="pass1" >
            </div>
        </div>
    </div>
     <div class="pmrow">        
        <div class="pm-col">
            <div class="pm-form-field-icon"></div>
            <div class="pm-field-lable">
                <label for="pass2"><?php _e('Repeat new password','profile-magic');?><sup class="pm_estric">*</sup></label>
            </div>
            <div class="pm-field-input pm_required">
                <input type="password" size="20" value="" autocomplete="off" id="pass2" name="pass2" >
            </div>
        </div>
    </div>  
    
    <div class="buttonarea pm-full-width-container">
        <div id="pm_reset_passerror" style="display:none;"></div>
        <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($uid); ?>" />
      <input type="submit" value="<?php _e('Submit','profile-magic');?>" name="my_account_submit">
    </div>
  </form>
</div>