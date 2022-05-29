<?php

/*

 * Author Name: Arvik

 * Method:      upload text file for content

 * Description: upload text file for content

 *

 */

add_action('wp_ajax_cvf_upload_files_content_process', 'cvf_upload_files_content_process');


function cvf_upload_files_content_process(){
    //code-bookmark this is where content files are uploaded to

    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  cvf_upload_files_content_process
    * input-sanitized :content_id
    */
    global $wpdb;
    $response = null;
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

        $upload_handler = new UploadHandler([
            'upload_dir' => $content_folder,
            'upload_url' => $content_url_root,
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


                    $orginal_filename = $f->name;
                    $extension = pathinfo($f->name, PATHINFO_EXTENSION);
                    $base_file_name = basename($orginal_filename, '.' . $extension);

                    $our_file_name = 'content-' .$content_id. '-' . time(). '-'. UploadHandler::generate_random_safe_characters();

                    $our_file_name_with_extension = $our_file_name . '.' . $extension;
                    //code-notes [file-paths]  changed the upload to use new path and format


                    $public_name_dirty = $base_file_name . '.' . $extension;
                    $public_name = FLInput::filter_string_default($public_name_dirty);

                    $full_file_path = $content_folder . $our_file_name_with_extension;
                    $relative_path_with_file = $relative_path . $our_file_name_with_extension;
                    $relative_path_with_file = FLInput::filter_string_default($relative_path_with_file);
                    $old_full_path = $content_folder  . $f->name;
                    $b_rename_ok = rename($old_full_path, $full_file_path);
                    if (!$b_rename_ok) {
                        unlink($old_full_path);
                        throw new RuntimeException("failed to rename $old_full_path to $full_file_path ");

                    }


                    $wpdb->query("INSERT INTO wp_content_files(content_id,file_path,user_id,public_file_name)
                        VALUES($content_id,'$relative_path_with_file',$user_id,'$public_name')");

                    will_throw_on_wpdb_error($wpdb,'inserting content file');

                    $content_file_id = will_get_last_id($wpdb, 'making new content file');
                    $response['status'] = true;

                    $response['content_file_id'] = $content_file_id;
                    $response['public_name'] = $public_name; //code-notes added regular file name to the output
                    $response['new_name_with_time'] = $our_file_name_with_extension;
                    $response['message'] = 'A ok';

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

        will_send_to_error_log('cvf_upload_files_content_process has error',will_get_exception_string($e));
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