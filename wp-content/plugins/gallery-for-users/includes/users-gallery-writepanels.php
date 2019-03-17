<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpug_users_gallery writepanel class.
 */
class Wpug_users_gallery_writepanels
{
	function __construct(){
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	/**
	 * add_meta_boxes function.
	 */
	public function add_meta_boxes() {
		add_meta_box( 'users_gallery_video_url_metabox',__('Video url', 'wp-users-gallery'),array( $this, 'users_gallery_video_url_metabox' ),'users_gallery', 'normal', 'high');
	}

	/*
	 * Show data in metabox
	 */
	public function users_gallery_video_url_metabox(){
		global $post_id;
		$video_url = get_post_meta($post_id, 'gallery_item_video_url', true);
		?>
		<fieldset>
			<label for="gallery_item_video_url"><?php _e('Video url', 'wp-users-gallery'); ?></label>
			<input type="text" name="gallery_item_video_url" id="gallery_item_video_url" value="<?php echo esc_html($video_url); ?>">
		</fieldset>
		<?php
	}
}
new Wpug_users_gallery_writepanels();