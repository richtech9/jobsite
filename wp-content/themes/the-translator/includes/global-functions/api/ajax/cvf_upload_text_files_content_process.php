<?php

/*

 * Author Name: Arvik

 * Method:      upload text file for content

 * Description: upload text file for content

 *

 */

add_action('wp_ajax_cvf_upload_text_files_content_process', 'cvf_upload_text_files_content_process');


function cvf_upload_text_files_content_process(){

    /*
    * current-php-code 2020-Sep-30
    * ajax-endpoint  cvf_upload_text_files_content_process
    * input-sanitized :
     * mapped to html atc_text_files_content,
     * when the freelancer is making content chapters, there is a check if creator of this content
    */
    global $wpdb;
    $response = null;
    $full_path_to_file = null;
    try {
        $content_id = (int)FLInput::get('content_id');
        $user_id = get_current_user_id();
        $check_res = $wpdb->get_results("SELECT id from wp_linguist_content WHERE id = $content_id AND user_id = $user_id");
        will_throw_on_wpdb_error($wpdb);
        if (empty($check_res)) {
            will_send_to_error_log("cvf_upload_files_content_process cannot run as the user $user_id does not create the content $content_id");
            throw new RuntimeException("Either content id is wrong or invalid user");
        }

        $content_folder = FreelinguistContentHelper::get_content_directory_and_make_it_if_not_exists($content_id,$relative_path).'/';
        $content_url_root = get_site_url() . '/' . $relative_path;
        $file_param_name = 'text_files';

        $upload_handler = new UploadHandler([
            'upload_dir' => $content_folder,
            'upload_url' => $content_url_root,
            'print_response' => false,
            'param_name' => $file_param_name,
            UploadHandler::OPTION_WHITELIST => [
                FileUploadWhitelist::TYPE_TEXT_ANY, //allow any text file regardless of mime type
            ]
        ]);

        $response = $upload_handler->get_response();

        if (!empty($response[$file_param_name])) {

            foreach ($response[$file_param_name] as $f) {

                if (!isset($f->error)) {


                    $full_path_to_file = $content_folder  . $f->name;

                    $uploaded_words = file_get_contents($full_path_to_file);

                    if (empty($uploaded_words)) {
                        throw new RuntimeException("Could not read text from the file $full_path_to_file");
                    }

                    $base_file_name = $f->name;
                    $response['status'] = true;
                    $response['uploaded_words'] = $uploaded_words;
                    $response['uploaded_title'] = $base_file_name;
                    unlink($full_path_to_file);
                    $full_path_to_file = null;
                    wp_send_json($response);

                    exit();

                } else {
                    throw new RuntimeException($f->error);
                }

            }

        }else {
            throw new RuntimeException("No File Received");
        }

        exit();


    } catch (Exception $e) {

        will_send_to_error_log('cvf_upload_text_files_content_process has error',will_get_exception_string($e));
        if (is_array($response)) {
            $json_reply = $response;
        } else {
            $json_reply = [];
        }
        $json_reply['status'] = false;
        $json_reply['message'] = $e->getMessage();
        wp_send_json($json_reply,510); exit; //will be caught by the file upload on error
    } finally {
        if ($full_path_to_file) {
            unlink($full_path_to_file);
        }
    }

}