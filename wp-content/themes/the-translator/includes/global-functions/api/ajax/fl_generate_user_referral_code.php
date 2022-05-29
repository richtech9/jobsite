<?php

add_action( 'wp_ajax_fl_generate_user_referral_code',  'fl_generate_user_referral_code'  );

/**
 * code-notes Only when the user to be added is the same as the logged in user
 */
function fl_generate_user_referral_code(){

    /*
   * current-php-code 2020-Oct-10
   * ajax-endpoint  fl_generate_user_referral_code
   * input-sanitized : lang
   */
    try {
        $user_id = get_current_user_id();
        $old_referral_code = FreelinguistUserLookupDataHelpers::get_user_referral_code($user_id);
        if ($old_referral_code) {
            throw new LogicException("User $user_id already has a referral code");
        }
        
        $new_code = FreelinguistUserLookupDataHelpers::set_referal_code_for_user_id($user_id);
        
        wp_send_json( [
            'status' => true,
            'message' => __('Your New Reference Code Is Generated!'),
            'user_id'=>$user_id,
            'referral_code'=>$new_code]);
        exit;



    } catch (Exception $e) {
        will_send_to_error_log('fl_generate_user_referral_code',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage(),'url'=>null]);
    }



}