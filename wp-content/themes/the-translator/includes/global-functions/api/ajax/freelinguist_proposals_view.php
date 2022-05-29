<?php
add_action( 'wp_ajax_freelinguist_proposals_view',  'freelinguist_proposals_view'  );

/*
 *ajax returns FreelinguistBasicAjaxResponse
 *  int status
 *  string message
 */
function freelinguist_proposals_view() {
    /*
       * current-php-code 2020-Nov-8
       * ajax-endpoint  freelinguist_proposals_view
       * input-sanitized:  contest_id
       */
    global $wpdb;

    $contest_id = (int)FLInput::get('contest_id',0);

    try {
        $user_id = get_current_user_id();
        $user_balance = (float)get_user_meta($user_id, 'total_user_balance', true);
        $fee = floatval(get_option('view_other_proposals_fee',10));
        $new_user_balance = $user_balance - $fee;

        $post_title = FreelinguistProjectAndContestHelper::is_contest($contest_id);

        if (empty($post_title)) {
            throw new InvalidArgumentException("the contest id of '$contest_id' is not valid");
        }

        $this_exists_result = $wpdb->get_results(
            "SELECT t.ID , p.post_title
                     FROM wp_fl_transaction t
                     INNER JOIN wp_posts p ON p.ID = t.project_id
                    WHERE user_id = $user_id AND type = 'view_Sealed'  AND project_id = $contest_id");

        if (!empty($this_exists_result)) {
            $post_title = $this_exists_result[0]->post_title;
            throw new InvalidArgumentException("The files are already sealed".
                " in the contest $post_title");
        }


        fl_transaction_insert( -$fee, 'done', 'view_Sealed',
            $user_id, NULL, 'show unsealed uploads',
            'wallet', '', $contest_id, NULL,NULL );



        update_user_meta($user_id, 'total_user_balance', $new_user_balance);

        $ret = [
            'status'=>1,
            'message' => "Can now view files in the contest $post_title"
        ];

        wp_send_json($ret);


    } catch (Exception $e) {
        $ret = [
            'status'=>0,
            'message' => $e->getMessage()
        ];

        wp_send_json($ret);
    }
}