<?php

add_action('wp_ajax_hz_conStatus_save', 'hz_conStatus_save_cb');

function hz_conStatus_save_cb()
{

    /*
       * current-php-code 2021-Jan-11
       * ajax-endpoint  hz_conStatus_save
       * input-sanitized : dbId,selStatus
       */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }

    try {
        $dbId = (int)FLInput::get('dbId');


        $selStatus = FLInput::get('selStatus');


        $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
            "SELECT * FROM wp_dispute_cases WHERE `ID` = %d", array($dbId)), ARRAY_A);


        $contestId = $result['contestId'];

        $proposal_id = $result['proposal_id'];

        $execut = false;
        if ($proposal_id) {

            if ($selStatus == "rejected_by_mediator") {


                $mediator_id = get_current_user_id();


                $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases SET status = %s, mediator_id = %d WHERE proposal_id = %d", "rejected_by_mediator", $mediator_id, $proposal_id));

                /*Start Paying to customer*/
                $author_id = get_post_field('post_author', $contestId);

                $customer_balance = (float)get_user_meta($author_id, 'total_user_balance', true);

                $contest_amount = (float)get_post_meta($contestId, 'estimated_budgets', true);


                $net_amount = max($contest_amount, 0);

                update_user_meta($author_id, 'total_user_balance', ($customer_balance + $net_amount));

                $new_txn_id = fl_transaction_insert($net_amount, 'done', 'contestDisputeRejected', $author_id, NULL,
                    'Proposal dispute: job cancelled by admin', 'wallet', '', $contestId, NULL, NULL);

                $sql = "UPDATE wp_fl_transaction set proposal_id = $proposal_id  WHERE id = $new_txn_id";
                will_throw_on_wpdb_error($wpdb,'setting proposal id to the new trx id of '.$new_txn_id);
                $wpdb->query($sql);

                $wpdb->update('wp_proposals', array(
                    'status' => 'rejected',
                    'updated_at' => date('Y-m-d H:i:s')
                ), array('id' => $proposal_id));

                $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases 
                SET status = %s 
                WHERE ID = %d", $selStatus, $dbId));

                fl_message_insert("Proposal dispute rejected by " . get_da_name($mediator_id) . "", "", $proposal_id);


            } else if ($selStatus == "approved") {


                $mediator_id = get_current_user_id();


                $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases 
                SET status = %s, mediator_id = %d 
                WHERE proposal_id = %d",
                    "approved", $mediator_id, $proposal_id));

                /*Start Paying to customer*/
                $contest_awardedPrizes = get_post_meta($contestId, 'contest_awardedProposalPrizes', true);

                $contest_completed_proposals = get_post_meta($contestId, 'contest_completed_proposals', true);


                $proposalPresent = explode(',', $contest_awardedPrizes);

                $job_type = get_post_meta($contestId, 'fl_job_type', true);

                if (count($proposalPresent) > 0) {

                    $estimated_budgets = (float)get_post_meta($contestId, 'estimated_budgets', true);
                    if ($job_type == 'contest') {

                        $linguist_referral_fee = floatval(get_option('linguist_referral_fee') ? get_option('linguist_referral_fee') : 15);
                        $linguist_flex_referral_fee = floatval(get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15);
                        $totalPay = $estimated_budgets - $linguist_referral_fee - (($estimated_budgets * $linguist_flex_referral_fee) / 100);
                    } else {
                        $totalPay = $estimated_budgets / count($proposalPresent);
                    }

                    $totalPay = max($totalPay, 0);


                    $proposals = $wpdb->get_results(
                        "SELECT * FROM wp_proposals WHERE id= $proposal_id");

                    $by_user_id = (int)$proposals[0]->by_user;
                    if (!$by_user_id) {
                        throw new RuntimeException("User does not exist for this proposal");
                    }
                    $linguMoney = (float)get_user_meta($proposals[0]->by_user, 'total_user_balance', true);

                    $addTolingu = $linguMoney + $totalPay;


                    update_user_meta($proposals[0]->by_user, 'total_user_balance', amount_format($addTolingu));


                    $new_txn_id = fl_transaction_insert($totalPay, 'done', 'contestDisputeApproved', $proposals[0]->by_user,
                        NULL, 'Proposal dispute: job approved by admin', 'wallet', '', $contestId, NULL, NULL);

                    $sql = "UPDATE wp_fl_transaction set proposal_id = $proposal_id  WHERE id = $new_txn_id";
                    will_throw_on_wpdb_error($wpdb,'setting proposal id to the new trx id of '.$new_txn_id);
                    $wpdb->query($sql);

                    if ($contest_completed_proposals) {
                        $contest_completed_proposals_array = explode(',', $contest_completed_proposals);
                    } else {
                        $contest_completed_proposals_array = array();
                    }

                    if (!in_array($proposal_id, $contest_completed_proposals_array)) {

                        if ($contest_completed_proposals != '') {

                            $allAwards = $contest_completed_proposals . ',' . $proposal_id;

                            $wpdb->update('wp_proposals', array(
                                'status' => 'completed'
                            ), array('id' => $proposal_id));

                            update_post_meta($contestId, 'contest_completed_proposals', $allAwards);
                        } else {
                            $allAwards = $proposal_id;

                            $wpdb->update('wp_proposals', array(
                                'status' => 'completed'
                            ), array('id' => $proposal_id));

                            update_post_meta($contestId, 'contest_completed_proposals', $allAwards);
                        }

                    }

                    $wpdb->update('wp_proposals', array(
                        'status' => 'completed',
                        'updated_at' => date('Y-m-d H:i:s')
                    ), array('id' => $proposal_id));

                    fl_message_insert("Proposal dispute approved by " . get_da_name($mediator_id) . "", "", $proposal_id);


                }


            }

            will_do_nothing($execut);


        } else {
            throw new InvalidArgumentException( 'Proposal not found');
        }

        wp_send_json( ['status' => true, 'message' => 'Contest Saved']);
    } catch (Exception $e) {
        will_send_to_error_log('admin save contest',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}