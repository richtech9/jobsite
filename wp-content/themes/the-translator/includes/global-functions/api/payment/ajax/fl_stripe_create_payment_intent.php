<?php

use Stripe\PaymentIntent;
use Stripe\Stripe;

use Ramsey\Uuid\Uuid;




add_action('wp_ajax_fl_stripe_create_payment_intent', ['FLStripePaymentIntent','fl_stripe_create_payment_intent']);


class FLStripePaymentIntent  extends  FreelinguistDebugging {
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const META_KEY_NAME_TRANSACTION = 'transaction_post_id';
    const META_KEY_NAME_HISTORY = 'payment_history_id';
    const META_KEY_NAME_CUSTOMER_EMAIL = 'customer_email';
    const META_KEY_NAME_CUSTOMER_FIRST_NAME = 'customer_first_name';
    const META_KEY_NAME_CUSTOMER_LAST_NAME = 'customer_last_name';

    const ALLOWED_SOURCES = [
        'alipay','card'
    ];

    public static function make_new_transaction($amount_without_fee,&$transaction_id,&$new_payment_history_id) {
        $amount = $amount_without_fee;
        $item_name = 'Refill';
        $item_title =  'Refill For Wallet ';
        $processing_fee = (float)get_refill_processing_charges($amount);
        $type_payment = FLPaymentHistoryIPN::PAYMENT_METHOD_STRIPE;
        $new_payment_history_id = FLPaymentHistory::make_new_payment($amount,$processing_fee,$type_payment,$item_name,
            $item_title,$transaction_id,FLTransactionPost::TRANSACTION_NEW);

    }

    static function fl_stripe_create_payment_intent()
    {

        try {
            $amount = (float)FLInput::get('amount',0);
            $source = FLInput::get('source','card');
            $lang = FLInput::get('lang','en');


            if (!in_array($source,static::ALLOWED_SOURCES)) {
                throw new InvalidArgumentException("Source given was '$source' must be one of ".
                    implode('|',static::ALLOWED_SOURCES));
            }

            $enabled_payment_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_PAYMENT_GATEWAYS,FLPaymentGateways::DEFAULT_PAYMENT_GATEWAY_NAMES_ARRAY);

            if ($source === 'alipay') {
                if (!in_array(FLPaymentGateways::GATEWAY_ALIPAY,$enabled_payment_gateways_array_of_names)) {
                    throw new RuntimeException("AliPay is not enabled as a payment gateway");
                }
            }

            if ($source === 'card') {
                if (!in_array(FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD,$enabled_payment_gateways_array_of_names)) {
                    throw new RuntimeException("Stripe Credit Cards is not enabled as a payment gateway");
                }
            }


            $user_id = get_current_user_id();
            $user_data = get_userdata($user_id);
            $first_name = $user_data->first_name;
            $last_name = $user_data->last_name;
            if (empty($last_name)) {
                $last_name = get_da_name($user_id);
            }
            if (empty($amount)) {
                static::log(static::LOG_ERROR, 'Amount not given');
                throw new InvalidArgumentException("Amount not given");
            }
            if ($amount <= 0) {
                static::log(static::LOG_ERROR, "Amount not a positive number: $amount");
                throw new InvalidArgumentException("Amount not a positive number: $amount");
            }
            $uuid_object = Uuid::uuid4();
            $uuid = $uuid_object->toString();
            Stripe::setApiKey(STRIPE_SECRET_KEY);

            static::make_new_transaction($amount,$transaction_post_id,$payment_history_id);
            $amount_in_cents = intval(floor($amount *100));

            $wallet_url = freeling_links('wallet_url');
            $return_url = add_query_arg(  ['redirect_to'=> 'payment','lang'=>$lang], $wallet_url);

            $paymentIntent = PaymentIntent::create(
                [
                    'amount' => $amount_in_cents,
                    'currency' => strtolower(FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE),
                    'description' => 'Refill',
                    'payment_method_types' => [$source],
                    'metadata' => [
                        'server_guid'=> FLStripeWebHookManager::get_server_id(),
                        static::META_KEY_NAME_CUSTOMER_FIRST_NAME => $first_name,
                        static::META_KEY_NAME_CUSTOMER_LAST_NAME => $last_name,
                        static::META_KEY_NAME_CUSTOMER_EMAIL => $user_data->user_email,
                        static::META_KEY_NAME_TRANSACTION => $transaction_post_id,
                        static::META_KEY_NAME_HISTORY => $payment_history_id
                    ]
                ],
                [
                    'idempotency_key' => $uuid
                ]);

            $output = [
                'status' => true,
                'idempotency_key' => $uuid ,
                'message'=> 'ok',
                'publishable_key' => STRIPE_PUBLISHIABLE_KEY,
                'client_secret' => $paymentIntent->client_secret,
                'return_url' => $return_url
            ];
            static::log(static::LOG_DEBUG,'payment intent',[
                '$paymentIntent' => $paymentIntent,
                'return'=> $output
            ]);
            wp_send_json($output);
        } catch (Exception $e) {
            static::log(static::LOG_ERROR,'stripe_create_payment_intent', will_get_exception_string($e));
            wp_send_json(['status' => false, 'message' => $e->getMessage()],500);
        }
    }
}

FLStripePaymentIntent::turn_on_debugging(FLStripePaymentIntent::LOG_WARNING);
