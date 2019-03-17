<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wpug_users_gallery ads class.
 */
class Wpug_users_gallery_ads {

    function __construct() {
        add_filter('wpug_users_gallery_admin_ads', array($this, 'plugin_pro_add'));
        return true;
    }

    /**
     * Careate add block
     */
    public function plugin_pro_add(){
        ?>
        <style>
                
            </style>
            <div class="add-wrap">
                <div class="wpug-add-container">
                    <h1><?php _e('Gallery for Users - PREMIUM VERSION', 'wc-disable-categories'); ?></h1>
                    <p><?php _e('Feel free to check out our premium version of the plugin, where we have added these awesome extra features:', 'wc-disable-categories'); ?></p>
                    <ul>
                        <li>
                            <p><?php _e('Add a widget which displays the most recent additions to the gallery', 'wc-disable-categories'); ?></p>
                        </li>
                        <li>
                            <p><?php _e('Admin can control which user roles are allowed to contribute to the gallery'); ?></p>
                        </li>
                        <li>
                            <p><?php _e('Admin can see (and delete) images and videos from backend'); ?></p>
                        </li>
                        <li>
                            <p><?php _e('Admin can add images and videos from backend'); ?></p>
                        </li>
                    </ul>
                    <div class="actions">
                        <?php echo '<a target="_blank" href="https://awesometogi.com/product/gallery-for-users-plugin-for-wordpress/" class="btn-green">' . __('Upgrade now', 'wc-disable-categories') . '</a>'; ?>
                        <?php echo '<a target="_blank" href="https://awesometogi.com/gallery-for-users-plugin-for-wordpress/" class="btn-white">' . __('Read more and see screenshots', 'wc-disable-categories') . '</a>'; ?>
                    </div>
                </div>
            </div>
        <?php
    }

}

new Wpug_users_gallery_ads();
