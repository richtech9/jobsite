<?php

new FLPaypalHandler(true,true);


class FLPaypalHandler extends  FreelinguistDebugging {
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const ACTION_NAME = 'fl_paypal';

    public function __construct($b_setup_hook = true,$b_debug = false){

        if ($b_setup_hook) {
            add_action('init', [$this,'fl_paypal_handler']);
        }

        if ($b_debug) {
            static::turn_on_debugging(static::LOG_DEBUG);
            PaypalIPNVerify::turn_on_debugging(static::LOG_DEBUG);
            FLPaymentHistory::turn_on_debugging(static::LOG_DEBUG);
            FLPaymentHistoryIPN::turn_on_debugging(static::LOG_DEBUG);
        } else {
            static::turn_on_debugging(static::LOG_WARNING);
            PaypalIPNVerify::turn_on_debugging(static::LOG_WARNING);
            FLPaymentHistory::turn_on_debugging(static::LOG_WARNING);
            FLPaymentHistoryIPN::turn_on_debugging(static::LOG_WARNING);
        }
    }

    function fl_paypal_handler(){
        /*
            * current-php-code 2020-Dec-28
            * ajax-endpoint  paypalPayments (not a true ajax)
            * input-sanitized : action,lang,payment_notify
            */
        global $_REAL_REQUEST,$_REAL_POST;

        $action = FLInput::get('action');
        $amount = floatval(FLInput::get('amount'));
        $payment_notify = FLInput::get('payment_notify');

        if($action !== static::ACTION_NAME) { return; }

        try {

            if (FLInput::exists('payment_notify') && ($payment_notify !== FLPaymentGateways::GATEWAY_PAYPAL)) {
               throw new RuntimeException("non paypal payment_notify");
            }



            if (!isset($_REAL_POST["txn_id"]) && !isset($_REAL_POST["txn_type"]) && isset($_REAL_POST['amount'])){
                //create a new pending transaction post
                $item_name = 'Refill';
                $item_title =  'Refill amount: '. $amount;
                $processing_fee = (float)get_refill_processing_charges($amount);
                $type_payment = FLPaymentHistoryIPN::PAYMENT_METHOD_PAYPAL;
                /*$new_payment_history_id = */ FLPaymentHistory::make_new_payment($amount,$processing_fee,$type_payment,$item_name,
                    $item_title,$transaction_id);
                $full_amount = $amount + $processing_fee;
                //call paypal
                $this->call_paypal_to_start_payment_process($full_amount,$transaction_id,$item_name);
            } else {
                static::log(static::LOG_DEBUG, "got response", $_REAL_REQUEST);




                $ipn_object = $this->check_ipn($transaction_id, $payment_history_id);

                $ipn_object->set_payment_history();
                $ipn_object->save();
                $ipn_object->update_transaction_from_ipn();

                FLPaymentHistory::payment_actions($ipn_object);
            }

            wp_send_json([
                'status' => 1,
                'message' => 'ok'
            ],
                200);
            die(); //never reached but makes code easier to read by phpstorm and human






        } catch (Exception $e) {

            static::log(static::LOG_WARNING,"non paypal payment_notify",
                [
                    "exception"=>will_get_exception_string($e),
                    "request"=>$_REAL_REQUEST
                ]);

            wp_send_json([
                    'status' => 0,
                    'message' => $e->getMessage()
                ],
                200);
            die(); //never reached but makes code easier to read by phpstorm and human
        }

    }

    /**
     * @param float $amount
     * @param int $transaction_post_id
     * @param string $item_name
     * Writes request to paypal, exits
     */
    protected function call_paypal_to_start_payment_process($amount,$transaction_post_id,$item_name) {

        if ($amount <= 0) {throw new InvalidArgumentException("Amount needs to be greater than zero");}
        $lang = FLInput::get('lang','en');

        $enabled_payment_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_PAYMENT_GATEWAYS,FLPaymentGateways::DEFAULT_PAYMENT_GATEWAY_NAMES_ARRAY);
        if (!in_array(FLPaymentGateways::GATEWAY_PAYPAL,$enabled_payment_gateways_array_of_names)) {
            throw new RuntimeException("Paypal is not enabled as a payment gateway");
        }


        /*
        * code-notes We do not create a webhook if the fl_is_fake_url_for_development is > 0 and if fl_ngrok_testing is empty
        */
        $b_is_fake_url  = intval(get_option('fl_is_fake_url_for_development',0));
        $str_ngrok = get_option('fl_ngrok_testing','');
        if ($b_is_fake_url && empty($str_ngrok)) {
           throw new RuntimeException("Cannot start the paypal because this is a fake website without ngrok activated");
        }

        $image_to_use = FLInput::get('cpp_header_image');

        $wallet_url = freeling_links('wallet_url');
        $notify_url = add_query_arg(  ['action'=> static::ACTION_NAME,'lang'=>$lang], $wallet_url);

        $return_url = add_query_arg(  ['redirect_to'=> 'payment','lang'=>$lang], $wallet_url);

        $cancel_url =  add_query_arg(  ['redirect_to'=> 'payment','lang'=>$lang,'option'=>'cancel'], $wallet_url);


        $data_to_paypal = [
            "business" => PAYPAL_EMAIL,
            "item_name" =>$item_name  ,
            "return" => $return_url,
            "cancel_return" => $cancel_url,
            "notify_url" => $notify_url,
            "custom" => intval($transaction_post_id),
            "amount" => $amount,
            "no_shipping" =>1,
            "handling" => 0,
            "currency_code" => FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE ,
            "cmd" => "_xclick",
            "cpp_header_image" => $image_to_use,
            "cpp_logo_image" => $image_to_use,
            "image_url" => $image_to_use

        ];

        $querystring = http_build_query($data_to_paypal);

        $url = PAYPAL_API_BASE_URL.'/cgi-bin/webscr?'.$querystring;

        static::log(static::LOG_DEBUG,'sending to paypal', [
            'data'=> $data_to_paypal,
            'url' => $url
        ]);
        // Redirect to paypal IPN
        header('location:'.$url);
        exit();
    }

    /**
     * @param int $transaction_id OUTREF
     * @param int $payment_history_id OUTREF
     * @throws Exception
     * @return FLPaymentHistoryIPN
     */
    protected function check_ipn(&$transaction_id,&$payment_history_id) {
        global $wpdb,$_REAL_POST;
        $b_is_sandbox = get_option('payment_mode','not-set') !== 'Live';

        $ipn = new PaypalIPNVerify();
        if ($b_is_sandbox) {
            $ipn->useSandbox();
        }
        $verified = $ipn->verifyIPN();
        if (!$verified) {throw new RuntimeException("ipn not verified");}

        $ipn_object  = new FLPaymentHistoryIPN(FLPaymentHistoryIPN::PAYMENT_METHOD_PAYPAL,$_REAL_POST);
        $post_id = $ipn_object->custom_int;
        if (!$post_id) {
            throw new RuntimeException("Could not find transaction post in the IPN");
        }
        $sql= "SELECT history.* FROM wp_payment_history history 
                WHERE transaction_post_id = $post_id
                ";
        $res = $wpdb->get_results($sql);
        if(empty($res)) {throw new RuntimeException("Could not find payment history from transaction post $post_id");}
        $history = $res[0];
        $history->payment_amount = floatval($history->payment_amount);

        $error_msg = sprintf("Amount does not match in transaction of %s : '.
        'History amount is %f and IPN amount is %f ; Post transaction id is %d",
            $ipn_object->txn_id,$history->payment_amount,$ipn_object->amount,$post_id);

        if ($ipn_object->fl_payment_status === FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE) {
            if ($ipn_object->amount !== $history->payment_amount) {throw new RuntimeException($error_msg);}
        }

        $error_msg = sprintf("Currency does not match in transaction of %s : '.
        'History currency is %s and IPN currecy is %s ; Post transaction id is %d",
            $ipn_object->txn_id,$history->currency,$ipn_object->currency,$post_id);

        if (strtolower($ipn_object->currency) !== strtolower($history->currency)) {throw new RuntimeException($error_msg);}

        $error_msg = sprintf("Our Email is empty or does not match in transaction of %s : '.
        'Our email is %s and IPN email is %s ; Post transaction id is %d",
            PAYPAL_EMAIL,$ipn_object->receiver_email,$ipn_object->currency,$post_id);

        if (empty($ipn_object->receiver_email)) {throw new RuntimeException($error_msg);}

        if (strcmp($ipn_object->receiver_email,PAYPAL_EMAIL) !== 0) {throw new RuntimeException($error_msg);}

        $transaction_id = $post_id;
        $payment_history_id = (int)$history->id;

        return $ipn_object;

    }

}
