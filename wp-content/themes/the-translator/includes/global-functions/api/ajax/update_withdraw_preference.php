<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_withdraw_preference

 * Description: update_withdraw_preference

 *

 */

add_action('wp_ajax_update_withdraw_preference', 'update_withdraw_preference');


function update_withdraw_preference(){
    /*
    * current-php-code 2020-Oct-5
    * ajax-endpoint  update_withdraw_preference
    * input-sanitized : withdraw_pref
    */

    //credit_card_stripe,paypal,alipay
    $withdraw_pref = FLInput::get('withdraw_pref');
    $enabled_withdraw_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_WITHDRAW_GATEWAYS,FLPaymentGateways::DEFAULT_WITHDRAW_GATEWAY_NAMES_ARRAY);

    if ($withdraw_pref && in_array($withdraw_pref,$enabled_withdraw_gateways_array_of_names)) {

        update_user_meta( get_current_user_id(), FLPaymentGateways::OPTION_NAME_DEFAULT_WITHDRAW_METHOD, $withdraw_pref );

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}