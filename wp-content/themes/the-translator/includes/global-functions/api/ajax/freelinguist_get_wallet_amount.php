<?php
add_action( 'wp_ajax_freelinguist_get_wallet_amount',  'freelinguist_get_wallet_amount'  );

/*
 *ajax returns FreelinguistWalletResponse
 *  int status
 *  string message
 *  float wallet_amount
 */


/**
 * Checks to make sure that the proposal_id is owned by the current user
 * checks to make sure this has not already been done
 */
function freelinguist_get_wallet_amount() {

    /*
    * current-php-code 2020-Nov-17
    * ajax-endpoint  freelinguist_get_wallet_amount
    * input-sanitized:
    */


    try {
        $user_id = get_current_user_id();
        if (!$user_id) { throw new InvalidArgumentException("Not logged in");}
        $user_balance = (float)get_user_meta($user_id, 'total_user_balance', true);


        $ret = [
            'status'=>1,
            'message' => "Wallet Balance",
            'wallet_amount' => $user_balance
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