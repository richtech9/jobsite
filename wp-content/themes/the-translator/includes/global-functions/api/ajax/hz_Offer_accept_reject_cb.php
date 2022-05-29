<?php

add_action( 'wp_ajax_hz_Offer_accept_reject',  'hz_Offer_accept_reject_cb'  );

/**
 * code-notes Only when the user is the freelancer which created the content
 */
function hz_Offer_accept_reject_cb()
{
    /*
     * current-php-code 2020-Oct-15
     * ajax-endpoint  hz_Offer_accept_reject
     * input-sanitized : contestId,request,offerShoot
     */
    //code-bookmark here is the ajax where the freelancer accepts or declines an offer for content
    global $wpdb;



    $log = [];
    try {

        $content_id = (int)FLInput::get('contestId');
        $cusid = (int)FLInput::get('cusid');
        $request =  FLInput::get('request');

        $user_id = get_current_user_id();
        $sql_statment =
            "SELECT * FROM wp_linguist_content WHERE id =$content_id AND user_id = $user_id ";
        $offerList = $wpdb->get_results($sql_statment);
        will_throw_on_wpdb_error($wpdb);
        if (empty($offerList)) {
            throw new RuntimeException("Either invalid content id or user not authorized to decide");
        }
        //will_send_to_error_log_and_array($log,'SQL for getting contest', $wpdb->last_query);

        //code-notes check to see if customer id is valid and existing user
        $sql_is_user_alive = "SELECT u.ID FROM wp_users u WHERE ID = $cusid ";
        $alive_res = $wpdb->get_results($sql_is_user_alive);
        if (empty($alive_res)) {
            throw new InvalidArgumentException("Customer is not there anymore, cannot accept or reject the bid for user id $cusid");
        }

        $allOffers = $offerList[0]->offersBy;

        $unserOffer = unserialize($allOffers);
        //will_send_to_error_log_and_array($log,'hz_Offer_accept_reject_cb: $unserOffer', $unserOffer);

        $key = array_search($cusid, array_column($unserOffer, 'cust_id'));
        // will_send_to_error_log_and_array($log,'hz_Offer_accept_reject_cb: $key', $key);

        if ($key === false) {
            throw new RuntimeException("Customer of $cusid NOT found in the offers made");
        }


        if ($request == 'accept') {

            $getDed_amount = floatval($unserOffer[$key]['amount']);

            $referral_fee = floatval(get_option('client_referral_fee') ? get_option('client_referral_fee') : 2);
            $referral_fee_per = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 15);
            $referral_flex_fee = ($getDed_amount * $referral_fee_per) / 100;

            $getDed = $referral_fee + $referral_flex_fee + $getDed_amount;
            $custBalance = floatval(get_user_meta($cusid, 'total_user_balance', true));

            $fee = $referral_fee + $referral_flex_fee;

            //will_send_to_error_log_and_array($log,'hz_Offer_accept_reject_cb: $fee', $fee);

            if (($unserOffer[$key]['cust_id'] === $cusid)) {


                $unserOffer[$key]['status'] = 'accepted';

                //code-notes put rejected for all the other offers
                foreach ($unserOffer as &$inOffers) {
                    if ($inOffers['cust_id'] === $cusid) { continue;}
                    $inOffers['status'] = 'rejected';
                }
                //will_send_to_error_log_and_array($log,'hz_Offer_accept_reject_cb: modified $unserOffer', $unserOffer);

                $neserialized_array = serialize($unserOffer);
                //will_send_to_error_log_and_array($log,'hz_Offer_accept_reject_cb: $neserialized_array', $neserialized_array);


                $wpdb->update( 'wp_linguist_content',
                    array(
                        'publish_type' => 'Purchased',
                        'purchased_by' => $cusid ,
                        'purchase_amount' => $getDed_amount,
                        'purchased_at' => current_time('Y-m-d H:i:s', $gmt = 1),
                        'updated_at' =>  current_time('Y-m-d H:i:s', $gmt = 1),
                        'offersBy' => $neserialized_array
                    ),
                    array( 'id' => $content_id),
                    array( '%s', '%d','%f','%s','%s','%s' ),
                    array( '%d' ) );

                $sql_to_update = "UPDATE wp_linguist_content SET 
                                    purchased_at = NOW(),
                                    updated_at = NOW()
                                    WHERE ID = $content_id";
                $wpdb->query($sql_to_update);
                will_log_on_wpdb_error($wpdb,'update wp_linguist_content purchase times');
                //will_send_to_error_log_and_array($log,'hz_Offer_accept_reject_cb: update nserialized sql ', $wpdb->last_query);
                will_throw_on_wpdb_error($wpdb);
                //will_send_to_error_log_and_array($log,'SQL for updating contest for offer accepted', [$wpdb->last_query,$update_Offer]);
                //code-bookmark the buyer is charged for the content here, but no update on the content row to show he owns it
                fl_transaction_insert((-1 * $getDed_amount), 'done', 'buy_content', $cusid, NULL,
                    'offer accepted by linguist.', '', '', NULL,NULL,NULL, 0);

                fl_transaction_insert((-1 * $fee), 'done', 'buy_content', $cusid, NULL,
                    'Processing fee.', '', '', NULL,NULL,NULL, 0);



                $sealDeduc = $custBalance - $getDed;

                update_user_meta($cusid, 'total_user_balance', $sealDeduc);

                //code-notes update content, updates existing per and top only
                FreelinguistUnitGenerator::generate_units($log,[],[$content_id]);

                $respo = array('status' => true, 'type' => 'accept', 'message' => '<div style="color:green;">Customer offer accepted. Notification email sent to Customer.</div>','code'=>201,'log'=>$log);

            } else {
                $respo = array('status' => false, 'type' => 'accept', 'message' => '<div style="color:red;">Customer offer does not exist.</div>','code'=>201,'log'=>$log);

            }


        } elseif (($request == 'reject') && ($key >= 0) && ($unserOffer[$key]['cust_id'] == $cusid)) {


            $unserOffer[$key]['status'] = 'rejected';


            $neserialized_array = serialize($unserOffer);
            $wpdb->update('wp_linguist_content', array('offersBy' => $neserialized_array), array('id' => $content_id));
            will_throw_on_wpdb_error($wpdb);
            //will_send_to_error_log_and_array($log,'SQL for updating contest for offer rejected', [$wpdb->last_query,$update_Offer]);
            $respo = array('status' => true, 'type' => 'reject', 'message' => '<div style="color:red;">Customer has been rejected. Notification email sent to Customer.</div>','code'=>201,'log'=>$log);

        } else {

            $respo = array('status' => false,'type' => 'error','message' => '<div style="color:red;">Some internal error occured. Please email admin.</div>','code'=>500,'log'=>$log);

        }

        wp_send_json($respo,201);

    } catch(Exception $e) {
        will_send_to_error_log('accept/reject offer',will_get_exception_string($e));
        wp_send_json(['status'=>false,'type' => 'error','message'=>'Could not successfully run hz_Offer_accept_reject_cb (stopped execution after): '.$e->getMessage(),'code'=>$e->getCode(),'log'=>$log],200);
    }
}