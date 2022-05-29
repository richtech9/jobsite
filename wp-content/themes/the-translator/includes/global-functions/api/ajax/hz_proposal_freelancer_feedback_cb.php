<?php


add_action( 'wp_ajax_hz_proposal_freelancer_feedback', 'hz_proposal_freelancer_feedback_cb'  );

/**
 * code-notes Only allowed if the logged in user made the proposal and this proposal  has been awarded and completed
 */
function hz_proposal_freelancer_feedback_cb(){
    //code-bookmark ajax for feedback from the freelancer to the customer in the contest winning proposal
    global $wpdb;
    /*
     * current-php-code 2020-Oct-12
     * ajax-endpoint  hz_proposal_freelancer_feedback
     * input-sanitized : data keys ->  customer,   comments_by_freelancer,proposal_id,rating_by_freelancer
     */
    try {
        FLInput::onlyPost(true);
        $posted_data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);
        FLInput::onlyPost(false);

        $data = [];

        parse_str($posted_data_string, $data);

        $rating_by_freelancer = (int)FLInput::clean_data_key($data, 'rating_by_freelancer');

        $comments_by_freelancer = FLInput::clean_data_key($data, 'comments_by_freelancer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $proposal_id = (int)FLInput::clean_data_key($data, 'proposal_id');

        $user_id = get_current_user_id();

        $sql_for_check = "SELECT p.id as proposal_id,f.ID as job_id, 
                                  f.post_author as customer_id,
                                  f.ID as job_id
                FROM wp_proposals p 
                INNER JOIN wp_posts f ON f.ID = p.post_id
                WHERE p.by_user = $user_id AND p.id=$proposal_id ";

        $check_result = $wpdb->get_results($sql_for_check);
        will_throw_on_wpdb_error($wpdb);

        if (empty($check_result)) {
            throw new RuntimeException("Either wrong proposal id or user did not make this proposal");
        }

        $job_id = $check_result[0]->job_id;
        $customer_id = $check_result[0]->customer_id;

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

        $upd = $wpdb->update('wp_proposals', array(
            "rating_by_freelancer" => $rating_by_freelancer,
            "comments_by_freelancer" => $comments_by_freelancer,
            "customer" => $customer_id
        ), array("id" => $proposal_id));

        will_throw_on_wpdb_error($wpdb);

        if ($upd === false) {
            throw new RuntimeException("Cannot save feedback");
        }

        update_customer_average_rating($customer_id);

        $resp = array('status' => true, 'message' => 'Feedback submitted');
        wp_send_json($resp);
        die(); //above dies, but phpstorm does not know that, so adding it here for editing
    } catch (Exception $e) {
        will_send_to_error_log('Freelancer Proposal Rating', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }

}