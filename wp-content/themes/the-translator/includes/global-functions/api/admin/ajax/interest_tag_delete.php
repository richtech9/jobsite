<?php

add_action('wp_ajax_interest_tag_delete', 'interest_tag_delete');

function interest_tag_delete()
{
    /*
       * current-php-code 2021-Jan-11
       * ajax-endpoint  interest_tag_delete
       * input-sanitized : tag_id
       */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }

    $tag_id = (int)FLInput::get('tag_id');
    if ($tag_id) {
        $getTags = (int)$wpdb->get_var(  "SELECT count(id) FROM wp_interest_tags WHERE ID = $tag_id");

        if ($getTags) {
            $isDone = $wpdb->delete("wp_interest_tags", array('ID' => $tag_id));
            if ($isDone) {
                $response = array('status' => 1, 'message' => 'Deleted Successfully');
            } else {
                $response = array('status' => 0, 'message' => 'Try Again.');
            }
        } else {
            $response = array('status' => 0, 'message' => 'Invalid Tags');
        }

    } else {
        $response = array('status' => 0, 'message' => 'Invalid Tags');
    }
    wp_send_json($response);
}