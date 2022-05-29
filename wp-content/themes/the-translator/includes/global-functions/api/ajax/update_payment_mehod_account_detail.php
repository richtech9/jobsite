<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      update_payment_mehod_account_detail

 * Description: update_payment_mehod_account_detail

 *

 */

add_action('wp_ajax_update_payment_mehod_account_detail', 'update_payment_mehod_account_detail');


function update_payment_mehod_account_detail(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_payment_mehod_account_detail
    * input-sanitized : alipay_account, paypal_account
    */

    $paypal_account = FLInput::get('paypal_account');
    $alipay_account = FLInput::get('alipay_account');

    if(empty($paypal_account) && empty($alipay_account)){

        echo "empty_data";

        exit;

    }else{

        if(isset($paypal_account) && isset($alipay_account)){

            update_user_meta( get_current_user_id(), 'paypal_account', strip_tags($paypal_account));

            update_user_meta( get_current_user_id(), 'alipay_account', strip_tags($alipay_account));

            echo 'success';

            exit;

        }else{

            echo 'failed';

            exit;

        }

    }

}