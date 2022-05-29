<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      requestWithdraw_info

 * Description: requestWithdraw_info

 *

 */

add_action('wp_ajax_requestWithdraw_info', 'requestWithdraw_info');



function requestWithdraw_info(){

    /*
       * current-php-code 2020-Dec-28
       * ajax-endpoint  requestWithdraw_info
       * input-sanitized : amount,withdrawal_message,request_payment_notify
       */
    $amount = floatval(FLInput::get('amount',0.0));
    $withdraw_message = FLInput::get('withdrawal_message','', FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

    $request_payment_notify = FLInput::get('request_payment_notify');

    if( $amount ){

        $user               = get_userdata( get_current_user_id() );

        $total_user_balance = floatval(get_user_meta( get_current_user_id(), 'total_user_balance',true));

        $admin_email        = get_option('admin_email');




        if(true){



            if($total_user_balance >= $amount){

                $payment_notify = $request_payment_notify;
                $updated_amount = $total_user_balance - $amount;
                $updated_amount_formatted = amount_format($updated_amount) ;


                update_user_meta( get_current_user_id(), 'total_user_balance', $updated_amount_formatted);

                $transaction_id = transaction_updated('Withdrawl Amount: $'.$amount .' '.$payment_notify,
                    get_current_user_id(),
                    $amount,
                    'Withdrawl Amount: $'.$amount,
                    FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW],
                    false,FLTransactionPost::TRANSACTION_PENDING);

                update_post_meta($transaction_id,FLTransactionLookup::META_KEY_REQUEST_PAYMENT_NOTIFY,$request_payment_notify);

                update_post_meta($transaction_id,FLTransactionLookup::META_KEY_WITHDRAWAL_MESSAGE ,$withdraw_message);

                $variables = array();

                $variables['user_email'] = $user->user_email;

                $variables['withdrawal_amount'] = $amount;

                $variables['withdrawal_message'] = $withdraw_message;

                emailTemplateForUser($admin_email,ADMIN_RECIEVE_WITHDRAWL_REQUEST_TEMPLATE,$variables);

                echo 'success';

                exit;

            }else{

                echo "not_available";

                exit;

            }

        }else{

            echo "pending_job_exist";

            exit;

        }

    }else{

        echo 'failed';

        exit;

    }

}