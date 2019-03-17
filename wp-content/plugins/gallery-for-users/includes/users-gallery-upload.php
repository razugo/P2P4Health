<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpug_users_gallery upload class.
 */
class Wpug_users_gallery_upload
{
	function __construct(){
		//Load initial content
		$this->initiate_page();
	}

	public function initiate_page(){
		echo '<div id="user-gallery-2hats" class="user-gallery-2hats">';
		//Load menu
		global $g_global;
		$g_global->load_menu();

		if(!is_user_logged_in()){
			//Show login if not logged in already
			$g_global->login_form();
		}
		else{            
			$this->save_upload_form();
			$this->show_upload_form();
		}
		echo '</div>';
	}

	public function show_upload_form(){
		wp_enqueue_script('chosen');
		wp_enqueue_script('validate');
		wp_enqueue_script('additional-methods');

		$categories = 	get_terms( 'category',
						array(
				    		'hide_empty' => false,
				   		)
					);
		?>
		<form id="users_gallery_add_item" method="post" action="#" enctype="multipart/form-data" novalidate>
			<?php wp_nonce_field( 'users_gallery_nonce', 'users_gallery_nonce' ); ?>
			<fieldset>
				<label for="gallery_item_title"><?php _e('Title', 'wp-users-gallery'); ?>
				<input type="text" name="gallery_item_title" id="gallery_item_title" value="" placeholder="<?php _e('Title', 'wp-users-gallery'); ?>" required>
				</label>
			</fieldset>
			<fieldset>
				<label><?php _e('Category', 'wp-users-gallery'); ?></label>
				<select name="category" class="category" style="width:150px;">
					<option value=""></option>
					<?php
					foreach ($categories as $category) {
						if ($category->name != 'Uncategorized'){
							echo '<option value="'.esc_attr($category->term_id).'">'.esc_html($category->name).'</option>';
						}
					}
					?>
				</select>
			</fieldset>
            <fieldset class="type">
				<label><?php _e('Type', 'wp-users-gallery'); ?></label>
				<label for="image"><?php _e('Image', 'wp-users-gallery'); ?></label>
		  		<input type="radio" name="gallery_item_type" id="image" value="image" required>
		  		<label for="video"><?php _e('Video', 'wp-users-gallery'); ?></label>
		  		<input type="radio" name="gallery_item_type" id="video" value="video" required>
		  	</fieldset>

		  	<!--If image element-->
		  	<fieldset id="gallery_image" style="display:none;">
		  		<label for="gallery_item_image"><?php _e('Image', 'wp-users-gallery'); ?></label>
		  		<input type="file" name="gallery_item_image" id="gallery_item_image"  multiple="false" required />
		  	</fieldset>
		  	<!--If Video element-->
		  	<fieldset id="gallery_video" style="display:none;">
		  		<fieldset>
		  			<label for="gallery_item_video_url"><?php _e('Youtube video URL', 'wp-users-gallery'); ?></label>
					<input type="text" name="gallery_item_video_url" id="gallery_item_video_url" value="" placeholder="<?php _e('Video url', 'wp-users-gallery'); ?>" required>
				</fieldset>
				<fieldset>
		  			<label for="gallery_item_video_thumb"><?php _e('Thumbnail image (Optional)', 'wp-users-gallery'); ?></label>
		  			<input type="file" name="gallery_item_video_thumb" id="gallery_item_video_thumb"  multiple="false"/>
		  		</fieldset>
		  	</fieldset>

			<input id="submit" class="users_gallery_button submit" name="submit" type="submit" value="<?php _e('Submit', 'wp-users-gallery'); ?>" />
		</form>

		<script type="text/javascript">

			jQuery(document).ready(function($){
				//Enable chosen
				$(".category").chosen({placeholder_text_single: '<?php _e('Select an option', 'wp-users-gallery'); ?>'});

				//Show or hide input upload fields
				$('input[name="gallery_item_type"]').click(function() {
			    	if($(this).attr('id') == 'image') {
			    		$('#gallery_video').hide();
			            $('#gallery_image').show();
			    	}
			    	else {
			    		$('#gallery_image').hide();
			            $('#gallery_video').show();
			    	}
				});

				// Validate on form submission

				$('#users_gallery_add_item').validate({
				    rules: {
					    gallery_item_image: {
					    		required: true,
					    		extension:'jpe?g,png',
					    		uploadFile:true,
					    }
				    },
				   	messages: {
				   		gallery_item_image: '<?php _e('File must be JPG, GIF or PNG less than 5MB', 'wp-users-gallery'); ?>'
				   	}
				});

				//File validation methods
				$.validator.addMethod("uploadFile", function (val, element) {
											var size = element.files[0].size;
											if (size > 5242880){
												return false;
											} else {
												return true;
											}
										}, "<?php _e('File type error', 'wp-users-gallery'); ?>");
			});

		</script>

		<?php
	}


	/**
	 *save uploaded form
	 */
	public function save_upload_form(){
		if(empty($_POST))
			return;

		if ( isset( $_POST['users_gallery_nonce'], $_POST['gallery_item_title'] ) && wp_verify_nonce( $_POST['users_gallery_nonce'], 'users_gallery_nonce' ) ){

			//Form field data
			$user_id 				 = get_current_user_id();
			$gallery_item_title 	 = sanitize_text_field($_POST['gallery_item_title']);
			$gallery_item_type		 = sanitize_text_field($_POST['gallery_item_type']);
			$gallery_item_video_url  = sanitize_text_field($_POST['gallery_item_video_url']);
			$post_category 			 = array($_POST['category']);

			//Validate fields
			if(empty($post_category) || $gallery_item_title == '' || $gallery_item_type == '')
				return;

			//Insert post
			$new_post = array(
                'post_title'	=> $gallery_item_title,
                'post_type'		=> 'users_gallery',
                'post_content'	=> '',
                'post_status' 	=> 'publish',
                'post_author' 	=> $user_id,
                'post_category' => $post_category,
            );
            $post_id = wp_insert_post($new_post);
            //Assign type
			wp_set_object_terms( $post_id, array( $gallery_item_type ), 'gallery_types' );

			// These files need to be included as dependencies when on the front end file upload.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			
			//Add media
			if($gallery_item_type == 'image'){
				$attachment_id = media_handle_upload( 'gallery_item_image', $post_id );
				if( !is_wp_error( $attachment_id )) {
					//If uploaded successfully
					update_post_meta($post_id, '_thumbnail_id', $attachment_id);
				}
			}
			elseif($gallery_item_type == 'video'){
				update_post_meta($post_id, 'gallery_item_video_url', $gallery_item_video_url);

				$attachment_id = media_handle_upload( 'gallery_item_video_thumb', $post_id );
				if( !is_wp_error( $attachment_id )) {
					//If uploaded successfully
					update_post_meta($post_id, '_thumbnail_id', $attachment_id);
				}
			}
		} else {
			echo __('File type error', 'wp-users_gallery');
		}
		return true;
	}
}
new Wpug_users_gallery_upload();