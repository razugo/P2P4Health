<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wpug_users_gallery overview class.
 */
class Wpug_users_gallery_overview {

    function __construct() {
        //Load initial content
        $this->initiate_page();
        
    }

    /**
     * initiate page content
     */
    public function initiate_page() {
        //Load menu
        echo '<div id="user-gallery-2hats" class="user-gallery-2hats">';
        global $g_global;
        $g_global->load_menu();
        if (isset($_GET['open']))
            $this->load_users_posts();
        else
            $this->load_content();
        echo '</div>';
    }

    /**
     * Load all data initially
     */
    public function load_content() {

        $this->load_filters();

        global $wpdb;
        $post_table = $wpdb->prefix . 'posts';
        /**
         * Get one latest post for each user
         */

        $args = array(
            'orderby' => 'post_date',
            'order' => 'DESC',
            'post_type' => 'users_gallery',
            'posts_per_page' => -1,
        );

        //Add taxonomy query for filter
        if (isset($_GET['filter']) && $_GET['filter'] != null && $_GET['filter'] != 'all') {
            $term = sanitize_text_field($_GET['filter']);
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'gallery_types',
                    'field' => 'slug',
                    'terms' => $term,
                    'include_children' => true
                )
            );
        }
        //Get posts
        $posts = get_posts($args);

        $selected_authors = array();

        if (count($posts) > 0) {
            foreach ($posts as $item) {

                $post_author = $item->post_author;

                if (in_array($post_author, $selected_authors)) {
                    continue;
                }
                $selected_authors[] = $post_author;

                $post_id = $item->ID;
                $post_title = $item->post_title;
                $author_info = get_userdata($post_author);
                $author_name = $author_info->display_name;
                $post_thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'users-gallery-thumb-small')[0];
                $post_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full')[0];
                $post_type = get_the_terms($post_id, 'gallery_types')[0]->slug;
                $video_url = ($post_type == 'video') ? get_post_meta($post_id, 'gallery_item_video_url', true) : '';

                //Get youtube images for video if no other thumbnail exist
                if ($post_type == 'video' && $post_thumb == '') {
                    global $g_global;
                    //get video id
                    $video_id = $g_global->get_video_id($video_url);

                    $post_thumb = 'http://img.youtube.com/vi/' . $video_id . '/0.jpg';
                }

                //Create next link with all filters
                $post_link = '?g_page=overview&open='.$post_author.'&view='.$post_id;

                if (isset($_GET['filter']) && $_GET['filter'] != '') {
                    $post_link .= '&filter=' . sanitize_text_field($_GET['filter']);
                }
                ?>
                <div class="overview-blocks">
                    <div class="overview-block">
                        <a href="<?php echo esc_url($post_link); ?>">
                            <img src='<?php echo esc_url($post_thumb); ?>' alt="<?php echo esc_attr($post_title); ?>">
                            <div class="name-block"><?php echo esc_html($author_name); ?></div>
                        </a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo __('Nothing found', 'wp-users-gallery');
        }
    }

    /**
     * Load single uses post
     */
    public function load_users_posts() {
        wp_enqueue_script('amazingSlider');
        wp_enqueue_script('sliderEngine');

        $itemLink = get_permalink().'?g_page=overview&open='.sanitize_text_field($_GET['open']).'&view=';
        ?>
        <script type="text/javascript">
            var jsFolder = "<?php echo WPUG_USER_GALLERY_PLUGIN_URL ?>/assets/images/";
            var userGallerySelectedSlide = 0;
        </script>
        <?php
        $this->load_filters();

        $user_id = sanitize_text_field($_GET['open']);
        //Show user contents
        $author = get_userdata($user_id);

        $args = array(
            'author' => $user_id,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'post_type' => 'users_gallery',
            'posts_per_page' => -1,
        );

        //Add taxonomy query for filter
        if (isset($_GET['filter']) && $_GET['filter'] != null && $_GET['filter'] != 'all') {
            $term = sanitize_text_field($_GET['filter']);
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'gallery_types',
                    'field' => 'slug',
                    'terms' => $term,
                    'include_children' => true
                )
            );
        }

        $gallery_items = get_posts($args);
        echo '<h3 class="titile">'.__('Recent posts by', 'wp-users-gallery').' '.ucfirst($author->display_name).'</h3>';
        echo '<div class="eq-row"><div id="amazingslider-12" style="display:block;position:relative;margin:0px auto 0px;">';
        if (count($gallery_items) > 0) {

            echo '<ul class="amazingslider-thumbnails" style="display:none;">';
            foreach ($gallery_items as $key => $item) {
                $post_id = $item->ID;
                $post_title = $item->post_title;
                $post_thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'users-gallery-thumb-small')[0];
                $post_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full')[0];
                $post_type = get_the_terms($post_id, 'gallery_types')[0]->slug;
                $video_url = ($post_type == 'video') ? get_post_meta($post_id, 'gallery_item_video_url', true) : '';

                //Set selected image
                if(isset($_GET['view']) && $_GET['view'] != '' && $_GET['view'] == $post_id){
                ?>
                    <script type="text/javascript">
                        userGallerySelectedSlide = <?php echo $key; ?>;
                    </script>
                <?php
                }


                if ($post_type == 'video') {
                    global $g_global;
                    //get video id
                    $vid_id = $g_global->get_video_id($video_url);

                    $video_url = 'https://www.youtube.com/embed/' . $vid_id . '?autoplay=1';
                    if ($post_thumb == '') {
                        $post_thumb = 'http://img.youtube.com/vi/' . $vid_id . '/0.jpg';
                    }
                }

                echo '<li><img src="' . esc_url($post_thumb) . '" /></li>';
            }
            echo '</ul>';

            //Show data ul
            echo '<ul class="amazingslider-slides" style="display:none;">';
            foreach ($gallery_items as $item) {
                $post_id = $item->ID;
                $post_title = $item->post_title;
                $post_thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'users-gallery-thumb-small')[0];
                $post_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full')[0];
                $post_type = get_the_terms($post_id, 'gallery_types')[0]->slug;
                $video_url = ($post_type == 'video') ? get_post_meta($post_id, 'gallery_item_video_url', true) : '';

                if ($post_type == 'video') {
                    global $g_global;
                    //get video id
                    $vid_id = $g_global->get_video_id($video_url);

                    $video_url = 'https://www.youtube.com/embed/' . $vid_id . '?autoplay=1';
                    if ($post_thumb == '') {
                        $post_thumb = 'http://img.youtube.com/vi/' . $vid_id . '/0.jpg';
                    }
                    $post_image = $post_thumb;
                }
                ?>
                <li data-usr-date="<?php echo get_the_date('j. F Y', $post_id); ?>" data-usr-url="<?php echo esc_url($itemLink.$post_id); ?>">
                    <img src="<?php echo esc_url($post_image); ?>" />
                    <?php
                    if ($post_type == 'video') {
                        echo '<video preload="none" src="' . esc_url($video_url) . '" ></video>';
                    }
                    ?>
                </li>
                <?php
            }
            echo '</ul>';
        } else {
            echo __('No posts', 'wp-users-gallery');
        }
        echo '</div></div>';

        //load sharing buttons
        global $g_global;
        $g_global->load_sharing();
        ?>
        <div class="meta-data">
            <h4 class="titile">
                <?php _e('Uploaded by', 'wp-users-gallery') ?> : <?php echo ucfirst($author->display_name); ?>
            </h4>
            <h4 class="titile">
                <?php _e('Upload date', 'wp-users-gallery') ?> : <span id="upload_date"><?php echo get_the_date('j. F Y', @$gallery_items[0]->ID); ?></span>
            </h4>
        </div>
        <?php
    }

    /**
     * Filters ul
     */
    public function load_filters() {
        $params = $_GET;
        $current_filter = (isset($params['filter']) && $params['filter'] != "") ? sanitize_text_field($params['filter']) : "";
        unset($params['filter']);
        $url = get_permalink();
        if (!empty($params)) {
            $url .= '?';
            foreach ($params as $key => $value) {
                $url .= $key . '=' . $value . '&';
            }
        }
        //Add get value to url if not exist
        else {
            $url .= '?g_page=overview&';
        }

        //Get filters
        $filters = get_terms('gallery_types', 
                array(
                    'hide_empty' => false,
                )
        );

        ?>
        <div class="sort-box">
            <div class="custom-select">
                <select onchange='this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);'>                    
                    <option value="<?php echo esc_html($url); ?>filter=all" <?php echo ($current_filter == '' || $current_filter == 'all') ? esc_attr('selected') : ''; ?>><?php _e('All', 'wp-users-gallery'); ?></option>
                    <?php
                    foreach ($filters as $filter) {
                        ?>
                        <option value="<?php echo esc_html($url); ?>filter=<?php echo esc_html($filter->slug); ?>" <?php echo $current_filter == $filter->slug ? esc_attr('selected') : ''; ?>><?php echo esc_html($filter->name); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }

}

new Wpug_users_gallery_overview();
