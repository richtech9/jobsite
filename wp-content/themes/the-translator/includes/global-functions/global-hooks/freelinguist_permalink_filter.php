<?php

/*
 * current-php-code 2021-Jan-17
 * current-hook
 * input-sanitized :
 */

if (!function_exists('icl_get_languages') ) {
    //code-notes when disabling the WPML Multilingual CMS need to still add the default language to the urls, so not to change old code
    add_filter('page_link', 'freelinguist_permalink_filter', 1, 2);
    add_filter('post_link', 'freelinguist_permalink_filter', 1, 2);
    add_filter('post_type_link', 'freelinguist_permalink_filter', 1, 2);
}

function freelinguist_permalink_filter( $link, $post ) {
    if ( ! $post ) {
        return $link;
    }

    if (!is_string($link)) {return $link;}

    if (parse_url($link, PHP_URL_QUERY)) {
        return $link;
    }

    $link = rtrim($link,'/?');
    return $link.'/?lang=en';

}