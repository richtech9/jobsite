<?php

add_action( 'wp_ajax_hz_submit_report',  'hz_submit_report_cb'  );

/******** submit report ******/

 function hz_submit_report_cb(){
    //code-bookmark this is where reports get added after clicking the report button
    global $wpdb;
     /*
      * current-php-code 2020-Oct-12
      * ajax-endpoint  hz_submit_report_cb
      * input-sanitized : {all}
      */


    $data = FLInput::copy_and_clean_all_post();
    unset($data['action']);
    //code-notes unsetting new post values, from form form protection, to avoid sql error
    unset($data['_form_key']);
    unset($data['_wpnonce']);
    unset($data['_form_security_name']);

    $insert = $wpdb->insert('wp_reports',$data);
    if($insert){
        echo "success";
        exit;
    }

    else{
        echo $wpdb->last_error ;
        exit;
    }
}