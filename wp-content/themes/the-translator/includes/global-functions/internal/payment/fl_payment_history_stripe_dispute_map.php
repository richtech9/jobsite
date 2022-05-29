<?php
use Carbon\Carbon;
use Stripe\Dispute;


/*
    * Same way to get our own information from this event, as the refund, check to see if payment intent is a a string id, or an object
    * Once we have the payment id
    *
    * status
        warning_needs_response, warning_under_review, warning_closed, needs_response, under_review, charge_refunded, won, or lost.
       ----
       If get this, it means alipay has removed money from the account, so this moves the payment to failed, if not already
    */

class FLPaymentHistoryStripeDisputeMap {


    public static function make_map() {
        return [
            'custom_int' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Dispute $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Dispute $refund
                         */
                        $refund = $object;
                        return FLPaymentHistoryStripeIntentMap::get_transaction_post_id_from_mixed($refund->payment_intent);
                    }
            ],

            'amount' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Dispute $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Dispute $refund
                         */
                        $refund = $object;
                        $iso_currency = $refund->currency;
                        if ($iso_currency !== strtolower(FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE)) {
                            throw new RuntimeException("Not configured for currencies other than ". FLPaymentHistory::DEFAULT_CURRENCY_ISO_CODE);
                        }
                        switch ($iso_currency) {
                            case 'usd': {
                                $amount_in_cents = $refund->amount;
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
                 * @param Dispute $object
                 * @return string
                 */
                    function($object) {
                        will_do_nothing($object); //no warnings
                        return null;
                        //code-notes not important enough to spend time and get it from the charge, can lookup the charge gui and get the country if needed later

                    }

            ],

            'currency' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Dispute $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Dispute $refund
                         */
                        $refund = $object;
                        $iso_currency = $refund->currency;
                        return $iso_currency;
                    }
            ],

            'txn_id' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Dispute $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Dispute $refund
                         */
                        $refund = $object;
                        return $refund->id;

                    }
            ],

            'txn_type' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Dispute $object
                 * @return int
                 */
                    function($object) {
                        will_do_nothing($object); //no warnings
                        return 'generic-refund-type';
                    }
            ],

            'payment_status' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Dispute $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Dispute $refund
                         */
                        $refund = $object;
                        return $refund->status;

                    }
            ],

            'item_name' => ['field' => '', 'type'=> 'null'],
            'item_number' => ['field' => '', 'type'=> 'null'],
            'receiver_email' => ['field' => '', 'type'=> 'null'],
            'payer_email' => ['field' => '', 'type'=> 'null'],
            'first_name' => ['field' => '', 'type'=> 'null'],
            'last_name' => ['field' => '', 'type'=> 'null'],




            'payment_date' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Dispute $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Dispute $refund
                         */
                        $refund = $object;
                        $timestamp =  $refund->created;
                        $carbon = Carbon::createFromTimestamp($timestamp);
                        return $carbon->toIso8601String();

                    }
            ],
        ];
    }

    public static function status_to_fl($payment_status) {
        switch ($payment_status) {
            case null :
            case 'warning_needs_response':
            case 'warning_under_review':
            case 'warning_closed':
            case '':
                { return null; }

            case 'lost': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'charge_refunded': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'under_review': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'needs_response': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'won': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE; }

            default: { return 'unknown'; }

        }
    }
}