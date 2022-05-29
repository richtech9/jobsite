<?php


function modify_this_site_query_string($key,$value) {
    /*
     * current-php-code 2020-Oct-16
     * internal-call
     * input-sanitized :
     */

    $url = get_site_url() .  $_SERVER["REQUEST_URI"];
    $new_url =  add_query_arg(  [$key => $value], $url);
    return $new_url;
}