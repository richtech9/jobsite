<?php

// The function that handles the AJAX request
add_action( 'wp_ajax_delete_profile_attachment', 'delete_profile_attachment_callback' );
function delete_profile_attachment_callback() {
    /*
      * current-php-code 2020-Jan-11
      * ajax-endpoint  delete_profile_attachment
      * input-sanitized : attach_id
      */
    if ( !current_user_can( 'manage_options' ) ) {  exit;}

    try {
        $attach_id = (int)FLInput::get('attach_id');
        //The parent ID of our attachments

        $current_user = wp_get_current_user();
        if ($attach_id && in_array('administrator', $current_user->roles)) {
            FLWPFileHelper::delete_wp_files_via_id_array([$attach_id]);
            wp_send_json( ['status' => true, 'message' => 'Deleted file attachement '.$attach_id]);
            die();
        } else {
            throw new InvalidArgumentException( 'failed to delete file beause you are not an admin');
        }
    } catch (Exception $e) {
        will_send_to_error_log('delete profile attachment',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }
}