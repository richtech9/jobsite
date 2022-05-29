<?php


add_action( 'wp_ajax_hz_change_status_contest_proposal',  'hz_change_status_contest_proposal' );


/*** change status of proposal *****/

/**
 * code-notes only allowed when the user is either the owner of the contest or the proposal
 */
 function hz_change_status_contest_proposal(){
    global $wpdb;
    //code-bookmark reference code  on hz_change_status_contest_proposal
     /*
     * current-php-code 2020-Oct-12
     * ajax-endpoint  hz_change_status_contest_proposal
     * input-sanitized : contestId,proposalId,proposal_status,revision_text,rejection_txt
     */

     try {
         FLInput::onlyPost(true);
         $contestId = (int)FLInput::get('contestId');
         if (!$contestId) {
             throw new RuntimeException("No contest id");
         }
         $proposalId = (int)FLInput::get('proposalId');
         if (!$proposalId) {
             throw new RuntimeException("No proposal id");
         }
         $proposal_status = FLInput::get('proposal_status');
         $revision_text = FLInput::get('revision_text');
         $rejection_txt = FLInput::get('rejection_txt');

         FLInput::onlyPost(false);

         $user_id = get_current_user_id();

         $proposals = $wpdb->get_results(
             "
          SELECT prop.*,
                  freelancer_user.user_email as freelancer_email,
                  customer_user.user_email as customer_email,
                  meta_for_id.meta_value as contest_modified_id
            FROM wp_proposals prop
              INNER JOIN wp_posts post on prop.post_id = post.ID
              LEFT JOIN wp_postmeta meta_for_id ON meta_for_id.post_id = post.ID AND meta_for_id.meta_key = 'modified_id'
              
               LEFT JOIN wp_users freelancer_user ON freelancer_user.ID = prop.by_user
               LEFT JOIN wp_users customer_user ON customer_user.ID = post.post_author
               
            WHERE prop.id= $proposalId AND
                  (post.post_author = $user_id OR prop.by_user = $user_id)
          
          ");

         will_throw_on_wpdb_error($wpdb);
         if (empty($proposals)) {
             throw new RuntimeException("Could not find proposal or user not authorized: $proposalId");
         }


         $user = wp_get_current_user();

         $user_role = $user->roles[0];


         if ($proposal_status == 'request_rejection') {

             $sql_statement = "UPDATE wp_proposals 
                          SET
                            rejection_requested = 1,
                            rejected_at = NOW(),
                            updated_at = NOW(),
                            revision_text = '$revision_text',
                            rejection_txt = '$rejection_txt'
                          WHERE
                            ID = $proposalId";

             $wpdb->query($sql_statement);
             will_throw_on_wpdb_error($wpdb);

             fl_message_insert("Rejection requested by " . get_da_name($user->ID) . "", "", $proposalId);

             update_post_meta($contestId, 'project_new_status', 'Proposal ' . $proposals[0]->number . ': Dispute');
             emailTemplateForUser( $proposals[0]->freelancer_email,
                 EMAIL_TEMPLATE_CONTEST_REQUEST_REJECTION,
                 [
                     'job_id'=> $proposals[0]->id,
                     'job_title' => $proposals[0]->contest_modified_id,
                     'job_status' => $rejection_txt
                 ] );
             $resp = array('status' => true, 'message' => 'Rejection Requested for Proposal');
         } else if ($proposal_status == 'rejected' || $proposal_status == 'cancelled') {


             $author_id = get_post_field('post_author', $contestId);

             $customer_balance = get_user_meta($author_id, 'total_user_balance', true);

             $contest_amount = get_post_meta($contestId, 'estimated_budgets', true);

             $net_amount = max($contest_amount, 0);

             update_user_meta($author_id, 'total_user_balance', ($customer_balance + $net_amount));

             $new_txn_id = fl_transaction_insert($net_amount, 'done', 'contestRejected',
                 $author_id, NULL, 'The competition is cancelled',
                 'wallet', '', $contestId, NULL,NULL);

             $sql = "UPDATE wp_fl_transaction set proposal_id = $proposalId  WHERE id = $new_txn_id";
             will_throw_on_wpdb_error($wpdb,'setting proposal id to the new trx id of '.$new_txn_id);
             $wpdb->query($sql);


             $sql_statement = "UPDATE wp_proposals 
                          SET
                            status = '$proposal_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text',
                            rejection_txt = '$rejection_txt'
                          WHERE
                            ID = $proposalId";

             $wpdb->query($sql_statement);
             will_throw_on_wpdb_error($wpdb);

             fl_message_insert("Proposal rejected by " . get_da_name($user->ID) . "", "", $proposalId);

             update_post_meta($contestId, 'project_new_status', 'Proposal ' . $proposals[0]->number . ': Rejected');
             $resp = array('status' => true, 'message' => "Proposal $proposal_status ");
         } elseif ($proposal_status == 'hire_mediator') {
             if ($user_role == 'customer') {
                 throw new RuntimeException("Customers cannot hire mediators for proposals");
             }
             $project_id = $contestId;

             $job_data = get_post($project_id);

             $author_id = $job_data->post_author;

             $linguId = get_current_user_id();

             $getBalance = get_user_meta($linguId, 'total_user_balance', true);

             $mediator_fee = get_option('hire_mediator_fee') ? get_option('hire_mediator_fee') : 14.99;


             $new_txn_id = fl_transaction_insert('-' . $mediator_fee . '', 'done',
                 'disputeRaise', $linguId, NULL,
                 'Linguist raised Dispute', 'wallet', '',
                 $project_id, NULL, NULL);

             $sql = "UPDATE wp_fl_transaction set proposal_id = $proposalId  WHERE id = $new_txn_id";
             will_throw_on_wpdb_error($wpdb,'setting proposal id to the new trx id of '.$new_txn_id);
             $wpdb->query($sql);

             $sealDeduc = $getBalance - $mediator_fee;

             update_user_meta($linguId, 'total_user_balance', $sealDeduc);


             $sql_statement = "INSERT INTO wp_dispute_cases 
                  (status,linguist_id,milestone_id,customer_id,contestId,
                  proposal_id,content_id,post_date,post_modified)
                  VALUES (
                     'under_process', -- status
                      $linguId, -- linguist_id
                      NULL, -- milestone_id
                      $author_id, -- customer_id
                      $contestId, -- contestId
                      $proposalId,  -- proposal_id
                      NULL, -- content_id 
                      NOW(), -- post_date
                      NOW() -- post_modified
                  )";
             $wpdb->query($sql_statement);
             will_throw_on_wpdb_error($wpdb);
             will_get_last_id($wpdb, 'dispute proposal');

             $sql_statement = "UPDATE wp_proposals 
              SET
                status = '$proposal_status',
                updated_at = NOW(),
                revision_text = '$revision_text',
                rejection_txt = '$rejection_txt'
              WHERE
                ID = $proposalId";

             $wpdb->query($sql_statement);
             will_throw_on_wpdb_error($wpdb);

             fl_message_insert("Mediator is hired by " . get_da_name($user->ID) . "", "", $proposalId);

             update_post_meta($contestId, 'project_new_status', 'Proposal ' . $proposals[0]->number . ': Mediation');


             $body = 'An independent mediator has been hired to mediate the case.' .
                 ' The mediator will carefully consider any information presented and' .
                 ' contact you shortly for an agreeable solution. ' .
                 'If you want to present any additional information other than those in the Job Details, ' .
                 'please email them to: dispute@peerok.com within 5 business days.';


             emailTemplateForUser( $proposals[0]->customer_email,
                 EMAIL_TEMPLATE_CONTEST_MEDIATOR_HIRED,
                 [
                     'job_id'=> $proposals[0]->id,
                     'job_title' => $proposals[0]->contest_modified_id,
                     'job_status' => $body
                 ] );
             $resp = array('status' => true, 'message' => "Mediator Hired for Proposal and Mail Sent ");



         } else {

             $sql_statement = "UPDATE wp_proposals 
                          SET
                            status = '$proposal_status',
                            updated_at = NOW(),
                            revision_text = '$revision_text',
                            rejection_txt = '$rejection_txt'
                          WHERE
                            ID = $proposalId";

             $wpdb->query($sql_statement);
             will_throw_on_wpdb_error($wpdb);


             fl_message_insert("Proposal  " . $proposal_status . " by " . get_da_name($user->ID) . "", "", $proposalId);

             if ($proposal_status == 'request_completion') {
                 update_post_meta($contestId, 'project_new_status',
                     'Proposal ' . $proposals[0]->number .
                     ': Review(<span class="demo_time_proposal_review" id="proposal_' .
                     $proposalId . '" data-proposal_id="' . $proposalId . '"  data-new_date="' .
                     ((time()*1000) + (floatval(get_option("auto_job_approvel_customer_hours")) * 60 * 60 * 1000)) .
                     '"></span>)'
                 );
                 emailTemplateForUser( $proposals[0]->customer_email,
                     EMAIL_TEMPLATE_CONTEST_REQUEST_COMPLETION,
                     [
                         'job_id'=> $proposals[0]->id,
                         'job_title' => $proposals[0]->contest_modified_id,
                         'job_status' => $revision_text
                     ] );
                 $resp = array('status' => true, 'message' => "Requested Completion for Proposal ");
             } else if ($proposal_status == 'completed') {
                 update_post_meta($contestId, 'project_new_status', 'Proposal ' . $proposals[0]->number . ': Completed');
                 $resp = array('status' => true, 'message' => "Proposal Completed ");
             } else {
                 //code-notes If a proposal is accepted, then close out any potential cancel request that may exist
                 FreelinguistContestCancellation::safely_close_potential_requests($contestId);
                 update_post_meta($contestId, 'project_new_status', 'Working');
                 $resp = array('status' => true, 'message' => "Proposal Working:$proposal_status  ");
             }

         }





         wp_send_json($resp);
         die();//above dies, but phpstorm does not know that, so adding it here for editing
     } catch (Exception $e) {
         will_send_to_error_log('content proposal ajax', will_get_exception_string($e));
         $resp = array('status' => false, 'message' => $e->getMessage());
         wp_send_json($resp);
         die();//above dies, but phpstorm does not know that, so adding it here for editing
     }
}