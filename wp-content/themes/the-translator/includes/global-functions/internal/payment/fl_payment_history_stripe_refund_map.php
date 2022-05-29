<?php
use Carbon\Carbon;
use Stripe\Refund;

/*
 * The refund object, as given, may not have the full payment intent object, and thus no meta data
 * A) Check to see if refund object has full payment intent object, if so, then use meta data, else
 * B) get the refund object, then the payment intent id, then search the ipns for that id, if found will now have the payment id and transaction
 * once A or B, then proceed as normal
 *
 * https://stripe.com/docs/api/refunds/object
 * status
    string
    Status of the refund. For credit card refunds, this can be pending, succeeded, or failed.
     For other types of refunds, it can be pending, succeeded, failed, or canceled
        -----
        if the refund succeeds then this whole payment now has failed status (if not already),
        if the refund fails, it means the money is back in the account (they had a bank or cc error and could not be given the money) so credit them again
            this means the payment succeeds again for us (if not already successful)

        Other than these two states, we do not care at all in this part of the code, so do nothing
 */

class FLPaymentHistoryStripeRefundMap {


    public static function make_map() {
        return [
            'custom_int' => [
                'field' => '',
                'type'=> 'function',
                'function' =>
                /**
                 * @param Refund $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Refund $refund
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
                 * @param Refund $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Refund $refund
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
                 * @param Refund $object
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
                 * @param Refund $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Refund $refund
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
                 * @param Refund $object
                 * @return string
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Refund $refund
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
                 * @param Refund $object
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
                 * @param Refund $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Refund $refund
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
                 * @param Refund $object
                 * @return int
                 */
                    function($object) {
                        if (!is_object($object)) {return null;}
                        /**
                         * @var Refund $refund
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
            case '':{return null;}

            case 'pending': { return null; }
            case 'succeeded': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'failed': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE; }
            case 'canceled': { return null; }

            default: { return 'unknown'; }

        }
    }
}