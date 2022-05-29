<?php

add_action('wp_ajax_hz_manage_milestone', 'hz_manage_milestone_cb');

/**
 * code-notes only the owner of the project, the author of the milestone or the recipient of the milestone can alter the status
 */
function hz_manage_milestone_cb()
{
    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  hz_manage_milestone
    * input-sanitized : fight,mid,revision_text,rejection_txt
    */
    //code-bookmark hz_manage_milestone_cb manages the milestones for customer and freelancer
    global $wpdb;

    $resp = array('status' => false, 'message' => 'Logic: not initialized');
    try {
        $fight = FLInput::get('fight');
        $mid = (int)FLInput::get('mid');
        if (!$mid) {
            throw new RuntimeException('empty milestone id');
        }
        $revision_text = FLInput::get('revision_text');
        $rejection_txt = FLInput::get('rejection_txt');
        $user_id = get_current_user_id();

        $user_role = xt_user_role();

        $sql_statement =
            "SELECT m.* ,
        UNIX_TIMESTAMP(m.completed_at) as completed_at_ts,
        UNIX_TIMESTAMP(m.updated_at) as updated_at_ts,
        post.post_author
        FROM wp_fl_milestones m
        INNER JOIN wp_posts post on m.project_id = post.ID
        WHERE m.id = $mid AND 
          (post.post_author = $user_id OR m.linguist_id = $user_id OR m.author = $user_id)";

        $milestone = $wpdb->get_results($sql_statement);

       will_throw_on_wpdb_error($wpdb);


        if ($fight == 'reject') {

            $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                rejection_requested = 1,
                                rejected_at = NOW(),
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
        ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);


            $da_name = get_da_name($user_id);
            fl_message_insert("Rejection request by " . $da_name . "", $milestone[0]->ID);

            update_post_meta($milestone[0]->project_id, 'project_new_status',
                $da_name . ', milestone ' . $milestone[0]->number . ': Dispute');
            $resp = array('status' => true, 'message' => 'Status updated successfully!!');

        } elseif ($fight == 'request_completion') {

            $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                completion_requested = 1,
                                completed_at = NOW(),
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
        ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            $da_name = get_da_name($user_id);
            fl_message_insert("Approve completion Request by " . $da_name . "", $milestone[0]->ID);


            update_post_meta($milestone[0]->project_id, 'project_new_status',
                $da_name . ', milestone ' .
                $milestone[0]->number . ': Review (<span class="demo_time_milestone_review" id="milestone_' . $milestone[0]->ID .
                '" data-milestone_id="' . $milestone[0]->ID . '" data-rejected_at="' . $milestone[0]->rejected_at .
                '" data-completed_at="' . $milestone[0]->completed_at_ts . //not used
                '" data-updated_at="' . $milestone[0]->completed_at_ts . //not used
                '" data-new_date="' .

                (intval($milestone[0]->completed_at_ts) +
                    (60 * 60 * floatval(get_option("auto_job_approvel_customer_hours")))) * 1000 .

                '"></span>)');

            $resp = array('status' => true, 'message' => 'Status updated successfully!!');

        } elseif ($fight == 'approved_rejection') {

            $ms_amount = $milestone[0]->amount;
            $project_id = $milestone[0]->project_id;

            $author = get_post_field('post_author', $project_id);

            $customer_balance = get_user_meta($author, 'total_user_balance', true);

            $amount = $ms_amount;

            $amount = max($amount, 0);

            $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
           ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            $da_name = get_da_name($user_id);
            fl_message_insert("Rejection approved by " . $da_name . "", $milestone[0]->ID);
            update_user_meta($author, 'total_user_balance', amount_format($customer_balance + $amount));

            $da_name = get_da_name($user_id);
            update_post_meta($milestone[0]->project_id, 'project_new_status',
                $da_name . ', milestone ' . $milestone[0]->number . ': Rejected');


            fl_transaction_insert($amount, 'done', 'milestoneRejected',
                $author, NULL, 'Milestone rejected by linguist',
                'wallet', '', NULL,NULL,NULL);

            $resp = array('status' => true, 'message' => 'Status updated successfully!!');
        } elseif ($fight == 'approve') {
            if ($user_role == 'customer') {


                $percentage = get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5; //Will manage from admin

                $ms_amount = $milestone[0]->amount;
                $job_id = $milestone[0]->job_id;
                $project_id = $milestone[0]->project_id;

                $fee = ($ms_amount * $percentage) / 100;

                $status = 'done';

                $type = 'milestone_created_by_customer';

                $user_id_added_by = $user_id;

                $description = 'Milestone created';

                $gateway = '';

                $txn_id = '';

                $milestone_id = $mid;

                $refundable = 0;

                $user_amount = get_user_meta($user_id, 'total_user_balance', true);

                $user_amount = $user_amount - ($ms_amount + $fee);


                if (update_user_meta($user_id, 'total_user_balance', amount_format($user_amount))) {


                    fl_transaction_insert('-' . $ms_amount, $status, $type, $user_id, $user_id_added_by, $description,
                        $gateway, $txn_id, $project_id, $job_id, $milestone_id, $refundable);

                    fl_transaction_insert('-' . $fee, $status, $type, $user_id, $user_id_added_by,
                        'Processing fee', $gateway, $txn_id, $project_id, $job_id, $milestone_id, $refundable);

                    $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
           ";
                    $wpdb->query($sql_statement);
                    will_throw_on_wpdb_error($wpdb);

                    $da_name = get_da_name($user_id);
                    fl_message_insert("Milestone approved by " . $da_name . "", $milestone[0]->ID);

                    update_post_meta($milestone[0]->project_id, 'project_new_status', 'Working');
                    $resp = array('status' => true, 'message' => 'Status updated successfully!!');



                } else {

                    throw new RuntimeException( 'Error!! Please Try again. Cannot update balance');

                }


            } else {

                $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
           ";
                $wpdb->query($sql_statement);
                will_throw_on_wpdb_error($wpdb);

                $da_name = get_da_name($user_id);
                fl_message_insert("Rejection " . $fight . " by " . $da_name . "", $milestone[0]->ID);

                update_post_meta($milestone[0]->project_id, 'project_new_status', 'Working');
                $resp = array('status' => true, 'message' => 'Status updated successfully!!');

            }
        } elseif ($fight == 'rejected') {

            $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
           ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            $da_name = get_da_name($user_id);
            fl_message_insert("New milestone rejected by " . $da_name . "", $milestone[0]->ID);

            update_post_meta($milestone[0]->project_id, 'project_new_status', 'Working');
            $resp = array('status' => true, 'message' => 'Status updated successfully!!');

        } elseif ($fight == 'hire_mediator') {
            if ($user_role == 'customer') {
            } else {

                $project_id = $milestone[0]->project_id;

                $job_data = get_post($project_id);

                $author_id = $job_data->post_author;


                $linguId = get_current_user_id();

                $getBalance = get_user_meta($linguId, 'total_user_balance', true);

                $mediator_fee = get_option('hire_mediator_fee') ? get_option('hire_mediator_fee') : 14.99;
                if ($linguId != '') {

                    fl_transaction_insert('-' . $mediator_fee . '', 'done',
                        'disputeRaise', $linguId, NULL, 'Linguist raised Dispute',
                        'wallet', '', $project_id, NULL,NULL);

                    $sealDeduc = $getBalance - $mediator_fee;

                    update_user_meta($linguId, 'total_user_balance', $sealDeduc);

                    $da_name = get_da_name($user_id);
                    update_post_meta($milestone[0]->project_id, 'project_new_status',
                        $da_name . ', milestone ' . $milestone[0]->number . ': Mediation');


                    $sql_statement = "INSERT INTO wp_dispute_cases 
                              (status,linguist_id,milestone_id,customer_id,contestId,
                              proposal_id,content_id,post_date,post_modified)
                              VALUES (
                                 'under_process', -- status
                                  $linguId, -- linguist_id
                                 {$milestone[0]->ID}, -- milestone_id
                                  $author_id, -- customer_id
                                  NULL, -- contestId
                                  NULL,  -- proposal_id
                                  NULL, -- content_id 
                                  NOW(), -- post_date
                                  NOW() -- post_modified
                              )
           ";
                    $wpdb->query($sql_statement);
                    will_throw_on_wpdb_error($wpdb);
                    $insert = $wpdb->insert_id;

                    if ($insert) {

                        $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
           ";
                        $wpdb->query($sql_statement);
                        will_throw_on_wpdb_error($wpdb);

                        $da_name = get_da_name($user_id);
                        fl_message_insert("Mediator is hired by " . $da_name . "", $milestone[0]->ID);
                        $resp = array('status' => true, 'message' => '');


                    } else {
                        throw new RuntimeException( 'Some error at time of dispute.');
                    }

                }
            }


        } else {


            $sql_statement = "UPDATE wp_fl_milestones 
                              SET
                                status = '$fight',
                                updated_at = NOW(),
                                revision_text = '$revision_text',
                                rejection_txt = '$rejection_txt'
                              WHERE
                                ID = $mid
           ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            $da_name = get_da_name($user_id);
            fl_message_insert("Revision request by " . $da_name . "", $milestone[0]->ID);

            update_post_meta($milestone[0]->project_id, 'project_new_status', 'Working');
            $resp = array('status' => true, 'message' => 'Status updated successfully!!');

        }


        wp_send_json($resp);
        die(); //above dies, but phpstorm does not know that, so adding it here for editing
    } catch (Exception $e) {
        will_send_to_error_log('milestone ajax', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing
    }

}

