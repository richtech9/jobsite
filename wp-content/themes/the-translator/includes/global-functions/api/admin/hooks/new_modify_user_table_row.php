<?php

add_filter('manage_users_custom_column', 'new_modify_user_table_row', 10, 3);

function new_modify_user_table_row($val, $column_name, $user_id)
{
    $lang = FLInput::get('lang','en');
    /*
    * current-php-code 2020-Jan-11
    * current-hook
    * input-sanitized :
    */

    switch ($column_name) {
        case FreelinguistUserHelper::META_KEY_NAME_TAX_FORM :
            $tax_arr = get_the_author_meta(FreelinguistUserHelper::META_KEY_NAME_TAX_FORM, $user_id);
            if (empty($tax_arr)) {
                return 'Not exist';
            } else {
                $tax_arr = explode('/', $tax_arr);
                $upload_dir = wp_upload_dir();
                $user_dirname = $upload_dir['baseurl'];
                $file_path = get_user_meta($user_id, FreelinguistUserHelper::META_KEY_NAME_TAX_FORM, true);
                $file = $user_dirname . '/' . $file_path;
                $signedform_name = '<a class="downloaed_form" href="' . $file . '" target="_blank">' . $tax_arr[count($tax_arr) - 1] . '</a>';
                return $signedform_name;
            }
            break;
        case 'display_name' :
            $user_info = get_userdata($user_id);
            return $user_info->display_name;
            break;
        case 'view_link':
            {
                $user_info = get_userdata($user_id);
                $href = site_url() . "/user-account/?lang=$lang&profile_type=translator&user=" . $user_info->user_nicename;
                return "<a href='$href' target='_blank'>View</a>";
            }
        default:
    }
    return $val;
}