<?php

add_action( 'wp_ajax_hz_reportAdminnote_save', 'hz_reportAdminnote_save_cb'  );


function hz_reportAdminnote_save_cb(){
    /*
         * current-php-code 2021-Jan-15
         * ajax-endpoint  hz_reportAdminnote_save
         * input-sanitized : dbId,partial
         */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }

    $dbId = (int)FLInput::get('dbId');
    $admin_comment =  FLInput::get('value','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);



    $wpdb->update( 'wp_reports', array('admin_comment'=>$admin_comment,'processed_by'=>get_current_user_id(),'status'=>'processed'), array('id'=>$dbId) );

    echo "succees";
    wp_die();


}


