<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wpug_users_gallery admin class.
 */
class Wpug_users_gallery_admin {

    function __construct() {
        add_action('save_post', array($this, 'save_user_gallery_meta'), 10, 3);

        //Save settings if form submitted
        if (isset($_POST['users_gallery_user_role_submit'])) {
            $this->users_gallery_save_settings();
        }

        //Load settings menu
        add_action('admin_menu', array($this, 'users_gallery_settings_menu'));
    }

    /*
     * Save meta data of user gallery post type
     */

    public function save_user_gallery_meta($post_id, $post) {
        if ($post->post_type != 'users_gallery')
            return;
        //Save url data
        $gallery_item_video_url = !empty($_POST['gallery_item_video_url']) ? mysql_real_escape_string($_POST['gallery_item_video_url']) : '';
        update_post_meta($post_id, 'gallery_item_video_url', $gallery_item_video_url);
    }

    /**
     * Add settings page 
     */
    function users_gallery_settings_menu() {
        add_submenu_page(
                'edit.php?post_type=users_gallery', 'Settings', 'Settings', 'manage_options', 'users_gallery_settings', array($this, 'users_gallery_show_settings')
        );
    }

    /**
     * show settings page
     */
    public function users_gallery_show_settings() {
        //Load color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        $colors     = get_option('users_gallery_button_colours');
        $user_font  = get_option('users_gallery_text_font');
        ?>
        <div class="wrap">
            <h1><?php _e('Users gallery settings', 'wp-users-gallery'); ?></h1>

            <h3><?php _e('Custom button colors', 'wp-users-gallery'); ?></h3>
            <hr>
            <form action="" method="post" name="users_gallery_user_role_form">
                <table class="form-table">
                    <tr>
                        <th width="33%" scope="row">
                            <label for="button_colour"><?php _e('Button colour', 'wp-users-gallery'); ?></label>
                        </th>
                        <td>
                            <input class="color_picker" data-default-color="#000000" type="text" id="button_colour" name="button_colour" style="width:220px;" value="<?php echo esc_attr($colors['button_colour']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th width="33%" scope="row">
                            <label for="button_text_colour"><?php _e('Button text colour', 'wp-users-gallery'); ?></label>
                        </th>
                        <td>
                            <input class="color_picker" data-default-color="#ffffff" type="text" id="button_text_colour" name="button_text_colour" style="width:220px;" value="<?php echo esc_attr($colors['button_text_colour']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th width="33%" scope="row">
                            <label for="button_colour_on_hover"><?php _e('Button colour on hover', 'wp-users-gallery'); ?></label>
                        </th>
                        <td>
                            <input class="color_picker" data-default-color="#474747" type="text" id="button_colour_on_hover" name="button_colour_on_hover" style="width:220px;" value="<?php echo esc_attr($colors['button_colour_on_hover']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th width="33%" scope="row">
                            <label for="button_text_colour_on_hover"><?php _e('Button text colour on hover', 'wp-users-gallery'); ?></label>
                        </th>
                        <td>
                            <input class="color_picker" data-default-color="#ffffff" type="text" id="button_text_colour_on_hover" name="button_text_colour_on_hover" style="width:220px;" value="<?php echo esc_attr($colors['button_text_colour_on_hover']); ?>">
                        </td>
                    </tr>
                </table>

                <h3><?php _e('Font', 'wp-users-gallery'); ?></h3>
                <hr>
                <table class="form-table">
                    <tr>
                        <th width="33%" scope="row">
                            <label for="users_gallery_text_font"><?php _e('Font', 'wp-users-gallery'); ?></label>
                        </th>
                        <td>
                            <select name="users_gallery_text_font" class="users_gallery_user_role_submit" id="users_gallery_text_font" style="width:220px;">
                                <option value='"Open Sans", sans-serif' <?= ($user_font == '"Open Sans", sans-serif')? esc_attr('selected'):''; ?>>Open sans</option>
                                <option value='"Times New Roman", Times, serif' <?= ($user_font == '"Times New Roman", Times, serif')? esc_attr('selected'):''; ?>>Times New Roman</option>
                                <option value='Georgia, serif' <?= ($user_font == 'Georgia, serif')? esc_attr('selected'):''; ?>>Georgia</option>
                                <option value='"Palatino Linotype", "Book Antiqua", Palatino, serif' <?= ($user_font == '"Palatino Linotype", "Book Antiqua", Palatino, serif')? esc_attr('selected'):''; ?>>Palatino Linotype</option>
                                <option value='Arial, Helvetica, sans-serif' <?= ($user_font == 'Arial, Helvetica, sans-serif')? esc_attr('selected'):''; ?>>Arial</option>
                                <option value='"Arial Black", Gadget, sans-serif' <?= ($user_font == '"Arial Black", Gadget, sans-serif')? esc_attr('selected'):''; ?>>Arial Black</option>
                                <option value='"Comic Sans MS", cursive, sans-serif' <?= ($user_font == '"Comic Sans MS", cursive, sans-serif')? esc_attr('selected'):''; ?>>Comic Sans MS</option>
                                <option value='Impact, Charcoal, sans-serif' <?= ($user_font == 'Impact, Charcoal, sans-serif')? esc_attr('selected'):''; ?>>Impact</option>
                                <option value='"Lucida Sans Unicode", "Lucida Grande", sans-serif' <?= ($user_font == '"Lucida Sans Unicode", "Lucida Grande", sans-serif')? esc_attr('selected'):''; ?>>Lucida Sans Unicod</option>
                                <option value='Tahoma, Geneva, sans-serif' <?= ($user_font == 'Tahoma, Geneva, sans-serif')? esc_attr('selected'):''; ?>>Tahoma</option>
                                <option value='"Trebuchet MS", Helvetica, sans-serif' <?= ($user_font == '"Trebuchet MS", Helvetica, sans-serif')? esc_attr('selected'):''; ?>>Trebuchet MS</option>
                                <option value='Verdana, Geneva, sans-serif' <?= ($user_font == 'Verdana, Geneva, sans-serif')? esc_attr('selected'):''; ?>>Verdana</option>
                                <option value='"Courier New", Courier, monospace' <?= ($user_font == '"Courier New", Courier, monospace')? esc_attr('selected'):''; ?>>Courier New</option>
                                <option value='"Lucida Console", Monaco, monospace' <?= ($user_font == '"Lucida Console", Monaco, monospace')? esc_attr('selected'):''; ?>>Lucida Console</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit"><input type="submit" name="users_gallery_user_role_submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'wp-users-gallery'); ?>"></p>
            </form>

            <h3><?php _e('Available shortcodes', 'wp-users-gallery'); ?></h3>
            <hr>
            <table class="form-table">
                <tr>
                    <th width="33%" scope="row">
                        <label for="overview_page"><?php _e('Overview page', 'wp-users-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="overview_page" name="overview_page" style="width:220px;" value="[users_gallery]" onfocus="jQuery(this).select();">
                    </td>
                </tr>
                <tr>
                    <th width="33%" scope="row">
                        <label for="overview_page"><?php _e('Search page', 'wp-users-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="overview_page" name="overview_page" style="width:220px;" value='[users_gallery g_page="search"]' onfocus="jQuery(this).select();">
                    </td>
                </tr>
                <tr>
                    <th width="33%" scope="row">
                        <label for="overview_page"><?php _e('Upload page', 'wp-users-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="overview_page" name="overview_page" style="width:220px;" value='[users_gallery g_page="upload"]' onfocus="jQuery(this).select();">
                    </td>
                </tr>
                <tr>
                    <th width="33%" scope="row">
                        <label for="overview_page"><?php _e('My files page', 'wp-users-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="overview_page" name="overview_page" style="width:220px;" value='[users_gallery g_page="files"]' onfocus="jQuery(this).select();">
                    </td>
                </tr>
            </table>

            <?php
                do_action('wpug_users_gallery_admin_ads');
            ?>



        </div>
        <?php
    }

    /**
     * Save settings
     */
    public function users_gallery_save_settings() {
        $user_gallery_button_colours = array(
            'button_colour' => sanitize_text_field($_POST['button_colour']),
            'button_text_colour' => sanitize_text_field($_POST['button_text_colour']),
            'button_colour_on_hover' => sanitize_text_field($_POST['button_colour_on_hover']),
            'button_text_colour_on_hover' => sanitize_text_field($_POST['button_text_colour_on_hover']),
            'widget_background_colour' => sanitize_text_field($_POST['widget_background_colour'])
        );

        $user_font = sanitize_text_field($_POST['users_gallery_text_font']);

        update_option('users_gallery_text_font', $user_font);
        update_option('users_gallery_button_colours', $user_gallery_button_colours);
        return;
    }

}

new Wpug_users_gallery_admin();
