<?php 

 if( ! defined('ABSPATH' ) ){
	exit;
}

function wpfm_shortcode_render($atts= null) {

	$shortcode_params = $atts;

	
	$group_id = wpfm_extrac_group_from_shortcode($atts);
	set_query_var('group_id', $group_id);
	
	$allow_public = wpfm_is_guest_upload_allow( $shortcode_params );
	
	if ( !is_user_logged_in() && !$allow_public) {

		$public_message = wpfm_get_option('_public_message');
		if($public_message != ''){
			ob_start ();
		
			printf(__('%s', 'wpfm'), $public_message);
			$output_string = ob_get_contents ();
			ob_end_clean ();
			return $output_string;
		}else{
			echo '<script type="text/javascript">
			window.location = "'.wp_login_url( get_permalink() ).'"
			</script>';
			return ;
		}
	}

	// Loading all required scripts
	wpfm_load_scripts();
	
	$template_vars = array('group_ids' => $group_id);
	
	ob_start ();
	
		wpfm_load_templates('wpfm-main.php', $template_vars, false);
		$output_string = ob_get_contents ();
	ob_end_clean ();

	return $output_string;
}