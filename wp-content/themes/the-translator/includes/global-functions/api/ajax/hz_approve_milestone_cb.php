<?php
add_action( 'wp_ajax_hz_approve_milestone',  'hz_approve_milestone_cb'  );


/**
 * code-notes Only the owner of the project can approve the milestone
 */
 function hz_approve_milestone_cb(){
    //code-bookmark hz_approve_milestone_cb called to approve milestones by customer and have the fee taken out
     /*
     * current-php-code 2020-Oct-7
     * ajax-endpoint  hz_approve_milestone
     * input-sanitized : id
     */
     global $wpdb;
    try {
        $id = (int)FLInput::get('id');

        $user_id = get_current_user_id();

        $mtbl = $wpdb->prefix . 'fl_milestones';

        $mstons = $wpdb->get_row(
            "SELECT m.*,post.post_author
                    FROM wp_fl_milestones m
                    INNER JOIN wp_posts post on m.project_id = post.ID
                    WHERE m.ID = $id and post.post_author = $user_id;
      ");

        if (empty($mstons)) {
            throw new RuntimeException("Invalid milestone or user");
        }

        //code-notes [red dot actions]  make sure wallet balance >=0 before completed, same for proposals and milestone and content
        $customer_amount = get_user_meta($user_id, 'total_user_balance', true);
        if ($customer_amount < 0) {
            throw new RuntimeException("Cannot complete: Customer wallet is negative");
        }

        $percentage = get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15;
        $user_amount = get_user_meta($mstons->linguist_id, 'total_user_balance', true);
        $fee = ($mstons->amount * $percentage) / 100;

        $wpdb->update($mtbl, array('status' => 'completed'), array('ID' => $id));
        will_throw_on_wpdb_error($wpdb);
        $da_name = get_da_name($user_id);

        fl_message_insert("Milestone completed by " . $da_name . "", $id);

        update_post_meta($mstons->project_id, 'project_new_status', $da_name . ', milestone ' . $mstons->ID . ': Completed');

        $user_amount = $user_amount + ($mstons->amount - $fee);

        $net_amount = $mstons->amount - $fee;

        $net_amount = max($net_amount, 0);

        update_user_meta($mstons->linguist_id, 'total_user_balance', amount_format($user_amount));

        fl_transaction_insert($net_amount, 'done', 'milestone_completed', $mstons->linguist_id, get_current_user_id(),
            'Milestone Completed', '', '', $mstons->project_id, $mstons->job_id, $mstons->ID, 0);

        wp_send_json( ['status' => true, 'message' => 'Posted Successfully.']);

    } catch (Exception $e) {
        will_send_to_error_log('approve milestone',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}