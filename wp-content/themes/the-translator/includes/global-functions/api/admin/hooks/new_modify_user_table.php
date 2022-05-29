<?php
add_filter('manage_users_columns', 'new_modify_user_table');
function new_modify_user_table($column)
{
    /*
    * current-php-code 2020-Jan-11
    * current-hook
    * input-sanitized :
    */

    $column[FreelinguistUserHelper::META_KEY_NAME_TAX_FORM] = 'Tax form';
    $column['display_name'] = 'Name';
    $column['view_link'] = 'View';
    return $column;
}

