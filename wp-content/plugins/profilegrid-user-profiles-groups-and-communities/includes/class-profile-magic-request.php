<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-profile-magic-request
 *
 * @author ProfileGrid
 */
class PM_request {
//put your code here
	public function sanitize_request($post,$identifier,$exclude=array()) 
	{
        $pmsanitizer = new PM_sanitizer;
        
        if ( !get_magic_quotes_gpc() ) { $post = $pmsanitizer->remove_magic_quotes($post); }

        foreach ($post as $key => $value) {
            if( !in_array($key, $exclude) ) {
                if ( !is_array($value) ) {
                    $data[$key] = $pmsanitizer->get_sanitized_fields($identifier, $key, $value);
                } else {
                    $data[$key] = maybe_serialize( $this->sanitize_request_array($value, $identifier) );
                }
            }
        }
        
        if ( isset($data) ) { return $data; }
        else { return NULL; }
	}
	
	public function sanitize_request_array($post, $identifier) 
	{
	    $pmsanitizer = new PM_sanitizer;
	    
	    foreach ($post as $key => $value) {
                if ( is_array($value) ) {
                    $data[$key] = $this->sanitize_request_array($value, $identifier);
                } else {
                    $data[$key] = $pmsanitizer->get_sanitized_fields($identifier, $key, $value);
                }
	    }
	    
	    if ( isset($data) ) { return $data; }
        else { return NULL; }
	}
	
	public function get_field_key($type,$id)
	{
		switch($type)
		{
			case 'first_name':
				$key = $type;
				break;
			case 'last_name':
				$key = $type;
				break;
			case 'description':
				$key = $type;
				break;
			case 'user_name':
				$key = 'user_login';
				break;
			case 'user_email':
				$key = $type;
				break;
			case 'user_pass':
				$key = $type;
				break;
			case 'confirm_pass':
				$key = $type;
				break;
			case 'user_url':
				$key = $type;
				break;
			case 'user_avatar':
				$key = 'pm_user_avatar';
				break;
			default:
				$key = 'pm_field_'.$id;
		}
		return sanitize_key($key);
	}





	public function get_default_key_type($type)
	{
		switch($type)
		{
			case 'first_name':
			case 'last_name':
			case 'description':
			case 'user_name':
			case 'user_email':
			case 'user_pass':
			case 'confirm_pass':
			case 'user_url':
			case 'user_avatar':
				$value = TRUE;
				break;
			default:
				$value = FALSE;
		}
		return $value;
	}
	
	public function get_userrole_name($userid) 
	{
		global $wp_roles;
		$user_info = get_userdata($userid);
		$roles = $user_info->roles;
		$role = array_shift($roles);
		return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
	}
	
	public function get_user_custom_fields_data($id,$visibility=false)
	{
                $dbhandler = new PM_DBhandler;
		$gids = get_user_meta($id,'pm_group',true);
                $gid = $this->pg_filter_users_group_ids($gids);
                if(!isset($gid) || $gid =='')$gid = array();
                
                $gid_in = "associate_group in(".implode(',',$gid).")";
		$data=array();
		if($gid!='' && $gid!=false && !empty($gid_in))
		{
			if($visibility==false)
			{
				$where = 1;
			}
			else
			{
				$where = array('visibility'=>$visibility);
			}
			$fields =  $dbhandler->get_all_result('FIELDS', $column = '*',$where,'results',0,false, $sort_by = 'ordering',false,$gid_in);
			
			if(!empty($fields))
			{
				foreach($fields as $field)
				{
					$data[$field->field_name] = get_user_meta($id,$field->field_key,true);	
				}
			}
			
		}
		return $data;
	}
	
	public function make_upload_and_get_attached_id($filefield,$allowed_ext,$require_imagesize=array(),$parent_post_id=0)
	{
		$allowfieldstypes = strtolower(trim($allowed_ext));
		$attach_id = '';	
		if(is_array($filefield) && !empty($filefield))
		{
			$file = array('name' => $filefield['name'],
			'type' => $filefield['type'],
			'tmp_name' => $filefield['tmp_name'],
			'error' => $filefield['error'],
			'size' => $filefield['size']);
                        
                        if(!empty($require_imagesize) && !empty($file['tmp_name']))
                        {
                            $imagesize = getimagesize($file['tmp_name']);
                             $image_width = $imagesize[0];
                             $image_height = $imagesize[1];
                             
                             if(isset($require_imagesize[2]) && $file['size'] > $require_imagesize[2])
                             {
                                 $too_small = sprintf( __( 'Image size exceeds the maximum limit. Maximum allowed image size is %d byte.','profile-magic' ), $require_imagesize['2'] );
                             }
                             elseif ( $image_width < $require_imagesize['0'] || $image_height < $require_imagesize['1'] ) 
                             {
                                 $too_small = sprintf( __( 'Image dimensions are too small. Minimum size is %d by %d pixels.','profile-magic' ), $require_imagesize['0'],$require_imagesize['1'] );
                             }
                             else
                             {
                                 $too_small = false;
                             }
                        }
                        else
                        {
                            $too_small = false;
                        }
                        
			if ($filefield['error']=== 0)
			{			
			  if ( ! function_exists( 'wp_handle_upload' ) )
			  {
				  require_once( ABSPATH . 'wp-admin/includes/file.php' );
				  require_once( ABSPATH . 'wp-admin/includes/image.php' );
			  }
			  $upload_overrides = array( 'test_form' => false );
			  $movefile = wp_handle_upload( $file, $upload_overrides );
			  if ( $movefile )
			  {
				  // $filename should be the path to a file in the upload directory.
				  $filename = $movefile['file'];
				  // The ID of the post this attachment is for.
				  
				  // Check the type of tile. We'll use this as the 'post_mime_type'.
				  $filetype = wp_check_filetype( basename( $filename ), null );
				  $current_file_type = strtolower($filetype['ext']);
				  if(strpos($allowfieldstypes,$current_file_type)!==false && $too_small==false)
				  {
                                          
					  // Get the path to the upload directory.
					  $wp_upload_dir = wp_upload_dir();
					  // Prepare an array of post data for the attachment.
					  $attachment = array(
						  'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
						  'post_mime_type' => $filetype['type'],
						  'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
						  'post_content'   => '',
						  'post_status'    => 'inherit'
					  );
					  // Insert the attachment.
					  include_once( ABSPATH . 'wp-admin/includes/image.php' );
					  $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );  
					  $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					  wp_update_attachment_metadata( $attach_id, $attach_data );

				  }
				  else
				  {
                                        if(strpos($allowfieldstypes,$current_file_type)===false)
                                        {
                                            return __("This file type is not allowed.",'profile-magic');
                                        }
                                        else
                                        {
					    return $too_small;
                                        }
				  }
			  }  
			}
		}
		return $attach_id;
							
	}
	
	public function pm_check_field_exist($gid,$type,$signup=false)
	{
        $dbhandler = new PM_DBhandler;
		if( isset($gid) && isset($type) )
		{
			$where = array('associate_group'=>$gid,'field_type'=>$type);
			if($signup=true)
			{
				$where['show_in_signup_form']=1;	
			}
            $result = $dbhandler->get_all_result('FIELDS', '*', $where, 'results', 0, false, 'ordering');
			
            if($result==null)
			{
				return false;	
			}
			else
			{
				return true;	
			}
		}
		else
		{
			return false;	
		}
			
	}
	
	public function pm_get_currency_symbol()
	{
                $dbhandler = new PM_DBhandler;
		$currency = $dbhandler->get_global_option_value('pm_paypal_currency','USD');
		switch ($currency)
		{
			case 'USD':
				$sign = '&#36;';
				break;
			case 'EUR':
				$sign = '&#0128;';
				break;
			case 'GBP':
				$sign = '&#163;';
				break;
			case 'AUD':
				$sign = '&#36;';
				break;
			case 'BRL':
				$sign = 'R&#36;';
				break;
			case 'CAD':
				$sign = '&#36;';
				break;
			case 'HKD':
				$sign = '&#36;';
				break;
			case 'ILS':
				$sign = '&#8362;';
				break;
			case 'JPY':
				$sign = '&#165;';
				break;
			case 'MXN':
				$sign = '&#36;';
				break;
			case 'NZD':
				$sign = '&#36;';
				break;
			case 'SGD':
				$sign = '&#36;';
				break;
			case 'THB':
				$sign = '&#3647;';
				break;
			case 'INR':
				$sign = '&#8377;';
				break;
			case 'TRY':
				$sign = '&#8378;';
				break;
			default:
				$sign = $currency;
		}
		return $sign;
		
	}
	
	public function pm_encrypt_decrypt_pass($action,$string) 
	{
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$secret_key = 'This is my secret key';
		$secret_iv = 'This is my secret iv';
		// hash
		$key = hash('sha256', $secret_key);
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		if( $action == 'encrypt' ) {
                    if (function_exists('openssl_encrypt')) {
                        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
                    }
                    else
                    {
			$output = base64_encode($string);
                    }
			
		}
		else if( $action == 'decrypt' ){
                    
                    if (function_exists('openssl_decrypt')) {
                        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
                    }
                    else
                    {
			$output = base64_decode($string);
                    }
                    
			
		}
		return $output;
	}
	
	public function profile_magic_captcha_verification($response,$remote_ip) {
                $dbhandler = new PM_DBhandler;
		$secret_key = $dbhandler->get_global_option_value('pm_recaptcha_secret_key');
		// make a GET request to the Google reCAPTCHA Server
		$request = wp_remote_get(
			'https://www.google.com/recaptcha/api/siteverify?secret=' .$secret_key . '&response=' . $response . '&remoteip=' . $remote_ip
		);
		// get the request response body
		$response_body = wp_remote_retrieve_body( $request );
		$result = json_decode( $response_body, true );
		return $result['success'];
	}
	
	public function profile_magic_check_username_exist($username)
	{
		if(username_exists($username))
		{
			 if(is_multisite() && is_user_member_of_blog(username_exists($username))==false)
			 { 
				return false;		
			 }
			 else
			 {
				return true;
			 }
		}
		else
		{
			return false;
		}
	}
	
	public function profile_magic_check_user_email_exist($email)
	{
		if(email_exists($email))
		{
			 if(is_multisite() && is_user_member_of_blog(email_exists($email))==false)
			 { 
				return false;		
			 }
			 else
			 {
				return true;
			 }
		}
		else
		{
			return false;
		}
	}
	
	public function profile_magic_frontend_server_validation($post,$files,$server,$fields,$textdomain,$type='')
	{
                
                $dbhandler = new PM_DBhandler;
		$error = array();
                if(isset($fields) && !empty($fields))
                {
                    foreach($fields as $field) 
                    {
                            $field_key = $field->field_key;
                            if($field->field_type=='user_email')
                            {

                                    if(!isset($post[$field_key]) || $post[$field_key]=='')
                                    {
                                            $error[] =  $field->field_name. __(" is a required field",'profile-magic');	
                                    }
                                    else
                                    {
                                            $useremail = sanitize_email($post[$field_key]);
                                            if(is_email($useremail)==false)
                                            {
                                                    $error[] =  __("Please enter a valid e-mail address",'profile-magic');	 
                                            }

                                            if($this->profile_magic_check_user_email_exist($useremail))
                                            {
                                                    $error[] =  __("This user is already registered. Please try with different email.",'profile-magic');
                                            }
                                    }
                            }
                            if($field->field_type=='user_name')
                            {

                                    if(!isset($post[$field_key]) || $post[$field_key]=='')
                                    {
                                            $error[] =  $field->field_name. __(" is a required field",'profile-magic');	
                                    }
                                    else
                                    {
                                            $username = sanitize_user($post[$field_key],true);
                                            if($username =='' || $username ==NULL)
                                            {
                                                $error[] =  __('This username is invalid because it uses illegal characters. Please enter a valid username.','profile-magic');
                                            }

                                            if($this->profile_magic_check_username_exist($username))
                                            {
                                                    $error[] =  __("Sorry, username already exist.",'profile-magic');
                                            }


                                    }
                            }
                            if($field->field_type=='user_pass')
                            {
                                    if(!isset($post[$field_key]) || $post[$field_key]=='')
                                    {
                                            $error[] =  $field->field_name. __(" is a required field",'profile-magic').'<br />';	
                                    }
                                    else
                                    {
                                            if(strlen($post[$field_key])<7)
                                            {
                                                    $error[] .=  __("Password is too short. At least 7 characters please!",'profile-magic').'<br />';
                                            }	
                                    }
                            }
                            if($field->field_type=='confirm_pass')
                            {
                                    if(!isset($post[$field_key]) || $post[$field_key]=='')
                                    {
                                            $error[] =  $field->field_name. __(" is a required field",'profile-magic').'<br />';	
                                    }
                                    else
                                    {
                                            if($post[$field_key]!==$post['user_pass'])
                                            {
                                                    $error[] .=  __("Password and confirm password do not match.",'profile-magic').'<br />';
                                            }	
                                    }
                            }
                            if($field->is_required == 1 && $field->field_type!='file' && $field->field_type!='user_avatar' && $field->field_type!='user_name' && $field->field_type!='user_email' && $field->field_type!='user_pass' && $field->field_type!='confirm_pass')
                            {
                                    if(!isset($post[$field_key]) || $post[$field_key]=='')
                                    {
                                            $error[] =  $field->field_name. __(" is a required field",'profile-magic').'<br />';	
                                    }
                                    else
                                    {
                                            if(is_array($post[$field_key]))
                                            {
                                                    $value = implode(',',$post[$field_key]);
                                                    if(!isset($value) || $value=='') $error[] =  $field->field_name. __(" is a required field",'profile-magic').'<br />';
                                            }
                                    }
                            }
                            if($field->is_required == 1 && ($field->field_type=='file' || $field->field_type=='user_avatar') && $type!='edit_profile')
                            {
                                    $filefield = $files[$field_key];			
                                    if(is_array($filefield) && empty($filefield['name'][0]))
                                    {
                                            $error[] =  $field->field_name. __(" is a required field",'profile-magic').'<br />';	
                                    }
                            }

                            if(($field->field_type=='file' || $field->field_type=='user_avatar')  && isset($files[$field_key]) && !empty($files[$field_key]['name'][0]))
                            {
                                    $field_options = maybe_unserialize($field->field_options);
                                    $allowed_ext = (($field_options['allowed_file_types']!='')?$field_options['allowed_file_types']:$dbhandler->get_global_option_value('pm_allow_file_types','jpg|jpeg|png|gif'));
                                    //$current_file_type = '';
                                    if($field->field_type=='user_avatar')
                                    {
                                            $allowed_ext = 'jpg|jpeg|png|gif';
                                            $require_imagesize = $this->pm_get_minimum_requirement_user_avatar();
                                    }
                                    $allowfieldstypes = strtolower(trim($allowed_ext));
                                    $filefield = $files[$field_key];

                                    if(is_array($filefield))
                                    {

                                            for( $i =0; $i<count($filefield['name']); $i++ ) 
                                            {
                                                    $file = array(
                                                                              'name'     => $filefield['name'][$i],
                                                                              'type'     => $filefield['type'][$i],
                                                                              'tmp_name' => $filefield['tmp_name'][$i],
                                                                              'error'    => $filefield['error'][$i],
                                                                              'size'     => $filefield['size'][$i]
                                                                            );
                                                     $filetype = wp_check_filetype( basename($file['name'] ), null );
                                                     $current_file_type = strtolower($filetype['ext']);
                                                     if(empty($current_file_type) || $current_file_type=='')
                                                     {
                                                             $error[] = __("This file type is not allowed.",'profile-magic');
                                                     }
                                                     elseif(strpos($allowfieldstypes,$current_file_type)===false)
                                                     {
                                                            $error[] = __("This file type is not allowed.",'profile-magic');
                                                     }

                                                    if(!empty($require_imagesize))
                                                    {
                                                        $imagesize = getimagesize($file['tmp_name']);
                                                         $image_width = $imagesize[0];
                                                         $image_height = $imagesize[1];

                                                         if(isset($require_imagesize[2]) && $file['size'] > $require_imagesize[2])
                                                         {
                                                             $error[] = sprintf( __( 'Image size exceeds the maximum limit. Maximum allowed image size is %d byte.','profile-magic' ), $require_imagesize['2'] );
                                                         }
                                                         elseif ( $image_width < $require_imagesize['0'] || $image_height < $require_imagesize['1'] ) 
                                                         {
                                                             $error[] = sprintf( __( 'Image dimensions are too small. Minimum size is %d by %d pixels.','profile-magic' ), $require_imagesize['0'],$require_imagesize['1'] );
                                                         }

                                                    }


                                            }
                                    }
                            }

                            if($field->field_type=='email' && isset($post[$field_key]) && $post[$field_key]!="")
                            {
                                     if(is_email($post[$field_key])==false)
                                     {
                                            $error[] =  __("Please enter a valid e-mail address",'profile-magic').'<br />';	 
                                     }
                            }

                            if($field->field_type=='number' && isset($post[$field_key]) && $post[$field_key]!="")
                            {
                                     if(is_numeric($post[$field_key])==false)
                                     {
                                            $error[] =  __("Please enter a valid number",'profile-magic').'<br />';	 
                                     }
                            }
                            if($field->field_type=='pricing' && isset($post[$field_key]) && $post[$field_key]!="")
                            {
                                     if(is_numeric($post[$field_key])==false)
                                     {
                                            $error[] =  __("Please enter a valid amount",'profile-magic').'<br />';	 
                                     }
                            }		
                            if($field->field_type=='DatePicker' && isset($post[$field_key]) && $post[$field_key]!="")
                            {
                                     if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$post[$field_key]))
                                     {
                                            $error[] =  __("Please enter a valid date (yyyy-mm-dd format)",'profile-magic').'<br />';	 
                                     }
                            }
                    }
                }
                $error = apply_filters('pm_frontend_server_validation',$error,$post);
                return $error;
	}
	
	public function profile_magic_show_captcha($option)
	{
                $dbhandler = new PM_DBhandler;
		$enable_recaptcha = $dbhandler->get_global_option_value('pm_enable_recaptcha');
		$enable_recaptcha_form = $dbhandler->get_global_option_value($option);
		if($enable_recaptcha ==1 && $enable_recaptcha_form==1)
		{
			return true;	
		}
		else
		{
			return false;
		}
	
	}
	
	public function profile_magic_generate_password()
	{
		$password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		return $password;
	}
	
	public function profile_magic_check_paid_group($gid)
	{
                $dbhandler = new PM_DBhandler;
		$options = maybe_unserialize($dbhandler->get_value('GROUPS','group_options',$gid,'id'));
		
		if(!empty($options) && isset($options['is_paid_group']) && $options['is_paid_group']==1)
		{
			$price = $options['group_price'];
		}
		else{$price = 0;}
		return $price;
	}
	
	public function profile_magic_get_frontend_url($page,$default)
	{
                $dbhandler = new PM_DBhandler;
		 $profile_magic_url = $dbhandler->get_global_option_value($page,'0');
                 
		 if($profile_magic_url==0)
		 {
			$url = $default;
		 }
		 else
		 {
                    $post_status = get_post_status($profile_magic_url);
                    if($post_status=='publish')
                    {
                        $url = get_permalink($profile_magic_url);
                    }
                    else
                    {
                        $url = $default;
                    }
			
		 }
		 return $url;
	}
	
	public function profile_magic_get_error_message($error_code,$textdomain)
	{
		switch ( $error_code ) 
		{
			case 'empty_username':
				$message = __( 'You do have an email address, right?','profile-magic' );
				break;	 
			case 'empty_password':
				$message = __( 'You need to enter a password to login.','profile-magic' );
				break;
			case 'invalid_username':
				$message = __("We don't have any users with that email address. Maybe you used a different one when signing up?",'profile-magic');
				break;
			case 'incorrect_password':
				$err = __("You entered incorrect password. Please try again or click on <a href='%s'>“Forgot Password”</a>",'profile-magic');
                                $forget_password_url = $this->profile_magic_get_frontend_url('pm_forget_password_page',site_url('/wp-login.php?action=lostpassword'));
				$message =  sprintf( $err, $forget_password_url );
                                break;
			case 'empty_username':
				$message = __( 'You need to enter your email address to continue.','profile-magic');
				break;
			case 'account_disabled':
				$message = __('Account disabled.','profile-magic');
				break;
			case 'payment_pending':
				$url = __("Your account has been deactivated due to a pending payment. <a href='%s'>Do you wish to pay now?</a>",'profile-magic');
				$message =  sprintf( $url,$this->pm_get_repayment_url($_REQUEST['id']));
				break;
			 
			case 'invalid_email':
				$message = __( 'We could not recognize the email address you just entered. Please check back and try again.','profile-magic' );
				break;
			case 'invalidcombo':
				$message = __( 'We could not recognize the username or email address you just entered. Please check back and try again.','profile-magic' );
				break;
			case 'expiredkey':
				$message = __( 'The password reset link you used is not valid anymore.','profile-magic' );
				break;
			case 'invalidkey':
				$message = __( 'The password reset link you used is not valid anymore.','profile-magic' );
				break;
			 
			case 'password_reset_mismatch':
				$message = __( "The two passwords you entered don't match.",'profile-magic' );
				break;
				 
			case 'password_reset_empty':
				$message = __( "Sorry, we don't accept empty passwords.",'profile-magic' );
				break;
			case 'password_too_short':
				$message = __( "Password is too short. At least 7 characters please!",'profile-magic' );
				break;
				
			case 'loginrequired':
                                $err = __("Login required to view this page. Please <a href='%s'>Login</a>.",'profile-magic');
                                $login_page_url = $this->profile_magic_get_frontend_url('pm_user_login_page',site_url('/wp-login.php'));
				$message =  sprintf( $err, $login_page_url );
				break;
			case 'not_permitted':
				$message = __("You have not permitted to view this page.",'profile-magic');
				break;
			case 'ajx_failed_del':
				$message = __("Failed to upadte user information.Can not activate user.",'profile-magic');
				break;
                        case 'invalid_code':
				$message = __("Invalid Activation code.",'profile-magic');
				break;
                        case 'pm_reset_pw_limit_exceed':
                                $message = __("You have reached the limit for requesting password change for this user.","profile-magic");
                                break;
                        case 'inactivity':
                            $message = __("You have been logged out due to inactivity.",'profile-magic');
                            break;
                        case 'blocked_ip':
                            $message = __("Your IP has been Banned. You don't access this page.",'profile-magic');
                            break;
                        case 'blocked_email':
                            $message = __("Sorry, you cannot register since this email has been blocked by a site administrator.",'profile-magic');
                            break;
                        case 'blocked_domain':
                            $message = __("You don't able to logged in due to your domain has been blocked by administrator.",'profile-magic');
                            break;
                        case 'blocked_words':
                            $message = __("Sorry, you cannot registered with this username since it uses a word blocked by a site administrator.",'profile-magic');
                            break;
                        case 'blocked_email_on_login':
                            $message = __("Sorry, you are not allowed to login. This email has been blocked by a site administrator.",'profile-magic');
                            break;
                        case 'account_deleted':
                             $message = __("Your account was successfully removed and all profile data deleted.",'profile-magic');
                            break;
                        case 'need_activation':
                             $message = __("An activation link is sent to your email. Please verify.",'profile-magic');
                            break;
			default:
				$message = __( 'An unknown error occurred. Please try again later.','profile-magic' );
				break;
    	}
     	return $message;
	}
	
        public function pg_get_blog_post_data($postid,$field)
        {
            if($postid)
            {
                $post = get_post($postid);
                switch($field)
                {
                    case 'post_name':
                        $value =  $post->post_title;
                        break;
                    case 'edit_post_link':
                        $value = $this->pg_get_edit_blog_post_link($postid);
                        
                        break;
                    case 'post_link':
                        $value = get_permalink($postid);
                        break;
                    default:
                        $value = '';
                        break;
                }
            }
            else
            {
                $value = '';
            }
            
            return $value;  
        }

        public function profile_magic_get_user_field_value($userid,$field,$field_type='')
	{
		$user_info = get_userdata($userid);
		$dbhandler = new PM_DBhandler;
                if($user_info==false)
                {
                    return '';
                }
                else
                {
                    switch($field)
                    {

                            case 'user_login':
                                    $value =  $user_info->user_login;
                                    break;
                            case 'user_pass':
                                    $pass =  get_user_meta($userid,'user_pass',true);
                                    $value = $this->pm_encrypt_decrypt_pass('decrypt',$pass);
                                    break;
                            case 'user_nicename':
                                    $value =  $user_info->user_nicename;
                                    break;
                            case 'user_email':
                                    $value =  $user_info->user_email;
                                    break;
                            case 'user_url':
                                    $value =  $user_info->user_url;
                                    break;
                            case 'user_registered':
                                    $value =  $user_info->user_registered;
                                    break;
                            case 'display_name':
                                    $value =  $user_info->display_name;
                                    break;
                            case 'first_name':
                                    $value =  $user_info->first_name;
                                    break;
                            case 'last_name':
                                    $value =  $user_info->last_name;
                                    break;
                            case 'nickname':
                                    $value =  $user_info->nickname;
                                    break;
                            case 'description':
                                    $value =  $user_info->description;
                                    break;
                            case 'wp_capabilities':
                                    $value =  $user_info->wp_capabilities;
                                    break;
                            case 'admin_color':
                                    $value =  $user_info->admin_color;
                                    break;
                            case 'closedpostboxes_page':
                                    $value =  $user_info->closedpostboxes_page;
                                    break;
                            case 'primary_blog':
                                    $value =  $user_info->primary_blog;
                                    break;
                            case 'rich_editing':
                                    $value =  $user_info->rich_editing;
                                    break;
                            case 'source_domain':
                                    $value =  $user_info->source_domain;
                                    break;
                            case 'profile_link':
                                    $value = $this->pm_get_user_profile_url($userid);
                                    break;
                            case 'site_name':
                                    $value = get_bloginfo('name');
                                    break;
                            case 'group_name':
                                    $gids = get_user_meta($userid,'pm_group',true);
                                    $gid = $this->pg_filter_users_group_ids($gids);
                                    if(isset($gid) && is_array($gid)){ $gid = $this->pg_get_primary_group_id($gid);}
                                    $groupinfo = $dbhandler->get_row('GROUPS',$gid);
                                    if(isset($groupinfo)){$value = $groupinfo->group_name;}else{$value = '';}
                                    break;
                            case 'group_admin_label':
                                    $gids = get_user_meta($userid,'pm_group',true);
                                    $gid = $this->pg_filter_users_group_ids($gids);
                                    if(isset($gid) && is_array($gid)){ $gid = $this->pg_get_primary_group_id($gid);}
                                    $groupinfo = $dbhandler->get_row('GROUPS',$gid);
                                    if(isset($groupinfo)){$group_options = maybe_unserialize($groupinfo->group_options);}
                                    if(isset($group_options['admin_label'])){$value = $group_options['admin_label'];}else{$value = '';}
                                    break;
                            default:
                                    $value = get_user_meta($userid,$field,true);
                                    $additional = maybe_unserialize($value);
                                    if(is_array($additional) && isset($additional['rm_file_field']))
                                    {
                                        unset($additional['rm_file_field']);
                                        $additional_value = implode(',', $additional);
                                        $value = $this->profile_magic_get_user_attachment($additional_value);
                                    }
                                    if($field_type!='' && $field_type=='file' && $value!='')
                                    {
                                            $value = $this->profile_magic_get_user_attachment($value);

                                    }
                                    break;
                    }
                    return $value;
                }
	}
	
	public function profile_magic_get_user_attachment($value,$key='')
	{
		$attachment_html = '';
		$values = explode(',',$value);
			
		foreach($values as $fileid)
		{ 
                    $attachment_html .='<span class="pm_frontend_attachment">';
			$attachment_html .= '<span class="attachment_icon">'.wp_get_attachment_link($fileid,'thumbnail',false,true,false).'</span>';
			$attachment_html .='<span class="pm-attachment-title pm-dbfl"><a href="'.wp_get_attachment_url( $fileid ).'">'.get_the_title( $fileid ).'</a></span>';
                        if($key != '')
                        {
                        $attachment_html .='<a class="removebutton"><span onClick="pm_remove_attachment(this,\''.$key.'\','.$fileid.')" class="remove">'.__('Remove','profile-magic').'</span></a>';
                        }
                    $attachment_html .='</span>';
                }
		
		unset($values);
		return $attachment_html;
	}
        
        public function profile_magic_edit_user_attachment($value,$key='')
        {
		$values = explode(',',$value);
		foreach($values as $fileid)
		{                     
                ?>
                    <span class="pm_frontend_attachment<?php if($key != '') { echo ' pm_edit_attachment'; }?>">
                        <span class="attachment_icon"> <?php echo wp_get_attachment_link($fileid,'full',false,true,false); ?></span>
                        <a class="pm_removebutton"><span onClick="pm_remove_attachment(this, '<?php echo $key ?>', <?php echo $fileid; ?>)" class="remove"><?php _e('Remove','profile-magic');?></span></a>
                    </span>
                <?php
                }
		unset($values);
		return;
        }
        
	public function profile_magic_get_from_email()
	{
                $dbhandler = new PM_DBhandler;
		if($dbhandler->get_global_option_value('pm_enable_smtp')==1)
		{
			$email_address = $dbhandler->get_global_option_value('pm_smtp_from_email_address',get_option('admin_email'));
		}
		else
		{
			$from_name = $dbhandler->get_global_option_value('pm_from_email_name',get_bloginfo( 'name' ));
			$email_address = $from_name.' <'.$dbhandler->get_global_option_value('pm_from_email_address',get_option('admin_email')).'>';
		}
		return $email_address;
	}
	
	public function profile_magic_get_admin_email()
	{
                $dbhandler = new PM_DBhandler;
                $email_address='';
                $email_array = maybe_unserialize($dbhandler->get_global_option_value('pm_admin_email'));
                if(!empty($email_array))
                {
                  $email_address = implode(',',$email_array);  
                }
		if($email_address=='' || empty($email_address)) $email_address = get_option('admin_email');
		return $email_address;
	}
	
	public function profile_magic_frontend_registration_request($post,$files,$server,$gid,$fields)
	{
                $dbhandler = new PM_DBhandler;
                $pmsanitizer = new PM_sanitizer;
		$user_email = $pmsanitizer->get_sanitized_frontend_field('user_email',$post['user_email']);
		$user_role = $dbhandler->get_value('GROUPS','associate_role',$gid,'id');
		$password = (isset($post['user_pass'])?$post['user_pass']:$this->profile_magic_generate_password());	
		
		if(isset($post['user_login']))
		{
			$user_name = $pmsanitizer->get_sanitized_frontend_field('user_login',$post['user_login']);
		}
		else
		{
			$user_name = $pmsanitizer->get_sanitized_frontend_field('user_login',$post['user_email']);
		}
               
		$user_id = $dbhandler->pm_add_user($user_name,$password,$user_email,$user_role);
                $is_paid_group = $this->profile_magic_check_paid_group($gid);
                $group_type = $this->profile_magic_get_group_type($gid);
                do_action('profile_magic_submit_data_before_join_group',$post,$files,$server,$gid,$fields,$user_id,'profile-magic');
                
		$newpass = $this->pm_encrypt_decrypt_pass('encrypt',$password);
		update_user_meta( $user_id,'user_pass',$newpass);
		update_user_meta( $user_id,'rm_user_status','1');
		$this->pm_update_user_custom_fields_data($post,$files,$server,$gid,$fields,$user_id);		
		if($is_paid_group=="0")
                {
                    $this->profile_magic_join_group_fun($user_id,$gid,$group_type);
                }
                
                return $user_id;
	}
	
	public function pm_admin_notification_message_html($post,$gid,$fields,$exclude=array())
	{
		$html = '';
		if(!empty($fields))
		{
			$html .= '<table>';
			foreach($fields as $field)
			{
				if(in_array($field->field_type,$exclude)) continue;
                                if(isset($post[$field->field_key]))
                                {
                                    if(is_array($post[$field->field_key]))
                                    {
                                            $value = implode(',',$post[$field->field_key]);	
                                    }
                                    else
                                    {
                                            $value = $post[$field->field_key];	
                                    }
                                    $html .='<tr><td>'.$field->field_name.'</td><td>'.$value.'</td></tr>';
                                }
                                
                        }
			$html .= '</table>';
		}
		return $html;
	}
	
        public function pm_get_minimum_requirement_user_avatar()
        {
            $dbhandler = new PM_DBhandler;
            $minimum_width = trim($dbhandler->get_global_option_value('pg_profile_photo_minimum_width','DEFAULT'));
            if($minimum_width=='' || $minimum_width=='DEFAULT')
            {
                $minimum_height = 150;
            }
            else
            {
                $minimum_height = $minimum_width;
            }
            
            $maximum_size = trim($dbhandler->get_global_option_value('pg_profile_image_max_file_size',''));
            $minimum_require = array();
            if($minimum_width=='' || $minimum_width=='DEFAULT')
            {
                $minimum_require[0]=150;
            }
            else
            {
                $minimum_require[0]=$minimum_width;
            }
            $minimum_require[1] = $minimum_height;

            if($maximum_size!='')
            {
                $minimum_require[2] = $maximum_size;
            }
            
            return $minimum_require;
        }
        
	public function pm_update_user_custom_fields_data($post,$files,$server,$gid,$fields,$user_id)
	{
                $dbhandler = new PM_DBhandler;
                $pmsanitizer = new PM_sanitizer;
		if(isset($fields) && !empty($fields)):
		foreach($fields as $field)
		{
			if($field->field_type=='user_pass' || $field->field_type=='confirm_pass') continue;
			$value = '';
			$field_key = $field->field_key;
			$field_options = maybe_unserialize($field->field_options);
			if($field->field_type=='file' || $field->field_type=='user_avatar')
			{
				$allowed_ext = (($field_options['allowed_file_types']!='')?$field_options['allowed_file_types']:$dbhandler->get_global_option_value('pm_allow_file_types','jpg|jpeg|png|gif'));
				$filefield = $files[$field_key];
				if(is_array($filefield))
				{
					$attchment_id = array();
					for( $i =0; $i<count($filefield['name']); $i++ ) 
					{
						$file = array(
									  'name'     => $filefield['name'][$i],
									  'type'     => $filefield['type'][$i],
									  'tmp_name' => $filefield['tmp_name'][$i],
									  'error'    => $filefield['error'][$i],
									  'size'     => $filefield['size'][$i]
									);
                                                if($field->field_type=='user_avatar')
                                                {
                                                    $minimum_requirement = $this->pm_get_minimum_requirement_user_avatar();
                                                }
                                                else
                                                {
                                                    $minimum_requirement = array();
                                                }
						$attchmentid = $this->make_upload_and_get_attached_id($file,$allowed_ext,$minimum_requirement);
                                                if($attchmentid!='' && $attchmentid!= NULL)
                                                {
                                                    $attchment_id[] = $attchmentid;
                                                }
                                                
					}
                                        if(!empty($attchment_id))
                                        {
                                            $value = implode(',',$attchment_id);
                                            unset($attchment_id);
                                        }
                                        
				}
				
				
			}
			else
			{
				if(isset($post[$field_key]))
				{
					if(is_array($post[$field_key]))
					{
						//$value = sanitize_text_field(rtrim(implode(',',$post[$field_key]),','));
                                                $value = maybe_serialize($post[$field_key]);
					}
					else
					{
						$value = $pmsanitizer->get_sanitized_frontend_field($field->field_type,$post[$field_key]);	
					}
					
				}
			}
			if($field->field_type=='user_url')
			{
				wp_update_user( array( 'ID' => $user_id, 'user_url' => $value ) );
			}
			elseif($field->field_type=='user_email')
                        {
                            wp_update_user( array( 'ID' => $user_id, 'user_email' => $value ) );
                        }
                        else
			{
				if($field->field_type=='file')
				{
					if($value!='')
					{
                                            $user_attachment = get_user_meta($user_id, $field_key,true);
                                            
                                            if($user_attachment != '' && $dbhandler->get_global_option_value('pm_allow_multiple_attachments') == 1)
                                            {
                                                $oldids = explode(',',$user_attachment);
                                                $newids = explode(',',$value);
                                                $all_attachments = array_merge($oldids,$newids);
                                                $values = implode(',',$all_attachments);
                                                update_user_meta( $user_id,$field_key,$values);
                                            }
                                            else
                                            {
                                                update_user_meta( $user_id,$field_key,$value);
                                            }
					}
				}
                                elseif($field->field_type=='user_avatar')
                                {
                                    if($value!='')
                                    {
                                        update_user_meta( $user_id,$field_key,$value);
                                    }
                                }
				else
				{
					update_user_meta( $user_id,$field_key,$value);	
				}	
			}
		}
		endif;
	}
	public function pm_get_user_avatar($userid)
	{
		$avatar = get_avatar($userid, 274,'',false,array('class'=>'pm-user'));
		//$avatar = get_avatar($user_info->user_email, 300 );
		return $avatar;
	}
	
	public function pm_get_repayment_url($uid)
	{
		$gids = get_user_meta( $uid, 'pm_group', true );
                $gid = $pmrequests->pg_filter_users_group_ids($gids);
		$registration_url  = $this->profile_magic_get_frontend_url('pm_registration_page','');
		$registration_url = add_query_arg( 'gid',$gid,$registration_url );
		$registration_url = add_query_arg( 'uid',$uid,$registration_url );
		$registration_url = add_query_arg( 'action','re_process',$registration_url );
		return esc_url( $registration_url );

	}
	
	public function pm_get_user_redirect($gid)
	{
                $dbhandler = new PM_DBhandler;
		$options = maybe_unserialize($dbhandler->get_value('GROUPS','group_options',$gid));
		$url = '';
		if(!empty($options) && isset($options['redirect']))
		{
			switch($options['redirect'])
			{
				case 'url':
					$url = $options['redirect_url'];
					break;
				case 'page':
					$url = get_permalink($options['redirect_page_id']);
					break;
				default:
					$url = '';
					break;	
			}
		}
		return $url;
	}
	
	public function pm_get_user_date_query($get)
	{
		$date_query = array();
		if(isset($get['time']) && $get['time']!='' && $get['time']!='all' )
		{
			switch($get['time'])
			{
				case 'today':
					$today = getdate();
					$date_query[] = array(
						'year'  => $today['year'],
						'month' => $today['mon'],
						'day'   => $today['mday'],
					);
					break;	
				case 'yesterday':
					$yesterday = getdate(strtotime("-1 day"));
					$date_query[] = array(
						'year'  => $yesterday['year'],
						'month' => $yesterday['mon'],
						'day'   => $yesterday['mday'],
					);
					break;
				case 'this_week':
					$start_date  =  date("Y-m-d", strtotime("previous monday"));
					$newend_date = strtotime($start_date);
					$today = getdate();
					$date_query[] = array(
                                                        'after'     => array(
								'year'  => date('Y',$newend_date),
								'month' => date('m',$newend_date),
								'day'   => date('d',$newend_date),
							),
							'before'    => array(
								'year'  => $today['year'],
								'month' => $today['mon'],
								'day'   => $today['mday'],
							),
							'inclusive' => true,

						);
					break;
				case 'last_week':
					$end_date  =  date("Y-m-d", strtotime("previous sunday"));					
					$now = strtotime($end_date);
					$WeekMon  = mktime(0, 0, 0, date("m", $now)  , date("d", $now)-6, date("Y", $now));
					$start_date = getdate($WeekMon); 
					$newend_date = strtotime($end_date);
					$date_query[] = array(
							'after'     => array(
								'year'  => $start_date['year'],
								'month' => $start_date['mon'],
								'day'   => $start_date['mday'],
							),

							'before'    => array(
								'year'  => date('Y',$newend_date),
								'month' => date('m',$newend_date),
								'day'   => date('d',$newend_date),
							),
							'inclusive' => true,
						);
					
					break;
				case 'this_month':
					$date_query[] = array(
						'year'  => date( 'Y' ),
						'month' =>  date( 'm' ),
					);
					break;
				case 'this_year':
					$date_query[] = array(
						'year'  => date( 'Y' ),
					);
					break;
				case 'specific':
					if(isset($get['start_date']) && $get['end_date'])
					{
						$end_date = strtotime($get['end_date']);
						$start_date = strtotime($get['start_date']);
						$date_query[] = array(
							'after'     => array(
								'year'  => date('Y',$start_date),
								'month' => date('m',$start_date),
								'day'   => date('d',$start_date),
							),
							'before'    => array(
								'year'  => date('Y',$end_date),
								'month' => date('m',$end_date),
								'day'   => date('d',$end_date),
							),
							'inclusive' => true,
						);
					}
					break;
			}
		}
		return $date_query;
	}
	public function pm_get_user_meta_query($get)
	{
		$meta_query_array = array();
                
                if(isset($get['gid']) && $get['gid'] === '0')
                {
                    $meta_query_array[] = array(
                                                'relation' => 'OR',
                                                array(
									'key'     => 'pm_group',
                                                                        'compare' => 'NOT EXISTS'
								),
                                                array(
									'key'     => 'pm_group',
                                                                        'value'   => 'a:0:{}',
                                                                        'compare' => 'LIKE'
								),
                   );
                    
                   return $meta_query_array;
                    
                }
                
                
		if(isset($get['gid']) && $get['gid']!='' && $get['gid'] !== '0')
		{
			$meta_query_array['relation'] = 'AND';
			$meta_query_array[] = array(
									'key'     => 'pm_group',
									'value'   => sprintf(':"%s";',$get['gid']),
                                                                        'compare' => 'like'
								); 
		}
                else
                {
                    $meta_query_array['relation'] = 'AND';
                    $meta_query_array[] =array('key' => 'pm_group','meta_value'   => '','meta_compare' => '!=');    
                }
		
		if(isset($get['status']) && $get['status']!='')
		{
			if($get['status']!='all')
			{
			       
                                    $meta_query_array[] = array(
                                    'key'     => 'rm_user_status',
                                    'value'   => $get['status'],
                                    'compare' => '=');				
                            
			}
		}
		
                if(isset($get['connection']) && $get['connection']!='')
		{    
                    $meta_query_array[] = array(
                    'key'     => 'pm_'.$get['connection'].'_connected',
                    'value'   => 1,
                    'compare' => '=' );
		}
                
		if(isset($get['match_field']) && $get['match_field']!='' && isset($get['field_value']) &&  $get['field_value']!='' )
		{
                    if(in_array($get['match_field'],array('user_url','user_email','user_nicename','user_login')))
                    {
                        if($get['match_field']=='user_login')
                        {
                            $field_value = substr($get['field_value'], 0, strpos($get['field_value'], '@'));
                        }
                        else
                        {
                            $field_value = $get['field_value'];
                        }
                        $search_columns = array($get['match_field']);
                        $meta_query_array['search'] = "*{$field_value}*";
                        $meta_query_array['search_columns'] = $search_columns;
                                        
                    }
                    else
                    {
                       $meta_query_array[] =array(
                                                    'key'     => $get['match_field'],
                                                    'value'   => $get['field_value'],
                                                    'compare' => 'LIKE'
                                            );  
                    }
			
			
		}
		
		return $meta_query_array;
	}
        
    public function pm_get_user_advance_search_meta_query($get)
    {
        $meta_query_array = array();
        $search_string = esc_attr(trim($get['pm_search']));
        //MATCH GID FOR SEARCH
            if (isset($get['gid']) && $get['gid'] != '')
            {
                    $meta_query_array['relation'] = 'AND';
                    $meta_query_array[] = array
                                            (
                                                'key'     => 'pm_group',
                                                'value'   => sprintf(':"%s";',$get['gid']),
                                                'compare' => 'like'
                                             );
            }
            else
            {
                    $meta_query_array['relation'] = 'AND';
                    $meta_query_array[] =array('key' => 'pm_group','meta_value' => '','meta_compare' => '>');
            }
            
		
		if(isset($get['status']) && $get['status']!='')
		{
			if($get['status']!='all')
			{
			       
                                    $meta_query_array[] = array(
                                    'key'     => 'rm_user_status',
                                    'value'   => $get['status'],
                                    'compare' => '=');				
                            
			}
		}
            
            
            if (isset($get['match_fields']) && isset($get['pm_search']) && $get['pm_search'] != '') 
            {
                if (is_array($get['match_fields'])) 
                    {
                       $match_field_array['relation'] = 'OR';
                       foreach ($get['match_fields'] as $value)
                       {
                            $match_field_array[] = array
                            (
                                'key' => $value,
                                'value' => $search_string,
                                'compare' => 'LIKE'
                            );
                        }
                   
                        $meta_query_array[]=$match_field_array;
                       }
                   else
                    {
                        $meta_query_array[] = array
                        (
                            'key' => $get['match_fields'],
                            'value' => $search_string,
                            'compare' => 'LIKE'
                        );
                   }
            }
                
        return $meta_query_array;
    }
        
        
        public function pm_get_hide_users_array()
        {
            $dbhandler = new PM_DBhandler;
            $id= array();
            $allowhiddenusers = $dbhandler->get_global_option_value('pm_allow_user_to_hide_their_profile','');
            if($allowhiddenusers == 1)
            {
                $args = array(
                    'meta_query' => array(
                          array(
                            'key' => 'pm_hide_my_profile',
                            'value' => '1',
                            'compare' => '='
                        )
                    )
                ); 
                
               $users = get_users($args);
               foreach($users as $user)
               {
                   $id[] = $user->ID;
               }
               
            }
            return $id;
        }
	
	public function pm_get_frontend_user_meta($uid,$gid,$group_leader,$view='',$section='',$exclude='')
	{
                $dbhandler = new PM_DBhandler;
		$data = array();
                if(is_array($gid)){$gid_array = $gid;} else{$gid_array = array($gid);}
                $additional = "associate_group in(".implode(',',$gid_array).")";
		$where = array('display_on_profile'=>1);
		if($view=='group')
		{
			$where['display_on_group']=1;
                        $where['associate_group'] = $gid;
		}
		
		if($section!='')
		{
			$where['associate_section']=$section;		
		}
		
		if(is_user_logged_in())
		{
			$current_user = wp_get_current_user();
			if((!empty($group_leader) && in_array($current_user->ID,$group_leader)) || $current_user->ID == $uid)
			{
				$additional = 'AND visibility in(1,2,3)';
			}
			else
			{
				$additional = 'AND visibility in(1,2)';
			}
		}
		else
		{
			$additional = 'AND visibility = 1';
		} 
		if($exclude!='')
		{
			$additional .= ' AND field_type not in('.$exclude.')';	
		}
		$fields =  $dbhandler->get_all_result('FIELDS', $column = '*',$where,'results',0,false, $sort_by = 'ordering',false,$additional);
		return $fields;
	}
	
	public function pm_get_backend_user_meta($uid,$gid,$group_leader,$view='',$section='',$exclude='')
	{
                $dbhandler = new PM_DBhandler;
		$data = array();
                if(is_array($gid)){$gid_array = $gid;} else{$gid_array = array($gid);}
                $additional = "associate_group in(".implode(',',$gid_array).")";
		$where = array();
		if($section!='')
		{
			$where['associate_section']=$section;		
		}
		
		if($exclude!='')
		{
			$additional = ' AND field_type not in('.$exclude.')';	
		}
                if(empty($where))$where = 1;
		$fields =  $dbhandler->get_all_result('FIELDS', $column = '*',$where,'results',0,false, $sort_by = 'ordering',false,$additional);
		return $fields;
	}
	
	public function profile_magic_check_is_group_leader($userid,$gid=false)
	{
                $dbhandler = new PM_DBhandler;
		$group_leader_id = false;
                if($gid==false)
                {
                    $gids = get_user_meta($userid,'pm_group',true);
                    $ugid = $this->pg_filter_users_group_ids($gids);
                    $gid = $this->pg_get_primary_group_id($ugid);
                }
		$is_group_leader = $dbhandler->get_value('GROUPS','is_group_leader',$gid,'id');
		$group_leader_name = $dbhandler->get_value('GROUPS','leader_username',$gid,'id');
		if(isset($group_leader_name) && $group_leader_name!='' && $is_group_leader!=0)
		{
			$group_leader_id = username_exists($group_leader_name);	
		}
		return $group_leader_id;	
	}
	
	public function profile_magic_set_group_leader($gid)
	{
                $dbhandler = new PM_DBhandler;
		$is_group_leader = $dbhandler->get_value('GROUPS','is_group_leader',$gid,'id');
		$group_leader_name = $dbhandler->get_value('GROUPS','leader_username',$gid,'id');
		if(isset($group_leader_name) && $group_leader_name!='' && $is_group_leader!=0)
		{
			$userid = username_exists($group_leader_name);	
			update_user_meta( $userid,'pm_group',$gid);
		}		
	}	
        
        public function profile_magic_get_group_icon($group=NULL,$class='',$path='')
        {
            if($path == ''):
                $path =  plugins_url( '../public/partials/images/default-group.png', __FILE__ );
            endif;
            
            if(isset($group) && $group->group_icon!=0):
                $image = wp_get_attachment_image($group->group_icon,'full',true,array('class'=>$class));
            else :
                $image = '<img src="'.$path.'" class="'.$class.'" />';
            endif;
            
            if(isset($group) && !wp_attachment_is_image($group->group_icon))
            {
                $image = '<img src="'.$path.'" class="'.$class.'" />';
            }
            return $image;
        }
	
        public function profile_magic_get_cover_image($uid,$class='')
        {
            $dbhandler = new PM_DBhandler;
            $imageid = $this->profile_magic_get_user_field_value($uid,'pm_cover_image');
            $path =  plugins_url( '../public/partials/images/default-cover.jpg', __FILE__ );
            $avatarid = $dbhandler->get_global_option_value('pm_default_cover_image','');
           
            $admin_path =  plugins_url( '../public/partials/images/admin-default-cover.jpg', __FILE__ );
            if(isset($imageid) && !empty($imageid)):
                $image = wp_get_attachment_image($imageid,'full',false,array('class'=>$class));
             elseif($avatarid!=''):
                $image = wp_get_attachment_image($avatarid,'full',false,array('class'=>$class));
             else:
                 if(is_super_admin($uid ))
                 {
                     $image = '<img src="'.$admin_path.'" class="'.$class.'" />';
                 }
                 else
                 {
                     $image = '<img src="'.$path.'" class="'.$class.'" />';
                 }
                
            endif;
            return $image;
        }
        
        public function profile_magic_get_pm_theme_name()
        {
            $dirname = array();
            $pm_theme_path = plugin_dir_path( __FILE__ ) . '../public/partials/themes/';
            $wp_theme_dir = get_template_directory();
            $override_pm_theme_path = $wp_theme_dir . "/profilegrid-user-profiles-groups-and-communities/themes/";
            if(file_exists($pm_theme_path))
            {
                foreach(glob($pm_theme_path.'*', GLOB_ONLYDIR) as $dir) 
                {
                      $dirname[] =  basename($dir) ;   
                }  
            }
            
            if(file_exists($override_pm_theme_path))
            {
                foreach(glob($override_pm_theme_path.'*', GLOB_ONLYDIR) as $dir2) 
                {
                      $dirname[] =  basename($dir2) ;   
                }  
            }
            return array_unique($dirname);
        }
        
        public function pm_update_user_activation_code($user_id)
        {
            if ((int) $user_id)
            {
                $pass = wp_generate_password(10, false);
                $activation_code = md5($pass);
                update_user_meta($user_id, 'pm_activation_code', $activation_code);
            }
        }
        
        public function pm_create_user_activation_link($user_id,$activation_code)
        {
            $user_data_obj = new stdClass();
            $user_data_obj->user_id = $user_id;
            $user_data_obj->activation_code = $activation_code;
            $user_data_json = json_encode($user_data_obj);
            $user_data_enc = urlencode($this->pm_encrypt_decrypt_pass('encrypt',$user_data_json));
            $user_activation_link = admin_url('admin-ajax.php') . '?action=pm_activate_user_by_email&user=' . $user_data_enc;
            return $user_activation_link;
        }
        
         public function pm_get_display_name($uid)
         {
             
           $user = get_userdata($uid);
           if(isset($user) && $user!=false)
           {
               $firstname = $user->first_name;
               $lastname = $user->last_name;
               $username = $user->display_name;
               $displayname = $username;
               if(isset($firstname)&&($firstname!=''))
               {
                       if(isset($lastname) && $lastname!='')
                       {
                           $displayname = $firstname.' '.$lastname;
                       }
                       else
                       {
                           $displayname = $firstname;
                       }
               }
               
               $displayname = apply_filters( 'profile_magic_filter_display_name',$displayname,$uid );
               
               return $displayname;              
           }
         }
         
         
     
         
         public function pm_five_star_review_banner()
         {?>
<div class="pm_five_star_Banner">
    <p align="center"><?php _e("Do you like ProfileGrid? Help us  make it better…Please rate it ",'profile-magic'); ?>
        <span class="pm-star">
            <i class="fa fa-star" aria-hidden="true"></i>
            <i class="fa fa-star" aria-hidden="true"></i>
            <i class="fa fa-star" aria-hidden="true"></i>
            <i class="fa fa-star" aria-hidden="true"></i>
            <i class="fa fa-star" aria-hidden="true"></i>
        </span><?php _e(" Stars on ",'profile-magic'); ?><a target="_blank" href="https://wordpress.org/support/plugin/profilegrid-user-profiles-groups-and-communities/reviews/?rate=5#new-post"><?php _e("WordPress.org","profile-magic");?></a></p>
</div>

         <?php    
         }
         
         
         public function pm_get_user_all_threads($uid) {
        if ($uid) {
            $dbhandler = new PM_DBhandler;
            $identifier = 'MSG_THREADS';
            $where = 1;
            $additional = ' s_id = '.$uid.' OR r_id = '.$uid;
            $threads = $dbhandler->get_all_result($identifier, $column = '*', $where, 'results', 0, false, $sort_by = 'timestamp', true,$additional);
            return $threads;
        }
    }

    public function pm_create_message($sid, $rid, $content) {
        $mid='false';
        if($sid!=''&& $rid!='')
        {
            $dbhandler = new PM_DBhandler;
            $pmemail = new PM_Emails;

            $identifier = 'MSG_CONVERSATION';
            $status = 2;
            $allowed_html = array();
            $content = wp_kses($content,$allowed_html);
            $tid = $this->fetch_or_create_thread($sid, $rid);
            //return $tid.' '.$sid.' '.$rid;
            $data = array('s_id' => $sid,'t_id'=>  $tid, 'content' => $content, 'status' => $status,'timestamp'=> current_time('mysql',true));
            //$data = $this->sanitize_request($data,$identifier);
            //return $data;
            $mid = $dbhandler->insert_row($identifier, $data);
            $args = array($mid,$sid, $rid, $content);
            wp_schedule_single_event( time() + 1800, 'pm_send_message_notification',array($mid,$args));
            $send_email = $dbhandler->get_global_option_value('pm_unread_message_notification','0');
            if($send_email=='1')
            {
                $pmemail->pm_send_unread_message_notification($sid,$rid);                        
            }
        }
       if($mid=='false'){
           return false;
       }else{
       
       $this->pm_update_thread_time($tid); 
       $this->pm_update_thread_status($tid,$rid); //RID is sent for status of thread
       return true;
       }
          
    }
    
    public function pm_update_thread_time($tid,$status=''){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $data=array('timestamp'=> current_time('mysql',true));
        if($status!='')$data['status']=$status;
        $data = $this->sanitize_request($data,$identifier);
        $updated=$dbhandler->update_row($identifier, 't_id',$tid,$data);
     
    }
    
    public function pm_update_thread_status($tid,$status){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $data=array('status'=> $status);
        $data = $this->sanitize_request($data,$identifier);
        $updated=$dbhandler->update_row($identifier, 't_id',$tid,$data);
     
    }
    
    

    public function fetch_or_create_thread($sid, $rid) {
        $dbhandler =new PM_DBhandler;
         $identifier = 'MSG_THREADS';
        if ($this->is_thread_exsist($sid, $rid)) {
        
            $tid = $this->get_thread_id($sid, $rid);
        } else {
               
            $thread_desc = array();
            $thread_desc["$sid"]['typing_timestamp']=0;
            $thread_desc["$sid"]['delete_mid']=0;
            $thread_desc["$sid"]['typing_status']='nottyping';
            $thread_desc["$rid"]['typing_timestamp']=0;
            $thread_desc["$rid"]['delete_mid']=0;
            $thread_desc["$rid"]['typing_status']='nottyping';
            $value= maybe_serialize($thread_desc);
            $data = array('s_id' => $sid, 'r_id' => $rid,'thread_desc'=>$value,'timestamp'=> current_time('mysql',true));     
            $data = $this->sanitize_request($data,$identifier);
            $tid = $dbhandler->insert_row($identifier, $data);
            //$tid = $this->get_thread_id($sid, $rid);
        }
        return $tid;
    }

    public function is_thread_exsist($sid, $rid) {
        if($sid!='' && $rid!='' ){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $where = 1;
        $additional = " s_id in ($sid,$rid) AND r_id in ($sid,$rid)";
        $thread = $dbhandler->get_all_result($identifier, $column = '*', $where, 'results', 0, false, $sort_by = 'timestamp', true, $additional);
        if ($thread>1) {
            return true;
        } else {
            return false;
        }
        }else{
            return false;
        }
    }

    public function get_thread_id($sid, $rid) {
        if($sid!=''&& $rid!=''){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $where = 1;
        $additional = " s_id in ($sid,$rid) AND r_id in ($sid,$rid)";
        $thread = $dbhandler->get_all_result($identifier, $column = 't_id', $where, 'results', 0, false, $sort_by = 'timestamp', true, $additional);
        
        if (isset($thread) && sizeof($thread)>0) {
            $tid = $thread[0]->t_id;
        return $tid;     
        }else{
            return false;
        }
        }else{
            return false;
        }
       
    }
    public function get_unread_msg_count($tid){
        $dbhandler = new PM_DBhandler;
        $identifier='MSG_CONVERSATION';
        $uid = wp_get_current_user()->ID;
        $where = 1;
        $status = 2;
        $additional = " t_id = $tid AND s_id NOT IN ($uid) AND status =$status ";
        $message = $dbhandler->get_all_result($identifier, $column = 'm_id', $where, 'results', 0, FALSE, $sort_by = 'timestamp', true,$additional);
         
        $count= sizeof($message);
        return $count;
        
    }


    public function get_message_of_thread($tid,$limit=false,$offset=0,$descending=true){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_CONVERSATION';
        $where = 1;
        $additional = " t_id = $tid";
        $message = $dbhandler->get_all_result($identifier, $column = '*', $where, 'results', $offset, $limit, $sort_by = 'timestamp', $descending,$additional);
        if(isset($message) && !empty($message)):
            if(sizeof($message)>0){
               return $message;
           }
        endif;
    }
    
    public function update_message_status_to_read($tid){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_CONVERSATION';
        $uid = wp_get_current_user()->ID;
        $where = 1;
        $status = 2;
        $additional = " t_id = $tid AND s_id NOT IN ($uid) AND status =$status ";
        $messages = $dbhandler->get_all_result($identifier, $column = 'm_id', $where, 'results', 0, $limit=false, $sort_by = 'timestamp', true,$additional);
        $data=array('status'=> '1');
        $data = $this->sanitize_request($data,$identifier);
        if(sizeof($messages)>0){
            foreach($messages as $message){
              $updated=$dbhandler->update_row($identifier, 'm_id',$message->m_id,$data);
           }
       }
         
      
    }
    
    public function delete_thread($tid){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $uid = wp_get_current_user()->ID;
        $message =$this->get_message_of_thread($tid, '1');
        $thread = $dbhandler->get_row('MSG_THREADS',$tid,'t_id');
        $thread_desc=maybe_unserialize( $thread->thread_desc);
        $thread_desc["$uid"]['delete_mid']=$message[0]->m_id;
        $value= maybe_serialize($thread_desc);
        $data = array('thread_desc'=>$value);
        $return= $dbhandler->update_row('MSG_THREADS','t_id',$tid, $data);

    return $return;
    }
    
    public function update_typing_timestamp($tid,$activity){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $uid = $this->get_other_uid_of_thread($tid);
        $thread = $dbhandler->get_row('MSG_THREADS',$tid,'t_id');
        $thread_desc=maybe_unserialize( $thread->thread_desc);
        $thread_desc["$uid"]['typing_timestamp']=current_time('mysql',true);
        $thread_desc["$uid"]['typing_status']=$activity;
        $value= maybe_serialize($thread_desc);
        $data = array('thread_desc'=>$value);
        $return= $dbhandler->update_row('MSG_THREADS','t_id',$tid, $data);

    return $return;
    }
    
    public function get_typing_timestamp($tid)
    {
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $uid = wp_get_current_user()->ID;
        $thread = $dbhandler->get_row('MSG_THREADS',$tid,'t_id');
        $thread_desc=maybe_unserialize( $thread->thread_desc);
        return $thread_desc["$uid"]['typing_timestamp'];
    }
    
        public function get_typing_status($tid)
    {
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $uid = wp_get_current_user()->ID;
        $thread = $dbhandler->get_row('MSG_THREADS',$tid,'t_id');
        $thread_desc=maybe_unserialize( $thread->thread_desc);
        return $thread_desc["$uid"]['typing_status'];
    }

    public function get_other_uid_of_thread($tid)
    {
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $uid = wp_get_current_user()->ID;
        $thread = $dbhandler->get_row('MSG_THREADS',$tid,'t_id');
        if($thread->s_id==$uid){
            $other_uid = $thread->r_id;
        }else{
            $other_uid = $thread->s_id;
        }
        return $other_uid;
    }

    public function pm_filter_deleted_threads($threads)
    {
        $filtered_threads=array();
        $current_user = wp_get_current_user();
        $uid= $current_user->ID;
        $last_mid = '';
        foreach($threads as $thread)
        { 
            $thread_desc=maybe_unserialize( $thread->thread_desc);
            $delete_mid =(isset($thread_desc["$uid"]['delete_mid']))?$thread_desc["$uid"]['delete_mid']:0; 
            $message =$this->get_message_of_thread($thread->t_id, '1');
            if(isset($message[0]->m_id))
            $last_mid = $message[0]->m_id;

            if($delete_mid < $last_mid){
                  $filtered_threads[]=$thread;
              }
        }
        return $filtered_threads;
    }
        
        public function pm_filter_deleted_message($messages,$tid){
            $dbhandler = new PM_DBhandler;
            $uid = wp_get_current_user()->ID;
            $filtered_messages=array();
            
           $thread = $dbhandler->get_row('MSG_THREADS',$tid,'t_id');
       

            $thread_desc=maybe_unserialize( $thread->thread_desc);
             if(isset($thread_desc) && isset($thread_desc["$uid"]['delete_mid']))
             {
                 $delete_mid = $thread_desc["$uid"]['delete_mid'];
             }else{
                    $delete_mid=0; 
             }   
         foreach($messages as $message){
              
                $message_mid = $message->m_id;
                if($message_mid>$delete_mid){
                    $filtered_messages[]=$message;
                }
            }
            return $filtered_messages;
        }
        
        public function get_data_of_thread($tid){
        $dbhandler = new PM_DBhandler;
        $identifier = 'MSG_THREADS';
        $where = 1;
        $additional  = " t_id in ($tid) ";
        $thread = $dbhandler->get_all_result($identifier, $column = '*', $where, 'results', 0, false, $sort_by = 'timestamp', true,$additional);
        return $thread;
        }

        public function pm_get_user_online_status($uid)
        {
            //return get_user_meta( $uid, 'pm_login_status', true );
            return $this->is_user_online($uid);
        }
        
        public function pm_get_profile_slug_by_id($uid)
        {
            $slug = $uid;
            $slug = apply_filters('profile_magic_get_filter_slug_by_id',$slug,$uid);  
            return $slug;
        }
        
        public function pm_get_uid_from_profile_slug($slug)
        {
            $uid = $slug;
            $uid = apply_filters('profile_magic_get_filter_uid_by_slug',$uid,$slug);  
            return $uid;
        }
        
        public function pm_get_user_profile_url($uid)
        {
            $slug = $this->pm_get_profile_slug_by_id($uid);
            $profile_url = $this->profile_magic_get_frontend_url('pm_user_profile_page', '');
            $profile_url = add_query_arg('uid',$slug, $profile_url); 
            return esc_url( $profile_url );
        }
        
        public function pm_get_user_edit_profile_url($uid,$gid)
        {
            $slug = $this->pm_get_profile_slug_by_id($uid);
            $profile_url = $this->profile_magic_get_frontend_url('pm_user_profile_page', '');
            $profile_url = add_query_arg('user_id',$uid, $profile_url); 
            $profile_url = add_query_arg('gid',$gid, $profile_url); 
            $profile_url = add_query_arg('rd','members',$profile_url);
            
            return esc_url( $profile_url );
        }
        
        public function pm_is_eligable_for_edit_profile($uid,$gid,$user_id)
        {
            $dbhandler = new PM_DBhandler;
            $row = $dbhandler->get_row('GROUPS',$gid);
            $group_leader = username_exists($row->leader_username);
            if(is_user_logged_in())
            {
                $filter_user_id = $this->pm_get_profile_slug_by_id($uid);
                if($user_id==$filter_user_id)
                {
                    return true;
                }
                elseif($group_leader==$uid || is_super_admin())
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
                
            
            
        }

        public function pm_user_ip() 
        {
		$ip = '127.0.0.1';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
                
		return apply_filters( 'pm_user_ip', $ip );
	}
        
        public function pg_get_strings_between_tags($string, $tagname)
        {
            $pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
            preg_match($pattern, $string, $matches);
            if(!empty($matches))
            {
            return $matches[1];
            }
            else
            {
                return $string;
            }
        }
        
        public function pg_get_default_avtar_src()
        {
            $path =  plugin_dir_url(__FILE__);
            $dbhandler = new PM_DBhandler;
            $avatarid = $dbhandler->get_global_option_value('pm_default_avatar','');
            if($avatarid=='')
            {
                if ( ! is_admin() ) 
                {
                     $pm_avatar = $path.'/partials/images/default-user.png';
                }
                else
                {
                     $pm_avatar = $path.'../admin/partials/images/default-user.png';
                }
                
                //$pm_avatar = '<img src="'.$default_avatar_path.'" width="'.$size.'" height="'.$size.'" class="user-profile-image" />';
            }
            else
            {
                $pm_avatar = wp_get_attachment_url($avatarid);  
            }
            
             return $pm_avatar;
        }
        
        public function pg_get_default_cover_image_src()
        {
            $path =  plugin_dir_url(__FILE__);
            $dbhandler = new PM_DBhandler;
            $avatarid = $dbhandler->get_global_option_value('pm_default_cover_image','');
            if($avatarid=='')
            {
                if ( ! is_admin() ) 
                {
                    
                     $pm_avatar = $path.'/partials/images/default-cover.jpg';
                }
                else
                {
                     $pm_avatar = $path.'../admin/partials/images/default-cover.jpg';
                }
                
                //$pm_avatar = '<img src="'.$default_avatar_path.'" width="'.$size.'" height="'.$size.'" class="user-profile-image" />';
            }
            else
            {
                $pm_avatar = wp_get_attachment_url($avatarid);  
            }
            
             return $pm_avatar;
        }
        
        public function profile_magic_check_profile_access_permission($profile_id)
	{
		$current_user_id = get_current_user_id();
                $access_level = $this->profile_magic_get_user_field_value($profile_id,'pm_profile_privacy');
                $profile_user_groups = $this->profile_magic_get_user_field_value($profile_id,'pm_group');
                $profile_user_group = $this->pg_filter_users_group_ids($profile_user_groups);
                
                $current_user_groups = $this->profile_magic_get_user_field_value($current_user_id,'pm_group');
                $current_user_group = $this->pg_filter_users_group_ids($current_user_groups);
                
                if(!is_array($profile_user_group)){$profile_user_group = array($profile_user_group);}
                if(!is_array($current_user_group)){$current_user_group = array($current_user_group);}
                $is_group_member = array_intersect($profile_user_group,$current_user_group);
                $pmfriends = new PM_Friends_Functions;
                $pmrequests = new PM_request;
                $access = false;
                $is_my_friend = $pmfriends->profile_magic_is_my_friends($profile_id,$current_user_id);
                switch($access_level)
                {
                    case '1':
                        $access = true;
                        break;
                    case '2':
                        if ( is_user_logged_in() && isset($is_my_friend) && !empty($is_my_friend)) 
                        {
                            $access = true;
                        }
                        break;
                    case '3':
                        if(is_user_logged_in() && is_array($is_group_member) && !empty($is_group_member))
                        {
                            $access = true;
                        }
                        break;
                    case '4':
                        if((is_user_logged_in() && is_array($is_group_member) && !empty($is_group_member)) || ( is_user_logged_in() && isset($is_my_friend) && !empty($is_my_friend)))
                        {
                            $access = true;
                        }
                        break;
                    case '5':
                        if($current_user_id == $profile_id)
                        {
                            $access = true;
                        }
                        break;
                    default:
                            $access = true;
                        break;
                }
                if($current_user_id == $profile_id)
                {
                    $access = true;
                }
                
                return $access;
        }
        
        public function pg_auto_create_default_fields($gid,$sid)
        {
            $dbhandler = new PM_DBhandler;
            $lastrow = $dbhandler->get_all_result('FIELDS','field_id',1,'var',0,1,'field_id','DESC');
            $field_option = 'a:15:{s:17:"place_holder_text";s:0:"";s:19:"css_class_attribute";s:0:"";s:14:"maximum_length";s:0:"";s:13:"default_value";s:0:"";s:12:"first_option";s:0:"";s:21:"dropdown_option_value";s:0:"";s:18:"radio_option_value";a:1:{i:0;s:0:"";}s:14:"paragraph_text";s:0:"";s:7:"columns";s:0:"";s:4:"rows";s:0:"";s:18:"term_and_condition";s:0:"";s:18:"allowed_file_types";s:0:"";s:12:"heading_text";s:0:"";s:11:"heading_tag";s:2:"h1";s:5:"price";s:0:"";}';
            
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'Username','field_type'=>'user_name','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'display_on_profile'=>1,'visibility'=>1,'ordering'=>$ordering,'field_key'=>'user_login');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'First Name','field_type'=>'first_name','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'display_on_profile'=>1,'visibility'=>1,'ordering'=>$ordering,'field_key'=>'first_name');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'Last Name','field_type'=>'last_name','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'display_on_profile'=>1,'visibility'=>1,'ordering'=>$ordering,'field_key'=>'last_name');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'Email','field_type'=>'user_email','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'display_on_profile'=>1,'visibility'=>1,'ordering'=>$ordering,'field_key'=>'user_email');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'Password','field_type'=>'user_pass','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'ordering'=>$ordering,'field_key'=>'user_pass');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'Confirm Password','field_type'=>'confirm_pass','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'ordering'=>$ordering,'field_key'=>'confirm_pass');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'Website','field_type'=>'user_url','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'display_on_profile'=>1,'visibility'=>1,'ordering'=>$ordering,'field_key'=>'user_url');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            $ordering = $lastrow + 1;
            $field_data = array('field_name'=>'Biographical Info','field_type'=>'description','field_options'=>$field_option,'field_icon'=>0,'associate_group'=>$gid,'associate_section'=>$sid,'show_in_signup_form'=>1,'is_required'=>1,'display_on_profile'=>1,'visibility'=>1,'ordering'=>$ordering,'field_key'=>'description');
            $field_arg = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%s');
            $lastrow = $dbhandler->insert_row('FIELDS', $field_data,$field_arg);
            
            return $lastrow;
        }

        public function pg_get_edit_blog_post_link( $id = 0, $context = 'display' ) 
        {
            if ( ! $post = get_post( $id ) )
                    return;

            if ( 'revision' === $post->post_type )
                    $action = '';
            elseif ( 'display' == $context )
                    $action = '&amp;action=edit';
            else
                    $action = '&action=edit';

            $post_type_object = get_post_type_object( $post->post_type );
            if ( !$post_type_object )
                    return;

            if ( $post_type_object->_edit_link ) {
                    $link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
            } else {
                    $link = '';
	}

	/**
	 * Filters the post edit link.
	 *
	 * @since 2.3.0
	 *
	 * @param string $link    The edit link.
	 * @param int    $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands
	 *                        are encoded.
	 */
	return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );
}

        public function pg_auto_create_default_email_template($gid)
        {
            $dbhandler = new PM_DBhandler;
            $group_options = array();
            $group = $dbhandler->get_row('GROUPS',$gid);
            if($group->group_options!="")$group_options = maybe_unserialize($group->group_options);
            $group_options['enable_notification'] = '1';
            $body = "Dear {{first_name}} {{last_name}},<br /><br />You are now a member of {{group_name}} Group on {{site_name}}.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'On Joining Group','email_subject'=>'Welcome to your new Group!','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_registration'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
           
            $body = "Dear {{first_name}} {{last_name}},<br /><br />Admin has activated your user account on {{site_name}}. You can now login and access your member's area. <br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'User Account Activated','email_subject'=>'User Account Activated','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_user_activate'] = $dbhandler->insert_row('EMAIL_TMPL',$tmpl_data,$tmpl_arg);
            
            $body = "Dear {{first_name}} {{last_name}},<br /><br />Admin has deactivated your user account on {{site_name}}. For now, you cannot login or access your member area.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'User Account Deactivated','email_subject'=>'User Account Deactivated','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_user_deactivate'] = $dbhandler->insert_row('EMAIL_TMPL',$tmpl_data,$tmpl_arg);
            
            $body = "Dear {{first_name}} {{last_name}},<br /><br />Password for your user account on {{site_name}} was successfully changed. If you did not changed it, please contact the Manager immediately.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'Password Successfully Changed','email_subject'=>'Password Successfully Changed','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_password_change'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Dear {{first_name}} {{last_name}},<br /><br />Your user account on {{site_name}} associated with username {{user_login}} has been deleted successfully.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'User Account Deleted','email_subject'=>'User Account Deleted','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_account_deleted'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Dear {{first_name}} {{last_name}},<br /><br />Your blog post titled {{post_name}} was approved and published successfully.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'New User Blog Post','email_subject'=>'Your Post was Published Successfully!','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_published_new_post'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that you have been made {{group_admin_label}} of {{group_name}} on {{site_name}}. You can manage your group by logging in and accessing settings on the Group page.<br /><br />Regards.";
            $tmpl_data = array('tmpl_name'=>'Group Manager assignment','email_subject'=>'Congratulation! You have been made {{group_admin_label}} of {{group_name}}','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_admin_assignment'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that you are no longer {{group_admin_label}} of {{group_name}} on {{site_name}}. Consequently, you will not be able to access and manage group settings.<br /><br />Regards.";
            $tmpl_data = array('tmpl_name'=>'Group Manager Removal','email_subject'=>'You are no longer {{group_admin_label}} of {{group_name}}','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_admin_removal'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that a {{group_admin_label}} on {{site_name}} has reset your password. Your new password is:<br /><br />{{user_pass}}<br /><br />You can now login using your new password.<br /><br />Regards.";
            $tmpl_data = array('tmpl_name'=>'Password Reset by Group Manager','email_subject'=>'Your password was reset successfully','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_admin_reset_password'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Dear {{first_name}} {{last_name}},<br /><br />Sorry, Your membership request for {{group_name}} was not approved.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'On Request Denial','email_subject'=>'Membership request not approved','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_request_denied'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Dear {{group_admin_label}},<br /><br />You have received a new membership request for your {{group_name}} Group. You can approve or reject the request by visiting the Group management area.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'Membership Request','email_subject'=>'You have a new membership request','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_membership_request'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $group_data = maybe_serialize($group_options);
            $result = $dbhandler->update_row('GROUPS','id',$gid,array('group_options'=>$group_data),array('%s'),'%d');
            $dbhandler->update_global_option_value('pg_email_templates_created','1');
            $dbhandler->update_global_option_value('pg_email_templates_created_upgrade','1');
        }
        
        public function pg_auto_create_default_template_during_update()
        {
            $dbhandler = new PM_DBhandler;
            $group_options = array();
            
            $body = "Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that you are no longer {{group_admin_label}} of {{group_name}} on {{site_name}}. Consequently, you will not be able to access and manage group settings.<br /><br />Regards.";
            $tmpl_data = array('tmpl_name'=>'Group Manager Removal','email_subject'=>'You are no longer {{group_admin_label}} of {{group_name}}','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_admin_removal'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Hello {{first_name}} {{last_name}},<br /><br />We wish to inform you that a {{group_admin_label}} on {{site_name}} has reset your password. Your new password is:<br /><br />{{user_pass}}<br /><br />You can now login using your new password.<br /><br />Regards.";
            $tmpl_data = array('tmpl_name'=>'Password Reset by Group Manager','email_subject'=>'Your password was reset successfully','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_admin_reset_password'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Dear {{first_name}} {{last_name}},<br /><br />Sorry, Your membership request for {{group_name}} was not approved.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'On Request Denial','email_subject'=>'Membership request not approved','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_request_denied'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            
            $body = "Dear {{group_admin_label}},<br /><br />You have received a new membership request for your {{group_name}} Group. You can approve or reject the request by visiting the Group management area.<br /><br />Kind Regards.";
            $tmpl_data = array('tmpl_name'=>'Membership Request','email_subject'=>'You have a new membership request','email_body'=>$body);
            $tmpl_arg = array('%s','%s','%s');
            $group_options['on_membership_request'] = $dbhandler->insert_row('EMAIL_TMPL', $tmpl_data,$tmpl_arg);
            $dbhandler->update_global_option_value('pg_email_templates_created_upgrade','1');
            
        }
        
        public function pg_check_email_template_if_used_in_any_group($tid)
        {
            $dbhandler = new PM_DBhandler;
            $groups =  $dbhandler->get_all_result('GROUPS','*',1,'results');
            foreach($groups as $group)
            {
                if($group->group_options!="")
                {
                    $options = maybe_unserialize($group->group_options);
                    if(isset($options['on_registration']) && $options['on_registration']==$tid)
                    {
                       return $group->group_name;
                    }
                    if(isset($options['on_user_activate']) && $options['on_user_activate']==$tid)
                    {
                       return $group->group_name;
                    }
                    if(isset($options['on_user_deactivate']) && $options['on_user_deactivate']==$tid)
                    {
                       return $group->group_name;
                    }
                    if(isset($options['on_password_change']) && $options['on_password_change']==$tid)
                    {
                       return $group->group_name;
                    }
                    if(isset($options['on_account_deleted']) && $options['on_account_deleted']==$tid)
                    {
                       return $group->group_name;
                    }
                    if(isset($options['on_published_new_post']) && $options['on_published_new_post']==$tid)
                    {
                       return $group->group_name;
                    }
                    unset($options);
                }
            }
            return false;
            //print_r(count($groups));die;
        }
        
        public function is_pg_dashboard_page()
        {
        $page = filter_input(INPUT_GET, 'page');
        $plugin = get_current_screen();
        $pg_pages = array(
            'pm_general_settings',
            'pm_security_settings',
            'pm_user_settings',
            'pm_email_settings',
            'pm_third_party_settings',
            'pm_import_options',
            'pm_export_options',
            'pm_seo_settings',
            'pm_upload_settings',
            'pm_friend_settings',
            'pm_message_settings',
            'pm_payment_settings',
            'pm_tools',
            'pm_export_users',
            'pm_import_users',
            'pm_blog_settings',
            'pm_manage_groups',
            'pm_add_group',
            'pm_profile_fields',
            'pm_add_field',
            'pm_add_section',
            'pm_user_manager',
            'pm_user_edit',
            'pm_profile_view',
            'pm_add_email_template',
            'pm_shortcodes',
            'pm_settings',
            'pm_extensions',
            'pm_email_templates',
            'pm_email_preview',
            'pm_analytics',
            'pm_membership',
            'pm_content_restrictions',
            'pm_bbpress_settings',
            'pm_group_fields_settings',
            'pm_display_name_settings',
            'pm_front_end_groups_settings',
            'pm_geolocation_settings',
            'pm_group_photos_settings',
            'pm_group_wall_settings',
            'pm_mailchimp',
            'pm_add_mailchimp_list',
            'pm_mailchimp_settings',
            'pm_uid_changer_settings',
            'pm_woocommerce_settings',
            'pm_social_connect_settings');
        if(in_array($page,$pg_pages))
        {
            return true;
        }
        else
        {
            return true;
        }
        
        
    }
    
    public function pm_to_array($groups)
    {
        if(is_array($groups))
        {
            $group_ids = array();
            foreach ($groups as $key => $group)
            {
                $group_ids[$key] = $group['id'];
            }
        }

        return $group_ids;
    }
    
    public function pm_get_group_admin_label($gid)
    {
        $dbhandler = new PM_DBhandler;
        $group = $dbhandler->get_row('GROUPS',$gid);
        $admin_label = 'Group Manager';
        if($group->group_options!="")
        {    
            $group_options = maybe_unserialize($group->group_options);
            if(isset($group_options['admin_label']) && trim($group_options['admin_label'])!='' )
            {
                $admin_label = $group_options['admin_label'];
            }
        }
        
        return apply_filters('pm_filter_group_admin_label',$admin_label,$gid);
    }
    
    public function pm_get_all_group_blogs($gid,$pagenum=1,$limit=10,$sort_by='title-asc',$search_in='post_title',$search='')
    {
        $dbhandler = new PM_DBhandler;
        $meta_query = array('relation'=>'AND',array('key'=> 'pm_group','value'   => sprintf(':"%s";',$gid),'compare' => 'like'));
        $users =  $dbhandler->pm_get_all_users('',$meta_query);
        $author_ids = array('0');
        $path =  plugins_url( '../public/partials/images/default-featured.jpg', __FILE__ );
        foreach($users as $user)
        {
           array_push($author_ids, $user->ID);
        }
        $offset = ( $pagenum - 1 ) * $limit;
        
        $args = array(
        'post_type'        => 'profilegrid_blogs',
        'posts_per_page' => -1,
        'post_status' => 'publish,pending,draft',
        'author__in' => $author_ids
        );
        
        $args = $this->generate_blog_args($args,$sort_by,$search_in,$search,$gid);
        
        //print_r($args);die;
        $posts = get_posts( $args );
        
        $total_posts = count($posts);
        $offset = ( $pagenum - 1 ) * $limit;
        //echo $total_posts;
        $args['posts_per_page'] = $limit;
        $args['offset']= $offset;
        $posts_array = get_posts( $args );
        // echo count($posts_array);
        $num_of_pages = ceil( $total_posts/$limit);
          //echo $num_of_pages;
        $pagination = $dbhandler->pm_get_pagination($num_of_pages,$pagenum); 
        ?>
<input type="hidden" name="pg_blogs_uids" id="pg_blogs_uids" value="<?php echo maybe_serialize($author_ids); ?>" />
<input type="hidden" name="pg_blog_select_type" id="pg_blog_select_type" value="this_page" />
<input type="hidden" name="pg_total_blog_post" id="pg_total_blog_post" value="<?php echo esc_attr($total_posts); ?>" />
<input type="hidden" name="pg_total_blog_post_sigle_page" id="pg_total_blog_post_sigle_page" value="<?php echo esc_attr($limit); ?>" />
<?php if(!empty($posts_array)){?> 
<table class="pg-group-members pg-blog-setting">
    <tbody>
        <tr>
            <th><input class="pg-blog-checked-all" type="checkbox" value="1" name="" onclick="pg_checked_all_blogs(this)"></th>
            <th>&nbsp;</th>
            <th><?php _e('Title','profile-magic');?></th>
            <th><?php _e('Status','profile-magic');?></th>
            <th><?php _e('Last Modified','profile-magic');?></th>
            <th><?php _e('Author','profile-magic');?></th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        <!--********************************-->
        
        <?php foreach($posts_array as $post):?>
        <?php
        $pm_admin_note_content = trim(get_post_meta($post->ID,'pm_admin_note_content',true));
        $is_user_active = $this->pm_is_user_active($post->post_author);
        ?>
        <tr>
            <td><input class="pg-blog-checked  <?php if($is_user_active==false) echo 'inactive'; else echo 'active'; ?>" type="checkbox" value="<?php echo $post->ID;?>" name="pg_edit_blog_id[]" onclick="pm_show_hide_batch_operation('blog')"></td>
            <td>
                <div class="pg-member-avatar">
                    <?php if($post->post_status === 'publish') { ?> 
                    <a href="<?php echo get_permalink( $post->ID )?>" target="_blank">
                    <?php } ?>
                        <?php if(has_post_thumbnail($post->ID))
                              {
                                 echo get_the_post_thumbnail($post->ID,array(26,26),array( 'class' => 'avatar avatar-96 photo' ) );
                              }
                              else
                              {
                                  ?>
                        <img src="<?php echo $path;?>" width="26" height="26" alt="<?php the_title(); ?>" class="avatar avatar-96 photo" />
                                <?php 
                              }
                              ?>
                        <?php if($post->post_status === 'publish') { ?></a> <?php } ?>

                </div>
            </td>
            <td class="pg-blog-title"><?php if($post->post_status === 'publish') { ?><a href="<?php echo get_permalink( $post->ID )?>" target="_blank"><?php echo $post->post_title;?></a><?php } else { echo '<div class="pg-pending-blog-title">'. $post->post_title.'</div>'; } ?></td>

            <td><?php echo $post->post_status;?></td>
            <td><?php echo $this->pm_change_date_in_different_format($post->post_modified);?></td>
            <td><?php echo $this->pm_get_author_name_and_link($post->post_author);?></td>
            <td>
            <?php if($pm_admin_note_content!='')
                  {?>
                <a onclick="pg_edit_blog_popup('blog','add_admin_note','<?php echo $post->ID;?>','<?php echo $gid;?>')"><span class="pg-update-message"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
    <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18zM18 14H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
    <path d="M0 0h24v24H0z" fill="none"/>
</svg>
</span></a>
                    <?php }else{echo '';}?></td>            
                        <td>     
                            <div class="pg-setting-dropdown" onclick="pg_toggle_dropdown_menu(this)">
                                <div class="pg-dropdown-icon"> <i class="fa fa-cog" aria-hidden="true"></i> </div>
                                <ul class="pg-dropdown-menu">
                                    <li><a onclick="pg_edit_blog_popup('blog','change_status','<?php echo $post->ID;?>','<?php echo $gid;?>')"><?php _e('Change Status','profile-magic'); ?></a></li>
                                    <li><a onclick="pg_edit_blog_popup('blog','access_control','<?php echo $post->ID;?>','<?php echo $gid;?>')"><?php _e('Access Control','profile-magic'); ?></a></li>
                                    <li><a onclick="pg_edit_blog_popup('blog','edit','<?php echo $post->ID;?>','<?php echo $gid;?>')"><?php _e('Edit','profile-magic'); ?></a></li>
                                    <li><a onclick="pg_edit_blog_popup('blog','add_admin_note','<?php echo $post->ID;?>','<?php echo $gid;?>')"><?php _e('Add Note','profile-magic'); ?></a></li>
                                    <?php if($is_user_active):?>
                                    <li><a onclick="pg_edit_blog_popup('blog','message','<?php echo $post->ID;?>','<?php echo $gid;?>')"><?php _e('Message','profile-magic'); ?></a></li>
                                    <?php else:?>
                                    <li><a class="pg-setting-disabled"><?php _e('Message','profile-magic'); ?></a></li>
                                    <?php endif;?>
                                </ul>
                            </div>
                        </td>
        </tr>
        <?php endforeach;?>



    </tbody>
</table>

<?php }elseif(!empty($search)) { ?> 
     <div class='pg-alert-warning pg-alert-info'><?php _e('No blog posts matches your search.','profile-magic'); ?></div>
         <?php }else
         {
             ?><div class='pg-alert-warning pg-alert-info'><?php _e('No blog posts have been written by members of this group.','profile-magic'); ?></div><?php
         }
         ?>
        <?php
        echo '<div class="pm-blog-pagination">'.$pagination.'</div>';
        
       
    }
    
    public function generate_blog_args($args,$sort_by,$search_in,$search,$gid)
    {
        $dbhandler = new PM_DBhandler;
        switch($sort_by)
        {
            case 'title_asc':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'title_desc':
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
            case 'modified_desc':
                $args['orderby'] = 'modified';
                $args['order'] = 'DESC';
                break;
             case 'modified_asc':
                $args['orderby'] = 'modified';
                $args['order'] = 'ASC';
                break;
            case 'pending_post':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                $args['post_status'] = 'pending';
                break;
            default:
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            
        }
        
        if(trim($search)!=''):
            switch($search_in)
            {
                case 'post_title':
                    $args['s'] = $search;
                    break;
                case 'post_tag':
                    $args['tag'] = $search;
                    break;
                case 'author_name':
                    
                    $meta_query = array('search' => $search,'search_columns' => array( 'user_login','display_name' ));
                    $users =  $dbhandler->pm_get_all_users($search,$meta_query);
				
                    $author_ids = array();
                    foreach($users as $user)
                    {
                       array_push($author_ids, $user->ID);
                    }
                    
                    if(empty($author_ids))
                    {
                            $author_ids[0] = '0';
                    }
                   
                    $args['author__in'] = $author_ids;
                    break;
                default:
                    $args['s'] = $search;
                    break;
            }
        endif;
        
        return $args;
    }
    
    public function pm_change_date_in_different_format($date,$tab='')
    {
        $timestamp = strtotime($date);
        $current_year = date('Y');
        $actual_year = date('Y',$timestamp);
        switch($tab)
        {
            case 'request':
                if($current_year>$actual_year)
                {
                    $date = date('h:iA jS M Y',$timestamp);
                }
                else
                {
                    $date = date('h:iA jS M',$timestamp);
                }
                break;
            default:
                if($current_year>$actual_year)
                {
                    $date = date('jS M Y',$timestamp);
                }
                else
                {
                    $date = date('jS M',$timestamp);
                }
                break;
        }
        
        
        return $date;
    }
    
    public function pm_is_user_active($uid)
    {
        $is_active = get_user_meta($uid,'rm_user_status',true);
       if($is_active=='1')
       {
           return false;
       }
       else
       {
           return true;
       }
    }

    public function pm_get_author_name_and_link($uid)
    {
       $user = get_user_by('ID',$uid);
       $name =  $user->display_name;
       $url = $this->pm_get_user_profile_url($uid);
       $is_active = $this->pm_is_user_active($uid);
       if($is_active)
       {
           return '<a href="'.$url.'" target="_blank">'.$name.'</a>';
           
       }
       else
       {
           return '<span title="'.__('Author is suspended','profile-magic').'">'.$name.'</span>';
       }
    }
                
    public function pm_get_all_users_from_group($gid,$pagenum=1,$limit=10,$sort_by='first_name_asc',$search_in='user_login',$search='')
    {
        $dbhandler = new PM_DBhandler;
        $offset = ( $pagenum - 1 ) * $limit;
        $get = array('gid'=>$gid);
        if(trim($search)!='')
        {
            $get['match_field'] =$search_in;
            $get['field_value'] = $search;
            $search='';
            $is_search = 1;
        }
        
        switch($sort_by)
        {
            case 'name_asc':
                $sortby = 'display_name';
                $order = 'ASC';
                break;
            case 'name_desc':
                $sortby = 'display_name';
                $order = 'DESC';
                break;
            case 'latest_first':
                $sortby = 'registered';
                $order = 'DESC';
                break;
             case 'oldest_first':
                $sortby = 'registered';
                $order = 'ASC';
                break;
            case 'suspended':
                $sortby = 'registered';
                $order = 'DESC';
                $get['status'] = '1';
                break;
            case 'first_name_asc':
                $sortby = 'first_name';
                $order = 'ASC';
                break;
            case 'first_name_desc':
                $sortby = 'first_name';
                $order = 'DESC';
                break;
            case 'last_name_asc':
                $sortby = 'last_name';
                $order = 'ASC';
                break;
            case 'last_name_desc':
                $sortby = 'last_name';
                $order = 'DESC';
                break;
            default:
                $sortby = 'display_name';
                $order = 'ASC';
                break;
            
        }
        $current_user = wp_get_current_user();
        
        $meta_query_array = $this->pm_get_user_meta_query($get);
       
        $user_query =  $dbhandler->pm_get_all_users_ajax($search,$meta_query_array,'',$offset,$limit,$order,$sortby,array($current_user->ID));
        $total_users = $user_query->get_total();
        $users = $user_query->get_results();
        $num_of_pages = ceil( $total_users/$limit);
        $pagination = $dbhandler->pm_get_pagination($num_of_pages,$pagenum);
        
        ?>
<?php if(!empty($users)){?>
<input type="hidden" name="pg_member_select_type" id="pg_member_select_type" value="this_page" />
<input type="hidden" name="pg_total_group_members" id="pg_total_group_members" value="<?php echo esc_attr($total_users); ?>" />
<input type="hidden" name="pg_total_group_members_sigle_page" id="pg_total_group_members_sigle_page" value="<?php echo esc_attr($limit); ?>" />
 <table class="pg-group-members">
    <tbody>
        <tr>
            <th><input class="pg-member-checked-all" type="checkbox"   value="1" name="" onclick="pg_checked_all_member(this)"></th>
            <th>&nbsp;</th>
            <th class="pg-group-member-name"><?php _e('First Name','profile-magic');?></th>
            <th class="pg-group-member-name"><?php _e('Last Name','profile-magic');?></th>
            <th>&nbsp;</th>
            <th><?php _e('Joined on','profile-magic');?></th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th> 
        </tr>
        <!--********************************-->
        
        <?php foreach($users as $user):?>
        <tr>
            <?php $user_status = get_user_meta($user->ID,'rm_user_status',true); ?>
            <td><input class="pg-member-checked <?php if($user_status=='1') echo 'inactive'; else echo 'active'; ?>" type="checkbox"   value="<?php echo $user->ID;?>" name=""onclick="pm_show_hide_batch_operation('member')"></td>
            <td>
                <div class="pg-member-avatar">
                    <a href="<?php echo $this->pm_get_user_profile_url($user->ID)?>" target="_blank">
                        <?php echo get_avatar($user->user_email, 26 );?>
                    </a>
                </div>
            </td>
            <td class="pg-group-member-name"><a href="<?php echo $this->pm_get_user_profile_url($user->ID)?>" target="_blank"><?php echo $user->first_name;?></a></td>
            <td class="pg-group-member-name"><a href="<?php echo $this->pm_get_user_profile_url($user->ID)?>" target="_blank"><?php echo $user->last_name;?></a></td>
            
            <td><?php  $login_status = ($this->pm_get_user_online_status($user->ID)==1 ?'pm-online':'pm-offline');?> <span class="pm-friend-status <?php echo $login_status;?>"></span></td>
            <td><?php echo $this->pg_get_group_joining_date($gid, $user->ID);?></td>
            <td><a class="pm-remove" onclick="pg_edit_blog_popup('member','remove_user','<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Remove');?></a></td>
            <?php $user_status = get_user_meta($user->ID,'rm_user_status',true);
            if($user_status=='1'):?>
            <td><a onclick="pg_activate_user('<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Activate');?></a></td>
             <?php else:?>
            <td><a onclick="pg_edit_blog_popup('member','deactivate_user','<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Suspend');?></a></td>
             <?php endif;?>
            
                        <td>     
                            <div class="pg-setting-dropdown" onclick="pg_toggle_dropdown_menu(this)">
                                <div class="pg-dropdown-icon"> <i class="fa fa-cog" aria-hidden="true"></i> </div>
                                <ul class="pg-dropdown-menu">
                                   
                                    <?php $is_user_active = $this->pm_is_user_active($user->ID); if($is_user_active):?>
                                     <li><a onclick="pg_edit_blog_popup('member','reset_password','<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Reset Password','profile-magic'); ?></a></li>
                                    <li><a onclick="pg_edit_blog_popup('member','message','<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Message','profile-magic'); ?></a></li>
                                    <li><a href="<?php echo $this->pm_get_user_edit_profile_url($user->ID,$gid)?>"><?php _e('Edit Profile','profile-magic'); ?></a></li>
                                    <?php else:?>
                                    <li> <a class="pg-setting-disabled"><?php _e('Reset Password','profile-magic'); ?></a></li>
                                    <li><a class="pg-setting-disabled"><?php _e('Message','profile-magic'); ?></a></li>
                                    <li><a class="pg-setting-disabled"><?php _e('Edit Profile','profile-magic'); ?></a></li>
                                     
                                    <?php endif;?>
                                     <?php do_action('pm_cog_option',$user->ID,$gid); ?>
                                </ul>
                            </div>
                        </td>
        </tr>
        <?php endforeach;?>



    </tbody>
</table>
<?php 

}elseif(!empty($is_search)) {
    ?> 
     <div class='pg-alert-warning pg-alert-info'><?php _e('No user matches your search.','profile-magic'); ?></div>
         <?php }else
         {
            ?> 
     <div class='pg-alert-warning pg-alert-info'><?php _e('There are no members in this group. You can start inviting new members by clicking <i>Add</i> Button.','profile-magic'); ?></div>
         <?php 
         }
         
         ?>
        <?php
        echo '<div class="pm-member-pagination">'.$pagination.'</div>';
        
       
    }
    
    public function generate_members_args($args,$sort_by,$search_in,$search,$gid)
    {
        $dbhandler = new PM_DBhandler;
        $get = 
        $meta_query = $this->pm_get_user_meta_query($get);
        switch($sort_by)
        {
            case 'name_asc':
                $args['orderby'] = 'login';
                $args['order'] = 'ASC';
                break;
            case 'name_desc':
                $args['orderby'] = 'login';
                $args['order'] = 'DESC';
                break;
            case 'latest_first':
                $args['orderby'] = 'registered';
                $args['order'] = 'DESC';
                break;
             case 'oldest_first':
                $args['orderby'] = 'registered';
                $args['order'] = 'ASC';
                break;
            case 'suspended':
                $args['orderby'] = 'registered';
                $args['order'] = 'DESC';
                $args['post_status'] = 'pending';
                break;
            default:
                $args['orderby'] = 'login';
                $args['order'] = 'ASC';
                break;
            
        }
        
        if(trim($search)!=''):
            switch($search_in)
            {
                case 'post_title':
                    $args['s'] = $search;
                    break;
                case 'post_tag':
                    $args['tag'] = $search;
                    break;
                case 'author_name':
                    $meta_query = array('relation'=>'OR',array('key'=>'first_name','value'=>$search,'compare'=>'LIKE'),array('key'=>'last_name','value'=>$search,'compare'=>'LIKE'));
                    $users =  $dbhandler->pm_get_all_users($search,$meta_query);
                    $author_ids = array();
                    foreach($users as $user)
                    {
                       array_push($author_ids, $user->ID);
                    }
                    if(isset($args['author__in']) && is_array($args['author__in']))
                    {
                        $args['author__in'] = array_intersect($author_ids,$args['author__in']);
                    }
                    else
                    {
                        $args['author__in'] = $author_ids;
                    }
                    break;
                default:
                    $args['s'] = $search;
                    break;
            }
        endif;
        
        return $args;
    }
    
    public function pg_get_user_groups_badge_slider($uid)
    {
        $dbhandler = new PM_DBhandler;
        $group_page_url = $this->profile_magic_get_frontend_url('pm_group_page','');
        $user_groups = $this->profile_magic_get_user_field_value($uid,'pm_group');
        $gid_array = $this->pg_filter_users_group_ids($user_groups);
	/*if(isset($gid) && $gid!='') $groupinfo = $dbhandler->get_row('GROUPS',$gid);*/
        if(!empty($gid_array))
        {
            if(count($gid_array)==1){$class='pm-single-group-badge';}else {$class = '';}
            echo '<ul class="'.$class.'">';
            $gid_array = array_reverse($gid_array);
            $i=0;
            foreach($gid_array as $gid)
            {
                if($i>=4){continue;}
                $group_page_link = add_query_arg( 'gid',$gid,$group_page_url );
                $groupinfo = $dbhandler->get_row('GROUPS',$gid);
               
                ?>
                <li>
                    <a href='<?php echo esc_url($group_page_link); ?>'>
                        <?php echo $this->profile_magic_get_group_icon($groupinfo,'pm-group-badge');?>
                    </a> 
                </li>
                <?php
                $i++;
            }
            echo '</ul>';
        }
    }
    
    public function pg_leave_group($uid,$gid)
    {
        $dbhandler = new PM_DBhandler;
        $pm_emails = new PM_Emails;
        $user_group = $this->profile_magic_get_user_field_value($uid,'pm_group');
        $user_groups = $this->pg_filter_users_group_ids($user_group);
        $is_leader = $this->pg_check_in_single_group_is_user_group_leader($uid, $gid);
        if($is_leader)
        {
            $row = $dbhandler->get_row('GROUPS',$gid);
            $group_leaders = maybe_unserialize($row->group_leaders);
            $group_leaders_array = maybe_serialize(array_merge(array_diff($group_leaders, array($uid))));
            $first_name = $this->profile_magic_get_user_field_value($uid,'first_name');
            $last_name = $this->profile_magic_get_user_field_value($uid, 'last_name');
            $user_name = $this->profile_magic_get_user_field_value($uid, 'user_login');
            $group_page_url  = admin_url('admin.php?page=pm_add_group');
            $group_page_url = add_query_arg( 'id',$gid, $group_page_url );
            $dbhandler->update_row('GROUPS','id',$gid,array('group_leaders'=>$group_leaders_array));
            $subject = __('An Manager left the group','profile-magic');
            $message = __("Hello,","profile-magic");
            $message .= "<br />\r\n\r\n";
            $message .= __("A {{group_admin_label}} has left the group {{group_name}}. Consequently, this group is without a {{group_admin_label}}. Closed user groups require a {{group_admin_label}} for proper group management. Please assign a new {{group_admin_label}} to this group by visiting {{edit_group_url}}. Alternatively, consider making it an Open group. For now, all messages for {{group_admin_label}} of {{group_name}} will be redirected to you.","profile-magic");
            $message .= "<br />\r\n\r\n";
            $message .= __( 'Regards.','profile-magic' ) . "<br />\r\n";
            $message = $pm_emails->pm_filter_email_content($message,$uid,false,$gid);
            $pm_emails->pm_send_admin_notification($subject, $message);
        }
	if(is_array($user_groups))
        {
            $gid_array = $user_groups;
        } 
        else
        {
            $gid_array = array($user_groups);
        }
        
        $gid_array = array_merge(array_diff($gid_array, array($gid)));
        
       $update =  update_user_meta($uid,'pm_group', $gid_array);
       if($update)
       {
           return 'success';
       }
       else
       {
           return 'failed';
       }
    }
    
    public function pg_get_group_leaders($gid)
    {
        $dbhandler = new PM_DBhandler;
        $leaders = array();
        $is_group_leader = $dbhandler->get_value('GROUPS','is_group_leader',$gid,'id');

        if($is_group_leader!=0)
        {
            $group_leaders = maybe_unserialize($dbhandler->get_value('GROUPS','group_leaders',$gid,'id'));
            if(is_array($group_leaders))
            {
                $leaders = $group_leaders;
            }
            else
            {
                $leaders['primary'] = $group_leaders;
            }
        }
        
        return $leaders;
        
    }
    public function pg_check_in_single_group_is_user_group_leader($userid,$gid)
    {
            $leaders = $this->pg_get_group_leaders($gid);
            if(!empty($leaders))
            {
                if(in_array($userid, $leaders))
                {
                    return true;	
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
    }
    
    public function profile_magic_get_group_type($gid)
    {
        $dbhandler = new PM_DBhandler;
        $group_options = maybe_unserialize($dbhandler->get_value('GROUPS','group_options',$gid));
        if(isset($group_options['group_type']))
        {
            return $group_options['group_type'];
        }
        else
        {
            return 'open';
        }
    }
    
    public function profile_magic_check_is_group_member($gid,$uid)
    {
        $user_group = $this->profile_magic_get_user_field_value($uid,'pm_group');
        $pm_group = $this->pg_filter_users_group_ids($user_group);
        if(is_array($pm_group) && !empty($pm_group))
        {
            if(in_array($gid,$pm_group))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    public function profile_magic_check_is_requested_to_join_group($gid,$uid)
    {
        $dbhandler = new PM_DBhandler;
        $where = array('gid'=>$gid,'uid'=>$uid,'status'=>'1');
        $row = $dbhandler->get_all_result('REQUESTS','*', $where);
        return $row;
    }
    public function profile_magic_get_join_group_button($gid)
    {
        $dbhandler = new PM_DBhandler;
        $pmrequests = new PM_request;
        $registration_url  = $this->profile_magic_get_frontend_url('pm_registration_page','');
        $registration_url = add_query_arg( 'gid',$gid, $registration_url );
        $is_group_limit = $dbhandler->get_value('GROUPS','is_group_limit',$gid);
        $meta_query_array = $pmrequests->pm_get_user_meta_query(array('gid'=>$gid));
        $limit = $dbhandler->get_value('GROUPS','group_limit',$gid);
        $total_users_in_group = count($dbhandler->pm_get_all_users('',$meta_query_array));
        if(is_user_logged_in())
        {
            $current_user = wp_get_current_user();
            $requested = $this->profile_magic_check_is_requested_to_join_group($gid,$current_user->ID);
            if($requested==null)
            {
                if($this->profile_magic_check_is_group_member($gid,$current_user->ID))
                {
                   ?>
                    <div class="pm-group-signup">
                   <a class="pm_button" onclick="pg_edit_blog_popup('group','remove_group','<?php echo $current_user->ID;?>','<?php echo $gid;?>')">
                       <button><?php _e('Leave Group','profile-magic');?></button>
                   </a>
                    </div>
                    <?php
                }
                else
                {
                    if($is_group_limit==1)
                    {
                        if($limit > $total_users_in_group)
                        {
                         ?>
                            <div class="pm-group-signup">
                                <form method="post">
                                    <input type="hidden" name="pg_uid" id="pg_uid" value="<?php echo $current_user->ID;?>" />
                                    <input type="hidden" name="pg_join_gid" id="pg_join_gid" value="<?php echo $gid;?>" />
                                    <input type="hidden" name="pg_join_group" id="pg_join_group" value="1" />
                                    <a class="pm_button" onclick="submit()">
                                        <button type="submit"><?php _e('Join Group','profile-magic');?></button>
                                    </a>
                                </form>
                            </div>
                         <?php 
                        }
                        else
                        {
                                
                                $message  = $dbhandler->get_value('GROUPS','group_limit_message',$gid);
                                echo '<div class="pg-group-limit-message">'. $message.'</div>';
                        }
                    }
                    else
                    {
                    ?>
                        <div class="pm-group-signup">
                                <form method="post">
                                    <input type="hidden" name="pg_uid" id="pg_uid" value="<?php echo $current_user->ID;?>" />
                                    <input type="hidden" name="pg_join_gid" id="pg_join_gid" value="<?php echo $gid;?>" />
                                    <input type="hidden" name="pg_join_group" id="pg_join_group" value="1" />
                                    <a class="pm_button" onclick="submit()">
                                        <button type="submit"><?php _e('Join Group','profile-magic');?></button>
                                    </a>
                                </form>
                        </div>
                    <?php 
                    }
                }
            }
            
        }
        else
        {
            if($is_group_limit==1)
            {
                if($limit > $total_users_in_group)
                {
            ?>
                <div class="pm-group-signup">
                    <a href="<?php echo $registration_url;?>" class="pm_button">
                        <button><?php _e('Join Group','profile-magic');?></button>
                    </a>
                </div>
                <?php
                }
                else
                {
                        $message  = $dbhandler->get_value('GROUPS','group_limit_message',$gid);
                        echo $message;
                }
            }
            else
            {
            ?>
                <div class="pm-group-signup">
                    <a href="<?php echo $registration_url;?>" class="pm_button">
                        <button><?php _e('Join Group','profile-magic');?></button>
                    </a>
                </div>                
            <?php 
            }
        }
        
      
    }
    
    public function profile_magic_join_group_fun($uid,$gid,$type)
    {
        $pmemails = new PM_Emails;
        $dbhandler = new PM_DBhandler;
        $notification = new Profile_Magic_Notification;
        if($type=='open')
        {
            $user_group = maybe_unserialize($this->profile_magic_get_user_field_value($uid,'pm_group'));
            $user_groups = $this->pg_filter_users_group_ids($user_group);
            $joining_dates = $this->profile_magic_get_user_field_value($uid,'pm_joining_date');
            
            if(is_array($user_groups))
            {
                $gid_array = $user_groups;
            } 
            else
            {
                if($user_groups != '' && $user_groups != NULL)
                {
                    $gid_array = array($user_groups);
                }
                else
                {
                    $gid_array = array();
                }
            }
            
            if(is_array($joining_dates) && !empty($joining_dates))
            {
                $joining_dates[$gid]=  date('Y-m-d');
            }
            else
            {
                $joining_dates = array();
                $joining_dates[$gid]=  date('Y-m-d');
             
            }
            if(!in_array($gid, $gid_array))
            {
                $gid_array = array_merge($gid_array, array($gid));
            }
            $update =  update_user_meta($uid,'pm_joining_date', $joining_dates);
            $update =  update_user_meta($uid,'pm_group', $gid_array);
            $where = array('gid'=>$gid,'uid'=>$uid);
            $data = array('status'=>'3');
            $request_id = $dbhandler->get_value_with_multicondition('REQUESTS','id',$where);
            if(!empty($request_id))
            {
                $dbhandler->remove_row('REQUESTS','id',$request_id);
            }
            
            do_action('profile_magic_join_group_additional_process',$gid,$uid);
            $pmemails->pm_send_group_based_notification($gid,$uid,'on_registration');
            $notification->pm_joined_new_group_notification($uid,$gid);
            return $update;
        }
        else
        {
           
            $date = date('Y-m-d H:i:s'); 
            $options = maybe_serialize(array('request_date'=>$date));
            $data = array('gid'=>$gid,'uid'=>$uid,'status'=>'1','options'=>$options);
            //check if request already submitted 
            $where = array('gid'=>$gid,'uid'=>$uid);
            $request_id = $dbhandler->get_value_with_multicondition('REQUESTS','id',$where);
            if($request_id=='' || $request_id==NULL)
            {
                $dbhandler->insert_row('REQUESTS',$data,array('%d','%d','%d','%s'));
            }
            $groupleaders = $this->pg_get_group_leaders($gid);
            if(empty($groupleaders))
            {
                $email_address = get_option('admin_email');
                $leader_info = get_user_by('email', $email_address);
                $groupleaders[] = $leader_info->ID;
            }
            
            foreach($groupleaders as $leader)
            {
                
                 $pmemails->pm_send_group_based_notification_to_group_admin($gid,$uid,'on_membership_request',$leader);
            }
            return true;
        } 
    }
    
    public function pm_get_all_join_group_requests($gid,$pagenum=1,$limit=10,$sort_by='first_name_asc',$search='',$search_in='user_login')
    {
       //echo $gid;die;
        $dbhandler = new PM_DBhandler;
        $offset = ( $pagenum - 1 ) * $limit;
        $user_request = array();
        $requested = $dbhandler->get_all_result('REQUESTS','*',array('gid'=>$gid,'status'=>'1'));
        if(!empty($requested))
        {
            foreach($requested as $request)
            {
                $user_request[] = $request->uid;
            }
        }
        else
        {
            $user_request[] = 0;
        }
        
        if(trim($search)!='')
        {
            $meta_query_array = array('key' => $search_in,'value' => $search,'compare' => 'LIKE'); 
        }
        else 
        {
            $meta_query_array = array();
        }
        
        switch($sort_by)
        {
            case 'name_asc':
                $sortby = 'login';
                $order = 'ASC';
                break;
            case 'name_desc':
                $sortby = 'login';
                $order = 'DESC';
                break;
            case 'latest_first':
                $sortby = 'registered';
                $order = 'DESC';
                break;
             case 'oldest_first':
                $sortby = 'registered';
                $order = 'ASC';
                break;
            case 'suspended':
                $sortby = 'registered';
                $order = 'DESC';
                $get['status'] = '1';
                break;
            case 'first_name_asc':
                $sortby = 'first_name';
                $order = 'ASC';
                break;
            case 'first_name_desc':
                $sortby = 'first_name';
                $order = 'DESC';
                break;
            case 'last_name_asc':
                $sortby = 'last_name';
                $order = 'ASC';
                break;
            case 'last_name_desc':
                $sortby = 'last_name';
                $order = 'DESC';
                break;
            default:
                $sortby = 'login';
                $order = 'ASC';
                break;
            
        }
       
        $current_user = wp_get_current_user();
        if(!empty($get)) { $meta_query_array = $this->pm_get_user_meta_query($get); }
        $user_query =  $dbhandler->pm_get_all_users_ajax($search,$meta_query_array,'',$offset,$limit,$order,$sortby,array($current_user->ID),array(),$user_request);
        $total_users = $user_query->get_total();
        $users = $user_query->get_results();
        $num_of_pages = ceil( $total_users/$limit);
        $pagination = $dbhandler->pm_get_pagination($num_of_pages,$pagenum);
        
        ?>
<input type="hidden" name="pg_request_select_type" id="pg_request_select_type" value="this_page" />
<input type="hidden" name="pg_total_request_users" id="pg_total_request_users" value="<?php echo esc_attr($total_users); ?>" />
<input type="hidden" name="pg_total_request_users_sigle_page" id="pg_total_request_users_sigle_page" value="<?php echo esc_attr($limit); ?>" />
<?php if(!empty($users)) { ?> 
<table class="pg-group-requests pg-group-members">
    <tbody>
        <tr>
            <th><input class="pg-requests-checked-all" type="checkbox"   value="1" name="" onclick="pg_checked_all_requests(this)"></th>
            <th>&nbsp;</th>
            <th class="pg-group-member-name"><?php _e('First Name','profile-magic');?></th>
            <th class="pg-group-member-name"><?php _e('Last Name','profile-magic');?></th>
            <th>&nbsp;</th>
            <th><?php _e('Request Sent On','profile-magic');?></th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        <!--********************************-->
        <?php foreach($users as $user):
             $requested = $dbhandler->get_all_result('REQUESTS','*',array('gid'=>$gid,'uid'=>$user->ID,'status'=>'1'));
             $request_options = maybe_unserialize ($requested[0]->options);
        ?>
        <tr>
            <td><input class="pg-request-checked" type="checkbox"   value="<?php echo $user->ID;?>" name=""onclick="pm_show_hide_batch_operation('requests')"></td>
            <td>
                <div class="pg-member-avatar">
                    <a href="<?php echo $this->pm_get_user_profile_url($user->ID)?>" target="_blank">
                        <?php echo get_avatar($user->user_email, 26 );?>
                    </a>
                </div>
            </td>
            <td class="pg-group-member-name"><a href="<?php echo $this->pm_get_user_profile_url($user->ID)?>" target="_blank"><?php echo $user->first_name;?></a></td>
            <td class="pg-group-member-name"><a href="<?php echo $this->pm_get_user_profile_url($user->ID)?>" target="_blank"><?php echo $user->last_name;?></a></td>
            <td><?php  $login_status = ($this->pm_get_user_online_status($user->ID)==1 ?'pm-online':'pm-offline');?> <span class="pm-friend-status <?php echo $login_status;?>"></span></td>
            <td><?php echo $this->pm_change_date_in_different_format($request_options['request_date'],'request');?></td>
            <td><a class="pm-remove" onclick="pg_edit_blog_popup('group','decline_request','<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Decline');?></a></td>
            <td><a onclick="pg_edit_blog_popup('group','accept_request','<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Approve');?></a></td>
            
            
                        <td>     
                            <div class="pg-setting-dropdown" onclick="pg_toggle_dropdown_menu(this)">
                                <div class="pg-dropdown-icon"> <i class="fa fa-cog" aria-hidden="true"></i> </div>
                                <ul class="pg-dropdown-menu">
                                    <li><a onclick="pg_edit_blog_popup('member','message','<?php echo $user->ID;?>','<?php echo $gid;?>')"><?php _e('Message','profile-magic'); ?></a></li>
                                </ul>
                            </div>
                        </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<?php } else { ?> 
     <div class='pg-alert-warning pg-alert-info'><?php _e('There are no pending membership requests.','profile-magic'); ?></div>
<?php } 
    
    echo '<div class="pm-request-pagination">'.$pagination.'</div>';
        
       
    }
    
    public function pg_get_primary_group_id($gid)
    {
        if(is_array($gid))
        {
            if(isset($gid[0]))
            {
               $single_group = $gid[0]; 
            }
            else
            {
                $single_group = array('0');
            }
        }
        else
        {
            $single_group = $gid;
        }
        return $single_group;
    }
    
    public function pm_get_all_rm_registration_form_dropdown_list($selected)
    {
        $dbhandler = new PM_DBhandler;
        $forms = $dbhandler->get_all_result('FORMS','*',array('form_type'=>'1'));
       // print_r($forms);die;
        if(!empty($forms)):
        foreach($forms as $form)
        {
           ?>
            <option value="<?php echo $form->form_id;?>" <?php selected($selected,$form->form_id); ?>><?php echo $form->form_name;?></option>
           <?php
        }
        endif;
    }
    
    public function pm_get_all_rm_registration_form_fields_dropdown_list($selected,$form_id)
    {
        $dbhandler = new PM_DBhandler;
        $fields = $dbhandler->get_all_result('FORM_FIELDS','*',array('form_id'=>$form_id));
       // print_r($forms);die;
        foreach($fields as $field)
        {
           if(in_array($field->field_type,array('UserPassword','Username'))) continue;
           ?>
            <option value="<?php echo $field->field_id;?>" <?php selected($selected,$field->field_id); ?>><?php echo $field->field_label;?></option>
           <?php
        }
    }
    
    public function pm_check_if_group_associate_with_rm_form($gid)
    {
        $dbhandler = new PM_DBhandler;
        $group_options = maybe_unserialize($dbhandler->get_value('GROUPS','group_options',$gid,'id'));
        
        if(isset($group_options) && isset($group_options['pg_rm_form']))
        {
            $form_type = $this->pm_check_rm_form_type($group_options['pg_rm_form']);
            if($form_type)
            {
                return $group_options['pg_rm_form'];
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return 0;
        }
    }
    
    public function pm_check_rm_form_type($form_id)
    {
        $dbhandler = new PM_DBhandler;
        $type = $dbhandler->get_value('FORMS','form_type',$form_id,'form_id');
        return $type;
    }
    
    public function pm_check_rm_form_associate_with_groups($form_id)
    {
        $dbhandler = new PM_DBhandler;
        $groups = $dbhandler->get_all_result('GROUPS');
        $associate_groups = array();
        foreach($groups as $group)
        {
            $group_options = maybe_unserialize($group->group_options);
            if(isset($group_options) && isset($group_options['pg_rm_form']) && $group_options['pg_rm_form']==$form_id)
            {
               $associate_groups[] =  $group->id;
            }
           unset($group_options);
        }
        return $associate_groups;
    }
    
    public function pm_get_map_fields_with_rm_form($gid)
    {
        $dbhandler = new PM_DBhandler;
        $mapping = array();
        $fields = $dbhandler->get_all_result('FIELDS','*',array('associate_group' => $gid));
        foreach($fields as $field)
        {
            $field_options = maybe_unserialize($field->field_options);
            if(!empty($field_options) && isset($field_options['field_map_with']))
            {
                $mapping[$field->field_key]  = array('field_map_with'=>$field_options['field_map_with'],'field_type'=>$field->field_type,'field_key'=>$field->field_key);
            }
            unset($field_options);
        }
        return $mapping;
        
    }
    
    public function pm_check_group_limit($gid)
    {
        $dbhandler = new PM_DBhandler;
        $message ='';
        $limit = $dbhandler->get_value('GROUPS','group_limit',$gid);
        $is_group_limit = $dbhandler->get_value('GROUPS','is_group_limit',$gid);
        $meta_query_array = $this->pm_get_user_meta_query(array('gid'=>$gid));
        $total_users_in_group = count($dbhandler->pm_get_all_users('',$meta_query_array));
        if($is_group_limit==1)
	{
            if($limit > $total_users_in_group)
            {
                    $message ='';	
            }
            else
            {
                    $message  = $dbhandler->get_value('GROUPS','group_limit_message',$gid);
            }
        }
        
        return $message;
    }
    
    
    public function pg_get_group_joining_date($gid,$uid)
    {
        $joining_dates = $this->profile_magic_get_user_field_value($uid,'pm_joining_date');
        if(is_array($joining_dates) && !empty($joining_dates) && isset($joining_dates[$gid]))
        {
            $date = $joining_dates[$gid];
        }
        else
        {
            $user = get_user_by('ID', $uid);
            $date = $user->user_registered;
        }
        
        return $this->pm_change_date_in_different_format($date);
    }
    
    public function is_user_online($user_to_check){
        // get the user activity the list
        $logged_in_users = get_transient('rm_user_online_status');

        $online = isset($logged_in_users[$user_to_check])
       && ($logged_in_users[$user_to_check] >  (time() - (15 * 60)));
        
        return $online;
    }
    
    public function pg_filter_users_group_ids($gids)
    {
        $dbhandler = new PM_DBhandler;
        $groups = array();
        if(isset($gids) && !empty($gids))
        {
            if(is_array($gids))
            {
                $usergroup = array_unique($gids);
                foreach($usergroup as $gid)
                {
                    $groupinfo = $dbhandler->get_row('GROUPS',$gid);
                    if(isset($groupinfo) && !empty($groupinfo))
                    {
                        $groups[] = $gid;
                    }
                }
            }
            elseif(is_numeric($gids)) 
            {
                $groupinfo = $dbhandler->get_row('GROUPS',$gids);
                if(isset($groupinfo) && !empty($groupinfo))
                {
                    $groups[] = $gids;
                }
            }
        }
        
        
        return $groups;
    }
    
    public function pg_get_rm_installation_plugin_url()
    {
        $plugin_slug= 'custom-registration-form-builder-with-submission-manager';
        $installUrl = admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug);
        $installUrl = wp_nonce_url($installUrl, 'install-plugin_' . $plugin_slug);
        
        return $installUrl;
    }
    
    public function pg_get_filter_rm_value($map_field,$rm_data,$user_id)
    {
        $field_type = $map_field['field_type'];
        $map_with= $map_field['field_map_with'];
        $value = $rm_data[$map_with]->value;
        $rm_field_type = $rm_data[$map_with]->type;
        if(is_array($value))
        {
            if(isset($value['rm_field_type']))
            {
                $rm_field_type = $value['rm_field_type'];
                unset($value['rm_field_type']);
            }
        }
        if(!empty($value))
        {
            if(isset($rm_field_type) && ($rm_field_type=='File' || $rm_field_type=='Image'))
            {
                if($field_type=='file' || $field_type=='user_avatar')
                {
                    $values = implode(',', $value);
                }
                else
                {
                    $value['rm_file_field'] = 1;
                    $values = maybe_serialize($value); 
                }
            }
            else
            {
                if($rm_field_type=='Address')
                {
                    if(isset($value['address1']))
                    {
                        $value['address_line_1'] =$value['address1'];
                        unset($value['address1']);
                    }
                    if(isset($value['address2']))
                    {
                        $value['address_line_2'] =$value['address2'];
                        unset($value['address2']);
                    }

                    if(isset($value['original']))
                    {
                        $value['address_line_1'] =$value['original'];
                        unset($value['original']);
                    }
                    if(isset($value['st_number']))
                    {
                        $value['address_line_2'] =$value['st_number'];
                        unset($value['st_number']);
                    }
                    if(isset($value['st_route']))
                    {
                        $value['address_line_2'] = $value['address_line_2'].' '.$value['st_route'];
                        unset($value['st_route']);
                    }

                    if(isset($value['city']))
                    {
                        $tmp =$value['city'];
                        unset($value['city']);
                        $value['city'] = $tmp;
                    }

                    if(isset($value['state']))
                    {
                        $tmp =$value['state'];
                        unset($value['state']);
                        $value['state'] = $tmp;
                    }

                     if(isset($value['country']))
                    {
                        $tmp =$value['country'];
                        unset($value['country']);
                        $value['country'] = $tmp;
                    }

                    if(isset($value['zip']))
                    {
                        $value['zip_code'] =$value['zip'];
                        unset($value['zip']);
                    }
                    
                    $this->pg_update_user_lat_long_using_address_using_rm($value,$user_id);
                    
                }
                
                if($field_type=='user_avatar')
                {
                    $value = '';
                }
                
                if($rm_field_type=='Terms' && $field_type=='term_checkbox')
                {
                    if($value=='on')
                    {
                        $value = 'yes';
                    }
                    else
                    {
                        $value = '';
                    }
                }
                
                if($rm_field_type=='Terms' && $field_type=='text')
                {
                    if($value=='on')
                    {
                        $value = __('Accepted Terms & Condition','profile-magic');
                    }
                    else
                    {
                        $value = __('Rejected Terms & Conditions','profile-magic');
                    }
                    
                }
                
                if($rm_field_type=='Country' && $field_type=='country')
                {
                    $value = substr($value, 0, -4);
                }
                
                if($rm_field_type=='Checkbox' && $field_type=='checkbox')
                {
                    $field_model= new RM_Fields();
                    $field_model->load_from_db($map_with);
                    $rm_values= $field_model->get_field_value();
                    $diff = array_diff($value, $rm_values);
                    if(!empty($diff))
                    {
                        $value[] = 'chl_other';
                    }
                   
                }
                
                if(($field_type=='file' || $field_type=='user_avatar') && ($rm_field_type!='File' || $rm_field_type!='Image'))
                {
                    $value = '';
                }
                
                $values = maybe_serialize($value);
            }
        
        }
        else
        {
            $values = '';
            
            if($rm_field_type=='Terms' && $field_type=='text')
            {
               $value = __('Rejected Terms & Conditions','profile-magic');
               $values = maybe_serialize($value);
            }
        }
        return $values;
    }
    
    public function pg_update_user_lat_long_using_address_using_rm($value,$user_id)
    {
        if (class_exists('Profilegrid_Geolocation_Public') ) {
            $addresss = implode(' ', $value);
            $address = urlencode($addresss);
         $pg_publicgeo = new Profilegrid_Geolocation_Public('','');
         $pg_publicgeo->pg_update_user_lat_long_using_address($address,$user_id);
        }
    }
    
    public function pg_get_shortcode_page_id($shortcode)
    {
        $string = '['.$shortcode;
        $page_id = 0;
        $my_query = new WP_Query( array('post_type' => 'any','s' => $string,'fields'=>'ids'));
        if(!empty($my_query->posts))
        {
            $page_id = $my_query->posts[0]; 
        }
        return $page_id;
    }
    
    public function pg_get_group_card_icon_link($gid)
    {
        $shortcode = 'PM_Group ID="'.$gid.'"';
        $page_id = $this->pg_get_shortcode_page_id($shortcode);
        if($page_id==0)
        {
            $html = '<a onclick="pg_create_group_page('.$gid.')" title="'.__('Click to create page for this group','profile-magic').'"><i class="fa fa-plus"></i></a>';
        }
        else
        {
            $link = get_edit_post_link($page_id);
            $html = '<a href="'.$link.'" target="_blank" title="'.__('Click to edit the page for this group','profile-magic').'"><i class="fa fa-file"></i></a>';
        }
        return $html;
    }
    
// class end
}
