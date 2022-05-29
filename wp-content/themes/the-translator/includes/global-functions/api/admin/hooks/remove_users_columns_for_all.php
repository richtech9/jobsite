<?php

add_filter('manage_users_columns','remove_users_columns_for_all');


function remove_users_columns_for_all($column_headers) {
    /*
    * current-php-code 2020-Jan-15
    * current-hook
    * input-sanitized :
    */
    unset($column_headers['name']);
    return $column_headers;
}