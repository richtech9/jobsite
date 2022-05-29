<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_content_chapter

 * Description: delete_content_chapter

 *

 */

add_action('wp_ajax_delete_linguist_content', 'delete_linguist_content');


function delete_linguist_content(){
      /*
       * current-php-code 2020-Oct-13
       * ajax-endpoint  delete_linguist_content
       * input-sanitized : delete_content_id
       */

    $linguist_content_id = (int)FLInput::get('delete_content_id',0);
    $return = [];
    $return['status'] = false;

    try {
        //code-notes now using new centralized deleting method
        FreelinguistContentHelper::delete_content($linguist_content_id,true);

        $return['message'] = get_custom_string_return("Successfully deleted content $linguist_content_id");

        $return['status'] = true;


    } catch (Exception $e) {
        $return['message'] = $e->getMessage();
    }

   wp_send_json($return);

    exit;

}