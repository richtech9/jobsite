<?php
add_action( 'wp_ajax_hz_content_freeze',  'hz_content_freeze'  );
function hz_content_freeze(){

     /*
        * current-php-code 2021-Jan-11
        * ajax-endpoint  hz_content_freeze
        * input-sanitized : dbId
        */

    global $wpdb;
    if ( !current_user_can( 'manage_options' ) ) {  exit;}

    $dbId = (int)FLInput::get('dbId');



    $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
        "SELECT * FROM wp_dispute_cases WHERE `ID` = %d", array($dbId)), ARRAY_A);


    $contentId = (int)$result['content_id'];



    if($contentId){

        $wpdb->update( 'wp_linguist_content', array('freezed'=>1,'updated_at'=>date('Y-m-d H:i:s')), array('id'=>$contentId) );

        $execut = $wpdb->query( $wpdb->prepare( /** @lang text */
            "UPDATE wp_dispute_cases SET freeze_job = %d  WHERE content_id = %d", 1,   $contentId ) );


        if($execut){ echo 'Updated'; } else{ echo 'Could not update';}

        wp_die();

    }else{
        echo 'Could not update';
        wp_die();
    }
}