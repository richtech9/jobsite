<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      selected_file_remove

 * Description: selected_file_remove

 *

 */

add_action('wp_ajax_selected_file_remove', 'selected_file_remove');

function selected_file_remove(){

    /*
    * current-php-code 2020-Sep-30
    * ajax-endpoint  selected_file_remove
    * input-sanitized : attach_id, date
    */

    $attach_id = FLInput::get('attach_id') ; //The parent ID of our attachments



    global $wpdb;

    $wp_upload_dir  = wp_upload_dir();


    $user_ID = get_current_user_id();

    $files      = $wpdb->get_results(
        "SELECT * FROM wp_files WHERE id = $attach_id AND by_user = $user_ID" );

    if(empty($files)) {
        $ret = [
          'status' =>false,
          'message' => "no files found with id of $attach_id and user $user_ID"  ,
          'results' => []
        ];
    } else {
        $ret = [
            'status' => true,
            'message' => "found with id of $attach_id and user $user_ID",
            'results' => []
        ];
        for ($i = 0; $i < count($files); $i++) {
            if (true) {
                $file_path = $wp_upload_dir['basedir'] . '/' . $files[$i]->file_path;
                $b_unlinked = unlink($file_path);
                $wpdb->delete('wp_files', array('id' => $files[$i]->id));
                $ret['results'][] = [
                    'file_path' => $file_path,
                    'deleted' => $wpdb->rows_affected,
                    'unlinked' => $b_unlinked
                ];
                if (!$b_unlinked) {
                    will_send_to_error_log("selected_file_remove: Could not unlink ", $file_path, false, true);
                }

                if (!(int)($wpdb->rows_affected)) {
                    will_send_to_error_log("selected_file_remove: Could not delete ", $wpdb->last_query);
                }
            }
        }
    }

    wp_send_json($ret); //dies


}