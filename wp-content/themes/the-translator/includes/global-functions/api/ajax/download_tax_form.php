<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      download_tax_form

 * Description: download_tax_form

 *

 */

add_action( 'send_headers', 'download_tax_form' );

function download_tax_form(){

    /*
      * current-php-code 2021-feb-1
      * ajax-endpoint  download_tax_form (not real ajax)
      * input-sanitized : action
      */
    $action = FLInput::get('action');
    $user_id = get_current_user_id();

    if($action && $user_id){

        if($action == 'download_tax_form'){

            $upload_dir = wp_upload_dir();

            $user_dirname = $upload_dir['basedir'];

            $file_path = get_user_meta($user_id,FreelinguistUserHelper::META_KEY_NAME_TAX_FORM,true);
            $file = null;
            if ($file_path) {
                $file = $user_dirname.'/'.$file_path;
            }


            switch(strtolower(substr(strrchr($file_path, '.'), 1))) {

                case 'pdf': $mime = 'application/pdf'; break;

                case 'zip': $mime = 'application/zip'; break;

                case 'jpeg':

                case 'jpg': $mime = 'image/jpg'; break;

                default: $mime = 'application/force-download';

            }

            //echo $mime; exit;

            if($file && file_exists($file)) {

                //header("Content-type:application/txt");

                $user_agent = getenv("HTTP_USER_AGENT");

                if(strpos($user_agent, "Mac") !== FALSE)    // MAc OS

                {

                    header("Pragma: public");

                    header("Expires: 0");

                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

                    header("Content-disposition: attachment; filename=".basename($file));

                    header("Cache-Control: private",false);

                    //header("Content-Type: application/$ext");

                    header('Content-Type: '.$mime);

                    readfile($file);

                    exit();

                }else{

                    header('Content-Description: File Transfer');

                    header('Content-Disposition: attachment; filename='.basename($file));

                    header('Content-Transfer-Encoding: binary');

                    header('Expires: 0');

                    header('Pragma: no-cache');

                    header('Content-Length: ' . filesize($file));

                    readfile($file);

                    exit();

                }



            }

        }

    }

}