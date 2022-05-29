<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_payment_preference

 * Description: update_payment_preference

 *

 */

add_action('wp_ajax_update_payment_preference', 'update_payment_preference');


function update_payment_preference(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_payment_preference
    * input-sanitized : payment_notify
    */

    //credit_card_stripe,paypal,alipay
    $payment_notify = FLInput::get('payment_notify');
    $enabled_payment_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_PAYMENT_GATEWAYS,FLPaymentGateways::DEFAULT_PAYMENT_GATEWAY_NAMES_ARRAY);

    if ($payment_notify && in_array($payment_notify,$enabled_payment_gateways_array_of_names)) {
        update_user_meta( get_current_user_id(), FLPaymentGateways::OPTION_NAME_DEFAULT_PAYMENT_METHOD, $payment_notify );

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}