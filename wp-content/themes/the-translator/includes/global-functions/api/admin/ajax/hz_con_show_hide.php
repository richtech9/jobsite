<?php

add_action( 'wp_ajax_hz_con_show_hide', 'hz_con_show_hide_cb'  );

 function hz_con_show_hide_cb(){

     /*
        * current-php-code 2021-Jan-11
        * ajax-endpoint  hz_con_delete
        * input-sanitized : content_id
        */

     global $wpdb;
     if (!current_user_can('manage_options')) {
         exit;
     }

     $content_id = (int)FLInput::get('content_id');
     $show_content = (int)FLInput::get('show_content');


    $result  = $wpdb->query(
        "update wp_linguist_content set show_content=$show_content where id=$content_id");


    if($result){
        echo $show_content;

    }else{
        echo "fail";

    }
    exit();

}