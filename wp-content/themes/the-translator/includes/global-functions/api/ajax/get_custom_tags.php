<?php
//moved from functions.php

add_action( 'wp_ajax_nopriv_get_custom_tags', 'get_custom_tags' );
add_action( 'wp_ajax_get_custom_tags', 'get_custom_tags' );

function get_custom_tags() {
    /*
    * current-php-code 2020-Sep-30
    * ajax-endpoint  get_custom_tags
    * input-sanitized : query
    */
    global $wpdb;
    $tag = FLInput::get('query');

    $getTags = $wpdb->get_results("SELECT * FROM wp_interest_tags WHERE (tag_name like '%{$tag}%')",ARRAY_A);
    $countryResult = array();
    foreach($getTags as $tag){
        $countryResult[] = array('name'=>$tag['tag_name'],'id'=>$tag['ID']);
    }
    echo json_encode($countryResult);
    exit();
}