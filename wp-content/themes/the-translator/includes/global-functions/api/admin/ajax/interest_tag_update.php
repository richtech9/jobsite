<?php

add_action('wp_ajax_interest_tag_update', 'interest_tag_update');


function interest_tag_update()
{
    /*
      * current-php-code 2021-Jan-11
      * ajax-endpoint  interest_tags_delete
      * input-sanitized : tag_id
      */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }

    $tag_id = (int)FLInput::get('tag_id');
    $tag_name = FLInput::get('tag_name','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

    if ($tag_id && $tag_name) {
        $tag_id = $_REQUEST['tag_id'];
        $getTags = (int)$wpdb->get_var("SELECT count(id) FROM wp_interest_tags WHERE ID = $tag_id");
        if ($getTags) {
            $isUpdate = $wpdb->update("wp_interest_tags", array('tag_name' => $tag_name), array('ID' => $tag_id));
            $response = array('status' => 1, 'message' => 'Update Successfully', 'is_updated' => $isUpdate);
        } else {
            $response = array('status' => 0, 'message' => 'Invalid Tags');
        }
    } else {
        $response = array('status' => 0, 'message' => 'Invalid Tags');
    }
    wp_send_json($response);
}