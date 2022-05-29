<?php
/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_user_profile_image

 * Description: delete_user_profile_image

 *

 */



add_action('wp_ajax_cvf_upload_files_order_process', 'cvf_upload_files_order_process');

/**
 * code-notes only allow if user is the owner of the job
 *
 */
function cvf_upload_files_order_process(){
    global $wpdb;
    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  cvf_upload_files_order_process
    * input-sanitized :indjob,job_id
     *
     * freelance uploads file to a project he is working on
    */
    $response = null;

    try {
        $job_id = (int)FLInput::get('job_id', 0); //wp_post_id
        $indi_job_id = (int)FLInput::get('indjob', 0); //wp_fl_job id

        $user_id = get_current_user_id();


        $sql_to_check = "SELECT ID from wp_fl_job WHERE linguist_id = $user_id AND ID = $indi_job_id";
        $check_result = $wpdb->get_results($sql_to_check);
        will_throw_on_wpdb_error($wpdb,'Checking Permission in cvf_upload_files_order_process');
        if (empty($check_result)) {
            throw new RuntimeException("No Permission for User $user_id to Upload File Here");
        }


        /* START: File upload */

        $upload_dir = wp_upload_dir();

        $our_middle_path = UploadHandler::number_to_path_part_string($job_id, '/') . '/';

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






        if (!empty($response['files'])) {

            foreach ($response['files'] as $f) {

                if (!isset($f->error)) {
                    $extension = pathinfo($f->name, PATHINFO_EXTENSION);
                    $filename = $f->name;
                    $words_count = 0;
                    $word_count_type = 0;
                    $old_full_path = $job_dirname . '/' . $f->name;

                    $valid_formats_word_count = array("txt", "pdf", "docx", "doc", "xlsx", "pptx"); //Supported file types

                    if (in_array(strtolower($extension), $valid_formats_word_count)) {

                        if (in_array(strtolower($extension), array("docx", "doc", "xlsx", "pptx"))) {

                            $docObj = new DocxConversion($old_full_path);

                            $docText = $docObj->convertToText();

                        } elseif (strtolower($extension) == 'pdf') {

                            $a_pdf = new PdfParser();

                            $docText = $a_pdf->parseFile($old_full_path);

                        } else {

                            $docText = file_get_contents($old_full_path);

                        }

                        $words_count = str_word_count($docText);

                        //$word_count_type = 1; //Disabled as per client requirement

                    }


                    $our_file_name = 'project-' . $job_id . '-job-' . $indi_job_id . '-' . time() . '-' . UploadHandler::generate_random_safe_characters();
                    $our_file_name_with_extension = $our_file_name . '.' . $extension;
                    $our_partial_path = $our_middle_path . $our_file_name_with_extension;
                    $our_full_path = $job_dirname . $our_file_name_with_extension;
                    //code-notes [file-paths]  changed the upload to use new path and format
                    //code-note rename file here to what we want, its already on the right path, but its easier to change it here than fight the upload handler
                    $b_rename_ok = rename($old_full_path, $our_full_path);
                    if (!$b_rename_ok) {
                        will_send_to_error_log("cvf_upload_files_order_process failed to rename $old_full_path to $our_full_path ");
                        unlink($old_full_path);
                        throw new RuntimeException("Could not rename file");
                    }


                    $wpdb->insert('wp_files', array(

                        'post_id' => $job_id,

                        'job_id' => $indi_job_id,

                        'file_name' => $filename,

                        'file_path' => $our_partial_path,

                        'word_count' => $words_count,

                        'price' => amount_format(0),

                        'by_user' => $user_id,

                        'type' => FLWPFileHelper::TYPE_FREELANCER_UPLOAD,

                        'word_count_type' => $word_count_type),

                        array('%d', '%d', '%s', '%s', '%d', '%f', '%d')

                    );

                    $attach_id = $wpdb->insert_id;

                    $response['attach_id'] = $attach_id;

                    wp_send_json($response); exit();

                } else {
                    throw new RuntimeException($f->error);
                }
            }

        } else {
            throw new RuntimeException("No File Received");
        }

        exit(); //should never reach here

    } catch (Exception $e) {
        will_send_to_error_log('cvf_upload_files_order_process has error',will_get_exception_string($e));
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