<?php

add_action( 'wp_ajax_hz_create_milestone',  'hz_create_milestone_cb'  );

/**
 * code-notes Only created when logged in user is the owner of the project or a hired linguist;
 * code-notes Only filled when logged in user is the owner
 */
function hz_create_milestone_cb(){
    //code-bookmark hz_create_milestone_cb milestone is created here

    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  hz_create_milestone
    * input-sanitized : data keys-> bid_id,job_id,linguist_id, ms_amount, ms_delivery_date,ms_details,project_id
    */
    global $wpdb;

    try {
        $data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);

        $data = [];

        parse_str($data_string, $data);

        $ms_amount = floatval(FLInput::clean_data_key($data, 'ms_amount'));

        $ms_delivery_date = FLInput::clean_data_key($data, 'ms_delivery_date', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $job_id = (int)FLInput::clean_data_key($data, 'job_id');

        $ms_details = FLInput::clean_data_key($data, 'ms_details', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $user_id = get_current_user_id();

        $sql = "
            SELECT
              j.id as job_id,
              post.ID as project_id,
              post.post_author as customer_id,
              j.linguist_id as freelancer_id,
              j.bid_id as bid_id
            FROM wp_fl_job j
              INNER JOIN wp_posts post on j.project_id = post.ID
            WHERE j.id = $job_id AND
                  (j.linguist_id = $user_id OR post.post_author = $user_id)
            ";

        $check_res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb);
        if (empty($check_res)) {
            throw new RuntimeException("Either this is invalid job or the user is not authorized");
        }

        $project_id = (int)$check_res[0]->project_id;
        $customer_id = (int)$check_res[0]->customer_id;
        $linguist_id = (int)$check_res[0]->freelancer_id;
        $bid_id = (int)$check_res[0]->bid_id;


        if (!$ms_amount) {
            throw new RuntimeException( 'please enter amount');

        }
        if (!$ms_delivery_date) {
            throw new RuntimeException( 'please enter date');
        }


        $percentage = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5); //Will manage from admin


        $fee = ($ms_amount * $percentage) / 100;


        $sql_statement =
            "select MAX(number) as number_count from wp_fl_milestones
         where project_id=" . $project_id . " AND linguist_id = $linguist_id";
        $latest_number = $wpdb->get_row($sql_statement);

        $number_count = $latest_number->number_count + 1;
        $wpdb->insert('wp_fl_milestones', array(

            'job_id' => $job_id,

            'project_id' => $project_id,

            'bid_id' => $bid_id,

            'content' => $ms_details,

            'amount' => $ms_amount,

            'author' => $user_id,

            'linguist_id' => $linguist_id,

            'delivery_date' => $ms_delivery_date,

            'post_date' => current_time('Y-m-d H:i:s', $gmt = 1),

            'status' => 'requested',
            'number' => $number_count
        ));

        $new_milestone_id = will_get_last_id($wpdb,'new milestone');

        $sql_to_update = "UPDATE wp_fl_milestones SET post_date = NOW() WHERE ID = $new_milestone_id";
        $wpdb->query($sql_to_update);
        will_throw_on_wpdb_error($wpdb, 'create milestone post_date');




        if ($user_id === $customer_id) {

            $status = 'done';

            $type = 'milestone_created_by_customer';

            $user_id_added_by = $user_id;

            $description = 'Milestone created';

            $gateway = '';

            $txn_id = '';

            $milestone_id = $new_milestone_id;

            $refundable = 0;

            $user_amount = get_user_meta($user_id, 'total_user_balance', true);

            $user_amount = $user_amount - ($ms_amount + $fee);

            $b_wallet_updated = update_user_meta($user_id, 'total_user_balance', amount_format($user_amount));
            if (!$b_wallet_updated) {
                throw new RuntimeException('Error updating user wallet!');
            }


            //fl_transaction_insert( $amount, $status, $type, $user_id, $user_id_added_by, $description, $gateway, $txn_id, $project_id, $job_id, $milestone_id, $refundable );
            fl_transaction_insert('-' . $ms_amount, $status, $type, $user_id, $user_id_added_by,
                $description, $gateway, $txn_id, $project_id, $job_id, $milestone_id, $refundable);

            fl_transaction_insert('-' . $fee, $status, $type, $user_id, $user_id_added_by,
                'Processing fee', $gateway, $txn_id, $project_id, $job_id, $milestone_id, $refundable);

            $sql_to_update = "UPDATE wp_fl_milestones SET updated_at = NOW(), status  = 'approve'  WHERE ID = $new_milestone_id";
            $wpdb->query($sql_to_update);
            will_throw_on_wpdb_error($wpdb, 'create milestone post_date');

            $da_name = get_da_name($user_id);
            fl_message_insert("Milestone created by " . $da_name . "", $new_milestone_id);
            $message = "Milestone created by customer";


        } else {
            $da_name = get_da_name($user_id);
            fl_message_insert("Milestone created by " . $da_name . "", $new_milestone_id);
            $message = "Milestone created by freelancer";
        }



        wp_send_json( ['status' => true, 'message' => $message,
            'form_key' => FreeLinguistFormKey::create_form_key('hz_create_milestone')]);
    } catch (Exception $e) {
        will_send_to_error_log('create milestone',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage(),
            'form_key' => FreeLinguistFormKey::create_form_key('hz_create_milestone')]);
    }

}