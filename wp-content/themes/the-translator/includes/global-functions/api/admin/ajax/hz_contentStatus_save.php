<?php

add_action('wp_ajax_hz_contentStatus_save', 'hz_contentStatus_save_cb');

/******** Manage content cases ****************************/
function hz_contentStatus_save_cb()
{

    /*
        * current-php-code 2021-Jan-11
        * ajax-endpoint  hz_contentStatus_save
        * input-sanitized : dbId,selStatus
        */

    global $wpdb;

    if (!current_user_can('manage_options')) {
        exit;
    }

    $dbId = (int)FLInput::get('dbId');
    $selStatus = FLInput::get('selStatus');


    $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
        "SELECT * FROM wp_dispute_cases WHERE `ID` = %d", array($dbId)), ARRAY_A);


    $contentId = $result['content_id'];

    $execut = false;
    if ($contentId) {

        if ($selStatus == "rejected_by_mediator") {


            $mediator_id = get_current_user_id();
            $content = $wpdb->get_row($wpdb->prepare(/** @lang text */
                "SELECT * FROM wp_linguist_content WHERE user_id IS NOT NULL AND id = %d", array($contentId)), ARRAY_A);



            $customer_amount = floatval(get_user_meta($content['purchased_by'], 'total_user_balance', true));

            /****** paying to customer *****/
            if ($content['content_sale_type'] == "Free") {

                $wpdb->update('wp_linguist_content', array('status' => 'rejected', 'updated_at' => date('Y-m-d H:i:s')), array('id' => $contentId));
                $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases 
                        SET status = %s, mediator_id = %d 
                        WHERE content_id = %d", "rejected_by_mediator",
                                $mediator_id, $contentId));

            } else if ($content['content_sale_type'] == "Offer") {

                $offersBy = unserialize($content['offersBy']);
                foreach ($offersBy as $k => $v) {
                    if ($v['status'] == "accepted") {

                        $amount = $v['amount'];
                        $net_amount = $amount;

                        $net_amount = max($net_amount, 0);

                        update_user_meta($content['purchased_by'], 'total_user_balance', amount_format($customer_amount + $net_amount));

                        fl_transaction_insert($net_amount, 'done', 'contentDisputeRejected', $content['purchased_by'], NULL,
                            'Content dispute: job cancelled by admin', 'wallet', '', NULL,
                            NULL, NULL,0,$contentId);

                        $wpdb->update('wp_linguist_content', array(
                                                                            'status' => 'rejected',
                                                                            'updated_at' => date('Y-m-d H:i:s')
                                                                        ), array('id' => $contentId));

                        $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                            "UPDATE wp_dispute_cases 
                                SET status = %s, mediator_id = %d 
                                WHERE content_id = %d", "rejected_by_mediator",
                                        $mediator_id, $contentId));
                    }
                }
            } else {

                $amount = $content['content_amount'];
                $net_amount = $amount;

                $net_amount = max($net_amount, 0);

                update_user_meta($content['purchased_by'], 'total_user_balance', amount_format($customer_amount + $net_amount));

                fl_transaction_insert($net_amount, 'done', 'contentDisputeRejected', $content['purchased_by'], NULL,
                    'Content dispute: job cancelled by admin', 'wallet', '', NULL, NULL,
                    NULL,0,$contentId);

                $wpdb->update('wp_linguist_content', array('status' => 'rejected', 'updated_at' => date('Y-m-d H:i:s')), array('id' => $contentId));

                $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases 
                    SET status = %s, mediator_id = %d 
                    WHERE content_id = %d", "rejected_by_mediator",
                            $mediator_id, $contentId));
            }

            fl_message_insert("Content dispute rejected by " . get_da_name($mediator_id) . "", "", "", $contentId);


        } else if ($selStatus == "approved") {

            $mediator_id = get_current_user_id();
            $content = $wpdb->get_row($wpdb->prepare(/** @lang text */
                "SELECT * FROM wp_linguist_content WHERE user_id IS NOT NULL AND id = %d", array($contentId)), ARRAY_A);


            /*Start Paying to customer*/


            $linguist_referral_fee = get_option('linguist_referral_fee') ? get_option('linguist_referral_fee') : 5;
            $linguist_referral_fee_per = get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15;

            $linguist_amount = get_user_meta($content['user_id'], 'total_user_balance', true);

            /****** paying to customer *****/
            if ($content['content_sale_type'] == "Free") {

                $wpdb->update('wp_linguist_content', array('status' => 'completed', 'updated_at' => date('Y-m-d H:i:s')), array('id' => $contentId));

                $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases 
                    SET status = %s, mediator_id = %d 
                    WHERE content_id = %d", "approved",
                    $mediator_id, $contentId));


            } else if ($content['content_sale_type'] == "Offer") {
                $offersBy = unserialize($content['offersBy']);
                foreach ($offersBy as $k => $v) {
                    if ($v['status'] == "accepted") {

                        $amount = $v['amount'];
                        $linguist_flex_referral_fee = ($amount * $linguist_referral_fee_per) / 100;
                        $net_amount = $amount - ($linguist_flex_referral_fee + $linguist_referral_fee);

                        $net_amount = max($net_amount, 0);


                        update_user_meta($content['user_id'], 'total_user_balance', amount_format($linguist_amount + $net_amount));

                        fl_transaction_insert($net_amount, 'done', 'contentDisputeApproved', $content['user_id'],
                            NULL, 'Content dispute: job approved by admin', 'wallet',
                            '', NULL, NULL, NULL,0,$contentId);

                        $wpdb->update('wp_linguist_content', array(
                            'status' => 'completed',
                            'updated_at' => date('Y-m-d H:i:s')
                        ), array('id' => $contentId));

                        $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                            "UPDATE wp_dispute_cases 
                            SET status = %s, mediator_id = %d
                             WHERE content_id = %d", "approved",
                            $mediator_id, $contentId));
                    }
                }
            } else {

                $amount = $content['content_amount'];
                $linguist_flex_referral_fee = ($amount * $linguist_referral_fee_per) / 100;
                $net_amount = $amount - ($linguist_flex_referral_fee + $linguist_referral_fee);


                $net_amount = max($net_amount, 0);

                update_user_meta($content['user_id'], 'total_user_balance', amount_format($linguist_amount + $net_amount));

                fl_transaction_insert($net_amount, 'done', 'contentDisputeRejected', $content['user_id'],
                    NULL, 'Content dispute: job approved by admin', 'wallet',
                    '', NULL, NULL, NULL,0,$contentId);

                $wpdb->update('wp_linguist_content', array(
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ), array('id' => $contentId));

                $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases 
                    SET status = %s, mediator_id = %d 
                    WHERE content_id = %d", "approved",
                    $mediator_id, $contentId));
            }

            fl_message_insert("Content dispute approved by " . get_da_name($mediator_id) . "", "", "", $contentId);


        }

        will_do_nothing($execut);


    } else {
        echo 'Content not found';
    }

    wp_die();

}