<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wpug_users_gallery post tyles class.
 */
class Wpug_users_gallery_post_types {

    function __construct() {
        //Register post types
        add_action('init', array($this, 'register_post_types'), 0);
        add_action('after_switch_theme', array($this, 'register_post_types'), 0);

        //Save terms only once
        add_action('init', array($this, 'create_terms'), 0);

        //Set thumb sizes
        add_action('after_setup_theme', array($this, 'add_custom_sizes'));

        //Add scripts
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));

        //Add admin scripts
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        //Change upload size
        add_filter('wp_max_upload_size', 'increase_upload_size');

        //Add custom styles in footer
        add_action('wp_enqueue_scripts', array($this, 'add_custom_styles'));
    }

    /**
     * Change upload size
     */
    public function increase_upload_size($bytes) {
        return 5242880; // 5 megabytes
    }

    /**
     * Set up menu
     */
    public function load_menu() {
        //get current page name
        global $post;

        $pattern = get_shortcode_regex();
        preg_match('/' . $pattern . '/s', $post->post_content, $matches);
        if (is_array($matches) && $matches[2] == 'users_gallery') {
            $shortcode = $matches[0];
        }

        //Get page attr from shortcode
        $shortcode = shortcode_parse_atts($shortcode);
        $attr_page = '';
        if (is_array($shortcode) && array_key_exists('1', $shortcode)) {
            $attr_page = explode('=', $shortcode[1]);
            $attr_page = preg_replace("/[^a-zA-Z]+/", "", $attr_page[1]);
        }
        //All available pages
        $available_pages = array('overview', 'search', 'upload', 'files');

        $current_page = (in_array($attr_page, $available_pages)) ? $attr_page : '';
        $current_page = (isset($_GET['g_page'])) ? sanitize_text_field($_GET['g_page']) : $current_page;
        $current_page = ($current_page == '') ? 'overview' : $current_page;
        ?>
        <ul class="menu">
            <li>
                <a href="?g_page=overview" class="users_gallery_button <?php echo $current_page == 'overview' ? esc_attr('active') : ''; ?>"><?php _e('Overview', 'wp-users-gallery'); ?></a>
            </li>
            <li>
                <a href="?g_page=search" class="users_gallery_button <?php echo $current_page == 'search' ? esc_attr('active') : ''; ?>"><?php _e('Search', 'wp-users-gallery'); ?></a>
            </li>
            <li>
                <a href="?g_page=upload" class="users_gallery_button <?php echo $current_page == 'upload' ? esc_attr('active') : ''; ?>"><?php _e('Upload', 'wp-users-gallery'); ?></a>
            </li>
            <li>
                <a href="?g_page=files" class="users_gallery_button <?php echo $current_page == 'files' ? esc_attr('active') : ''; ?>""><?php _e('My files', 'wp-users-gallery'); ?></a>
            </li>
        </ul>
        <?php
    }

    /**
     * Log in form for not logged in users
     */
    public function login_form() {
        echo '<a style="text-align:center;display: block;" href="' . esc_url(wp_login_url(home_url(add_query_arg(array())))) . '" title="Login">' . __('Login to view this', 'wp-users-gallery') . '</a>';
    }

    /**
     * Register post types function
     * Register taxonomies
     */
    public function register_post_types() {
        /*
         * Register Users Gallery Post type
         */
        $singular = __('Gallery item', 'wp-users-gallery');
        $plural = __('Gallery items', 'wp-users-gallery');
        $has_archive = false;

        $rewrite = array(
            'slug' => _x('users_gallery', 'Permalink - resave permalinks after changing this', 'wp-users-gallery'),
            'with_front' => false,
            'feeds' => true,
            'pages' => false
        );

        register_post_type("users_gallery", apply_filters("register_post_type_users_gallery", array(
            'labels' => array(
                'name' => $plural,
                'singular_name' => $singular,
                'menu_name' => __('Users Gallery', 'wp-users-gallery'),
                'all_items' => sprintf(__('All %s', 'wp-users-gallery'), $plural),
                'add_new' => __('Add New', 'wp-users-gallery'),
                'add_new_item' => sprintf(__('Add %s', 'wp-users-gallery'), $singular),
                'edit' => __('Edit', 'wp-users-gallery'),
                'edit_item' => sprintf(__('Edit %s', 'wp-users-gallery'), $singular),
                'new_item' => sprintf(__('New %s', 'wp-users-gallery'), $singular),
                'view' => sprintf(__('View %s', 'wp-users-gallery'), $singular),
                'view_item' => sprintf(__('View %s', 'wp-users-gallery'), $singular),
                'search_items' => sprintf(__('Search %s', 'wp-users-gallery'), $plural),
                'not_found' => sprintf(__('No %s found', 'wp-users-gallery'), $plural),
                'not_found_in_trash' => sprintf(__('No %s found in trash', 'wp-users-gallery'), $plural),
                'parent' => sprintf(__('Parent %s', 'wp-users-gallery'), $singular)
            ),
            'description' => sprintf(__('This is where you can create and manage %s.', 'wp-users-gallery'), $plural),
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'hierarchical' => false,
            'rewrite' => $rewrite,
            'taxonomies' => array('category'),
            'query_var' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'publicize'),
            'has_archive' => $has_archive,
            'menu_icon' => 'dashicons-images-alt2',
            'show_in_nav_menus' => true,
                ))
        );


        /**
         * Register taxonomies
         */
        $singular = __('Gallery item type', 'wp-users-gallery');
        $plural = __('Gallery item types', 'wp-users-gallery');

        $rewrite = false;
        $public = false;

        register_taxonomy('gallery_types', apply_filters('register_taxonomy_gallery_types_object_type', array('users_gallery')), apply_filters('register_taxonomy_gallery_types_args', array(
            'hierarchical' => true,
            'label' => $plural,
            'labels' => array(
                'name' => $plural,
                'singular_name' => $singular,
                'menu_name' => ucwords($plural),
                'search_items' => sprintf(__('Search %s', 'wp-users-gallery'), $plural),
                'all_items' => sprintf(__('All %s', 'wp-users-gallery'), $plural),
                'parent_item' => sprintf(__('Parent %s', 'wp-users-gallery'), $singular),
                'parent_item_colon' => sprintf(__('Parent %s:', 'wp-users-gallery'), $singular),
                'edit_item' => sprintf(__('Edit %s', 'wp-users-gallery'), $singular),
                'update_item' => sprintf(__('Update %s', 'wp-users-gallery'), $singular),
                'add_new_item' => sprintf(__('Add New %s', 'wp-users-gallery'), $singular),
                'new_item_name' => sprintf(__('New %s Name', 'wp-users-gallery'), $singular)
            ),
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'public' => $public,
            'rewrite' => $rewrite,
                ))
        );
    }

    /**
     * Register type terms
     */
    public function create_terms() {
        if (get_option('users_gallery_terms_added') == 1)
            return;

        $image_term = term_exists('Image', 'gallery_types');
        if (!$image_term) {
            wp_insert_term(
                    'Image', 'gallery_types', array(
                'description' => __('Users gallery image posts', 'wp-users-gallery'),
                'slug' => 'image',
                'parent' => ''
                    )
            );
        }

        $video_term = term_exists('Video', 'gallery_types');
        if (!$video_term) {
            wp_insert_term(
                    'Video', 'gallery_types', array(
                'description' => __('Users gallery video posts', 'wp-users-gallery'),
                'slug' => 'video',
                'parent' => ''
                    )
            );
        }

        update_option('users_gallery_terms_added', 1);
    }

    /**
     * Add custom thumb sizes 
     */
    function add_custom_sizes() {
        add_image_size('users-gallery-thumb-small', 200, 130, true, 'users_gallery');
    }

    /**
     * Admin styles and scripts
     */
    public function admin_scripts() {
        //Chosen plugin
        wp_register_script('chosen', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.js', array('jquery'), '1.1.0', true);
        wp_enqueue_style('chosen', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/css/chosen.css');

        wp_enqueue_script('wp-color-picker');
        //Custom admin scripts
        wp_register_script('custom', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/admin/custom.js', array('jquery'), '1.1.0', false);
        wp_enqueue_script('custom');
        //Ads styles
        wp_enqueue_style('ads', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/css/ads.css');
    }

    /**
     * Add script files
     */
    public function frontend_scripts() {
        //Gallery slider plugin
        wp_register_script('amazingSlider', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/amazingSlider.js', array('jquery'), '1.1.0', true)
        ;
        wp_register_script('sliderEngine', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/sliderEngine.js', array('jquery'), '1.1.0', true);

        wp_enqueue_style('user_gallery_style', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/css/style.css');

        //Chosen plugin
        wp_register_script('chosen', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.js', array('jquery'), '1.1.0', true);
        wp_enqueue_style('chosen', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/css/chosen.css');

        //Date picker
        wp_register_script('pickmeup', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/jquery.pickmeup.js', array('jquery'), '1.1.0', true);
        wp_enqueue_style('pickmeup', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/css/pickmeup.css');

        //Validation script
        wp_register_script('validate', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/jquery.validate.js', array('jquery'), '1.1.0', true);
        wp_register_script('additional-methods', WPUG_USER_GALLERY_PLUGIN_URL . '/assets/js/additional-methods.js', array('jquery'), '1.1.0', true);
    }

    //Public function 
    public function load_sharing() {
        ?>
        <div class="share-links">
            <p><?php _e('Share on social media', 'wp-users-gallery'); ?></p>
            <!--Social share buttons-->
            <a class="fb-share" onclick="fbshareCurrentPage()" data-href="<?php echo esc_url(home_url(add_query_arg(array()))) ?>" target="_blank" alt="Share on Facebook" >Facebook</a>
            <a class="tweet_share"  onclick="tweetCurrentPage()" data-href="<?php echo esc_url(home_url(add_query_arg(array()))) ?>" target="_blank" alt="Tweet this page">Twitter</a>
            <a class="gplus_share" onclick="share_googlePLus()" data-href="<?php echo esc_url(home_url(add_query_arg(array()))) ?>" target="_blank" alt="Share on google plus">Googleplus</a>
            <a class="linkedin_share"  onclick="share_linkedin()" data-href="<?php echo esc_url(home_url(add_query_arg(array()))) ?>" target="_blank" alt="Share on linkedin">linkedin</a>
            <script language="javascript">
                function fbshareCurrentPage() {
                    var href = jQuery('.fb-share').attr('data-href');
                    window.open("https://www.facebook.com/sharer/sharer.php?u=" + escape(href) + "&t=" + document.title, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');
                    return false;
                }

                function tweetCurrentPage() {
                    var twt_href = jQuery('.tweet_share').attr('data-href');
                    window.open("https://twitter.com/share?url=" + escape(twt_href) + "&text=" + document.title, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');
                    return false;
                }

                function share_googlePLus() {
                    var googlePlusLink = jQuery('.gplus_share').attr('data-href');
                    javascript:window.open("https://plus.google.com/share?url=" + escape(googlePlusLink), '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
                    return false;
                }

                function share_linkedin() {
                    var linkedinLink = jQuery('.linkedin_share').attr('data-href');
                    javascript:window.open("https://www.linkedin.com/cws/share?url=" + escape(linkedinLink), '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
                    return false;
                }
            </script>
        </div>
        <?php
    }

    /**
     * Extract video id from url
     */
    public function get_video_id($url = null) {
        if ($url == null)
            return;

        //Extract id form url
        parse_str(parse_url($url, PHP_URL_QUERY), $vars);
        $id = $vars['v'];

        if ($id == '') {
            $id = end(explode('/', $url));
        }

        return $id;
    }

    /**
     * Add custom styles to footer
     */
    public function add_custom_styles() {
        $font = get_option('users_gallery_text_font');
        $colors = get_option('users_gallery_button_colours');
        if (empty($colors)) {
            $colors = array();
        }
        if (!empty($colors) || $font != ''):
            ?>
            <style type="text/css">
            <?php if ($colors['button_colour'] != '' || $colors['button_text_colour'] != ''): ?>
                    .users_gallery_button{
                <?php
                if ($colors['button_text_colour'] != '') {
                    echo 'color:' . esc_html($colors['button_text_colour']) . ' !important;';
                }
                if ($colors['button_colour'] != '') {
                    echo 'background-color: ' . esc_html($colors['button_colour']) . ' !important;';
                }
                ?>
                    }
            <?php endif; ?>
            <?php if ($colors['button_colour_on_hover'] != '' || $colors['button_text_colour_on_hover'] != ''): ?>
                    #user-gallery-2hats.user-gallery-2hats ul.menu li a.active, .users_gallery_button:hover{
                <?php
                if ($colors['button_text_colour_on_hover'] != '') {
                    echo 'color:' . esc_html($colors['button_text_colour_on_hover']) . ' !important;';
                }
                if ($colors['button_colour_on_hover'] != '') {
                    echo 'background-color: ' . esc_html($colors['button_colour_on_hover']) . ' !important;';
                }
                ?>
                    }
            <?php endif; ?>
            <?php if ($colors['widget_background_colour'] != ''): ?>
                    .user-gallery-widget{
                        background-color: <?php echo esc_html($colors['widget_background_colour']); ?> !important;';
                    }
            <?php endif; ?>
            <?php if ($font != ''): ?>
                    #user-gallery-2hats.user-gallery-2hats {
                        font-family: <?= esc_html($font); ?> !important;
                    }
            <?php endif; ?> 
            </style>
            <?php
        endif;
    }

}

new Wpug_users_gallery_post_types();

//Set class as global
$g_global = new Wpug_users_gallery_post_types();
