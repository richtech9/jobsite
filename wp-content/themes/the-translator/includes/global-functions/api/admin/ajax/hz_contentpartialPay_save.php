<?php

add_action('wp_ajax_hz_contentpartialPay_save', 'hz_contentpartialPay_save_cb');

function hz_contentpartialPay_save_cb()
{

    /*
        * current-php-code 2021-Jan-11
        * ajax-endpoint  hz_contentpartialPay_save
        * input-sanitized : dbId,partial
        */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }

    $dbId = (int)FLInput::get('dbId');


    $partial = floatval(FLInput::get('partial'));


    if ($partial == 0) {
        $partial = 1;
    }


    $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
        "SELECT * FROM wp_dispute_cases WHERE `ID` = %d", array($dbId)), ARRAY_A);


    $contentId = (int)$result['content_id'];


    if ($contentId) {

        $mediator_id = get_current_user_id();
        $content = $wpdb->get_row($wpdb->prepare(/** @lang text */
            "SELECT * FROM wp_linguist_content WHERE user_id IS NOT NULL AND id = %d", array($contentId)), ARRAY_A);



        $linguist_referral_fee = floatval(get_option('linguist_referral_fee') ? get_option('linguist_referral_fee') : 5);
        $linguist_referral_fee_per = floatval(get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15);

        $linguist_amount = (float)get_user_meta($content['user_id'], 'total_user_balance', true);

        $execut = false;

        if ($content['content_sale_type'] == "Free") {
            $wpdb->update('wp_linguist_content', array(
                                    'status' => 'completed',
                                    'updated_at' => date('Y-m-d H:i:s')
                                ),
                                array('id' => $contentId));

            $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                "UPDATE wp_dispute_cases 
                     SET status = %s, mediator_id = %d ,approved_partially = %s 
                     WHERE content_id = %d", "approved_partially",
                        $mediator_id, $partial, $contentId));

        } else if ($content['content_sale_type'] == "Offer") {
            $offersBy = unserialize($content['offersBy']);
            foreach ($offersBy as $k => $v) {
                if ($v['status'] == "accepted") {

                    $amount = floatval($v['amount']);

                    $approved_price = ($partial / 100) * $amount;

                    $linguist_flex_referral_fee = ($approved_price * $linguist_referral_fee_per) / 100;
                    $net_amount = $approved_price - ($linguist_flex_referral_fee + $linguist_referral_fee);


                    $net_amount = max($net_amount, 0);

                    update_user_meta($content['user_id'], 'total_user_balance', amount_format($linguist_amount + $net_amount));

                    fl_transaction_insert($net_amount, 'done', 'contentDisputePartlyApproved',
                                            $content['user_id'], NULL, 'Earnings due to ' . $partial . '% partial approval',
                                    'wallet', '', NULL, NULL, NULL,0,$contentId);


                    /******** payment to customer ******/

                    $author = $content['purchased_by'];


                    $total_user_balance = get_user_meta($author, 'total_user_balance', true);

                    $client_percentage = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5);

                    $fee_on_complete_payment = ($amount * $client_percentage) / 100;
                    $fee_on_approved_payment = ($approved_price * $client_percentage) / 100;

                    $net_amount_to_pay = ($amount + $fee_on_complete_payment) - ($approved_price + $fee_on_approved_payment);


                    $net_amount_to_pay = max($net_amount_to_pay, 0);

                    update_user_meta($author, 'total_user_balance', amount_format($total_user_balance + $net_amount_to_pay));

                    fl_transaction_insert($net_amount_to_pay, 'done', 'contentDisputePartlyApproved', $author, NULL,
                                'Refund due to ' . $partial . '% partial approval', 'wallet', '', NULL,
                        NULL, NULL,0,$contentId);


                    $wpdb->update('wp_linguist_content', array(
                                        'status' => 'completed',
                                        'updated_at' => date('Y-m-d H:i:s')
                    ), array('id' => $contentId));

                    $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                        "UPDATE wp_dispute_cases 
                            SET status = %s, mediator_id = %d ,approved_partially = %s 
                            WHERE content_id = %d",
                        "approved_partially", $mediator_id, $partial, $contentId));
                }
            }
        } else {
            $amount = floatval($content['content_amount']);

            $approved_price = ($partial / 100) * $amount;

            $linguist_flex_referral_fee = ($approved_price * $linguist_referral_fee_per) / 100;
            $net_amount = $approved_price - ($linguist_flex_referral_fee + $linguist_referral_fee);

            $net_amount = max($net_amount, 0);

            update_user_meta($content['user_id'], 'total_user_balance', amount_format($linguist_amount + $net_amount));

            fl_transaction_insert($net_amount, 'done', 'contentDisputePartlyApproved', $content['user_id'],
                NULL, 'Earnings due to ' . $partial . '% partial approval', 'wallet',
                    '', NULL, NULL, NULL,0,$contentId);


            /******** payment to customer ******/

            $author = $content['purchased_by'];


            $total_user_balance = get_user_meta($author, 'total_user_balance', true);

            $client_percentage = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5);

            $fee_on_complete_payment = ($amount * $client_percentage) / 100;
            $fee_on_approved_payment = ($approved_price * $client_percentage) / 100;

            $net_amount_to_pay = ($amount + $fee_on_complete_payment) - ($approved_price + $fee_on_approved_payment);

            $net_amount_to_pay = max($net_amount_to_pay, 0);
            update_user_meta($author, 'total_user_balance', amount_format($total_user_balance + $net_amount_to_pay));

            fl_transaction_insert($net_amount_to_pay, 'done', 'contentDisputePartlyApproved', $author, NULL,
                    'Refund due to ' . $partial . '% partial approval', 'wallet', '',
                NULL, NULL, NULL,0,$contentId);


            $wpdb->update('wp_linguist_content', array(
                                    'status' => 'completed',
                                    'updated_at' => date('Y-m-d H:i:s')
                            ), array('id' => $contentId));

            $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                "UPDATE wp_dispute_cases 
                      SET status = %s, mediator_id = %d ,approved_partially = %s 
                      WHERE content_id = %d", "approved_partially",
                            $mediator_id, $partial, $contentId));
        }

        fl_message_insert("Content dispute " . $partial . "% approved by " . get_da_name($mediator_id) . "", "", "", $contentId);

        if ($execut) {
            echo 'Updated';
        } else {
            echo 'Could not update';
        }

        wp_die();

    } else {
        echo 'Could not update';
        wp_die();
    }
}