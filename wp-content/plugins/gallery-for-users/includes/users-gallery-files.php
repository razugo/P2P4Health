<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wpug_users_gallery files class.
 */
class Wpug_users_gallery_files {

    function __construct() {
        //Load initial content
        $this->initiate_page();
    }

    public function initiate_page() {
        echo '<div id="user-gallery-2hats" class="user-gallery-2hats">';
        //Load menu
        global $g_global;
        $g_global->load_menu();

        if (!is_user_logged_in()) {
            //Show login if not logged in already
            $g_global->login_form();
        } else {
            //Delete item action
            if (isset($_POST['user_gallery_item_delete'])) {
                $this->delete_user_file();
            }
            //Load current usr files
            $this->show_user_files();
        }
        echo '</div>';
    }

    /*
     * Show all files of current logged in user 
     */

    public function show_user_files() {
        //gallery_item_video_url
        $user_id = get_current_user_id();

        $args = array(
            'author' => $user_id,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'post_type' => 'users_gallery',
            'posts_per_page' => -1,
        );

        $gallery_items = get_posts($args);
        echo '<div class="eq-row">';
        foreach ($gallery_items as $item) {
            $post_id = $item->ID;
            $post_title = $item->post_title;
            $post_thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'users-gallery-thumb-small')[0];
            $post_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full')[0];
            $post_type = get_the_terms($post_id, 'gallery_types')[0]->slug;
            $video_url = ($post_type == 'video') ? get_post_meta($post_id, 'gallery_item_video_url', true) : '';

            //Get youtube images for video if no other thumbnail exist
            if ($post_type == 'video' && $post_thumb == '') {
                global $g_global;
                //get video id
                $vid_id = $g_global->get_video_id($video_url);
                $post_thumb = 'http://img.youtube.com/vi/' . $vid_id . '/0.jpg';
            }
            ?>
            <div class="overview-blocks">
                <div class="overview-block user-files">
                    <img src='<?php echo esc_url($post_thumb); ?>' alt="<?php echo esc_attr($post_title); ?>">
                    <!--Delete item form-->
                    <form action="" name="delete_user_gallery_item" method="post" class="delete-file">
                        <?php wp_nonce_field('user_gallery_nonce', 'user_gallery_nonce'); ?>
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                        <input type="hidden" name="author_id" value="<?php echo esc_attr($user_id); ?>">
                        <input type="submit" class="users_gallery_button" name="user_gallery_item_delete" value="<?php _e('Delete', 'wp-users-gallery'); ?>">
                    </form>
                </div>

            </div>
            <?php
        }
        echo '</div>';
    }

    /**
     * Delete post 
     */
    public function delete_user_file() {
        //Return if nonce error
        if (!wp_verify_nonce(sanitize_text_field($_POST['user_gallery_nonce']), 'user_gallery_nonce'))
            return;

        //Check if current logged in user can delete post
        $post = get_post(sanitize_text_field($_POST['post_id']));
        if(empty($post))
            return;
        $author_id = $post->post_author;

        if (get_current_user_id() != $author_id || get_current_user_id() != sanitize_text_field($_POST['author_id']))
            return;

        //Delete post
        wp_delete_post(sanitize_text_field($_POST['post_id']));
        return;
    }

}

new Wpug_users_gallery_files();
