<?php

add_action( 'wp_ajax_hz_con_delete', 'hz_con_delete_cb'  );

 function hz_con_delete_cb(){

     /*
       * current-php-code 2021-Jan-11
       * ajax-endpoint  hz_con_delete
       * input-sanitized : content_id
       */

     global $wpdb;
     if (!current_user_can('manage_options')) {
         exit;
     }

     $content_id = (int)FLInput::get('content_id');
     $return = [];
     $return['status'] = false;
     try {
         //code-notes now using new centralized deleting method without check for current user having access
         FreelinguistContentHelper::delete_content($content_id,false);
         $return['message'] = get_custom_string_return("Successfully deleted content $content_id");

         $return['status'] = true;
     }catch (Exception $e) {
         $return['message'] = $e->getMessage();
     }

     wp_send_json($return);
     exit();

}