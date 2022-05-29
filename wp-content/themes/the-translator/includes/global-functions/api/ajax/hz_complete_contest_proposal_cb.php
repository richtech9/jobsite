<?php


add_action( 'wp_ajax_hz_complete_contest_proposal',  'hz_complete_contest_proposal_cb'  );


/******* complete job with proposals ********///

/*
 * code-notes Only completes if the user owns the contest, the proposal has been awarded but not completed
 */
function hz_complete_contest_proposal_cb(){

    global $wpdb;
    //code-bookmark reference code on hz_complete_contest_proposal_cb
    /*
     * current-php-code 2020-Oct-12
     * ajax-endpoint  hz_complete_contest_proposal
     * input-sanitized : contestId,proposalId
     */
    try {
        FLInput::onlyPost(true);
        $contestId = FLInput::get('contestId');
        $proposalId = FLInput::get('proposalId');

        FLInput::onlyPost(false);


        $user_id = get_current_user_id();

        $job_check_result = $wpdb->get_row("
                       SELECT p.ID as po_id, r.id as proposal_id
                        FROM wp_posts p
                        LEFT JOIN wp_proposals r ON r.post_id = p.ID
                        WHERE 
                        p.ID = $contestId AND
                        p.post_author = $user_id AND 
                         r.id = $proposalId;
                        ",
            ARRAY_A);

        will_throw_on_wpdb_error($wpdb);
        if (empty($job_check_result)) {
            throw new RuntimeException("User does not own this contest, or wrong proposal or user for the contest");
        }

        //code-notes [red dot actions]  make sure wallet balance >=0 before completed, same for proposals and milestone and content
        $customer_amount = get_user_meta($user_id, 'total_user_balance', true);
        if ($customer_amount < 0) {
            throw new RuntimeException("Cannot complete: Customer wallet is negative");
        }


        $contest_awardedPrizes = get_post_meta($contestId, 'contest_awardedProposalPrizes', true);
        $contest_completed_proposals = get_post_meta($contestId, 'contest_completed_proposals', true);


        $proposalPresent = explode(',', $contest_awardedPrizes);

        if (!in_array($proposalId,$proposalPresent)) {
            throw new RuntimeException("Proposal has not been awarded yet");
        }

        if ($contest_completed_proposals) {
            $contest_completed_proposals_array = explode(',', $contest_completed_proposals);
        } else {
            $contest_completed_proposals_array = [];
        }

        if (in_array($proposalId,$contest_completed_proposals_array)) {
            throw new RuntimeException("Proposal has already been completed");
        }

        $job_type = get_post_meta($contestId, 'fl_job_type', true);


        $estimated_budgets = get_post_meta($contestId, 'estimated_budgets', true);
        if ($job_type == 'contest') {

            $linguist_referral_fee = get_option('linguist_referral_fee') ? get_option('linguist_referral_fee') : 15;
            $totalPay = $estimated_budgets - $linguist_referral_fee -
                (($estimated_budgets * (get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15)) / 100);
        } else {
            $totalPay = $estimated_budgets / count($proposalPresent);
        }

        $totalPay = max($totalPay, 0);

        //code-bookmark This is where the freelancer gets paid for a contest
        $proposals = $wpdb->get_results(
            "SELECT * FROM wp_proposals WHERE id= $proposalId");

        will_throw_on_wpdb_error($wpdb);
        if (empty($proposals)) {
            throw new RuntimeException("Could not get proposal information");
        }

        $by_user_id = (int)$proposals[0]->by_user;
        if (!$by_user_id) {
            throw new RuntimeException("User does not exist for this proposal");
        }
        $linguMoney = get_user_meta($proposals[0]->by_user, 'total_user_balance', true);

        $addTolingu = $linguMoney + $totalPay;

        update_user_meta($proposals[0]->by_user, 'total_user_balance', amount_format($addTolingu));

        $new_txn_id = fl_transaction_insert($totalPay, 'done', 'contestWinner', $proposals[0]->by_user,
            NULL, 'Earnings from competition', 'wallet',
            '', $contestId, NULL,NULL);

        $sql = "UPDATE wp_fl_transaction set proposal_id = $proposalId  WHERE id = $new_txn_id";
        will_throw_on_wpdb_error($wpdb,'setting proposal id to the new trx id of '.$new_txn_id);
        $wpdb->query($sql);


        if (!in_array($proposalId, $contest_completed_proposals_array)) {

            if ($contest_completed_proposals != '') {

                $allAwards = $contest_completed_proposals . ',' . $proposalId;
                $wpdb->update('wp_proposals', array('status' => 'completed'), array('id' => $proposalId));

                update_post_meta($contestId, 'contest_completed_proposals', $allAwards);
            } else {
                $allAwards = $proposalId;
                $wpdb->update('wp_proposals', array('status' => 'completed'), array('id' => $proposalId));
                update_post_meta($contestId, 'contest_completed_proposals', $allAwards);
            }

        }

        update_post_meta($contestId, 'project_new_status', 'Completed');
        $message =  'Contest Complete';


        $resp = array('status' => true, 'message' => $message);
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    } catch (Exception $e) {
        will_send_to_error_log('hz_complete_contest_proposal ajax', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }

}