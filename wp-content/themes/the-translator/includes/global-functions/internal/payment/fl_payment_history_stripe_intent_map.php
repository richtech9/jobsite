<?php
use Stripe\Charge;
use Stripe\PaymentIntent;
use Carbon\Carbon;

class FLPaymentHistoryStripeIntentMap {
    public static function make_map() {
        return [
            'custom_int' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $key = FLStripePaymentIntent::META_KEY_NAME_TRANSACTION;
                        if (isset($paymentIntent->metadata->$key)) {
                            return (int)$paymentIntent->metadata->$key;
                        }
                        throw new RuntimeException("Cannot find meta of $key");
                    }
            ],

            'amount' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $charge_list = $paymentIntent->charges;

                        /**
                         * @var Charge $charge
                         */
                        $charge = $charge_list->first();
                        if (empty($charge)) {
                            $iso_currency = $paymentIntent->currency;
                            if ($iso_currency !== strtolower(FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE)) {
                                throw new RuntimeException("Not configured for currencies other than ". FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE);
                            }
                            switch ($iso_currency) {
                                case 'usd': {
                                    $amount_in_cents = $paymentIntent->amount;
                                    return round($amount_in_cents /100,2);
                                }
                                default: {
                                    throw new RuntimeException("Not configured for currency of  ". $iso_currency);
                                }
                            }
                        }

                        $iso_currency = $charge->currency;
                        if ($iso_currency !== strtolower(FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE)) {
                            throw new RuntimeException("Not configured for currencies other than ". FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE);
                        }
                        switch ($iso_currency) {
                            case 'usd': {
                                $amount_in_cents = $charge->amount;
                                return round($amount_in_cents /100,2);
                            }
                            default: {
                                throw new RuntimeException("Not configured for currency of  ". $iso_currency);
                            }
                        }

                    }
            ],

            'country_code' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $charge_list = $paymentIntent->charges;

                        /**
                         * @var Charge $charge
                         */
                        $charge = $charge_list->first();

                        if (empty($charge)) {
                            $shipping = $paymentIntent->shipping;
                            if (!isset($shipping->address)) {
                                return null;
                            }
                            if (!isset($shipping->address->country)) {
                                return null;
                            }

                            return $shipping->address->country;
                        }

                        $country_code = null;
                        $billing_details = $charge->billing_details;
                        if (isset($billing_details->address)) {
                            if (!empty($billing_details->address->country)) {
                                $country_code = $billing_details->address->country;
                            }
                        }


                        if (empty($country_code) && isset( $charge->payment_method_details)) {
                            if (isset( $charge->payment_method_details->card)) {
                                if (isset( $charge->payment_method_details->card->country)) {
                                    $country_code = $charge->payment_method_details->card->country;
                                }
                            }
                        }
                        if(empty($country_code)) {$country_code = null;}
                        return $country_code;

                    }

            ],

            'currency' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $charge_list = $paymentIntent->charges;
                        /**
                         * @var Charge $charge
                         */
                        $charge = $charge_list->first();
                        $iso_currency = null;
                        if (empty($charge)) {
                            $iso_currency =  $paymentIntent->currency;
                        }

                        if ($charge && empty($iso_currency)) {
                            $iso_currency = $charge->currency;
                        }

                        if (empty($iso_currency)) {$iso_currency = null;}
                        return $iso_currency;

                    }
            ],

            'txn_id' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $charge_list = $paymentIntent->charges;

                        /**
                         * @var Charge $charge
                         */
                        $charge = $charge_list->first();

                        if (empty($charge)) {
                            return $paymentIntent->id;
                        }

                        $id = $charge->id;
                        return $id;

                    }
            ],

            'txn_type' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $charge_list = $paymentIntent->charges;
                        /**
                         * @var Charge $charge
                         */
                        $charge = $charge_list->first();

                        if (empty($charge)) {
                            return $paymentIntent->object; //mark it as a state of the payment intent, and not the charge, will be "payment_intent"
                        }

                        $payment_method_details = $charge->payment_method_details;

                        if (!isset($payment_method_details->type)) {
                            return null;
                        }

                        return $payment_method_details->type;

                    }
            ],

            'payment_status' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $charge_list = $paymentIntent->charges;

                        /**
                         * @var Charge $charge
                         */
                        $charge = $charge_list->first();

                        if (empty($charge)) {
                            return $paymentIntent->status;
                        }

                        return $charge->status;

                    }
            ],

            'item_name' => ['field' => 'description', 'type'=> 'string'],
            'item_number' => ['field' => '', 'type'=> 'string'],
            'receiver_email' => ['field' => '', 'type'=> 'null'],

            'payer_email' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $key = FLStripePaymentIntent::META_KEY_NAME_CUSTOMER_EMAIL;
                        if (isset($paymentIntent->metadata->$key)) {
                            return $paymentIntent->metadata->$key;
                        }
                        return null;
                    }
            ],

            'first_name' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $key = FLStripePaymentIntent::META_KEY_NAME_CUSTOMER_FIRST_NAME;
                        if (isset($paymentIntent->metadata->$key)) {
                            return $paymentIntent->metadata->$key;
                        }
                        return null;
                    }
            ],

            'last_name' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $key = FLStripePaymentIntent::META_KEY_NAME_CUSTOMER_LAST_NAME;
                        if (isset($paymentIntent->metadata->$key)) {
                            return $paymentIntent->metadata->$key;
                        }
                        return null;
                    }
            ],

            'payment_date' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param PaymentIntent $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var PaymentIntent $paymentIntent
                         */
                        $paymentIntent = $object;
                        $charge_list = $paymentIntent->charges;

                        /**
                         * @var Charge $charge
                         */
                        $charge = $charge_list->first();

                        if (empty($charge)) {
                            $timestamp =  $paymentIntent->created;
                            $carbon = Carbon::createFromTimestamp($timestamp);
                            return $carbon->toIso8601String();
                        }

                        $timestamp =  $charge->created;
                        $carbon = Carbon::createFromTimestamp($timestamp);
                        return $carbon->toIso8601String();

                    }
            ],
        ];
    }

    public static function status_to_fl($payment_status) {
        switch ($payment_status) {
            case null :
            case '':
                {
                    return null;
                }
            case 'payment_intent.amount_capturable_updated': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'payment_intent.canceled': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'payment_intent.created': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'payment_intent.payment_failed': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'payment_intent.processing': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'payment_intent.requires_action': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'payment_intent.succeeded': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE; }

            case 'requires_payment_method': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'requires_source_action': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'requires_confirmation': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'requires_action': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'processing': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'requires_capture': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'canceled': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'succeeded': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE; }

            case 'pending': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'failed': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }

            case 'requires_source': {return null;} //do not change any status as this is just the notification we are beginning

            default: { return 'unknown'; }

        }
    }

    /**
     * Sometimes we have a string guid, a full payment intent object, or some json
     * and this function allows a quick lookup without having the caller to think too hard about it
     * @param mixed $what
     * @return int
     */
    public static function get_transaction_post_id_from_mixed($what) {

        if (is_array($what)) {
            $proto_molecule = json_encode($what);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Cannot find transaction post id because cannot cast array to object successfully");
            }
            $what = json_decode($proto_molecule);
        }
        if (empty($what)) {throw new InvalidArgumentException("Empty value used to try to find transaction post id");}

        if (is_object($what)) {
            $key = FLStripePaymentIntent::META_KEY_NAME_TRANSACTION;
            if (isset($what->metadata->$key)) {
                return (int)$what->metadata->$key;
            }
            throw new RuntimeException("Cannot find meta of $key");
        }

        try {
            return FLPaymentHistoryIPN::get_top_transaction_post_id_from_any_guid($what);
        } catch (Exception $e) {
            //see if bloody json
            $my_object = json_decode($what);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Cannot find transaction post id from given value");
            }
            return static::get_transaction_post_id_from_mixed($my_object);
        }
    }
}