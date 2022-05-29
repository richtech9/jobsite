<?php
add_action( 'wp_ajax_freelinguist_proposal_seal',  'freelinguist_proposal_seal'  );

/*
 *ajax returns FreelinguistBasicAjaxResponse
 *  int status
 *  string message
 */


/**
 * Checks to make sure that the proposal_id is owned by the current user
 * checks to make sure this has not already been done
 */
function freelinguist_proposal_seal() {

    /*
    * current-php-code 2020-Nov-8
    * ajax-endpoint  freelinguist_proposal_seal
    * input-sanitized:  contest_id
    */
    global $wpdb;

    $contest_id = (int)FLInput::get('contest_id',0);

    try {
        $user_id = get_current_user_id();
        $user_balance = (float)get_user_meta($user_id, 'total_user_balance', true);
        $seal_fee = (float)get_option('seal_fee') ? get_option('seal_fee') : 10;
        $new_user_balance = $user_balance - $seal_fee;

        $post_title = FreelinguistProjectAndContestHelper::is_contest($contest_id);

        if (empty($post_title)) {
            throw new InvalidArgumentException("the contest id of '$contest_id' is not valid");
        }




        $seal_exists_result = $wpdb->get_results(
            "SELECT t.ID , p.post_title
                     FROM wp_fl_transaction t
                     INNER JOIN wp_posts p ON p.ID = t.project_id
                    WHERE user_id = $user_id AND type = 'seal_Files'  AND project_id = $contest_id");

        if (!empty($seal_exists_result)) {
            $post_title = $seal_exists_result[0]->post_title;
            throw new InvalidArgumentException("The files are already sealed for the contest $post_title");
        }


        fl_transaction_insert('-' . $seal_fee . '', 'done', 'seal_Files',
                $user_id, NULL, 'seal my uploads', 'wallet',
                '', $contest_id, NULL,NULL);



        update_user_meta($user_id, 'total_user_balance', $new_user_balance);

        $ret = [
            'status'=>1,
            'message' => "All your uploads are sealed."
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