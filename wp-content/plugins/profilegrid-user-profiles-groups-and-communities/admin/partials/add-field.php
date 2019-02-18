<?php
$dbhandler = new PM_DBhandler;
$pm_activator = new Profile_Magic_Activator;
$pmrequests = new PM_request;
$rm_url = $pmrequests->pg_get_rm_installation_plugin_url();
$textdomain = $this->profile_magic;
$path = plugin_dir_url(__FILE__);
$id = filter_input(INPUT_GET, 'id');
$str = filter_input(INPUT_GET, 'type');
$gid = filter_input(INPUT_POST, 'gid');
if ($gid == false || $gid == NULL) {
    $gid = filter_input(INPUT_GET, 'gid');
}
$identifier = 'FIELDS';

if ($id == false || $id == NULL) {
    $id = 0;
    $lastrow = $dbhandler->pm_count($identifier);
    $lastrow = $dbhandler->get_all_result($identifier, 'field_id', 1, 'var', 0, 1, 'field_id', 'DESC');
    $ordering = $lastrow + 1;
} else {
    $row = $dbhandler->get_row($identifier, $id);

    if (!empty($row)) {
        if ($row->field_options != "")
            $field_options = maybe_unserialize($row->field_options);
        $ordering = $row->ordering;
        $str = $row->field_type;
        $gid = $row->associate_group;
    }
}
$groups = $dbhandler->get_all_result('GROUPS', array('id','group_name'));
$sections = $dbhandler->get_all_result('SECTION', array('id','section_name'), array('gid' => $gid));

if (filter_input(INPUT_POST, 'submit_field')) {
    $field_type = filter_input(INPUT_POST, 'field_type');
    $check = $pmrequests->pm_check_field_exist($gid, 'user_email', true);
    if ($check == true && $field_type == 'user_email' && $str != 'user_email') {
        echo '<div class="error">' . __('you have already created a user email field.','profile-magic') . '</div>';
    } else {
        $retrieved_nonce = filter_input(INPUT_POST, '_wpnonce');
        if (!wp_verify_nonce($retrieved_nonce, 'save_pm_add_field'))
            die(__('Failed security check','profile-magic'));
        $fieldid = filter_input(INPUT_POST, 'field_id');
        $exclude = array("_wpnonce", "_wp_http_referer", "submit_field", "field_id");
        $post = $pmrequests->sanitize_request($_POST, $identifier, $exclude);
        $sectionname = $dbhandler->get_value('SECTION', 'section_name', $post['associate_section']);
        if ($field_type == 'user_email')
            $post['show_in_signup_form'] = 1;
        if (!isset($post['show_in_signup_form']))
            $post['show_in_signup_form'] = 0;
        if (!isset($post['is_required']))
            $post['is_required'] = 0;
        if (!isset($post['is_editable']))
            $post['is_editable'] = 0;
        if (!isset($post['display_on_profile']))
            $post['display_on_profile'] = 0;
        if (!isset($post['display_on_group']))
            $post['display_on_group'] = 0;
        if (!isset($post['visibility']))
            $post['visibility'] = 1;
        if ($post != false) {
            foreach ($post as $key => $value) {
                $data[$key] = $value;
                $arg[] = $pm_activator->get_db_table_field_type($identifier, $key);
            }
        }
        if ($data['field_key'] == '') {
            if ($fieldid == 0) {
                $field_key_id = $data['ordering'];
            } else {
                $field_key_id = $fieldid;
            }
            $data['field_key'] = $pmrequests->get_field_key($data['field_type'], $field_key_id);
        } else {
            if ($pmrequests->get_default_key_type($data['field_type'])) {
                $data['field_key'] = $pmrequests->get_field_key($data['field_type'], $field_key_id);
            }
        }
        if ($fieldid == 0) {
            $dbhandler->insert_row($identifier, $data, $arg);
        } else {
            $dbhandler->update_row($identifier, 'field_id', $fieldid, $data, $arg, '%d');
        }

        wp_redirect( esc_url_raw('admin.php?page=pm_profile_fields&gid=' . $gid . '#' . sanitize_key($sectionname)) );
        exit;
    }
}

if (filter_input(INPUT_POST, 'delete')) {
    $selected = filter_input(INPUT_POST, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

    foreach ($selected as $fid) {
        $dbhandler->remove_row($identifier, 'field_id', $fid, '%d');
    }

    wp_redirect( esc_url_raw('admin.php?page=pm_profile_fields&gid=' . $gid) );
    exit;
}

if (filter_input(INPUT_POST, 'duplicate')) {
    $selected = filter_input(INPUT_POST, 'selected', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    foreach ($selected as $fid) {
        $data = $dbhandler->get_row($identifier, $fid, 'field_id', 'ARRAY_A');
        unset($data['field_id']);
        $field_key_id = $ordering;
        $data['field_key'] = $pmrequests->get_field_key($data['field_type'], $field_key_id);
        $dbhandler->insert_row($identifier, $data);
    }
    wp_redirect( esc_url_raw('admin.php?page=pm_profile_fields&gid=' . $gid) );
    exit;
}

if (filter_input(INPUT_GET, 'action') == 'delete') {
    $dbhandler->remove_row($identifier, 'field_id', $id, '%d');
    wp_redirect( esc_url_raw('admin.php?page=pm_profile_fields&gid=' . $gid) );
    exit;
}
?>

<div class="uimagic">
    <form name="pm_add_group" id="pm_add_field" method="post">
        <!-----Dialogue Box Starts----->
        <div class="content">
            <?php if ($id == 0): ?>
                <div class="uimheader">
                    <?php _e('New Field','profile-magic'); ?>
                </div>
            <?php else: ?>
                <div class="uimheader">
                    <?php _e('Edit Field','profile-magic'); ?>
                </div>
            <?php endif; ?>
            <div class="uimsubheader">
                <?php
                //Show subheadings or message or notice
                ?>
            </div>
            <div class="uimrow">
                <div class="uimfield">
                    <?php _e('Profile Field Name','profile-magic'); ?>
                    <sup>*</sup></div>
                <div class="uiminput pm_required">
                    <input type="text" name="field_name" id="field_name" value="<?php if (!empty($row)) echo esc_attr($row->field_name); ?>" />
                    <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php _e('Label of the field as it appears on default registration form and User Profile. This does not apply to fields without labels such as Heading, Paragraph, Divider, Spacing.','profile-magic'); ?></div>
            </div>
            <div class="uimrow">
                <div class="uimfield">
                    <?php _e('Profile Field Description','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <textarea name="field_desc" id="field_desc"><?php if (!empty($row)) echo esc_attr($row->field_desc); ?>
                    </textarea>
                </div>
                <div class="uimnote"><?php _e('For your reference only. Not visible on front-end. Description can help you remember the purpose of the field.','profile-magic'); ?> </div>
            </div>

            <div class="uimrow" style="display:none;">
                <div class="uimfield">
                    <?php _e('Field Type','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <select name="field_type" id="field_type" onChange="pm_show_hide_field_option(this.value, 'field_options_wrapper')">
                        <option value=""><?php _e('Select A Field','profile-magic'); ?></option>
                        <option value="heading" <?php if (isset($str) && $str == 'heading') echo 'selected'; ?>><?php _e('Heading','profile-magic'); ?></option>
                        <option value="paragraph" <?php if (isset($str) && $str == 'paragraph') echo 'selected'; ?>><?php _e('Paragraph','profile-magic'); ?></option>
                        <option value="text" <?php if (isset($str) && $str == 'text') echo 'selected'; ?>><?php _e('Text','profile-magic'); ?></option>
                        <option value="select" <?php if (isset($str) && $str == 'select') echo 'selected'; ?>><?php _e('Drop Down','profile-magic'); ?></option>
                        <option value="radio" <?php if (isset($str) && $str == 'radio') echo 'selected'; ?>><?php _e('Radio Button','profile-magic'); ?></option>
                        <option value="textarea" <?php if (isset($str) && $str == 'textarea') echo 'selected'; ?>><?php _e('Text Area','profile-magic'); ?></option>
                        <option value="checkbox" <?php if (isset($str) && $str == 'checkbox') echo 'selected'; ?>><?php _e('Check Box','profile-magic'); ?></option>
                        <option value="DatePicker" <?php if (isset($str) && $str == 'DatePicker') echo 'selected'; ?>><?php _e('Date','profile-magic'); ?></option>
                        <option value="email" <?php if (isset($str) && $str == 'email') echo 'selected'; ?>><?php _e('Email','profile-magic'); ?></option>
                        <option value="number" <?php if (isset($str) && $str == 'number') echo 'selected'; ?>><?php _e('Number','profile-magic'); ?></option>
                        <option value="country" <?php if (isset($str) && $str == 'country') echo 'selected'; ?>><?php _e('Country','profile-magic'); ?></option>
                        <option value="timezone" <?php if (isset($str) && $str == 'timezone') echo 'selected'; ?>><?php _e('Timezone','profile-magic'); ?></option>
                        <option value="term_checkbox" <?php if (isset($str) && $str == 'term_checkbox') echo 'selected'; ?>><?php _e('T&C Checkbox','profile-magic'); ?></option>
                        <option value="file" <?php if (isset($str) && $str == 'file') echo 'selected'; ?>><?php _e('File Upload','profile-magic'); ?></option>
           <!-- <option value="pricing" <?php if (isset($str) && $str == 'pricing') echo 'selected'; ?>><?php _e('Pricing','profile-magic'); ?></option> -->
                        <option value="repeatable_text" <?php if (isset($str) && $str == 'repeatable_text') echo 'selected'; ?>><?php _e('Repeatable Text','profile-magic'); ?></option>
                        <option value="first_name" <?php if (isset($str) && $str == 'first_name') echo 'selected'; ?>><?php _e('First Name','profile-magic'); ?></option>
                        <option value="last_name" <?php if (isset($str) && $str == 'last_name') echo 'selected'; ?>><?php _e('Last Name','profile-magic'); ?></option>
                        <option value="user_name" <?php if (isset($str) && $str == 'user_name') echo 'selected'; ?>><?php _e('Username','profile-magic'); ?></option>
                        <?php
                        $check = $pmrequests->pm_check_field_exist($gid, 'user_email', true);
                        if ($check === false || $str == 'user_email'):
                            ?>
                            <option value="user_email" <?php if (isset($str) && $str == 'user_email') echo 'selected'; ?>><?php _e('User Email','profile-magic'); ?></option>
                        <?php endif; ?> 
                        <option value="user_url" <?php if (isset($str) && $str == 'user_url') echo 'selected'; ?>><?php _e('Website','profile-magic'); ?></option>
                        <option value="user_pass" <?php if (isset($str) && $str == 'user_pass') echo 'selected'; ?>><?php _e('Password','profile-magic'); ?></option>
                        <option value="confirm_pass" <?php if (isset($str) && $str == 'confirm_pass') echo 'selected'; ?>><?php _e('Confirm Password','profile-magic'); ?></option>
                        <option value="description" <?php if (isset($str) && $str == 'description') echo 'selected'; ?>><?php _e('Biographical Info','profile-magic'); ?></option>
                        <option value="user_avatar" <?php if (isset($str) && $str == 'user_avatar') echo 'selected'; ?>><?php _e('Profile Image','profile-magic'); ?></option>
                        <option value="mobile_number" <?php if (isset($str) && $str == 'mobile_number') echo 'selected'; ?>><?php _e('Mobile Number','profile-magic'); ?></option>
                        <option value="phone_number" <?php if (isset($str) && $str == 'phone_number') echo 'selected'; ?>><?php _e('Phone Number','profile-magic'); ?></option>
                        <option value="gender" <?php if (isset($str) && $str == 'gender') echo 'selected'; ?>><?php _e('Gender','profile-magic'); ?></option>
                        <option value="language" <?php if (isset($str) && $str == 'language') echo 'selected'; ?>><?php _e('Language','profile-magic'); ?></option>
                        <!--<option value="birth_date" <?php if (isset($str) && $str == 'birth_date') echo 'selected'; ?>><?php _e('Birth Date','profile-magic'); ?></option> -->
                        <option value="divider" <?php if (isset($str) && $str == 'divider') echo 'selected'; ?>><?php _e('Divider','profile-magic'); ?></option>
                        <option value="spacing" <?php if (isset($str) && $str == 'spacing') echo 'selected'; ?>><?php _e('Spacing','profile-magic'); ?></option>
                        <option value="multi_dropdown" <?php if (isset($str) && $str == 'multi_dropdown') echo 'selected'; ?>><?php _e('Multi Dropdown','profile-magic'); ?></option>
                        <option value="facebook" <?php if (isset($str) && $str == 'facebook') echo 'selected'; ?>><?php _e('Facebook','profile-magic'); ?></option>
                        <option value="twitter" <?php if (isset($str) && $str == 'twitter') echo 'selected'; ?>><?php _e('Twitter','profile-magic'); ?></option>
                        <option value="google" <?php if (isset($str) && $str == 'google') echo 'selected'; ?>><?php _e('Google+','profile-magic'); ?></option>
                        <option value="linked_in" <?php if (isset($str) && $str == 'linked_in') echo 'selected'; ?>><?php _e('Linked In','profile-magic'); ?></option>
                        <option value="youtube" <?php if (isset($str) && $str == 'youtube') echo 'selected'; ?>><?php _e('Youtube','profile-magic'); ?></option>
                        <option value="instagram" <?php if (isset($str) && $str == 'instagram') echo 'selected'; ?>><?php _e('Instagram','profile-magic'); ?></option>
                        <?php do_action('pg_add_field_in_dropdown', $str); ?>
                    </select>
                </div>
                <div class="uimnote"><?php _e('Change the type of this field. Please note, changing a type of an existing field with values may result in data loss. Use this carefully.','profile-magic'); ?></div>
            </div>

            <div class="" id="field_options_wrapper" style=" <?php
            if (isset($str)) {
                echo 'display:block;';
            } else {
                echo 'display:none;';
            }
            ?>">

                <div class="uimrow" id="place_holder_text_html">
                    <div class="uimfield">
<?php _e('Placeholder text:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="text" name="field_options[place_holder_text]" id="place_holder_text" value="<?php if (!empty($field_options)) echo esc_attr($field_options['place_holder_text']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Placeholder text appears inside input box as guidelines to the user. It disappears when the user clicks on the input box. For e.g. <i>Enter your age</i> inside a number input box.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="css_class_attribute_html">
                    <div class="uimfield">
<?php _e('CSS Class Attribute:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="text" name="field_options[css_class_attribute]" id="css_class_attribute" value="<?php if (!empty($field_options)) echo esc_attr($field_options['css_class_attribute']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('If you know a bit of CSS, you can create and assign a custom class to this field. Just enter the name of the class as it appears in your stylesheet. For e.g. <i>my-input-class</i>','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="maximum_length_html">
                    <div class="uimfield">
<?php _e('Maximum Length:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="number" min="1" name="field_options[maximum_length]" id="maximum_length" value="<?php if (!empty($field_options)) echo esc_attr($field_options['maximum_length']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Maximum length of the allowed field value in characters (count).','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="default_value_html">
                    <div class="uimfield">
<?php _e('Default Value:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="text" name="field_options[default_value]" id="default_value" value="<?php if (!empty($field_options)) echo esc_attr($field_options['default_value']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Default values work for selection boxes where you want a value to be pre-selected when the form loads. You need to enter the value exactly as you have created in the options below.','profile-magic'); ?> </div>
                </div>

                <div class="uimrow" id="first_option_html">
                    <div class="uimfield">
<?php _e('First Option Value:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="text" name="field_options[first_option]" id="first_option" value="<?php if (!empty($field_options) && isset($field_options['first_option'])) echo esc_attr($field_options['first_option']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Add options for your selection field below. Click on <i>Click to add option</i> to add an extra option to existing ones.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="field_options_html">
                    <div class="uimfield">
<?php _e('Options:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <textarea name="field_options[dropdown_option_value]" id="field_options"><?php if (!empty($field_options)) echo esc_attr($field_options['dropdown_option_value']); ?></textarea>
                        <div class="errortext"></div>
                    </div>
                    <div class="uimnote"><?php _e('Options for drop down list. Separate multiple values with a comma(,).','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="field_options_radio_html">
                    <div class="uimfield">
<?php _e('Options:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <ul class="uimradio" id="radio_option_ul_li_field">
                            <?php
                            if (!empty($field_options) && !empty($field_options['radio_option_value'])) {

                                foreach ($field_options['radio_option_value'] as $optionvalue) {
                                    if ($optionvalue == 'chl_other')
                                        continue;
                                    ?>
                                    <li class="pm_radio_option_field">
                                        <span class="pm_handle"></span>
                                        <input type="text" name="field_options[radio_option_value][]" value="<?php if (!empty($optionvalue)) echo esc_attr($optionvalue); ?>">
                                        <span class="pm_remove_field" onClick="remove_pm_radio_option(this)"><?php _e('Delete','profile-magic');?></span>
                                    </li>
                                    <?php
                                }
                            }
                            else {
                                ?>
                                <li class="pm_radio_option_field">
                                    <span class="pm_handle"></span>
                                    <input type="text" name="field_options[radio_option_value][]" value="<?php if (!empty($optionvalue)) echo esc_attr($optionvalue); ?>">
                                    <span class="pm_remove_field" onClick="remove_pm_radio_option(this)"><?php _e('Delete','profile-magic');?></span>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>

                        <ul class="uimradio pg-add-other-options" id="pm_radio_field_other_option_html">
                            <li><a class="pm_click_add_option pg-add-options"  onClick="add_pm_radio_option()" onKeyUp="add_pm_radio_option()">Click to add option</a></li>
                            <?php if (!empty($field_options) && !empty($field_options['radio_option_value']) && in_array('chl_other', $field_options['radio_option_value'])): ?> 
                                <li class="pm_radio_option_field" style=" margin-top:12px;"><input type="text" name="optionvalue[]" id="optionvalue[]" value="<?php _e('Their answer','profile-magic');?>" disabled><span class="removefield pm_remove_field" onClick="remove_pm_radio_option(this)"><?php _e('Delete','profile-magic');?></span><input type="hidden" name="field_options[radio_option_value][]" id="field_options[radio_option_value][]" value="chl_other" /></li>
                            <?php else: ?>
                                <li class="pm_add_other_button" onClick="add_pm_other_option()"><?php _e('or Add "Other"','profile-magic');?> </li>
<?php endif; ?>
                        </ul>

                        <div class="errortext"></div>
                    </div>
                    <div class="uimnote"><?php _e('Add options for your selection field below. Click on <i>Click to add option</i> to add an extra option to existing ones.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="paragraph_text_html">
                    <div class="uimfield">
<?php _e('Paragraph Text:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <textarea name="field_options[paragraph_text]" id="paragraph_text"><?php if (!empty($field_options)) echo esc_attr($field_options['paragraph_text']); ?></textarea>
                    </div>
                    <div class="uimnote"><?php _e('The text you want to appear in this field.','profile-magic'); ?> </div>
                </div>

                <div class="uimrow" id="columns_html">
                    <div class="uimfield">
<?php _e('Columns:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="number" min="2" name="field_options[columns]" id="columns" value="<?php if (!empty($field_options)) echo esc_attr($field_options['columns']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Number of columns (equivalent to character count) allowed in this field. Leave blank for no limit.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="rows_html">
                    <div class="uimfield">
<?php _e('Rows:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="number" min="2" name="field_options[rows]" id="rows" value="<?php if (!empty($field_options)) echo esc_attr($field_options['rows']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Number of rows (of text) allowed in this field. Leave blank for no limit.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="term_and_condition_html">
                    <div class="uimfield">
<?php _e('Terms & Conditions:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <textarea name="field_options[term_and_condition]" id="term_and_condition"><?php if (!empty($field_options)) echo esc_attr($field_options['term_and_condition']); ?></textarea>
                    </div>
                    <div class="uimnote"><?php _e('Paste the contents of your Terms and Conditions here. Users will be able to scroll through it and read in full before accepting them.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="allowed_file_types_html">
                    <div class="uimfield">
<?php _e('Define allowed file types (file extensions):','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <textarea name="field_options[allowed_file_types]" id="allowed_file_types"><?php if (!empty($field_options)) echo esc_attr($field_options['allowed_file_types']); ?></textarea>
                    </div>
                    <div class="uimnote"><?php _e('Separate multiple values by " | ". For example PDF|JPEG|XLS','profile-magic'); ?> </div>
                </div>

                <div class="uimrow" id="heading_text_html">
                    <div class="uimfield">
<?php _e('Heading Text:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="text" name="field_options[heading_text]" id="heading_text" value="<?php if (!empty($field_options)) echo esc_attr($field_options['heading_text']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Text inside your heading. Remember, headings have large font sizes, therefore a very long text may appear odd. Best used to grab attention for something important. Combine it with <i>Paragraph</i> field type to add extra content.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="heading_tag_html">
                    <div class="uimfield">
<?php _e('Heading Tag:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <select name="field_options[heading_tag]" id="heading_tag">
                            <option value="h1" <?php if (!empty($field_options)) selected($field_options['heading_tag'], 'h1'); ?>><?php _e('Heading 1','profile-magic'); ?></option>
                            <option value="h2" <?php if (!empty($field_options)) selected($field_options['heading_tag'], 'h2'); ?>><?php _e('Heading 2','profile-magic'); ?></option>
                            <option value="h3" <?php if (!empty($field_options)) selected($field_options['heading_tag'], 'h3'); ?>><?php _e('Heading 3','profile-magic'); ?></option>
                            <option value="h4" <?php if (!empty($field_options)) selected($field_options['heading_tag'], 'h4'); ?>><?php _e('Heading 4','profile-magic'); ?></option>
                            <option value="h5" <?php if (!empty($field_options)) selected($field_options['heading_tag'], 'h5'); ?>><?php _e('Heading 5','profile-magic'); ?></option>
                            <option value="h6" <?php if (!empty($field_options)) selected($field_options['heading_tag'], 'h6'); ?>><?php _e('Heading 6','profile-magic'); ?></option>
                        </select>
                    </div>
                    <div class="uimnote"><?php _e('Text size decreases from H2 to H6. There maybe additional style applied to the heading text based on your theme CSS.','profile-magic'); ?></div>
                </div>

                <div class="uimrow" id="price_html">
                    <div class="uimfield">
<?php _e('Price:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input type="number" min="0" name="field_options[price]" id="price" value="<?php if (!empty($field_options)) echo esc_attr($field_options['price']); ?>" />
                    </div>
                    <div class="uimnote"><?php _e('Price - Obviously, only numbers accepted. A read only field.','profile-magic'); ?></div>
                </div>



            </div>

            <div class="uimrow" id="field_icon_div">
                <div class="uimfield">
<?php _e('Profile Field Icon','profile-magic'); ?>
                </div>
                <div class="uiminput" id="icon_html">
                    <input id="field_icon" type="hidden" name="field_icon" class="icon_id" value="<?php if (!empty($row)) echo esc_attr($row->field_icon); ?>" />
                    <input id="field_icon_button" name="field_icon_button" class="button group_icon_button" type="button" value="<?php _e('Upload Icon','profile-magic');?>" />
                    <?php
                    if (!empty($row) && $row->field_icon != 0) {
                        echo wp_get_attachment_link($row->field_icon, array(50, 50), false, true, false);
                    }
                    ?>
                    <img src="" width="50px" id="group_icon_img" style="display:none;" />
                    <?php
                    if (!empty($row) && $row->field_icon != 0) {
                        ?>
                        <input type="button" name="remove_group_icon" id="remove_group_icon" class="remove_icon" value="<?php _e('Remove Icon','profile-magic');?>" />
                   <?php
                        }
                    ?>

                    <div class="errortext" id="icon_error"></div>
                </div>
                <div class="uimnote"> <?php _e('Icons appear at the beginning of the label in Default registration forms and User Profiles. For best results use a square image. For e.g. <i>16px x 16px, 256px x 256px, 512px x 512px</i>...','profile-magic'); ?></div>
            </div>

            <div class="uimrow">
                <div class="uimfield">
<?php _e('Associate with Group','profile-magic'); ?>
                </div>
                <div class="uiminput pm_select_required">
                    <select name="associate_group" id="associate_group" onchange="pm_ajax_sections_dropdown(this.value)">
                        <option value=""><?php _e('Select A Group','profile-magic');?></option>
                        <?php
                        foreach ($groups as $group) {
                            ?>
                            <option value="<?php echo $group->id; ?>" <?php if (!empty($gid)) selected($gid, $group->id); ?>><?php echo $group->group_name; ?></option>
<?php }
?>
                    </select>
                    <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php _e('Move this field to a different group. Use carefully, since this will remove the field from current group.','profile-magic'); ?></div>
            </div>

            <div class="uimrow">
                <div class="uimfield">
<?php _e('Associate with Section','profile-magic'); ?>
                </div>
                <div class="uiminput pm_select_required">
                    <select name="associate_section" id="associate_section">
                        <?php
                        foreach ($sections as $section) {
                            ?>
                            <option value="<?php echo $section->id; ?>" <?php if (!empty($row)) selected($row->associate_section, $section->id); ?>><?php echo $section->section_name; ?></option>
<?php }
?>
                    </select>
                    <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php _e('If you have multiple Profile sections in this group, you can move the field to a different section here. By default, each new field is added to the first section. For e.g. moving a date field labelled <i>Date of Birth</i> from <i>Career</i> section to <i>About</i> section.','profile-magic'); ?></div>
            </div>

              <div class="uimrow" id="displayonprofile">
                <div class="uimfield">
<?php _e('Display on Profile Page','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <input name="display_on_profile" id="display_on_profile" type="checkbox"  class="pm_toggle" value="1" <?php if (!empty($row)) checked($row->display_on_profile, 1); if($id==0)echo 'checked'; ?> style="display:none;"  onClick="pm_show_hide(this, 'displayprofilehtml')" />
                    <label for="display_on_profile"></label>
                </div>
                <div class="uimnote"><?php _e('Show this field on User Profile page of the members. It is totally possible to show a field in Default registration form but hide from the User Profile page.','profile-magic'); ?></div>
            </div>
            <div class="childfieldsrow" id="displayprofilehtml" style=" <?php
                        if (!empty($row) && $row->display_on_profile == '1') {
                            echo 'display:block;';
                        } 
                        elseif($id==0)
                        {
                           echo 'display:block;'; 
                        }
                        else {
                            echo 'display:none;';
                        }
                        ?>">
                
               
                
                <div class="uimrow">
                    <div class="uimfield">
<?php _e('Display on Group Page','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input name="display_on_group" id="display_on_group" type="checkbox"  class="pm_toggle" value="1" <?php if (!empty($row)) checked($row->display_on_group, 1); ?> style="display:none;" />
                        <label for="display_on_group"></label>
                    </div>
                    <div class="uimnote"><?php _e('Group pages show snippet of member profiles. While user profile and cover images appear there by default, you can show specific fields in these snippets too. Also adding too many large fields may not look very pretty. Try to strike a balance between infromation and structure.','profile-magic'); ?></div>
                </div>

                
            </div>
            <div class="uimrow" id="displayonsearch">
                <div class="uimfield">
                    <?php _e('Display in Advance Search','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <input name="field_options[display_on_search]" id="display_on_search" type="checkbox"  class="pm_toggle" value="1" <?php if (!empty($field_options['display_on_search'])) checked($field_options['display_on_search'], 1); ?> style="display:none;" />
                    <label for="display_on_search"></label>
                </div>
                <div class="uimnote"><?php _e('This will display the field in advance search above the members directory on front end. Selecting it during search will allow users to restrict keyword search to selected field(s).','profile-magic'); ?></div>
            </div>
            <div class="uimrow">
            <h3><?php _e('Default Registration Form','profile-magic');?></h3>
            </div>

            <div class="uimrow" id="show_signup">
                <div class="uimfield">
<?php _e('Display in Default Registration Form','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <input name="show_in_signup_form" id="show_in_signup_form" type="checkbox"  class="pm_toggle" value="1" <?php
                    if (!empty($row)) {
                        checked($row->show_in_signup_form, 1);
                    } else {
                        echo 'checked';
                    }
                    ?> style="display:none;" onClick="pm_show_hide(this, 'signup_html')" />
                    <label for="show_in_signup_form"></label>
                </div>
                <div class="uimnote"><?php _e('The field will appear in the Default group registration form when members sign up for this group. Alternatively, you can hide from the form, and users can then fill it up when editing their profiles later.','profile-magic'); ?></div>
            </div>
            <div class="childfieldsrow" id="signup_html" style=" <?php
            if (!empty($row)) {
                if ($row->show_in_signup_form == '1') {
                    echo 'display:block;';
                } else {
                    echo 'display:none;';
                }
            } else {
                echo 'display:block;';
            }
            ?>">
                <div class="uimrow">
                    <div class="uimfield">
<?php _e('Required?','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <input name="is_required" id="is_required" type="checkbox"  class="pm_toggle" value="1" <?php if (!empty($row)) checked($row->is_required, 1); ?> style="display:none;" />
                        <label for="is_required"></label>
                    </div>
                    <div class="uimnote"> <?php _e('Make this field mandatory. Users will receive an error if they try to submit the form without filling up this field.','profile-magic'); ?></div>
                </div>
               
            </div>
            
            <div class="uimrow">
                    <div class="uimfield">
                                <?php _e('Privacy:','profile-magic'); ?>
                    </div>
                    <div class="uiminput">
                        <ul class="uimradio">
                            <li>
                                <input type="radio" name="visibility" id="visibility" value="1"  <?php if (!empty($row)) checked($row->visibility, 1);if($id==0)echo 'checked';?>>
<?php _e('Public','profile-magic'); ?>
                            </li>
                            <li>
                                <input type="radio" name="visibility" id="visibility" value="2"  <?php if (!empty($row)) checked($row->visibility, 2); ?>>
<?php _e('Registered','profile-magic'); ?>
                            </li>
                            <li>
                                <input type="radio" name="visibility" id="visibility" value="3"  <?php if (!empty($row)) checked($row->visibility, 3); ?>>
                    <?php _e('Only Group Manager','profile-magic'); ?>
                            </li>

                        </ul>
                        <div class="errortext"></div>
                    </div>
                    <div class="uimnote"><?php _e('Set visibility of the field in User Profiles.','profile-magic'); ?></div>
                </div>

            <div class="uimrow" id="dateofbirth">
                <div class="uimfield">
<?php _e('range of year dropdown','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <input name="field_options[set_dob_range]" id="set_dob_range" type="checkbox"  class="pm_toggle" value="1" <?php if (!empty($field_options['set_dob_range'])) checked($field_options['set_dob_range'], 1); ?> style="display:none;" onClick="pm_show_hide(this, 'dateofbirth_range')" />
                    <label for="set_dob_range"></label>
                </div>
                <div class="uimnote"><?php _e('This will Enable this to force selection of date of birth from a certain range.','profile-magic'); ?></div>



            </div>
            <div class="childfieldsrow" id="dateofbirth_range" style=" <?php
            if (!empty($row) && !empty($field_options['set_dob_range']) && $field_options['set_dob_range'] == 1) {
                echo 'display:block;';
            } else {
                echo 'display:none;';
            }
?>">
                <div class="uimrow">
                    <div class="uimrow">
                        <div class="uimfield">
<?php _e('Start Year','profile-magic'); ?>
                        </div>
                        <div class="uiminput">
                            <input type="text" class="pm_calendar" value="<?php if (!empty($field_options['max_dob'])) echo $field_options['max_dob']; ?>" id="max_dob" name="field_options[max_dob]" />
                            <label for="set_dob_max_range"></label>
                        </div>
                        <div class="uimnote"><?php _e('Maximum date of for the field.','profile-magic'); ?></div>          
                    </div>
                    <div class="uimrow">
                        <div class="uimfield">
<?php _e('End Year','profile-magic'); ?>
                        </div>
                        <div class="uiminput">
                            <input type="text" class="pm_calendar" value="<?php if (!empty($field_options['min_dob'])) echo $field_options['min_dob']; ?>" id="min_dob" name="field_options[min_dob]" />
                            <label for="set_dob_min_range"></label>
                        </div>
                        <div class="uimnote"><?php _e('Minimum date of for the field.','profile-magic'); ?></div>          
                    </div>
                </div>
            </div>


            <div class="uimrow" id="address_pane">

                <div class="uimfield">
<?php _e('Allow fields','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <ul class="uimradio">
                        <li>  
                            <input type="checkbox" name="field_options[address_line_1]" value="1" <?php if (!empty($field_options['address_line_1'])) echo 'checked'; ?>> <?php _e('Address line 1','profile-magic');?> 
                        </li>
                        <li>
                            <input type="checkbox" name="field_options[address_line_2]" value="1" <?php if (!empty($field_options['address_line_2'])) echo 'checked'; ?>> <?php _e('Address line 2','profile-magic');?>
                        </li>
                        <li>
                            <input type="checkbox" name="field_options[city]" value="1" <?php if (!empty($field_options['city'])) echo 'checked'; ?>> <?php _e('City','profile-magic');?>    
                        </li>
                        <li>
                            <input type="checkbox" name="field_options[state]" value="1" <?php if (!empty($field_options['state'])) echo 'checked'; ?>> <?php _e('State','profile-magic');?> 
                        </li>
                         <li>
                            <input type="checkbox" name="field_options[country]" value="1" <?php if (!empty($field_options['country'])) echo 'checked'; ?>> <?php _e('Country','profile-magic');?> 
                        </li> 
                        <li>
                            <input type="checkbox" name="field_options[zip_code]" value="1" <?php if (!empty($field_options['zip_code'])) echo 'checked'; ?>> <?php _e('Zip Code','profile-magic');?>
                        </li>
                    </ul>
                    <label for="address"></label>
                    <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php _e('Select address fields which you want to show on frontend.','profile-magic'); ?></div>          

            </div>
            <div class="" id="pg_rm_field_html">
            <div class="uimrow">
            <h3><?php _e('Custom Registration Form','profile-magic');?></h3>
            </div>
            <?php if(class_exists('Registration_Magic')):?>
            <?php $is_associate_with_rm_form = $pmrequests->pm_check_if_group_associate_with_rm_form($gid);
            if($is_associate_with_rm_form!=0)
            {
                ?>
                <div class="uimrow">
                <div class="uimfield">
<?php _e('Map with','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <select name="field_options[field_map_with]" id="field_map_with">
                        <option value=""><?php _e('None','profile-magic');?></option>
                        <?php 
                        if(!empty($field_options) && isset($field_options['field_map_with']))
                        {
                            $selected = $field_options['field_map_with'];
                        }
                        else
                        {
                            $selected = 0;
                        }
                        $pmrequests->pm_get_all_rm_registration_form_fields_dropdown_list($selected,$is_associate_with_rm_form);?>
                        
                    </select>
                    <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php _e('Chose from the list of fields inside RegistrationMagic form to map with corresponding User Profile field. (Note: Password field will not appear in the the list)','profile-magic'); ?></div>
            </div>
            <?php
                
            }
            else
            {
                ?>
             <div class="uimrow">
                <div class="uimfield">
<?php _e('Map with','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <select name="field_options[field_map_with]" id="field_map_with" disabled>
                        <option value=""><?php _e('None','profile-magic');?></option>
                    </select>
                    <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php _e('No RegistrationMagic form has been assigned to this group. To map this profile field with a custom registration form field, assign a registration form from Group Settings → Group Registration Form.','profile-magic'); ?></div>
            </div>
            <?php
            }
            ?>
            
            <?php else:?>
            
            <div class="uimrow">
                <div class="uimfield">
<?php _e('Map with','profile-magic'); ?>
                </div>
                <div class="uiminput">
                    <select name="field_options[field_map_with]" id="field_map_with" disabled>
                        <option value=""><?php _e('None','profile-magic');?></option>
                    </select>
                    <div class="errortext"></div>
                </div>
                <div class="uimnote"><?php printf(__("RegistrationMagic is not installed. If you wish to use a customized registration form with this group, please install RegistrationMagic from <a href='%s'>here</a>. After that you will have the option to map this profile field with form’s field.","profile-magic"),$rm_url); ?></div>
            </div>
            <?php endif;?>
            </div>


<?php
if (!empty($row)) {
    $sectionname = $dbhandler->get_value('SECTION', 'section_name', $row->associate_section);
    $cancelurl = 'admin.php?page=pm_profile_fields&gid=' . $gid . '#' . sanitize_key($sectionname);
} else {
    $cancelurl = 'admin.php?page=pm_profile_fields&gid=' . $gid;
}
?>
            <div class="buttonarea"> <a href="<?php echo $cancelurl; ?>">
                    <div class="cancel">&#8592; &nbsp;
<?php _e('Cancel','profile-magic'); ?>
                    </div>
                </a>
                <input type="hidden" name="field_id" id="field_id" value="<?php echo $id; ?>" />
                <input type="hidden" name="field_key" id="field_key" value="<?php if (!empty($row)) echo esc_attr($row->field_key); ?>" />
                <input type="hidden" name="ordering" id="ordering" value="<?php echo $ordering; ?>" />
<?php wp_nonce_field('save_pm_add_field'); ?>
                <input type="submit" value="<?php _e('Save','profile-magic'); ?>" name="submit_field" id="submit_field" onClick="return add_field_validation()"  />
                <div class="all_error_text" style="display:none;"></div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    pm_show_hide_field_option('<?php echo $str; ?>', 'field_options_wrapper');
</script>
