<?php

$profile_id = '';

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wpug_users_gallery class.
 */
class Wpug_users_gallery {

    function __construct() {
        // Define constants
        define('WPUG_USER_GALLERY_VERSION', '1.0');
        define('WPUG_USER_GALLERY_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
        define('WPUG_USER_GALLERY_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));

        //Global settings file
        global $g_global;

        //Register post types
        include( 'includes/users-gallery-global.php' );
        //load metaboxes in admin area
        if (is_admin()) {
            include( 'includes/users-gallery-writepanels.php' );
            include( 'includes/users-gallery-admin.php' );
            include( 'includes/users-gallery-ads.php' );
        }

        //Initiate shortcode
        add_shortcode('users_gallery', array($this, 'load_users_gallery'));
        add_action('after_setup_theme', array($this, 'load_plugin_textdomain'));
        add_action('wp_print_scripts', array($this, 'wpcustom_inspect_scripts_and_styles'));

        //Add facebook data
        add_action('wp_head', array($this, 'facebook_data'), 0);
        add_action('plugin_row_meta', array($this, 'add_plugin_meta'), 10, 2);
    }

    /**
     * Get shortcode and load page
     */
    public function load_users_gallery($atts = array()) {
        //Get page param
        $g_page = (isset($atts['g_page'])) ? $atts['g_page'] : '';

        //********** TEST CODE Capstone 2019 **********
        global $profile_id;
        $profile_id = (isset($atts['profile_id'])) ? $atts['profile_id'] : '';
        if (isset($_GET['profile_id']))
            $g_page = sanitize_text_field($_GET['profile_id']);
        
        //Get current page from url
        if (isset($_GET['g_page']))
            $g_page = sanitize_text_field($_GET['g_page']);

        //Load views for each page
        switch ($g_page) {
            case 'overview':
                include( 'includes/users-gallery-overview.php' );
                break;
            case 'search':
                include( 'includes/users-gallery-search.php' );
                break;
            case 'upload':
                include( 'includes/users-gallery-upload.php' );
                break;
            case 'files':
                include( 'includes/users-gallery-files.php' );
                break;
            default:
                include( 'includes/users-gallery-overview.php' );
        }
    }

    /**
     * Localisation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('wp-users-gallery', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function wpcustom_inspect_scripts_and_styles() {
        global $wp_scripts;
        global $wp_styles;

        //Disable selectbox script from other plugin..
        wp_dequeue_script('selectbox');
    }

    /**
     * Show meta properties for facebook 
     */
    public function facebook_data() {
        if (!isset($_GET['g_page']) || !isset($_GET['open']) || !isset($_GET['view']))
            return;

        $id = sanitize_text_field($_GET['view']);
        $post = get_post($id);

        if(empty($post))
            return;

        $description = strip_tags(strip_shortcodes($post->post_content));
        $description = trim(substr($description, 0, 600)) . '...';

        $post_image = '';
        $meta = get_post_meta($id, '_thumbnail_id', true);
        if ($meta) {
            $post = get_post($meta);
            if ($post) {
                $post_image = $post->guid;
            }
        }

        //facebook meta tags
        echo '<meta property="og:title" content="' . $post->post_title . '" />' . "\n";
        echo '<meta property="og:description" content="' . $description . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . get_bloginfo('name') . '"/>' . "\n";
        echo '<meta property="og:image" content="' . $post_image . '" />' . "\n";
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:url" content="' . home_url(add_query_arg(array())) . '" />' . "\n";

        //Twitter meta tags
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:site" content="@' . get_bloginfo('name') . '" />' . "\n";
        echo '<meta name="twitter:creator" content="@' . get_bloginfo('name') . '">' . "\n";
        echo '<meta name="twitter:title" content="' . $post->post_title . '" />' . "\n";
        echo '<meta name="twitter:image" content="' . $post_image . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . $description . '" />' . "\n";

        //Linked in meta tags
        echo '<meta prefix="og: http://ogp.me/ns#" property="og:title" content="' . get_bloginfo('name') . '" />' . "\n";
        echo '<meta prefix="og: http://ogp.me/ns#" property="og:type" content="article" />' . "\n";
        echo '<meta prefix="og: http://ogp.me/ns#" property="og:image" content="' . $post_image . '" />' . "\n";
        echo '<meta prefix="og: http://ogp.me/ns#" property="og:url" content="' . $description . '" />' . "\n";

        //Google plus meta tags
        echo '<meta itemscope itemtype="http://schema.org/Article" />' . "\n";
        echo '<meta itemprop="name" content="' . get_bloginfo('name') . '">' . "\n";
        echo '<meta itemprop="description" content="' . $description . '">' . "\n";
        echo '<meta itemprop="image" content="' . $post_image . '">' . "\n";
    }

    public function add_plugin_meta($plugin_meta, $plugin_file) {

        if ($plugin_file == 'users-gallery/init.php') {
            $plugin_meta['upgrade'] = '<a target="_blank" href="http://togidata.dk/en/user-gallery-plugin-wordpress/">' . __('Upgrade to pro version', 'wp-users-gallery') . '</a>';
        }

        return $plugin_meta;
    }

}

new Wpug_users_gallery(); //Initiate class