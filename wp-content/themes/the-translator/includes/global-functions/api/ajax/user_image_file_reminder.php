<?php

add_action('wp_ajax_user_image_file_reminder', 'user_image_file_reminder');


function user_image_file_reminder(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  user_image_file_reminder
    * input-sanitized :
    */

    try {

        $max_file_size = 1024 * 2500; //in kb

        $path = FreelinguistUserHelper::get_user_directory_and_make_it_if_not_exists(get_current_user_id(), $relative_path) . '/';


        delete_logged_in_user_profile_image_internal(true);

        $name = $_FILES['user_image']['name'];

        $extension = pathinfo($name, PATHINFO_EXTENSION);

        $new_filename = "user-" . get_current_user_id() . "-" . time() . '-' .
            UploadHandler::generate_random_safe_characters() . '.' . $extension;

        try {
            $b_ok = FileUploadWhitelist::validate_file(
                $_FILES['user_image']['tmp_name'],
                $_FILES['user_image']['name'],
                [FileUploadWhitelist::IMAGE_TYPES],
                $mime_type,

                $valid_extension,
                $new_file_path
            );
            if (!$b_ok) {
                throw new RuntimeException( "The mime type of $mime_type is not allowed when uploading " . $_FILES['files']['name']);
            }
        } catch (FileUploadWhitelistException $ew) {
            throw $ew;
        }


        if ($_FILES['user_image']['size'] > $max_file_size) {

            throw new RuntimeException( get_custom_string_return('Image is too large'));


        }  else {
            $original_file_path = $path . $new_filename;
            if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $original_file_path)) {
                try {
                    //code-notes [image-sizing]  where to put the image size converter
                    $sizer = new FreelinguistSizeImages($original_file_path);
                    will_do_nothing($sizer);
                } catch (Exception $e) {
                    will_send_to_error_log('sizer has growth pains', $e);
                    throw $e;
                }
                $user_ID = get_current_user_id();

                $relative_path_with_file = $relative_path . $new_filename;
                update_user_meta($user_ID, 'user_image', $relative_path_with_file);

                //code-notes update units
                FreelinguistUnitGenerator::generate_units($log, [$user_ID], []);

                wp_send_json( ['status' => true, 'message' => 'Image Uploaded']);
                exit;

            } else {
                throw new RuntimeException("Cannot move upload file to $original_file_path");
            }

        }

    } catch (Exception $e) {
        will_send_to_error_log('user_image_file_reminder',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}