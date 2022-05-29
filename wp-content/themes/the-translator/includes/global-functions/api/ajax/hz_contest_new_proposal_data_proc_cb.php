<?php

add_action( 'wp_ajax_hz_contest_new_proposal_data_proc',  'hz_contest_new_proposal_data_proc_cb' );

/**
 * When the freelancer is making a  new proposal
 */
function hz_contest_new_proposal_data_proc_cb(){

    /*
     * current-php-code 2020-Oct-10
     * ajax-endpoint  hz_contest_new_proposal_data_proc
     * input-sanitized : project_id
    */

    global $wpdb;
    $response = null;


    try {
        $project_id = (int)FLInput::get('project_id');
        //code-notes protecting by making sure the user has entered the contest first
        $user_id = get_current_user_id();
        $is_participating = FLPostLookupDataHelpers::is_user_participant_in_contest($user_id, $project_id);
        if (!$is_participating) {
            throw new RuntimeException("No Permission for User $user_id to Upload File Here");
        }


        $upload_dir = wp_upload_dir();
        $path_to_undo = null;

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


        $ret = [];

        if (!empty($response['files'])) {

            foreach ($response['files'] as $f) {

                $filename = '';
                if (property_exists($f, 'name')) {
                    $filename = $f->name;
                }

                $proposal_id = null;
                $file_id = null;
                $file_path = null;
                try {
                    if (!isset($f->error)) {


                        $extension = pathinfo($f->name, PATHINFO_EXTENSION);
                        $our_file_name = 'contest-' . $project_id . '-' . time() . '-' . UploadHandler::generate_random_safe_characters();
                        $our_file_name_with_extension = $our_file_name . '.' . $extension;
                        $our_partial_path = $our_middle_path . $our_file_name_with_extension;
                        $our_full_path = $job_dirname . $our_file_name_with_extension;
                        $old_full_path = $job_dirname . '/' . $f->name;
                        $path_to_undo = $old_full_path;
                        //code-notes [file-paths]  changed the upload to use new path and format
                        //code-note rename file here to what we want, its already on the right path, but its easier to change it here than fight the upload handler
                        $b_rename_ok = rename($old_full_path, $our_full_path);
                        if (!$b_rename_ok) {
                            will_send_to_error_log("hz_contest_new_proposal_data_proc failed to rename $old_full_path to $our_full_path ");
                            unlink($old_full_path);
                            die();
                        }
                        $path_to_undo = $our_full_path;

                        $data_to_save = [

                            'file_name' => $filename,
                            'file_path' => $our_partial_path,

                        ];
                        $escaped_file_name = FLInput::clean_data_key($data_to_save, 'file_name', '',
                            FLInput::YES_I_WANT_CONVESION, FLInput::YES_I_WANT_DB_ESCAPING,
                            FLInput::NO_HTML_ENTITIES);

                        $escaped_file_path = FLInput::clean_data_key($data_to_save, 'file_path', '',
                            FLInput::YES_I_WANT_CONVESION, FLInput::YES_I_WANT_DB_ESCAPING,
                            FLInput::NO_HTML_ENTITIES);

                        $file_type = FLWPFileHelper::TYPE_FREELANCER_UPLOAD;
                        $wpdb->query("INSERT INTO wp_files (post_id,file_name,file_path,by_user,type)
                              VALUES (
                              $project_id,
                              '$escaped_file_name',
                              '$escaped_file_path',
                              $user_id,
                             $file_type 
                              )");
                        will_throw_on_wpdb_error($wpdb);

                        $file_id = $wpdb->insert_id;
                        if (!$file_id) {
                            throw new RuntimeException("Could not insert file");
                        }


                        //code-notes insert the new proposal, and get the id, and associate the file we just saved with that

                        $sql_statement =
                            "select MAX(number) as number_count from wp_proposals where post_id= $project_id AND by_user =  $user_id";
                        $latest_number = $wpdb->get_row($sql_statement);
                        will_throw_on_wpdb_error($wpdb,'hz_contest_new_proposal_data_proc select max proposals');

                        $number_count = intval($latest_number->number_count) + 1;

                        $proposal_type = FLWPFileHelper::TYPE_FREELANCER_UPLOAD;
                        $sql_statement = "INSERT INTO wp_proposals (post_id,by_user,type,number) VALUES (
                                          $project_id,
                                          $user_id,
                                          $proposal_type, 
                                          $number_count
                                          )";
                        $wpdb->query($sql_statement);
                        will_throw_on_wpdb_error($wpdb,' hz_contest_new_proposal_data_proc insert new proposal');
                        $proposal_id = $wpdb->insert_id;

                        if (!$proposal_id) {
                            throw new RuntimeException("Could not insert proposal");
                        }

                        //code-notes rename file with the newly generated proposal id
                        $new_file_name_with_proposal = 'contest-' . $project_id . '-proposal-' . $proposal_id . '-initial-' . time() . '-' .
                            UploadHandler::generate_random_safe_characters() . '.' . $extension;

                        $new_partial_path_with_proposal = $our_middle_path . $new_file_name_with_proposal;
                        $new_full_path_with_proposal = $upload_dir['basedir'] . '/' . $new_partial_path_with_proposal;

                        $b_rename_ok = rename($our_full_path, $new_full_path_with_proposal);
                        if (!$b_rename_ok) {
                            will_send_to_error_log("hz_contest_new_proposal_data_proc failed to rename $old_full_path to $our_full_path ");
                            throw new RuntimeException("new proposal could not move the file from $our_full_path to $new_full_path_with_proposal ");
                        }
                        $path_to_undo = $new_full_path_with_proposal;

                        $sql_statement = "UPDATE wp_files 
                                      SET proposal_id = $proposal_id, file_path = '$new_partial_path_with_proposal' 
                                      WHERE id = $file_id";
                        $wpdb->query($sql_statement);
                        will_throw_on_wpdb_error($wpdb,'hz_contest_new_proposal_data_proc new row');
                        if ($wpdb->rows_affected === 0) {
                            throw new RuntimeException("Could not set file to the proposal");
                        }

                        $ret[] = [
                            'status' => true,
                            'filename' => $filename,
                            'message' => 'ok',
                            'file_id' => $file_id,
                            'proposal_id' => $proposal_id
                        ];


                    } else {
                        throw new RuntimeException($f->error);
                    }
                } catch (Exception $err) {
                    will_send_to_error_log("New proposal has issues", will_get_exception_string($err));
                    $error_message = $err->getMessage();
                    //if file then unlink it and remove it from db
                    if ($path_to_undo) {
                        try {
                            $real_file_path = realpath($path_to_undo);

                            if (!$real_file_path) {
                                throw new RuntimeException("File $real_file_path does not exist");
                            }

                            if (!is_readable($real_file_path)) {
                                throw new RuntimeException("File $real_file_path is not readable");
                            }
                            if (!is_writable($real_file_path)) {
                                throw new RuntimeException("File $real_file_path is not writable");
                            }
                            $what = unlink($real_file_path);

                            if (!$what) {
                                throw new RuntimeException("File $real_file_path cannot be deleted");
                            }
                        } catch (Exception $e) {
                            $error_message .= "\n " . $e->getMessage();
                        }
                    }

                    if ($file_id) {
                        try {
                            $sql = "DELETE FROM wp_files WHERE id = $file_id";
                            $what = (int)$wpdb->query($sql);
                            will_throw_on_wpdb_error($wpdb);

                            if (!$what) {
                                throw new RuntimeException("Could not remove file $file_id from db");
                            }
                        } catch (Exception $e) {
                            $error_message .= "\n " . $e->getMessage();
                        }
                    }

                    if ($proposal_id) {
                        try {
                            $sql = "DELETE FROM wp_proposals where ID = $proposal_id";
                            $what = (int)$wpdb->query($sql);
                            will_throw_on_wpdb_error($wpdb);

                            if (!$what) {
                                throw new RuntimeException("Could not remove proposal $proposal_id from db");
                            }
                        } catch (Exception $e) {
                            $error_message .= "\n " . $e->getMessage();
                        }
                    }


                    //if proposal then delete it

//                will_send_to_error_log('exception in hz_contest_new_proposal_data_proc_cb',[
//                   will_get_exception_string($err),
//                   $f
//                ]);
                    throw new RuntimeException($error_message);
                }//end inner catch

            } //end foreach files


        }//end if not empty files

        $outer_ret = ['status' => true, 'message' => 'ok upload', 'data' => []];
        //set overall status here, only one file is processed at a time, so this works
        foreach ($ret as $r) {
            if (empty($r['status']) || !$r['status']) {
                $outer_ret['status'] = false;
                $outer_ret['message'] = $r['message'];
                break;
            }
        }
        $outer_ret['data'] = $ret;
        //will_send_to_error_log('hz_contest_new_proposal_data_proc_cb',$outer_ret);
        $response['file_upload_data'] = $outer_ret;
        $upload_handler->generate_response($response);

        exit;
    } catch (Exception $e) {
        will_send_to_error_log('hz_contest_new_proposal_data_proc has error',will_get_exception_string($e));
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