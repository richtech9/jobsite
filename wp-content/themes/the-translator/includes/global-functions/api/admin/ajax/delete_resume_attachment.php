<?php



/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_resume_attachment

 * Description: delete_resume_attachment

 *

 */

add_action('wp_ajax_delete_resume_attachment', 'delete_resume_attachment');


function delete_resume_attachment(){

    /*
    * current-php-code 2021-Feb-09
    * ajax-endpoint  delete_resume_attachment
    * input-sanitized : attach_id
    */
    global $wpdb;
    if ( !current_user_can( 'manage_options' ) ) {  exit;}

    try {
        $attach_id = (int)FLInput::get('attach_id'); //The parent ID of our attachments


        $user_ID = get_current_user_id();
        $current_user_id = $wpdb->get_var("SELECT by_user FROM wp_files WHERE  id= $attach_id and status =4");

        if ($current_user_id == $user_ID) {
            FLWPFileHelper::delete_wp_files_via_id_array([$attach_id]);

            wp_send_json( ['status' => true, 'message' => 'Deleted file attachement '.$attach_id]);

            exit;

        } else {

            throw new InvalidArgumentException( 'failed to delete file beause you do not own it');
        }

    } catch (Exception $e) {
        will_send_to_error_log('delete resume attachment',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}


