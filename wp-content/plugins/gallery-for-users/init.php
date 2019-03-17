<?php
/*
Plugin Name: Gallery for Users
Plugin URI: https://awesometogi.com/gallery-for-users-plugin-for-wordpress/
Description: Allows the users of the site to upload and display their own images and link to their Youtube videos in a gallery
Version: 2.0
Author: AWESOME TOGI
Author URI: https://awesometogi.com
Requires at least: 4.0
Tested up to: 4.9
Text Domain: wp-users-gallery

Copyright: 2016
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
/**
 * Check for premium version installation
 */
add_action('plugins_loaded', 'wpug_plugin_free_install', 11);
function wpug_plugin_free_install() {
	if (!function_exists('is_plugin_active')) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    if (defined('WPUG_PLUGIN_PREMIUM')) {
        add_action('admin_notices', 'wpugf_plugin_install_free_admin_notice');
        deactivate_plugins('users-gallery/init.php');
    }
}

function wpugf_plugin_install_free_admin_notice(){
  ?>
  <div class="error">
    <p><?php _e('You can\'t use the free version of Users gallery while you are using the premium one.', 'wp-users-gallery'); ?></p>
</div>
<?php
}

//Initiate function
if (!class_exists('Wpug_users_gallery')) {
    include 'users-gallery.php';
}


add_action( 'wp_ajax_nopriv_delete_user_media_file', 'delete_user_media_file' );
add_action( 'wp_ajax_delete_user_media_file', 'delete_user_media_file'  ); 

function delete_user_media_file() {
    
    $res = delete_user_gus_file();
    echo $res;
    die();
}

    /**
     * Delete post 
     */
    function delete_user_gus_file() {
        //Return if nonce error
        // if (!wp_verify_nonce(sanitize_text_field($_POST['user_gallery_nonce']), 'user_gallery_nonce'))
        //     return;

        //Check if current logged in user can delete post
        $post = get_post(sanitize_text_field($_POST['post_id']));
        if(empty($post))
            return 0;
        $author_id = $post->post_author;

        if (get_current_user_id() != $author_id || get_current_user_id() != sanitize_text_field($_POST['author_id']))
            return 0;

        //Delete post
        wp_delete_post(sanitize_text_field($_POST['post_id']));
        return 1;
    }
    function sample_admin_notice__success() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( '<b>Gallery for Users Pro</b> available now. <a href="https://awesometogi.com/gallery-for-users-plugin-for-wordpress/" target="_blank">Update to Pro</a>', 'sample-text-domain' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'sample_admin_notice__success' );
