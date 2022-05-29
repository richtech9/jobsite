<?php

add_action( 'wp_ajax_hz_content_translator_feedback', 'hz_content_translator_feedback_cb'  );

/**
 * Only when the content is completed, and the logged in user is the creator
 */
function hz_content_translator_feedback_cb(){
    //code-bookmark ajax for the freelancer giving feedback about the customer purchasing content
    global $wpdb;

    /*
     * current-php-code 2020-Nov-21
     * ajax-endpoint  hz_content_translator_feedback
     * input-sanitized : data   -> comments_by_freelancer, content_id,rating_by_freelancer
     */

    try {
        $data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);
        $data = [];
        parse_str($data_string, $data);

        $content_id = (int)FLInput::clean_data_key($data, 'content_id', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);


        $rating_by_freelancer = (int)FLInput::clean_data_key($data, 'rating_by_freelancer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $comments_by_freelancer = FLInput::clean_data_key($data, 'comments_by_freelancer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);


        $user_id = get_current_user_id();
        $sql_to_check = "SELECT id FROM wp_linguist_content WHERE status = 'completed' and user_id = $user_id";

        $check_result = $wpdb->get_results($sql_to_check);
        will_throw_on_wpdb_error($wpdb);

        if (empty($check_result)) {
            throw new RuntimeException("Either wrong content id or user did not purchase this content or content is not completed");
        }

        $content = $wpdb->get_results(
            "select * from wp_linguist_content where  user_id IS NOT NULL AND  id=$content_id");

        $customer_id = $content[0]->purchased_by;

        $upd = $wpdb->update('wp_linguist_content', [
            "rating_by_freelancer" => $rating_by_freelancer,
            "comments_by_freelancer" => $comments_by_freelancer
        ], array("id" => $content_id));

        will_throw_on_wpdb_error($wpdb);
        if ($upd === false) {
            throw new RuntimeException("Cannot save feedback");
        }

        update_customer_average_rating($customer_id);

        $resp = array('status' => true, 'message' => 'Feedback submitted');
        wp_send_json($resp);
        die(); //above dies, but phpstorm does not know that, so adding it here for editing

    } catch (Exception $e) {
        will_send_to_error_log('Freelancer Content Rating', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }

}