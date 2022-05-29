<?php

function delete_logged_in_user_profile_image_internal($b_force_delete_meta = false) {
    $user_ID = get_current_user_id();

    //code-notes delete the file

    //code-notes [image-sizing]  also delete the sized images
    $partial_image_path = get_user_meta($user_ID,'user_image',true);
    $b_deleted = false;
    if ($partial_image_path) {
        $wp_upload_dir      = wp_upload_dir();
        $base               = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR;
        $calculated_path          = $base.$partial_image_path;
        FreelinguistSizeImages::remove_associated_sizes_from_original_path($calculated_path);
        $real_path = realpath($calculated_path);
        if ($real_path) {
            if (is_writable($real_path)) {
                $b_what = unlink($real_path);
                if ($b_what) {
                    $b_deleted = true;
                }
                else{
                    will_send_to_error_log("delete_user_profile_image had issues deleting the file ",[
                        'base' => $base,
                        'partial_image_path' => $partial_image_path,
                        'calculated_path' => $calculated_path,
                        'real_path' => $real_path
                    ]);
                }
            } else {
                will_send_to_error_log("delete_user_profile_image cannot write to the file ",[
                    'base' => $base,
                    'partial_image_path' => $partial_image_path,
                    'calculated_path' => $calculated_path,
                    'real_path' => $real_path
                ]);
            }
        } else {
            will_send_to_error_log("delete_user_profile_image path not found ",[
                'base' => $base,
                'partial_image_path' => $partial_image_path,
                'calculated_path' => $calculated_path,
                'real_path' => $real_path
            ]);
        }


    }

    if ($b_force_delete_meta || $b_deleted) {
        delete_user_meta($user_ID, 'user_image');
        //code-notes update units
        FreelinguistUnitGenerator::generate_units($log,[$user_ID],[]);
    }



    return $b_deleted;
}