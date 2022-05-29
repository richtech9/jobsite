<?php


add_action( 'wp_ajax_hz_awardprize_to_proposal', 'hz_awardprize_to_proposal_cb'  );

/***** Awrd prize to proposal *******/

/**
 * code-notes Only awards when the user owns the contest ,and the proposal is attached to the contest and the correct freelancer id is used
 */
function hz_awardprize_to_proposal_cb(){
    global $wpdb;
    //code-bookmark reference hz_awardprize_to_proposal_cb
    /*
     * current-php-code 2020-Oct-12
     * ajax-endpoint  hz_awardprize_to_proposal
     * input-sanitized : contestId,proposalId
     */
    try {
        FLInput::onlyPost(true);
        $contestId = (int)FLInput::get('contestId');
        $proposalId = (int)FLInput::get('proposalId');
        $linguist_id = (int)FLInput::get('linguId');

        FLInput::onlyPost(false);

        $user_id = get_current_user_id();

        $proposal = $wpdb->get_row("
                       SELECT post.ID as po_id, prop.id as proposal_id,
                        freelancer_user.user_email as freelancer_email,
                        customer_user.user_email as customer_email,
                        meta_for_id.meta_value as contest_modified_id
                        FROM wp_posts post
                        LEFT JOIN wp_proposals prop ON prop.post_id = post.ID
                        LEFT JOIN wp_postmeta meta_for_id ON meta_for_id.post_id = post.ID AND meta_for_id.meta_key = 'modified_id'
                        LEFT JOIN wp_users freelancer_user ON freelancer_user.ID = prop.by_user
                        LEFT JOIN wp_users customer_user ON customer_user.ID = post.post_author
                        WHERE 
                        prop.by_user = $linguist_id AND 
                        post.ID = $contestId AND
                        post.post_author = $user_id AND 
                         prop.id = $proposalId;
                        "
            );

        will_throw_on_wpdb_error($wpdb);
        if (empty($proposal)) {
            throw new RuntimeException("User does not own this contest, or wrong proposal or user for the contest");
        }

        $prizesAwarded = get_post_meta($contestId, 'contest_awardedProposalPrizes', true);
        if ($prizesAwarded) {
            $proposalPresent = explode(',', $prizesAwarded);
        } else {
            $proposalPresent = array();
        }


        $userCurrBalance = get_user_meta($user_id, 'total_user_balance', true);


        $job_type = get_post_meta($contestId, 'fl_job_type', true);

        $estimated_budgets = get_post_meta($contestId, 'estimated_budgets', true);


        $getFee1 = get_option('client_referral_fee') ? get_option('client_referral_fee') : 2;
        $getFee_percentage = get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5;
        $getFee2 = ($estimated_budgets * $getFee_percentage) / 100;
        $getFee = $getFee1 + $getFee2;

        $dedecutTotal = $getFee + $estimated_budgets;

        if ($job_type !== 'contest') {
           throw new RuntimeException("Job is not a contest");
        }
        $finalBaal = $userCurrBalance - $dedecutTotal;
        //code-notes if its after the award period, then charge the customer for any awards
        $is_after_award_period = FreelinguistContestCancellation::is_after_award_date($contestId, $log, $award_period_ending_ts);
        $message= 'Prize awarded from prepaid contest';
        if ((count($proposalPresent) >= 1) || $is_after_award_period) {

            //code-bookmark this is where the user has money for the prize taken out, if there are extra awards

            update_user_meta($user_id, 'total_user_balance', $finalBaal);

            update_post_meta($contestId, 'project_new_status', 'Delivery');

            fl_transaction_insert('-' . $estimated_budgets, 'done', 'winner_added', $user_id,
                NULL, 'additioanl award prize', 'wallet', '',
                $contestId, NULL,NULL);

            fl_transaction_insert('-' . $getFee, 'done', 'winner_added', $user_id,
                NULL, 'Processing fee', 'wallet', '',
                $contestId, NULL,NULL);

            $message= 'Prize awarded from wallet';
        }



        if (in_array($proposalId, $proposalPresent)) {
            throw new RuntimeException('Proposal already awarded for this contest.');
        }

        if ($prizesAwarded != '') {

            $allAwards = $prizesAwarded . ',' . $proposalId;

            if (update_post_meta($contestId, 'contest_awardedProposalPrizes', $allAwards)) {
                update_post_meta($contestId, 'project_new_status', 'Delivery');
            } else {
                throw new RuntimeException("could not update meta A");
            }

        } else {

            $allAwards = $proposalId;

            if (update_post_meta($contestId, 'contest_awardedProposalPrizes', $allAwards)) {
                update_post_meta($contestId, 'project_new_status', 'Delivery');
            } else {
                throw new RuntimeException("could not update meta B");
            }

        }

        FLPostLookupDataHelpers::add_user_lookup_awarded_contest($contestId,$linguist_id,$proposalId);

        emailTemplateForUser( $proposal->freelancer_email,
            EMAIL_TEMPLATE_CONTEST_AWARDED,
            [
                'job_id'=> $proposal->proposal_id,
                'job_title' => $proposal->contest_modified_id,
                'job_status' => ''
            ] );

        $resp = array('status' => true, 'message' => $message);
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing


    } catch (Exception $e) {
        will_send_to_error_log('awardprize_to_proposal ajax', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }

}

