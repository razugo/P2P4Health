<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wpug_users_gallery search class.
 */
class Wpug_users_gallery_search {

    function __construct() {

        //Load initial content
        $this->initiate_page();
    }

    public function initiate_page() {

        echo '<div id="user-gallery-2hats" class="user-gallery-2hats">';
        //Load menu
        global $g_global;
        $g_global->load_menu();

        $search_string = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $author = isset($_GET['user']) ? sanitize_text_field($_GET['user']) : '';
        $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : '';
        $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : '';
        $category_name = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $type_name = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

        //load search form
        $this->search_form($search_string, $author, $from, $to, $category_name, $type_name);

        //Show result if search sring is not empty
        if ($search_string != '' || $author != '' || $from != '' || $to != '' || $category_name != '' || $type_name != '') {
            $this->search_result($search_string, $author, $from, $to, $category_name, $type_name);
        }
        echo '</div>';
    }

    /**
     * Seach form
     */
    public function search_form($search_string, $author, $from, $to, $category_name, $type_name) {
        wp_enqueue_script('chosen');
        wp_enqueue_script('pickmeup');

        $types = get_terms( 'gallery_types', 
                array(
                    'hide_empty' => false,
                )
            );
        $categories = get_terms( 'category',
                array(
                    'hide_empty' => false,
                )
            );

        global $wpdb;

        $qurey = "SELECT posts.post_author, user.display_name
        FROM ".$wpdb->prefix."posts posts, ".$wpdb->prefix."users user
        WHERE posts.post_author = user.ID AND posts.post_type='users_gallery' AND posts.post_status='publish'";

        $users = $wpdb->get_results($qurey);
        $displayed_users = array();

        ?>
        <form role="search" method="get" class="search-form" action="">
            <div class="row">
                <input type="hidden" name="g_page" value="search">            
                <fieldset class="select">
                    <label><?php _e('User', 'wp-users-gallery'); ?></label>
                    <select class="Users" name="user">
                        <option></option>
                        <?php
                        foreach ($users as $user) {
                            if(in_array($user->post_author, $displayed_users)){
                                continue;
                            }
                            
                            $displayed_users[] = $user->post_author;

                            $option = '<option value="' . $user->post_author . '"';
                            $option .= ($author == $user->post_author) ? ' selected ' : '';
                            $option .= '>' . $user->display_name . '</option>';
                            echo wp_check_invalid_utf8($option);
                        }
                        ?>
                    </select>

                </fieldset>
                <fieldset class="select">
                    <label><?php _e('Type', 'wp-users-gallery'); ?></label>
                    <select class="Category" name="type">
                        <option></option>
                        <?php
                        foreach ($types as $type) {
                            $option = '<option value="' . $type->slug . '"';
                            $option .= ($type_name == $type->slug) ? ' selected' : '';
                            $option .= '>' . $type->name . '</option>';
                            echo wp_check_invalid_utf8($option);
                        }
                        ?>
                    </select>
                </fieldset>
                <fieldset class="select">
                    <label><?php _e('Category', 'wp-users-gallery'); ?></label>
                    <select class="Category" name="category">
                        <option></option>
                        <?php
                        foreach ($categories as $category) {
                            //skip uncategorized
                            if($category->slug == 'uncategorized'){
                                continue;
                            }

                            $option = '<option value="' . $category->slug . '"';
                            $option .= ($category_name == $category->slug) ? ' selected' : '';
                            $option .= '>' . $category->name . '</option>';
                            echo wp_check_invalid_utf8($option);
                        }
                        ?>
                    </select>
                </fieldset>
                <div class="clear"></div>
                <fieldset>
                    <label><?php _e('Search', 'wp-users-gallery'); ?>
                        <input type="text" class="search-field" placeholder="<?php _e('Search', 'wp-users-gallery'); ?>..." value="<?php echo esc_attr($search_string); ?>" name="search">
                    </label>
                </fieldset>
                <fieldset>
                    <label><?php _e('From date', 'wp-users-gallery'); ?>
                        <input type="text" class="date" value="<?php echo esc_attr($from); ?>" name="from">
                    </label>
                    <label><?php _e('to', 'wp-users-gallery'); ?>
                        <input type="text" class="date" value="<?php echo esc_attr($to); ?>" name="to">
                    </label>
                </fieldset>

                <input type="submit" class="users_gallery_button submit" value="<?php _e('Search', 'wp-users-gallery'); ?>">
            </div>
        </form>
        <script type="text/javascript">
            jQuery(function ($) {
                $(".Category").chosen({allow_single_deselect: true, placeholder_text_single: '<?php _e('Select an option', 'wp-users-gallery'); ?>'});
                $(".Users").chosen({allow_single_deselect: true, placeholder_text_single: '<?php _e('Select an option', 'wp-users-gallery'); ?>'});
                $('.date').pickmeup({
                    hide_on_select: true
                });
            });
        </script>
        <!--Add variables to here to enable localization-->
        <script type="text/javascript">
            var pickmeUpDates = {
                            days        : [
                                            '<?php _e('Sunday', 'wp-users-gallery');?>', 
                                            '<?php _e('Monday', 'wp-users-gallery');?>', 
                                            '<?php _e('Tuesday', 'wp-users-gallery');?>', 
                                            '<?php _e('Wednesday', 'wp-users-gallery');?>', 
                                            '<?php _e('Thursday', 'wp-users-gallery');?>', 
                                            '<?php _e('Friday', 'wp-users-gallery');?>', 
                                            '<?php _e('Saturday', 'wp-users-gallery');?>', 
                                            '<?php _e('Sunday', 'wp-users-gallery');?>'
                                          ],
                            daysShort   : [
                                            '<?php _e('Sun', 'wp-users-gallery');?>',
                                            '<?php _e('Mon', 'wp-users-gallery');?>',
                                            '<?php _e('Tue', 'wp-users-gallery');?>',
                                            '<?php _e('Wed', 'wp-users-gallery');?>',
                                            '<?php _e('Thu', 'wp-users-gallery');?>',
                                            '<?php _e('Fri', 'wp-users-gallery');?>',
                                            '<?php _e('Sat', 'wp-users-gallery');?>',
                                            '<?php _e('Sun', 'wp-users-gallery');?>'
                                          ],
                            daysMin     : [
                                            '<?php _e('Su', 'wp-users-gallery');?>',
                                            '<?php _e('Mo', 'wp-users-gallery');?>',
                                            '<?php _e('Tu', 'wp-users-gallery');?>',
                                            '<?php _e('We', 'wp-users-gallery');?>',
                                            '<?php _e('Th', 'wp-users-gallery');?>',
                                            '<?php _e('Fr', 'wp-users-gallery');?>',
                                            '<?php _e('Sa', 'wp-users-gallery');?>',
                                            '<?php _e('Su', 'wp-users-gallery');?>'
                                          ],
                            months      : [
                                            '<?php _e('January', 'wp-users-gallery');?>',
                                            '<?php _e('February', 'wp-users-gallery');?>',
                                            '<?php _e('March', 'wp-users-gallery');?>',
                                            '<?php _e('April', 'wp-users-gallery');?>',
                                            '<?php _e('May', 'wp-users-gallery');?>',
                                            '<?php _e('June', 'wp-users-gallery');?>',
                                            '<?php _e('July', 'wp-users-gallery');?>',
                                            '<?php _e('August', 'wp-users-gallery');?>',
                                            '<?php _e('September', 'wp-users-gallery');?>',
                                            '<?php _e('October', 'wp-users-gallery');?>',
                                            '<?php _e('November', 'wp-users-gallery');?>',
                                            '<?php _e('December', 'wp-users-gallery');?>'
                                          ],
                            monthsShort : [
                                            '<?php _e('Jan', 'wp-users-gallery');?>',
                                            '<?php _e('Feb', 'wp-users-gallery');?>',
                                            '<?php _e('Mar', 'wp-users-gallery');?>',
                                            '<?php _e('Apr', 'wp-users-gallery');?>',
                                            '<?php _e('May', 'wp-users-gallery');?>',
                                            '<?php _e('Jun', 'wp-users-gallery');?>',
                                            '<?php _e('Jul', 'wp-users-gallery');?>',
                                            '<?php _e('Aug', 'wp-users-gallery');?>',
                                            '<?php _e('Sep', 'wp-users-gallery');?>',
                                            '<?php _e('Oct', 'wp-users-gallery');?>',
                                            '<?php _e('Nov', 'wp-users-gallery');?>',
                                            '<?php _e('Dec', 'wp-users-gallery');?>'
                                          ]
                        }
        </script>
    <?php
    }

    /**
     * Search query 
     */
    public function search_result($search_string, $author, $from, $to, $category_name, $type_name) {

        $args = array(
            's' => $search_string,
            'post_type' => 'users_gallery',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        //Search by author
        if ($author != '') {
            $args['author'] = $author;
        }
        // search by category name & type
        if($type_name != '' || $category_name != ''){
            $args['tax_query'] = array(
                    'relation' => 'AND'
                );
        }
        if ($type_name != '') {
            $args['tax_query'][] = array(
                    'taxonomy' => 'gallery_types',
                    'field' => 'slug',
                    'terms' => $type_name,
                    'include_children' => true
                );
        }
        if ($category_name != '') {
            $args['tax_query'][] = array(
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $category_name,
                    'include_children' => true
                );
        }
        //Search by date
        if ($from != '' || $to != '') {
            $args['date_query'] = array();
            $args['date_query']['inclusive'] = true;

            if ($from != '') {
                $date = explode('-', $from);
                $args['date_query']['after'] = array(
                    'year' => $date[2],
                    'month' => $date[1],
                    'day' => $date[0],
                );
            }

            if ($to != '') {
                $date = explode('-', $to);
                $args['date_query']['before'] = array(
                    'year' => $date[2],
                    'month' => $date[1],
                    'day' => $date[0],
                );
            }
        }

        $query = new WP_Query($args);

        $posts = $query->posts;
        echo '<div class="eq-row">';
        //Get results
        if (count($posts) > 0) {
            foreach ($posts as $item) {
                $post_id     = $item->ID;
                $post_title = $item->post_title;
                //Title character limit for safari
                $user_agent = $_SERVER['HTTP_USER_AGENT']; 
                if (stripos( $user_agent, 'Chrome') == false && stripos( $user_agent, 'Safari') !== false){
                   $post_title  = (strlen($post_title) > 20)? substr($post_title, 0, 20).' ..' : $post_title;
                }
                $post_author = $item->post_author;
                $author_info = get_userdata($post_author);
                $author_name = $author_info->display_name;
                $post_thumb  = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'users-gallery-thumb-small')[0];
                $post_image  = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full')[0];
                $post_type   = get_the_terms($post_id, 'gallery_types')[0]->slug;
                $video_url   = ($post_type == 'video') ? get_post_meta($post_id, 'gallery_item_video_url', true) : '';

                //Get youtube images for video if no other thumbnail exist
                if($post_type == 'video' && $post_thumb == ''){
                    global $g_global;
                    //get video id
                    $vid_id = $g_global->get_video_id($video_url);
                    
                    $post_thumb = 'http://img.youtube.com/vi/'.$vid_id.'/0.jpg';
                }
                ?>
                <div class="overview-blocks">
                    <div class="overview-block">
                        <a href="?g_page=overview&open=<?php echo esc_html($post_author); ?>&view=<?php echo esc_html($post_id); ?>">
                            <img src='<?php echo esc_url($post_thumb); ?>' alt="<?php echo esc_html($post_title); ?>">
                            <div class="name-block"><?php echo esc_html($post_title); ?></div>
                        </a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p>'.__('No matching posts found ', 'wp-users-gallery').'!</p>';
        }
        echo '</div>';
    }

}

new Wpug_users_gallery_search();