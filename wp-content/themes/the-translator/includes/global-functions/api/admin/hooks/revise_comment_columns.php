<?php

add_filter('manage_edit-comments_columns', 'revise_comment_columns');


function revise_comment_columns($columns)
{
    /*
    * current-php-code 2020-Jan-11
    * current-hook
    * input-sanitized :
    */

    return array_merge($columns, array(
        'revised_on' => __('Revised On'),
        'revised_by' => __('Revised By'),
    ));
}

