<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$textdomain = $this->profile_magic;
$path =  plugin_dir_url(__FILE__);
$identifier = 'SETTINGS';
if(filter_input(INPUT_POST,'submit_settings'))
{
	$retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
	if (!wp_verify_nonce($retrieved_nonce, 'save_security_settings' ) ) die( __('Failed security check','profile-magic') );
	$exclude = array("_wpnonce","_wp_http_referer","submit_settings");
	$post = $pmrequests->sanitize_request($_POST,$identifier,$exclude);
	if($post!=false)
	{
		if(!isset($post['pm_enable_recaptcha'])) $post['pm_enable_recaptcha'] = 0;
		if(!isset($post['pm_enable_recaptcha_in_reg'])) $post['pm_enable_recaptcha_in_reg'] = 1;
		if(!isset($post['pm_enable_recaptcha_in_login'])) $post['pm_enable_recaptcha_in_login'] = 0;
                if(!isset($post['pm_enable_auto_logout_user'])) $post['pm_enable_auto_logout_user'] = 0;
                if(!isset($post['pm_show_logout_prompt'])) $post['pm_show_logout_prompt'] = 0;
		foreach($post as $key=>$value)
		{
			$dbhandler->update_global_option_value($key,$value);
		}
	}
	
	wp_redirect( esc_url_raw('admin.php?page=pm_settings') );exit;
}
?>

<div class="uimagic">
    <form name="pm_security_settings" id="pm_security_settings" method="post" onsubmit="return add_section_validation()">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php _e( 'Security','profile-magic' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Enable reCAPTCHA:','profile-magic' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_recaptcha" id="pm_enable_recaptcha" type="checkbox" <?php checked($dbhandler->get_global_option_value('pm_enable_recaptcha'),'1'); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'enable_captcha_html')" />
          <label for="pm_enable_recaptcha"></label>
        </div>
        <div class="uimnote"><?php _e("Turns on reCAPTCHA on all registration forms.",'profile-magic');?></div>
      </div>
      <div class="childfieldsrow" id="enable_captcha_html" style=" <?php if($dbhandler->get_global_option_value('pm_enable_recaptcha',0)==1){echo 'display:block;';} else { echo 'display:none;';} ?>">
     
      
      
      
          <div class="uimrow">
        <div class="uimfield">
          <?php _e('reCAPTCHA Language:','profile-magic');?>
        </div>
        <div class="uiminput">
          <select name="pm_recaptcha_lang" id="pm_recaptcha_lang">
              <option value=""><?php _e('Select from common languages','profile-magic');?> </option>
              <option value="ar" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ar'); ?>><?php _e(" Arabic ","profile-magic");?></option>
              <option value="af" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'af'); ?>><?php _e(" Afrikaans ","profile-magic");?></option>
              <option value="am" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'am'); ?>><?php _e(" Amharic ","profile-magic");?></option>
              <option value="hy" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'hy'); ?>><?php _e(" Armenian ","profile-magic");?></option>
              <option value="az" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'az'); ?>><?php _e(" Azerbaijani ","profile-magic");?></option>
              <option value="eu" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'eu'); ?>><?php _e(" Basque ","profile-magic");?></option>
              <option value="bn" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'bn'); ?>><?php _e(" Bengali ","profile-magic");?></option>
              <option value="bg" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'bg'); ?>><?php _e(" Bulgarian ","profile-magic");?></option>
              <option value="ca" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ca'); ?>><?php _e(" Catalan ","profile-magic");?></option>
              <option value="zh-CN" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'zh-CN'); ?>><?php _e(" Chinese (China) ","profile-magic");?></option>
              <option value="zh-HK" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'zh-HK'); ?>><?php _e(" Chinese (Hong Kong) ","profile-magic");?></option>
              <option value="zh-TW" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'zh-TW'); ?>><?php _e(" Chinese (Taiwan) ","profile-magic");?></option>
              <option value="hr" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'hr'); ?>><?php _e(" Croatian ","profile-magic");?></option>
              <option value="cs" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'cs'); ?>><?php _e(" Czech ","profile-magic");?></option>
              <option value="da" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'da'); ?>><?php _e(" Danish ","profile-magic");?></option>
              <option value="nl" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'nl'); ?>><?php _e(" Dutch ","profile-magic");?></option>
              <option value="en" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'en'); ?>><?php _e(" English (US) ","profile-magic");?></option>
              <option value="en-GB" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'en-GB'); ?>><?php _e(" English (UK) ","profile-magic");?></option>
              <option value="et" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'et'); ?>><?php _e(" Estonian ","profile-magic");?></option>
              <option value="fil" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'fil'); ?>><?php _e(" Filipino ","profile-magic");?></option>
              <option value="fi" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'fi'); ?>><?php _e(" Finnish ","profile-magic");?></option>
              <option value="fr-CA" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'fr-CA'); ?>><?php _e(" French (Canadian) ","profile-magic");?></option>
              <option value="fr" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'fr'); ?>><?php _e(" French (France) ","profile-magic");?></option>
              <option value="gl" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'gl'); ?>><?php _e(" Galician ","profile-magic");?></option>
              <option value="ka" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ka'); ?>><?php _e(" Georgian ","profile-magic");?></option>
              <option value="de" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'de'); ?>><?php _e(" German ","profile-magic");?></option>
              <option value="de-AT" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'de-AT'); ?>><?php _e(" German (Austria) ","profile-magic");?></option>
              <option value="de-CH" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'de-CH'); ?>><?php _e(" German (Switzerland) ","profile-magic");?></option>
              <option value="el" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'el'); ?>><?php _e(" Greek ","profile-magic");?></option>
              <option value="gu" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'gu'); ?>><?php _e(" Gujarati ","profile-magic");?></option>
              <option value="iw" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'iw'); ?>><?php _e(" Hebrew ","profile-magic");?></option>
              <option value="hi" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'hi'); ?>><?php _e(" Hindi ","profile-magic");?></option>
              <option value="hu" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'hu'); ?>><?php _e(" Hungarian ","profile-magic");?></option>
              <option value="is" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'is'); ?>><?php _e(" Icelandic ","profile-magic");?></option>
              <option value="id" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'id'); ?>><?php _e(" Indonesian ","profile-magic");?></option>
              <option value="it" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'it'); ?>><?php _e(" Italian ","profile-magic");?></option>
              <option value="ja" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ja'); ?>><?php _e(" Japanese ","profile-magic");?></option>
              <option value="kn" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'kn'); ?>><?php _e(" Kannada ","profile-magic");?></option>
              <option value="ko" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ko'); ?>><?php _e(" Korean ","profile-magic");?></option>
              <option value="lo" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'lo'); ?>><?php _e(" Laothian ","profile-magic");?></option>
              <option value="lv" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'lv'); ?>><?php _e(" Latvian ","profile-magic");?></option>
              <option value="lt" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'lt'); ?>><?php _e(" Lithuanian ","profile-magic");?></option>
              <option value="ms" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ms'); ?>><?php _e(" Malay ","profile-magic");?></option>
              <option value="ml" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ml'); ?>><?php _e(" Malayalam ","profile-magic");?></option>
              <option value="mr" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'mr'); ?>><?php _e(" Marathi ","profile-magic");?></option>
              <option value="mn" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'mn'); ?>><?php _e(" Mongolian ","profile-magic");?></option>
              <option value="no" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'no'); ?>><?php _e(" Norwegian ","profile-magic");?></option>
              <option value="ps" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ps'); ?>><?php _e(" Pashto ","profile-magic");?></option>
              <option value="fa" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'fa'); ?>><?php _e(" Persian ","profile-magic");?></option>
              <option value="pl" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'pl'); ?>><?php _e(" Polish ","profile-magic");?></option>
              <option value="pt" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'pt'); ?>><?php _e(" Portuguese ","profile-magic");?></option>
              <option value="pt-BR" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'pt-BR'); ?>><?php _e(" Portuguese (Brazil) ","profile-magic");?></option>
              <option value="pt-PT" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'pt-PT'); ?>><?php _e(" Portuguese (Portugal) ","profile-magic");?></option>
              <option value="ro" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ro'); ?>><?php _e(" Romanian ","profile-magic");?></option>
              <option value="ru" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ru'); ?>><?php _e(" Russian ","profile-magic");?></option>
              <option value="sr" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'sr'); ?>><?php _e(" Serbian ","profile-magic");?></option>
              <option value="si" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'si'); ?>><?php _e(" Sinhalese ","profile-magic");?></option>
              <option value="sk" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'sk'); ?>><?php _e(" Slovak ","profile-magic");?></option>
              <option value="sl" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'sl'); ?>><?php _e(" Slovenian ","profile-magic");?></option>
              <option value="es-419" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'es-419'); ?>><?php _e(" Spanish (Latin America) ","profile-magic");?></option>
              <option value="es" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'es'); ?>><?php _e(" Spanish (Spain) ","profile-magic");?></option>
              <option value="sw" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'sw'); ?>><?php _e(" Swahili ","profile-magic");?></option>
              <option value="sv" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'sv'); ?>><?php _e(" Swedish ","profile-magic");?></option>
              <option value="ta" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ta'); ?>><?php _e(" Tamil ","profile-magic");?></option>
              <option value="te" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'te'); ?>><?php _e(" Telugu ","profile-magic");?></option>
              <option value="th" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'th'); ?>><?php _e(" Thai ","profile-magic");?></option>
              <option value="tr" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'tr'); ?>><?php _e(" Turkish ","profile-magic");?></option>
              <option value="uk" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'uk'); ?>><?php _e(" Ukrainian ","profile-magic");?></option>
              <option value="ur" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'ur'); ?>><?php _e(" Urdu ","profile-magic");?></option>
              <option value="vi" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'vi'); ?>><?php _e(" Vietnamese ","profile-magic");?></option>
              <option value="zu" <?php selected($dbhandler->get_global_option_value('pm_recaptcha_lang'),'zu'); ?>><?php _e(" Zulu ","profile-magic");?></option>
            </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e("Sometimes reCAPTCHA displays words as images. Choosing a language limits these words to a that specific language.",'profile-magic');?></div>
      </div>
      
          <div class="uimrow" id="pm_recaptcha_site_key_wrapper">
        <div class="uimfield">
          <?php _e('Site Key','profile-magic');?>
        </div>
        <div class="uiminput <?php if($dbhandler->get_global_option_value('pm_enable_recaptcha',0)==1){echo 'pm_required';} ?>">
          <input type="text" name="pm_recaptcha_site_key" id="pm_recaptcha_site_key" value="<?php echo $dbhandler->get_global_option_value('pm_recaptcha_site_key');?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Required to make reCAPTCHA work. You can generate site key from','profile-magic');?> <a target="blank" class="rm_help_link" href="https://www.google.com/recaptcha/admin#list"><?php _e("here",'profile-magic');?></a></div>
      </div>
      
      <div class="uimrow" id="pm_recaptcha_secret_key_wrapper">
        <div class="uimfield">
          <?php _e('Secret Key','profile-magic');?>
        </div>
        <div class="uiminput <?php if($dbhandler->get_global_option_value('pm_enable_recaptcha',0)==1){echo 'pm_required';} ?>">
          <input type="text" name="pm_recaptcha_secret_key" id="pm_recaptcha_secret_key" value="<?php echo $dbhandler->get_global_option_value('pm_recaptcha_secret_key');?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Required to make reCAPTCHA work. It will be provided when you generate site key.','profile-magic');?></div>
      </div>
      <div class="uimrow">
        <div class="uimfield">
          <?php _e('Request Method','profile-magic');?>
        </div>
        <div class="uiminput">
          <select name="pm_request_method" id="pm_request_method">
          	<option value="CurlPost" <?php selected($dbhandler->get_global_option_value('pm_request_method'),'CurlPost'); ?>><?php _e('CurlPost','profile-magic');?></option>
            <option value="SocketPost" <?php selected($dbhandler->get_global_option_value('pm_request_method'),'SocketPost'); ?>><?php _e('SocketPost','profile-magic');?></option>
          </select>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Depending on the PHP version configuration of the server, where site has been hosted, request method may be required to be updated. By default setting uses cURLPost, that is good for most of the cases. Change this setting to SocketPost (connection over SSL), only if your reCAPTCHA is not working as expected.','profile-magic');?></div>
      </div>
      </div>
     
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Auto Logout After Inactivity','profile-magic' ); ?>
        </div>
        <div class="uiminput">
           <input name="pm_enable_auto_logout_user" id="pm_enable_auto_logout_user" type="checkbox" <?php checked($dbhandler->get_global_option_value('pm_enable_auto_logout_user','0'),'1'); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'enable_auto_logout_user_html')" />
          <label for="pm_enable_auto_logout_user"></label>
        </div>
        <div class="uimnote"><?php _e("Automatically logs out user when they are logged in but inactive for certain amount of time. Important for privacy and security of the users. You can also display a custom prompt before they are logged out.",'profile-magic');?></div>
      </div>
       
    <div class="childfieldsrow" id="enable_auto_logout_user_html" style=" <?php if($dbhandler->get_global_option_value('pm_enable_auto_logout_user',0)==1){echo 'display:block;';} else { echo 'display:none;';} ?>" > 
        <div class="uimrow">
        <div class="uimfield">
          <?php _e('Define Period of Inactivity (in Seconds)','profile-magic');?>
        </div>
        <div class="uiminput <?php if($dbhandler->get_global_option_value('pm_enable_auto_logout_user',0)==1){echo 'pm_required';}?>">
            <input type="number" name="pm_auto_logout_time" id="pm_auto_logout_time" value="<?php echo $dbhandler->get_global_option_value('pm_auto_logout_time','600');?>">
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Users will be logged out (or prompted to be logged out, if set) after this time. For example, if you wish it to be 10 minutes of inactivity, use 600. A note: If your server is responding very slowly, please set this to a reasonable larger time window since users may experience minimal prompt time to reclaim their session.','profile-magic');?></div>
      </div>
        <div class="uimrow">
        <div class="uimfield">
          <?php _e('Display a Prompt Before Being Logged Out','profile-magic');?>
        </div>
        <div class="uiminput">
          <input name="pm_show_logout_prompt" id="pm_show_logout_prompt" type="checkbox" <?php checked($dbhandler->get_global_option_value('pm_show_logout_prompt','0'),'1'); ?> class="pm_toggle" value="1" style="display:none;"  onClick="pm_show_hide(this,'pm_show_logout_prompt_html')" />
          <label for="pm_show_logout_prompt"></label>
        </div>
        <div class="uimnote"><?php _e('A prompt box will be displayed before logging out is activated. Users will have option to act on the prompt and continue the session.','profile-magic');?></div>
      </div>
        <div class="childfieldsrow" id="pm_show_logout_prompt_html" style=" <?php if($dbhandler->get_global_option_value('pm_show_logout_prompt',0)==1){echo 'display:block;';} else { echo 'display:none;';} ?>">
            <div class="uimrow">
        <div class="uimfield">
          <?php _e('Content of the Prompt Box','profile-magic');?>
        </div>
        <div class="uiminput">
            <textarea name="pm_logout_prompt_text" id="pm_logout_prompt_text"><?php echo $dbhandler->get_global_option_value('pm_logout_prompt_text');?></textarea>
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Write custom content to be displayed inside the pre-logout prompt box.','profile-magic');?></div>
      </div>
        </div>
    </div>
        
      <div class="uimrow">
        <div class="uimfield">
          <?php _e('Whitelisted Dashboard IPs','profile-magic');?>
        </div>
        <div class="uiminput">
          <textarea name="pm_wpadmin_allow_ips" id="pm_wpadmin_allow_ips"><?php echo $dbhandler->get_global_option_value('pm_wpadmin_allow_ips');?></textarea>
          	
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Users coming from these IPs will always be allowed access to the dashboard area including login. Separate multiple entries with commas.','profile-magic');?></div>
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php _e('Blacklisted IPs','profile-magic');?>
        </div>
        <div class="uiminput">
          <textarea name="pm_blocked_ips" id="pm_blocked_ips"><?php echo $dbhandler->get_global_option_value('pm_blocked_ips');?></textarea>
          	
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Users coming from these IPs will be blocked on both front-end and dashboard area. They cannot signup from this IP. You can use IP range or wildcard too. Separate multiple entries with commas.','profile-magic');?></div>
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php _e('Blocked Email Addresses','profile-magic');?>
        </div>
        <div class="uiminput">
          <textarea name="pm_blocked_emails" id="pm_blocked_emails"><?php echo $dbhandler->get_global_option_value('pm_blocked_emails');?></textarea>
          	
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Users with blocked email addresses will not be allowed to register or login on your site. To block an entire domain, use *@domain.com. Separate multiple entries with commas.','profile-magic');?></div>
      </div>  
        
        <div class="uimrow">
        <div class="uimfield">
          <?php _e('Blacklisted Words','profile-magic');?>
        </div>
        <div class="uiminput">
          <textarea name="pm_blacklist_word" id="pm_blacklist_word"><?php echo $dbhandler->get_global_option_value('pm_blacklist_word');?></textarea>
          	
          <div class="errortext"></div>
        </div>
        <div class="uimnote"><?php _e('Blacklisted words cannot be used by guests to signup as their usernames. You can also block keywords important to your business to be used as usernames. Separate multiple entries with commas.','profile-magic');?></div>
      </div>  
        
      <div class="buttonarea"> <a href="admin.php?page=pm_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php _e('Cancel','profile-magic');?>
        </div>
        </a>
        <?php wp_nonce_field('save_security_settings'); ?>
        <input type="submit" value="<?php _e('Save','profile-magic');?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>
