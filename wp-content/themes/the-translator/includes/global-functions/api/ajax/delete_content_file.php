<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_content_chapter

 * Description: delete_content_chapter

 *

 */

add_action('wp_ajax_delete_content_file', 'delete_content_file');

/*
 * code-notes Only allow if the user is the one who created the content this file belongs to
 */
function delete_content_file(){
  /*
   * current-php-code 2020-Oct-13
   * ajax-endpoint  delete_content_file
   * input-sanitized : delete_content_file_id
   */



  try {

      global $wpdb;
      $delete_content_file_id = (int)FLInput::get('delete_content_file_id', 0);
      if (!$delete_content_file_id) {throw new RuntimeException("No content file id given");}
      $return = array();

      $user_id = get_current_user_id();

      $content_file_detail = $wpdb->get_row("
    select f.* 
    from wp_content_files f
    INNER JOIN wp_linguist_content wlc on f.content_id = wlc.id
    where f.id=$delete_content_file_id AND wlc.user_id = $user_id",
          ARRAY_A);

      will_throw_on_wpdb_error($wpdb);
      if (empty($content_file_detail)) {

          $return['message'] =
              get_custom_string_return($delete_content_file_id.': This file does not exist, or else the user does not have permissions');

          $return['status'] = false;

          $return['scrollToElement'] = true;

          wp_send_json($return);

          exit;

      } else {

          if ($content_file_detail['user_id'] != get_current_user_id()) {

              $return['message'] = get_custom_string_return("You are unautorized to remove content file $delete_content_file_id");

              $return['status'] = false;

              $return['scrollToElement'] = true;

              wp_send_json($return);

              exit;

          }

          FreelinguistContentHelper::delete_content_file($delete_content_file_id);

          $return['message'] = get_custom_string_return("Successfully deleted $delete_content_file_id");

          $return['status'] = true;

      }


      $return['scrollToElement'] = true;

      wp_send_json($return);

      exit;
  } catch (Exception $e) {
      will_send_to_error_log('delete content file ajax', will_get_exception_string($e));

      $resp = array('status' => false, 'message' => $e->getMessage(),'scrollToElement' => true);
      wp_send_json($resp);
      die();//above dies, but phpstorm does not know that, so adding it here for editing
  }

}