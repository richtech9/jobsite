<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      remove_all_files

 * Description: remove_all_files

 *

 */

add_action('wp_ajax_remove_all_files', 'remove_all_files');


function remove_all_files(){
        /*
        * current-php-code 2020-Oct-6
        * ajax-endpoint  remove_all_files
        * input-sanitized :
        */

    global $wpdb;

    $user_ID = get_current_user_id();

    $wp_upload_dir  = wp_upload_dir();

    $files = $wpdb->get_results("SELECT * FROM wp_files WHERE by_user = $user_ID and status = 0" );

    $ret = [
      'removed_attachment_ids' => []
    ];
    for($i=0;$i<count($files);$i++){


        $ret['removed_attachment_ids'][]= $files[$i]->id;
        unlink($wp_upload_dir['basedir'].'/'.$files[$i]->file_path);


        $wpdb->delete(  'wp_files',  array( 'id' => $files[$i]->id ) );

    }


    wp_send_json($ret); //dies here
    die(); //to make this easier to trace

}