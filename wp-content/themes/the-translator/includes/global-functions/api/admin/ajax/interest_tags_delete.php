<?php

add_action( 'wp_ajax_interest_tags_delete', 'interest_tags_delete' );

function interest_tags_delete(){
    /*
      * current-php-code 2021-Jan-11
      * ajax-endpoint  interest_tags_delete
      * input-sanitized : tag_id
      */


    if (!current_user_can('manage_options')) {
        exit;
    }
    global $wpdb;

    $tag_id_array_raw = FLInput::get('tag_id');
    $tag_id_array = [];
    foreach ($tag_id_array_raw as $what) {
        if (intval($what)) {$tag_id_array = (int)$what;}
    }
    if(count($tag_id_array)){
        $tag_idIn = implode(',',$tag_id_array);

        $getTags = (int)$wpdb->get_var("SELECT count(id) FROM wp_interest_tags WHERE ID IN($tag_idIn)");

        if($getTags){
            $isDone = $wpdb->query(  "DELETE FROM wp_interest_tags WHERE ID IN($tag_idIn)" );

            if($isDone){
                $response = array('status'=>1,'message'=>'Deleted Successfully');
            } else {
                $response = array('status'=>0,'message'=>'Try Again.');
            }
        } else {
            $response = array('status'=>0,'message'=>'Invalid Tags');
        }

    } else {
        $response = array('status'=>0,'message'=>'Invalid Tags');
    }
    wp_send_json($response);
}