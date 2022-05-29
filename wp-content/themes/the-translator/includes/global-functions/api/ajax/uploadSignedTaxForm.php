<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      uploadSignedTaxForm

 * Description: uploadSignedTaxForm

 *

 */

add_action('wp_ajax_uploadSignedTaxForm', 'uploadSignedTaxForm');


function uploadSignedTaxForm()
{
    /*
    * current-php-code 2020-Dec-12
    * ajax-endpoint  uploadSignedTaxForm
    * input-sanitized :
    */

    $new_file_path = null;
    try {
        if ($_SERVER['REQUEST_METHOD'] !== "POST") { throw new LogicException("Needs to be post");}
        if (! array_key_exists('files',$_FILES) ) { throw new InvalidArgumentException("No files entry found here"); }
        $max_image_upload = 1220; //Define how many images can be uploaded to the current post
        

        $user_id = get_current_user_id();
        $path = FreelinguistUserHelper::get_user_directory_and_make_it_if_not_exists($user_id,$relative_folder_path);


        //Check if user is trying to upload more than the allowed number of images for the current post

        if ((count($_FILES['files']['name'])) > $max_image_upload) {
            throw new RuntimeException( "Sorry you can only upload " . $max_image_upload . " files");
        }



        foreach ($_FILES as $file_thing) {
            will_send_to_error_log('file thing is ',$file_thing);
            $error_code = $file_thing['error'][0];
            $file_size = (int)$file_thing['size'][0];
            $orginal_filename = $file_thing['name'][0];
            $file_path = $file_thing['tmp_name'][0];
            $extension = pathinfo($orginal_filename, PATHINFO_EXTENSION);

            if ($file_size <= 0) {throw new RuntimeException("File did not have any content");}

            if ($error_code) {
                if (array_key_exists($error_code,UploadHandler::ERROR_MESSAGES)) {
                    throw new RuntimeException(UploadHandler::ERROR_MESSAGES[$error_code]);
                } else {
                    throw new RuntimeException(UploadHandler::ERROR_MESSAGES['unknown_error']);
                }
            }
            $b_ok = FileUploadWhitelist::validate_file(
                $file_path,
                $orginal_filename,
                [FileUploadWhitelist::IMAGE_TYPES,FileUploadWhitelist::TYPE_PDF],
                $mime_type,

                $valid_extension,
                $new_file_path
            );
            if (!$b_ok) {
                throw new RuntimeException( "The mime type of $mime_type is not allowed when uploading tax form" . $orginal_filename);
            }

            $file_to_move = $file_path;
            if ($new_file_path) {$file_to_move = $new_file_path;}

            $base_file_name = basename($orginal_filename, '.' . $extension);
            $new_filename = $base_file_name . "_" . time() . '.' . $extension;
            $full_new_path = $path .'/'. $new_filename;

            will_send_to_error_log('file debug',[
                'temp path' =>  $file_path,
                'new path' => $new_file_path,
                'file from ' => $file_to_move,
                'copy to here' => $full_new_path,
            ]);

            if (!is_readable($file_to_move)) {
                throw new RuntimeException("Cannot read from the file $file_to_move");
            }

            if (!is_writable($path)) {
                throw new RuntimeException("Cannot write from the directory $path");
            }

            if (copy($file_to_move, $full_new_path)) {

                $relative_path_with_file = $relative_folder_path . $new_filename;
                //Insert attachment to the database


                FLWPFileHelper::remove_user_tax_file($user_id,true);

                update_user_meta($user_id, FreelinguistUserHelper::META_KEY_NAME_TAX_FORM, $relative_path_with_file);


            } else {
                throw new RuntimeException("Could not move uploaded file from $file_to_move to $full_new_path");
            }


        } //end foreach file


        $json_reply = [];
        $json_reply['status'] = true;
        $json_reply['message'] = 'Uploaded Tax Form';
        wp_send_json($json_reply); exit;
    }  catch (Exception $e) {
        will_send_to_error_log('uploadSignedTaxForm has error',will_get_exception_string($e));

        $json_reply = [];
        $json_reply['status'] = false;
        $json_reply['message'] = $e->getMessage();
        wp_send_json($json_reply); exit;
    } finally {
        if ($new_file_path) {unlink($new_file_path);} //if a temp file was made during the whitelist, remove it no matter what at the end
    }

}