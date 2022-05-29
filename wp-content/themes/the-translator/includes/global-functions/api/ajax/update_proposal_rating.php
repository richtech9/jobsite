<?php

add_action('wp_ajax_update_proposal_rating', 'update_proposal_rating');

function update_proposal_rating()
{

    global $wpdb;
    /*
    * current-php-code 2021-March-5
    * ajax-endpoint  update_proposal_rating
    * input-sanitized : id,rating
    */
    try {

        $proposal_id = (int)FLInput::get('id');
        $rating = (int)FLInput::get('rating');

        $wpdb->query("UPDATE  wp_proposals set rating = $rating WHERE id = $proposal_id");
        will_throw_on_wpdb_error($wpdb, 'updating proposal');

        wp_send_json(['status' => true, 'message' => "Proposal [$proposal_id] now has rating of [$rating]"]);
        exit;
    } catch (Exception $e) {
        will_send_to_error_log('update proposal rating', will_get_exception_string($e));
        wp_send_json(['status' => false, 'message' => $e->getMessage()]);
    }


}