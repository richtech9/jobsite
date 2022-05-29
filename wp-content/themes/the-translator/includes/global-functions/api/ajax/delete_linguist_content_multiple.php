<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_content_chapter

 * Description: delete_content_chapter

 *

 */

add_action('wp_ajax_delete_linguist_content_multiple', 'delete_linguist_content_multiple');


function delete_linguist_content_multiple(){

    /*
       * current-php-code 2020-Oct-13
       * ajax-endpoint  delete_linguist_content_multiple
       * input-sanitized : delete_content_id
       */

    $linguist_content_ids_comma_delimited = FLInput::get('delete_content_id',0);

    //code-notes made it more secure
    $linguistcontent_ids = [];
    $maybe_ok_ints = explode(',', $linguist_content_ids_comma_delimited);
    foreach ($maybe_ok_ints as $maybe_ok_int) {
        $maybe_ok_int = intval(trim($maybe_ok_int));
        if ($maybe_ok_int) {
            $linguistcontent_ids[] = $maybe_ok_int;
        }
    }

    $return = [];
    $log = [];
    $return['status'] = false;
    $user_id = get_current_user_id();

    try {

        //see if all the selected content CAN be deleted first
        foreach ($linguistcontent_ids as $a_content_id) {
            $content_detail = FreelinguistContentHelper::get_content_extended_information($a_content_id);
            if ($user_id !== $content_detail['user_id']) {
                throw new InvalidArgumentException("Can only delete content you own [$a_content_id]");
            }

            if ($content_detail['number_copies_sold']) {
                throw new InvalidArgumentException("Cannot delete content that is sold [$a_content_id]");
            }
        }




        //code-notes Delete generated units when mass deleting content (if exists)
        $deese_nodes = [];
        foreach ($linguistcontent_ids as $goodbye) {
            $node = new _FreelinguistIdType();
            $node->type = 'content';
            $node->id = $goodbye;
            $deese_nodes[] = $node;
        }
        FreelinguistUnitGenerator::remove_compiled_units_from_es_cache($log, null, $deese_nodes);

        foreach ($linguistcontent_ids as $a_content_id) {
            //code-notes now using new centralized deleting method
            FreelinguistContentHelper::delete_content($a_content_id,true,false);
        }

        $return['message'] = 'Content Deleted';

        $return['status'] = true;


    } catch (Exception $e) {
        $return['message'] = $e->getMessage();
    }

    wp_send_json($return);

    exit;

}