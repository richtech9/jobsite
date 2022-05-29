<?php


add_filter('manage_comments_custom_column', 'revise_comment_column', 10, 2);

function revise_comment_column($column, $comment_ID)
{

    /*
    * current-php-code 2020-Jan-15
    * current-hook
    * input-sanitized :
    */

    switch ($column) {
        case 'revised_on':
            if ($meta = get_comment_meta($comment_ID, 'revised_on', true)) {
                echo $meta;
            } else {
                echo '-';
            }
            break;
        case 'revised_by':
            $user_id = get_comment_meta($comment_ID, 'revised_by', true);
            if ($user_id) {
                $user_data = get_userdata($user_id);
                echo $user_data->display_name;
            } else {
                echo '-';
            }
            break;
    }
}

