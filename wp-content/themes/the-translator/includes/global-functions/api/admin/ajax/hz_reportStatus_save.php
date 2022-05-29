<?php

add_action( 'wp_ajax_hz_reportStatus_save',  'hz_reportStatus_save_cb'  );



function hz_reportStatus_save_cb(){
    /*
        * current-php-code 2021-Jan-15
        * ajax-endpoint  hz_reportStatus_save
        * input-sanitized : dbId,partial
        */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }

    $dbId = (int)FLInput::get('dbId');
    $selStatus = FLInput::get('selStatus');

    $wpdb->update( 'wp_reports', array('status'=>$selStatus,'processed_by'=>get_current_user_id()), array('id'=>$dbId) );

    echo "succees";
    wp_die();


}
