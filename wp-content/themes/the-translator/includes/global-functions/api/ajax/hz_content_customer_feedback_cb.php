<?php

add_action( 'wp_ajax_hz_content_customer_feedback',  'hz_content_customer_feedback_cb'  );


/**
 * code-notes can only give feedback if the user bought this content, and the content is completed
 */
function hz_content_customer_feedback_cb(){
    //code-bookmark ajax for customer giving feedback for content he bought from freelancer
    global $wpdb;

    /*
     * current-php-code 2020-Nov-21
     * ajax-endpoint  hz_content_customer_feedback
     * input-sanitized : data   -> comments_by_customer, content_id,rating_by_customer
     */

    try {
        $data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);
        $data = [];
        parse_str($data_string, $data);

        $content_id = (int)FLInput::clean_data_key($data, 'content_id', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $rating_by_customer = (int)FLInput::clean_data_key($data, 'rating_by_customer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $comments_by_customer = FLInput::clean_data_key($data, 'comments_by_customer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $user_id = get_current_user_id();
        $sql_to_check = "SELECT id FROM wp_linguist_content WHERE  user_id IS NOT NULL AND status = 'completed' and purchased_by = $user_id";

        $check_result = $wpdb->get_results($sql_to_check);
        will_throw_on_wpdb_error($wpdb);

        if (empty($check_result)) {
            throw new RuntimeException("Either wrong content id or user did not purchase this content or content is not completed");
        }

        $content = $wpdb->get_results(
            "select * from wp_linguist_content where  user_id IS NOT NULL AND  id=$content_id");

        $linguist_id = $content[0]->user_id;

        $upd = $wpdb->update('wp_linguist_content',
            [
                "rating_by_customer" => $rating_by_customer,
                "comments_by_customer" => $comments_by_customer
            ],
            array("id" => $content_id)
        );
        will_throw_on_wpdb_error($wpdb);
        if ($upd === false) {
            throw new RuntimeException("Cannot save feedback");
        }
        update_freelancer_average_rating($linguist_id);

        $resp = array('status' => true, 'message' => 'Feedback submitted');
        wp_send_json($resp);
        die(); //above dies, but phpstorm does not know that, so adding it here for editing
    } catch (Exception $e) {
        will_send_to_error_log('Customer Content Rating', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }

}