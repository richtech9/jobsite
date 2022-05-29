<?php

add_action( 'wp_ajax_freelinguist_get_file_download_url',  'freelinguist_get_file_download_url'  );

/**
 * code-notes the job can only be hidden by the owner of the job
 * gets the url of the file , if the user is allowed to use it
 */
function freelinguist_get_file_download_url(){
    /*
    * current-php-code 2020-Oct-1
    * ajax-endpoint  freelinguist_get_file_download_url
    * input-sanitized : content_file_id, job_file_id
    */
    global $wpdb;
    try {

        $job_file_id = (int)FLInput::get('job_file_id');
        $content_file_id = (int)FLInput::get('content_file_id');


        $user_id = get_current_user_id();

        $url_fragment = check_user_download_permissions($user_id,$job_file_id,$content_file_id);

        if ($url_fragment === false) {
            throw new RuntimeException("Either invalid permissions or wrong file id");
        }

        if (empty($url_fragment)) {
            throw new RuntimeException("Missing file path");
        }



        $upload_dir = wp_upload_dir();

        $baseurl = $upload_dir['baseurl'];
        $url_to_download = $baseurl.'/'.$url_fragment;

        $base_dir = $upload_dir['basedir'];
        $full_path_to_download = $base_dir.DIRECTORY_SEPARATOR.$url_fragment;

        if (!is_readable($full_path_to_download)) {
            throw new RuntimeException("The file $url_to_download is either missing or not readable");
        }

        if(is_user_logged_in() && (xt_user_role() == "customer") && $job_file_id){
            $sql = "UPDATE wp_files SET last_downloaded_time = NOW() WHERE ID = $job_file_id ";
            $wpdb->query( $sql );

        }

        if(is_user_logged_in() && (xt_user_role() == "customer") && $content_file_id){
            $sql = "UPDATE wp_content_files SET last_downloaded_time = NOW() WHERE ID = $content_file_id ";
            $wpdb->query( $sql );

        }


        wp_send_json( ['status' => true, 'message' => 'Here is the file link','url'=>$url_to_download]);

    } catch (Exception $e) {
        will_send_to_error_log('get download url',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage(),'url'=>NULL]);
    }



}