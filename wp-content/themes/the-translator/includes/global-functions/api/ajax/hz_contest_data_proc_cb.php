<?php
add_action( 'wp_ajax_hz_contest_data_proc',  'hz_contest_data_proc_cb'  );

/**
 * code-notes only allow if user is the owner of the job
 */
function hz_contest_data_proc_cb(){

    global $wpdb;

    /*
     * current-php-code 2020-Oct-5
     * ajax-endpoint  hz_contest_data_proc
     * input-sanitized : project_id
     * when the contest owner is adding general instruction files (for all participants).
     * This is used when the customer is on the first edit screen (job details) or is viewing a winning proposal
    */

    $project_id     = (int)FLInput::get('project_id');

    //code-notes if the proposal id is included at it, to mark it as instructions for that proposal only
    //code-notes [contest customer private instructions]  now saving proposal id for making private instructions
    $proposal_id     = (int)FLInput::get('proposal_id');
    if (!$proposal_id) {
        $proposal_id = null; //if no proposal id here, it will have been cast to zero, but we want it as null to insert
    }
    $response = null;

    try {
        $user_id = get_current_user_id();

        $sql_to_check = "SELECT ID 
                        FROM wp_posts p
                        WHERE p.post_author = $user_id AND ID = $project_id";
        $check_result = $wpdb->get_results($sql_to_check);
        will_throw_on_wpdb_error($wpdb,'hz_contest_data_proc checking permissions');
        if (empty($check_result)) {
            throw new RuntimeException("No Permission for User $user_id to Upload File Here");
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

                    $our_file_name = 'contest-' . $project_id . '-' . time() . '-' . UploadHandler::generate_random_safe_characters();
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
                        throw new RuntimeException("hz_contest_data_proc failed to rename $old_full_path to $our_full_path ");
                    }

                    $filename = $f->name;

                    $wpdb->insert('wp_files',
                        array(
                            'post_id' => $project_id,
                            'proposal_id' => $proposal_id,
                            'file_name' => $filename,
                            'file_path' => $our_partial_path,
                            'by_user' => $user_id,
                            'type' => FLWPFileHelper::TYPE_INSTRUCTION_FILE
                        ),
                        array('%d', '%s', '%s', '%d', '%d'));

                    will_throw_on_wpdb_error($wpdb,'hz_contest_data_proc inserting new row');
                    $attach_id = $wpdb->insert_id;
                    $response['attach_id'] = $attach_id;

                    wp_send_json($response); exit();

                } else {
                    throw new RuntimeException($f->error);

                }

            }

        }  else {
            throw new RuntimeException("No File Received");
        }

        wp_die(); //should never reach here
    } catch (Exception $e) {
        will_send_to_error_log('hz_contest_data_proc has error',will_get_exception_string($e));
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

