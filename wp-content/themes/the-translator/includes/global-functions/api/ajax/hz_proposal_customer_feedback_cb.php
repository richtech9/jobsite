<?php


add_action( 'wp_ajax_hz_proposal_customer_feedback', 'hz_proposal_customer_feedback_cb'  );

/**
 * code-notes Only allowed if the logged in user owns the contest; the proposal is associated with contest, and has been awarded and completed
 */
function hz_proposal_customer_feedback_cb(){
    //code-bookmark ajax for the feedback from the customer to the freelancer in the contest winning proposal
    global $wpdb;

    /*
     * current-php-code 2020-Oct-12
     * ajax-endpoint  hz_proposal_customer_feedback
     * input-sanitized : data   -> comments_by_customer,proposal_id,rating_by_customer
     */

    try {
        FLInput::onlyPost(true);
        $posted_data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);
        FLInput::onlyPost(false);
        $data = [];

        parse_str($posted_data_string, $data);

        $rating_by_customer = (int)FLInput::clean_data_key($data, 'rating_by_customer');
        $comments_by_customer = FLInput::clean_data_key($data, 'comments_by_customer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);
        $proposal_id = (int)FLInput::clean_data_key($data, 'proposal_id');

        $user_id = get_current_user_id();

        $sql_for_check = "SELECT p.id as proposal_id,f.ID as job_id
                FROM wp_proposals p 
                INNER JOIN wp_posts f ON f.ID = p.post_id
                WHERE f.post_author = $user_id AND p.id=$proposal_id ";

        $check_result = $wpdb->get_results($sql_for_check);
        will_throw_on_wpdb_error($wpdb);

        if (empty($check_result)) {
            throw new RuntimeException("Either wrong proposal id or user does not own contest");
        }

        $job_id = $check_result[0]->job_id;

        $contest_awardedPrizes = get_post_meta($job_id, 'contest_awardedProposalPrizes', true);
        $contest_completed_proposals = get_post_meta($job_id, 'contest_completed_proposals', true);

        $proposalPresent = explode(',', $contest_awardedPrizes);

        if (!in_array($proposal_id,$proposalPresent)) {
            throw new RuntimeException("Proposal has not been awarded yet");
        }

        if ($contest_completed_proposals) {
            $contest_completed_proposals_array = explode(',', $contest_completed_proposals);
        } else {
            $contest_completed_proposals_array = [];
        }

        if (!in_array($proposal_id,$contest_completed_proposals_array)) {
            throw new RuntimeException("Proposal has not been completed");
        }



        $proposal = $wpdb->get_results(
            "select * from wp_proposals where id=$proposal_id");

        $linguist_id = (int)$proposal[0]->by_user;
        if (!$linguist_id) {throw new RuntimeException("User id does not exist for this proposal");}

        $upd = $wpdb->update('wp_proposals', array(
            "rating_by_customer" => $rating_by_customer,
            "comments_by_customer" => $comments_by_customer
        ), array("id" => $proposal_id));

        will_throw_on_wpdb_error($wpdb);

        if ($upd === false) {
            throw new RuntimeException("Cannot save feedback");
        }

        update_freelancer_average_rating($linguist_id);

        $resp = array('status' => true, 'message' => 'Feedback submitted');
        wp_send_json($resp);
        die(); //above dies, but phpstorm does not know that, so adding it here for editing
    } catch (Exception $e) {
        will_send_to_error_log('Customer Proposal Rating', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }

}