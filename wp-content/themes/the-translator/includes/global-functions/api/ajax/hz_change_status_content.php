<?php

add_action( 'wp_ajax_hz_change_status_content',  'hz_change_status_content'  );


/*** change status of content *****/

/*
 * code-notes only the content creator or the buyer can change the status of the content
 */
 function hz_change_status_content(){

     /*
    * current-php-code 2020-Oct-14
    * ajax-endpoint  hz_change_status_content
    * input-sanitized :contentId,content_status,rejection_txt,revision_text
    */
    global $wpdb;

    try {
        //code-bookmark hz_change_status_content called when doing content operations
        $contentId = (int)FLInput::get('contentId');
        $content_status = FLInput::get('content_status');
        $revision_text = FLInput::get('revision_text');
        $rejection_txt = FLInput::get('rejection_txt');


        if (!$contentId){
            throw new RuntimeException("Empty content id recieved");
        }

        $user_id = get_current_user_id();


        $content = $wpdb->get_results(
            "SELECT 
                    line.*,
                    freelancer_user.user_email as freelancer_email,
                    customer_user.user_email as customer_email
                        FROM wp_linguist_content line
                        LEFT JOIN wp_users freelancer_user ON freelancer_user.ID = line.user_id
                        LEFT JOIN wp_users customer_user ON customer_user.ID = line.purchased_by
                    WHERE  line.user_id IS NOT NULL AND  line.id= $contentId AND 
                    ( line.user_id = $user_id OR line.purchased_by = $user_id)");

        will_throw_on_wpdb_error($wpdb);

        if (empty($content)) { throw new RuntimeException("Content not found or user does not have privledge");}

        /**** fee ***/
        $linguist_referral_fee = get_option('linguist_referral_fee') ? get_option('linguist_referral_fee') : 5;
        $linguist_referral_fee_per = get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15;

        $linguist_amount = get_user_meta($content[0]->user_id, 'total_user_balance', true);
        $customer_amount = get_user_meta($content[0]->purchased_by, 'total_user_balance', true);

        $user = wp_get_current_user();

        //code-notes acceptable status is an enum whose values are listed below
        //'pending', 'completed', 'request_revision', 'cancelled', 'rejected',
        // 'hire_mediator', 'request_completion', 'request_rejection'


        if ($content_status == 'request_rejection') {

            $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            rejection_requested = 1,
                            rejected_at = NOW(),
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text',
                            rejection_txt = '$rejection_txt'
                          WHERE
                            ID = $contentId
           ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            fl_message_insert("Rejection Request by " . get_da_name($user->ID) . "", "", "", $contentId);


            emailTemplateForUser( $content[0]->freelancer_email,
                EMAIL_TEMPLATE_CONTENT_REQUEST_REJECTION,
                [
                    'job_id'=> $content[0]->id,
                    'job_title' => $content[0]->content_title,
                    'job_status' => $rejection_txt
                ] );
            $resp = array('status' => true, 'message' =>'Content Rejection has been requested');
        } else if ($content_status == 'completed') {
            //code-notes [red dot actions]  make sure wallet balance >=0 before completed, same for proposals and milestone
            if ($customer_amount < 0) {
                throw new RuntimeException("Cannot complete: Customer wallet is negative");
            }
            if ($content[0]->content_sale_type == "Free") {

                $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId
            ";
                $wpdb->query($sql_statement);
                will_throw_on_wpdb_error($wpdb);
                $resp = array('status' => true, 'message' =>'Free Content has been completed');

            } else if ($content[0]->content_sale_type == "Offer") {
                //code-bookmark here is ajax that processes content offers that are accepted
                $offersBy = unserialize($content[0]->offersBy);
                foreach ($offersBy as $k => $v) {
                    if ($v['status'] == "accepted") {

                        $amount = $v['amount'];
                        $linguist_flex_referral_fee = ($amount * $linguist_referral_fee_per) / 100;
                        $net_amount = $amount - ($linguist_flex_referral_fee + $linguist_referral_fee);
                        $net_amount = max($net_amount, 0);

                        update_user_meta($content[0]->user_id, 'total_user_balance', amount_format($linguist_amount + $net_amount));

                        fl_transaction_insert($net_amount, 'done', 'contentWinner', $content[0]->user_id, NULL,
                            'Earnings from content sales', 'wallet', '', NULL,NULL,NULL,
                            0,$contentId);



                        $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

                        $wpdb->query($sql_statement);
                        will_throw_on_wpdb_error($wpdb);
                    }
                }
                $resp = array('status' => true, 'message' =>'Bid Content has been completed');
            } else {

                $amount = $content[0]->content_amount;
                $linguist_flex_referral_fee = ($amount * $linguist_referral_fee_per) / 100;
                $net_amount = $amount - ($linguist_flex_referral_fee + $linguist_referral_fee);

                $net_amount = max($net_amount, 0);

                update_user_meta($content[0]->user_id, 'total_user_balance', amount_format($linguist_amount + $net_amount));

                fl_transaction_insert($net_amount, 'done', 'contentWinner', $content[0]->user_id, NULL,
                    'Earnings from content sales', 'wallet', '', NULL,NULL,NULL,
                    0,$contentId);

                $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

                $wpdb->query($sql_statement);
                will_throw_on_wpdb_error($wpdb);
                $resp = array('status' => true, 'message' =>'Purchased Content has been completed');
            }
            fl_message_insert("Content completed by " . get_da_name($content[0]->user_id) . "", "", "", $contentId);
        } else if ($content_status == 'rejected' || $content_status == 'cancelled') {
            //code-notes the status will be rejected if request_rejection if approved by the freelancer
            if ($content[0]->content_sale_type == "Free") {
                $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

                $wpdb->query($sql_statement);
                will_throw_on_wpdb_error($wpdb);
            } else if ($content[0]->content_sale_type == "Offer") {
                $offersBy = unserialize($content[0]->offersBy);
                foreach ($offersBy as $k => $v) {
                    if ($v['status'] == "accepted") {

                        $amount = $v['amount'];

                        $net_amount = max($amount, 0);

                        update_user_meta($content[0]->purchased_by, 'total_user_balance', amount_format($customer_amount + $net_amount));

                        fl_transaction_insert($net_amount, 'done', 'contentRejected', $content[0]->purchased_by,
                            NULL, 'Content was rejected',
                            'wallet', '', NULL,NULL,NULL,
                            0,$contentId);

                        $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

                        $wpdb->query($sql_statement);
                        will_throw_on_wpdb_error($wpdb);
                    }
                }
            } else {

                $amount = $content[0]->content_amount;

                $net_amount = max($amount, 0);

                update_user_meta($content[0]->purchased_by, 'total_user_balance', amount_format($customer_amount + $net_amount));

                fl_transaction_insert($net_amount, 'done', 'contentRejected', $content[0]->purchased_by, NULL,
                    'Content was rejected', 'wallet', '', NULL,
                    NULL,NULL,0,$contentId);

                $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

                $wpdb->query($sql_statement);
                will_throw_on_wpdb_error($wpdb);
            }
            fl_message_insert("Content rejected by " . get_da_name($user->ID) . "", "", "", $contentId);
            $resp = array('status' => true, 'message' =>'Content has been completed');
        } else if ($content_status == "hire_mediator") {

            $linguId = get_current_user_id();

            $getBalance = get_user_meta($linguId, 'total_user_balance', true);

            $mediator_fee = get_option('hire_mediator_fee') ? get_option('hire_mediator_fee') : 14.99;


            fl_transaction_insert('-' . $mediator_fee . '', 'done', 'disputeRaiseContent',
                $linguId, NULL, 'Linguist raised Dispute', 'wallet', '',
                NULL,NULL, NULL,0,$content[0]->id);

            $sealDeduc = $getBalance - $mediator_fee;

            update_user_meta($linguId, 'total_user_balance', $sealDeduc);


            $sql_statement = "INSERT INTO wp_dispute_cases 
                  (status,linguist_id,milestone_id,customer_id,contestId,
                  proposal_id,content_id,post_date,post_modified)
                  VALUES (
                     'under_process', -- status
                      $linguId, -- linguist_id
                     NULL, -- milestone_id
                      {$content[0]->purchased_by}, -- customer_id
                      NULL, -- contestId
                      NULL,  -- proposal_id
                      {$content[0]->id}, -- content_id 
                      NOW(), -- post_date
                      NOW() -- post_modified
                  )
";
            $wpdb->query($sql_statement);
            will_get_last_id($wpdb,'Set Mediator for Content');

            $sql_statement = "UPDATE wp_linguist_content 
              SET
                status = '$content_status',
                updated_at = NOW(),
                revision_text = '$revision_text'
              WHERE
                ID = $contentId ";

            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            fl_message_insert("Mediator is hired by " . get_da_name($user->ID) . "", "", "", $contentId);


            //code-notes fixed user id and subject line for mediation emails
            $author_id = $content[0]->user_id;
            $email = get_the_author_meta('user_email', $author_id);

            $subject = 'Mediation has been started for Job  ' . $content[0]->content_title;

            $body = 'An independent mediator has been hired to mediate the case. The mediator will carefully consider any information presented and contact you shortly for an agreeable solution. If you want to present any additional information other than those in the Job Details, please email them to: dispute@peerok.com within 5 business days.';

            $headers = 'MIME-Version: 1.0' . "\r\n";

            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            if (wp_mail($email, $subject, $body, $headers)) {

                $resp = array('status' => true, 'message' =>'Mediator is being hired, and email sent');

            } else {

                $resp = array('status' => true, 'message' =>'Mediator is being hired, but there was an issue sending email');

            }



        } //end if else status is hire mediator
        else if ($content_status === 'request_completion') {

            $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            requested_completion_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);
            $resp = array('status' => true, 'message' =>'Request for Completion Made');
            fl_message_insert("Content " . $content_status . " by " . get_da_name($user->ID) . "", "", "", $contentId);
            emailTemplateForUser( $content[0]->customer_email,
                EMAIL_TEMPLATE_CONTENT_REQUEST_COMPLETION,
                [
                    'job_id'=> $content[0]->id,
                    'job_title' => $content[0]->content_title,
                    'job_status' => $revision_text
                ] );
        }
        else if ($content_status === 'request_revision') {

            $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            requested_completion_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);
            $resp = array('status' => true, 'message' =>'Request for Revision Made');
            fl_message_insert("Content " . $content_status . " by " . get_da_name($user->ID) . "", "", "", $contentId);
            emailTemplateForUser( $content[0]->freelancer_email,
                EMAIL_TEMPLATE_CONTENT_REQUEST_REVISION,
                [
                    'job_id'=> $content[0]->id,
                    'job_title' => $content[0]->content_title,
                    'job_status' => $revision_text
                ] );
        }
        else {

            // requested_completion_at
            $sql_statement = "UPDATE wp_linguist_content 
                          SET
                            status = '$content_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text'
                          WHERE
                            ID = $contentId ";

            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);
            $resp = array('status' => true, 'message' =>'Content Status Changed');
            fl_message_insert("Content " . $content_status . " by " . get_da_name($user->ID) . "", "", "", $contentId);
        }


        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing


    } catch (Exception $e) {
        will_send_to_error_log('content ajax', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }
}