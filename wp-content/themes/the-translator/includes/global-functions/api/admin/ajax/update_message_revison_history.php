<?php

/*
 * Author Name: Lakhvinder Singh
 * Method:      update_message_revison_history
 * Description: update_message_revison_history
 *
 */
add_action('wp_ajax_update_message_revison_history', 'update_message_revison_history');
function update_message_revison_history(){

    /*
       * current-php-code 2021-Jan-11
       * ajax-endpoint  update_message_revison_history
       * input-sanitized : comment_id
       */


    if (!current_user_can('manage_options')) {
        exit;
    }
    global $wpdb;
    $comment_id = (int)FLInput::get('comment_id');
    if($comment_id){

        update_comment_meta( $comment_id, 'revised_on', current_time( 'mysql' ) );
        update_comment_meta( $comment_id, 'revised_by', get_current_user_id() );
        echo $comment_id;
        exit;
    }else{
        echo 'false';
        exit;
    }
}