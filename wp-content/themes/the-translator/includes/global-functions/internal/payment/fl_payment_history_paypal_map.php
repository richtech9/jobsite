<?php
use Stripe\Charge;
use Stripe\PaymentIntent;
use Carbon\Carbon;

class FLPaymentHistoryPayPalMap {
    public static function make_map() {
        return [
            'custom_int' => ['field' => 'custom', 'type'=> 'int'],
            'amount' => ['field' => 'mc_gross', 'type'=> 'float'],
            'country_code' => ['field' => 'address_country_code', 'type'=> 'string'],
            'currency' => ['field' => 'mc_currency', 'type'=> 'string'],
            'txn_id' => ['field' => 'txn_id', 'type'=> 'string'],
            'txn_type' => ['field' => 'txn_type', 'type'=> 'string'],
            'payment_status' => ['field' => 'payment_status', 'type'=> 'string'],
            'item_name' => ['field' => 'item_name', 'type'=> 'string'],
            'item_number' => ['field' => 'item_number', 'type'=> 'string'],
            'receiver_email' => ['field' => 'receiver_email', 'type'=> 'string'],
            'payer_email' => ['field' => 'payer_email', 'type'=> 'string'],
            'first_name' => ['field' => 'first_name', 'type'=> 'string'],
            'last_name' => ['field' => 'last_name', 'type'=> 'string'],
            'payment_date' => ['field' => 'payment_date', 'type'=> 'string'],
        ];
    }

    public static function status_to_fl($payment_status) {
        switch (strtolower(trim($payment_status))) {
            case null :
            case '': {
                return null;
            }

            case 'canceled_reversal': {return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE ;}
            case 'completed': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE; }
            case 'created': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'denied': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'expired': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'failed': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'pending': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'refunded': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'reversed': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'processed': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }
            case 'voided': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED; }
            case 'in-progress': { return FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING; }

            default: { return 'unknown'; }

        }
    }
}