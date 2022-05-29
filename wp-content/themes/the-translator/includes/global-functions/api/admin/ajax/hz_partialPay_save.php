<?php

add_action('wp_ajax_hz_partialPay_save', 'hz_partialPay_save_cb');


function hz_partialPay_save_cb()
{

    /*
        * current-php-code 2021-Jan-11
        * ajax-endpoint  hz_partialPay_save
        * input-sanitized : dbId,partial
        */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }

    try {
        $dbId = (int)FLInput::get('dbId');
        $partial = floatval(FLInput::get('partial'));


        if ($partial == 0) {
            $partial = 1;
        }


        $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
            "SELECT * FROM wp_dispute_cases WHERE `ID` = %d", array($dbId)), ARRAY_A);


        $contestId = $result['contestId'];

        $proposal_id = $result['proposal_id'];

        $execut = false;
        if ($proposal_id) {

            $mediator_id = get_current_user_id();

            $contest_awardedPrizes = get_post_meta($contestId, 'contest_awardedProposalPrizes', true);
            $contest_completed_proposals = get_post_meta($contestId, 'contest_completed_proposals', true);

            $proposalPresent = explode(',', $contest_awardedPrizes);

            $job_type = get_post_meta($contestId, 'fl_job_type', true);

            if (count($proposalPresent) > 0) {

                $estimated_budgets = (float)get_post_meta($contestId, 'estimated_budgets', true);

                $approved_price = ($partial / 100) * $estimated_budgets;

                if ($job_type == 'contest') {

                    $linguist_referral_fee = floatval(get_option('linguist_referral_fee') ? get_option('linguist_referral_fee') : 15);
                    $linguist_flex_referral_fee = floatval(get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15);
                    $totalPay = $approved_price - $linguist_referral_fee - (($approved_price * $linguist_flex_referral_fee) / 100);
                } else {
                    $totalPay = 0;
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


                $new_txn_id = fl_transaction_insert($totalPay, 'done', 'contestDisputePartlyApproved', $proposals[0]->by_user,
                    NULL, 'earnings  due to ' . $partial . '% partial approval',
                    'wallet', '', $contestId, NULL, NULL);

                $sql = "UPDATE wp_fl_transaction set proposal_id = $proposal_id  WHERE id = $new_txn_id";
                will_throw_on_wpdb_error($wpdb,'setting proposal id to the new trx id of '.$new_txn_id);
                $wpdb->query($sql);

                /******** payment to customer ******/

                $author = get_post_field('post_author', $contestId);


                $total_user_balance = get_user_meta($author, 'total_user_balance', true);

                $client_percentage = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5);

                $fee_on_complete_payment = ($estimated_budgets * $client_percentage) / 100;
                $fee_on_approved_payment = ($approved_price * $client_percentage) / 100;

                $net_amount_to_pay = ($estimated_budgets + $fee_on_complete_payment) - ($approved_price + $fee_on_approved_payment);

                update_user_meta($author, 'total_user_balance', amount_format($total_user_balance + $net_amount_to_pay));

                $new_txn_id =fl_transaction_insert($net_amount_to_pay, 'done', 'contestDisputePartlyApproved', $author, NULL,
                    'Refund due to ' . $partial . '% partial approval', 'wallet', '', $contestId, NULL, NULL);

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
                        $wpdb->update('wp_proposals', array('status' => 'completed'), array('id' => $proposal_id));

                        update_post_meta($contestId, 'contest_completed_proposals', $allAwards);
                    } else {
                        $allAwards = $proposal_id;
                        $wpdb->update('wp_proposals', array('status' => 'completed'), array('id' => $proposal_id));
                        update_post_meta($contestId, 'contest_completed_proposals', $allAwards);
                    }

                }

                $wpdb->update('wp_proposals', array(
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ), array('id' => $proposal_id));

                $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases 
                SET  status = %s, mediator_id = %d,approved_partially = %s 
                WHERE ID = %d", 'approved_partially',
                    $mediator_id, $partial, $dbId));

                fl_message_insert("Proposal dispute " . $partial . "% approved by " . get_da_name($mediator_id) . "", "", $proposal_id);

            }

            if ($execut) {
                wp_send_json( ['status' => true, 'message' => 'Contest Saved']);
            } else {
                throw new RuntimeException( 'Could not update');
            }

            wp_die();

        } else {
            throw new RuntimeException( 'Could not update');
        }
    } catch (Exception $e) {
        will_send_to_error_log('admin partial pay contest',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }
}