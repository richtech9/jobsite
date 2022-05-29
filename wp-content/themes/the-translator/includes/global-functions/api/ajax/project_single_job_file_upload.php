<?php



/*

 * Author Name: Lakhvinder Singh

 * Method:      project_single_job_file_upload

 *

 *

 */

add_action('wp_ajax_project_single_job_file_upload', 'project_single_job_file_upload');

/**
 * code-notes Only when the user is the project owner
 */
function project_single_job_file_upload(){
    global $wpdb;

    /*
     * current-php-code 2020-Oct-7
     * ajax-endpoint  project_single_job_file_upload
     * input-sanitized : project_id
     * when the project owner is adding instruction files, for a specific job, to a project
    */

    try {
        $response = null;
        $project_id = FLInput::get('project_id');

        $job_id = FLInput::get('job_id');

        $user_id = get_current_user_id();

        $sql_to_check = "SELECT ID 
                        FROM wp_posts p
                        WHERE p.post_author = $user_id AND ID = $project_id";
        $check_result = $wpdb->get_results($sql_to_check);
        will_throw_on_wpdb_error($wpdb,'project_single_job_file_upload checking permissions');
        if (empty($check_result)) {
            //code-notes just exit. This is a file handler library and its the best way. Log to notify for debugging
            throw new RuntimeException(
                "check failed for user id of '$user_id' being the owner");
        }


        $upload_dir = wp_upload_dir();


        $our_middle_path = UploadHandler::number_to_path_part_string($project_id, '/') . '/';

        $job_dirname = $upload_dir['basedir'] . '/' . $our_middle_path;

        $job_image_url = get_site_url() . '/' . $our_middle_path;

        $upload_handler = new UploadHandler([
            'upload_dir' => $job_dirname,
            'upload_url' => $job_image_url,
            'print_response' => false,
            UploadHandler::OPTION_WHITELIST => [
                FileUploadWhitelist::TYPE_PDF,  //we want to do additional filtering on pdf, so when this is set, the WP fallback does not handle it
                FileUploadWhitelist::IMAGE_TYPES, //process png,jpg ourselves
                FileUploadWhitelist::TYPE_TEXT_PLAIN
            ]
        ]);

        $response = $upload_handler->get_response();


        if (!empty($response['files'])) {

            foreach ($response['files'] as $f) {

                if (!isset($f->error)) {


                    $filename = $f->name;

                    $our_file_name = 'project-' . $project_id . '-job-' . $job_id . '-' . time() . '-' . UploadHandler::generate_random_safe_characters();
                    $extension = pathinfo($f->name, PATHINFO_EXTENSION);
                    $our_file_name_with_extension = $our_file_name . '.' . $extension;
                    $our_partial_path = $our_middle_path . $our_file_name_with_extension;
                    $our_full_path = $job_dirname . $our_file_name_with_extension;
                    $old_full_path = $job_dirname . '/' . $f->name;
                    //code-notes [file-paths]  changed the upload to use new path and format
                    //code-note rename file here to what we want, its already on the right path, but its easier to change it here than fight the upload handler
                    $b_rename_ok = rename($old_full_path, $our_full_path);
                    if (!$b_rename_ok) {
                        unlink($old_full_path);
                        throw new RuntimeException("project_single_job_file_upload failed to rename $old_full_path to $our_full_path ");

                    }

                    $wpdb->insert('wp_files',

                        array(

                            'job_id' => $job_id,

                            'post_id' => $project_id,

                            'file_name' => $filename,

                            'file_path' => $our_partial_path,

                            'by_user' => $user_id,

                            'type' => FLWPFileHelper::TYPE_INSTRUCTION_FILE,

                        ),

                        array('%d', '%s', '%s', '%s', '%d', '%f', '%d')

                    );
                    will_throw_on_wpdb_error($wpdb, 'project_single_job_file_upload inserting file project file upload');
                    $attach_id = $wpdb->insert_id;
                    $response['attach_id'] = $attach_id;

                    wp_send_json($response); exit();

                } else {
                    throw new RuntimeException($f->error);
                }

            }

        }else {
            throw new RuntimeException("No File Received");
        }

        wp_die(); //should never reach here
    } catch (Exception $e) {
        will_send_to_error_log('project_single_job_file_upload has error',will_get_exception_string($e));
        if (is_array($response)) {
            $json_reply = $response;
        } else {
            $json_reply = [];
        }
        $json_reply['status'] = false;
        $json_reply['message'] = $e->getMessage();
        wp_send_json($json_reply,510); exit; //will be caught by the file upload on error
    }

}