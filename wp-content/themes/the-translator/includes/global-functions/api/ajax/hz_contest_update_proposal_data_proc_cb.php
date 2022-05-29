<?php

//code-notes added code to report any errors back to the calling js, and to cleanup files if error
/*
 * called by :
 *  hz_contest_proposal_data file  - file input from the winner proposal for freelancer
 */

add_action( 'wp_ajax_hz_contest_update_proposal_data_proc',  'hz_contest_update_proposal_data_proc_cb'  );

/**
 * code-notes Only when the user owns the proposal
 */
 function hz_contest_update_proposal_data_proc_cb(){
       global $wpdb; 
     /*
     * current-php-code 2020-Oct-12
     * ajax-endpoint  hz_contest_update_proposal_data_proc
     * input-sanitized : project_id
      * when the freelancer, after winning a proposal, updates extra files
     */

     $response = null;

     try {
         FLInput::onlyPost(true);
         $project_id = (int)FLInput::get('project_id');
         $proposal_id = (int)FLInput::get('proposal_id');

         FLInput::onlyPost(false);

         $user_id = get_current_user_id();
         $check_result = $wpdb->get_results(
             "SELECT id,post_id,by_user from  wp_proposals WHERE id = $proposal_id AND by_user = $user_id");
         will_throw_on_wpdb_error($wpdb,'hz_contest_update_proposal_data_proc checking permissions');

         if (empty($check_result)) {
             throw new RuntimeException("Proposal does not exist or user does not have privlidges ");
         }
         $upload_dir = wp_upload_dir();
         $base_dir = $upload_dir['basedir'];
         $path_to_undo = null;


         $our_middle_path = UploadHandler::number_to_path_part_string($project_id, '/') . '/';

         $job_dirname = $upload_dir['basedir'] . '/' . $our_middle_path;

         $job_image_url = get_site_url() . '/' . $our_middle_path;

         $upload_handler = new UploadHandler([
             'upload_dir' => $job_dirname,
             'upload_url' => $job_image_url,
             'print_response' => false,
             UploadHandler::OPTION_WHITELIST => [
                 FileUploadWhitelist::TYPE_TEXT_ANY, //allow any text file regardless of mime type
                 FileUploadWhitelist::TYPE_WORDPRESS_FALLBACK, //if WP allows it, then it passes. However we still process the other types. So pdf will still get stripped, etc
                 FileUploadWhitelist::TYPE_PDF,  //we want to do additional filtering on pdf, so when this is set, the WP fallback does not handle it
                 FileUploadWhitelist::IMAGE_TYPES, //process png,jpg ourselves, and then let WP handle others
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
                 $file_id = null;
                 try {
                     if (!isset($f->error)) {


                         $extension = pathinfo($f->name, PATHINFO_EXTENSION);
                         $our_file_name = 'contest-' . $project_id . '-proposal-' . $proposal_id . '-extra-' . time() . '-' . UploadHandler::generate_random_safe_characters();
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
                         $wpdb->query("INSERT INTO wp_files (post_id,file_name,file_path,by_user,type,proposal_id)
                              VALUES (
                                $project_id,
                                '$escaped_file_name',
                                '$escaped_file_path',
                                $user_id,
                                $file_type,
                                $proposal_id
                                )");
                         will_throw_on_wpdb_error($wpdb);

                         $file_id = $wpdb->insert_id;
                         if (!$file_id) {
                             throw new RuntimeException("Could not insert file");
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
                             $full_file_path = $base_dir . '/' . $path_to_undo;
                             $real_file_path = realpath($full_file_path);

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
                     will_send_to_error_log('exception in hz_contest_update_proposal_data_proc_cb', [
                         will_get_exception_string($err),
                         $f
                     ]);
                    throw new RuntimeException($error_message);
                 } //end catch

             } //end foreach loop
         } //end if not empty files

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
         $response['file_upload_data'] = $outer_ret;
         $upload_handler->generate_response($response);

         exit;
     } catch (Exception $e) {
         will_send_to_error_log('hz_contest_update_proposal_data_proc has error',will_get_exception_string($e));
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