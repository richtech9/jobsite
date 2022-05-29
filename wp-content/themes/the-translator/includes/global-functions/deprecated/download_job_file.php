<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      download_job_file

 * Description: download_job_file

 *

 */

add_action( 'send_headers', 'download_job_file' );

/**
 * @deprecated
 */
function download_job_file(){

     /*
       * current-php-code 2020-December-10
       * ajax-endpoint  download_job_file  (not a true ajax)
       * public-api
       * input-sanitized : action, attach_id
       */

    global $wpdb;

    $action = FLInput::get('action');

    $attach_id = (int)FLInput::get('attach_id'); //wp_files id
    $user_id = get_current_user_id();


    if($action && $user_id && $attach_id){

        if($action == 'download_job_file'){

            $actual_file_partial_path = check_user_download_permissions($user_id,$attach_id,NULL);
            if (empty($actual_file_partial_path)) {die('no permissions or does not exist');}
            $upload_dir = wp_upload_dir();

            $baseurl = $upload_dir['baseurl'];
            $file = $baseurl.'/'.$actual_file_partial_path;
            $file_url = $baseurl.'/'.$actual_file_partial_path;



            if(file_exists($file) && $file_url) {

                if(is_user_logged_in() && (xt_user_role() == "customer") && $attach_id){
                    $sql = "UPDATE wp_files SET last_downloaded_time = NOW() WHERE ID = $attach_id ";
                    $wpdb->query( $sql );

                }

                if(is_user_logged_in() && (xt_user_role() == "customer") && $attach_id){
                    $sql = "UPDATE wp_content_files SET last_downloaded_time = NOW() WHERE ID = $attach_id ";
                    $wpdb->query( $sql );

                }

                echo '<a href="'.$file_url.'" id="download_file" style="display:none;" target="_blank" download>';
                echo '<script>  document.getElementById("download_file").click(); 
                setTimeout(function(){
                   window.close();
                   },3000);


                   </script>';
                exit();

            }


        }

    }

}