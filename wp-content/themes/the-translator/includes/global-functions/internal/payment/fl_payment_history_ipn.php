<?php
use Carbon\Carbon;


class FLPaymentHistoryIPN extends  FreelinguistDebugging
{
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const PAYMENT_METHOD_PAYPAL = FLTransactionLookup::PAYMENT_TYPE_VALUES[FLTransactionLookup::PAYMENT_TYPE_PAYPAL];
    const PAYMENT_METHOD_STRIPE = FLTransactionLookup::PAYMENT_TYPE_VALUES[FLTransactionLookup::PAYMENT_TYPE_STRIPE];
    const PAYMENT_METHOD_STRIPE_REFUND = 'stripe-refund';
    const PAYMENT_METHOD_STRIPE_DISPUTE = 'stripe-dispute';

    const FL_PAYMENT_STATUS_COMPLETE = 'complete';
    const FL_PAYMENT_STATUS_PENDING = 'pending';
    const FL_PAYMENT_STATUS_FAILED = 'failed';

    public static function status_to_fl($payment_method,$payment_status) {
        if ($payment_method === static::PAYMENT_METHOD_PAYPAL) {
            return FLPaymentHistoryPayPalMap::status_to_fl($payment_status);
        }elseif ($payment_method === static::PAYMENT_METHOD_STRIPE) {
           return FLPaymentHistoryStripeIntentMap::status_to_fl($payment_status);
        } elseif ($payment_method === static::PAYMENT_METHOD_STRIPE_REFUND) {
            return FLPaymentHistoryStripeRefundMap::status_to_fl($payment_status);
        } elseif ($payment_method === static::PAYMENT_METHOD_STRIPE_DISPUTE) {
            return FLPaymentHistoryStripeDisputeMap::status_to_fl($payment_status);
        }
        else {
            return '';
        }
    }

    protected function payment_status_to_fl() {
        return static::status_to_fl($this->payment_method,$this->payment_status);
    }

    protected static $TRANSPOSE_MAPS = [];//there will be a lot of maps later

    public static function generate_transpose_maps() {
        if (count(static::$TRANSPOSE_MAPS)) {return;}

        static::$TRANSPOSE_MAPS[static::PAYMENT_METHOD_PAYPAL] = FLPaymentHistoryPayPalMap::make_map();

        static::$TRANSPOSE_MAPS[static::PAYMENT_METHOD_STRIPE] =  FLPaymentHistoryStripeIntentMap::make_map();

        static::$TRANSPOSE_MAPS[static::PAYMENT_METHOD_STRIPE_REFUND] =  FLPaymentHistoryStripeRefundMap::make_map();

        static::$TRANSPOSE_MAPS[static::PAYMENT_METHOD_STRIPE_REFUND] =  FLPaymentHistoryStripeDisputeMap::make_map();
    }






    /**
     * @var int $id primary key
     */
    public $id = null;


    /**
     * @var object $history_reference_object
     */
    public $history_reference_object = null;

    /**
     * @var int $payment_id not null
     */
    public $payment_id = null;

    /**
     * @var int|null $other_transaction_id
     */
    public $other_transaction_id = null;

    /**
     * @var float $amount decimal(10,2)
     * @transpose mc_gross
     */
    public $amount = null;

    /**
     * @var string|null $payment_date carbon of payment_date to unixtime, then put in as date
     * @transpose payment_date
     */
    public $payment_date = null;

    /**
     * @var string|null $country_code char(2)
     * @transpose address_country_code
     */
    public $country_code = null;

    /**
     * @var string|null mc_currency char(3)
     * @transpose mc_currency
     */
    public $currency = null;

    /**
     * @var string|null $fl_payment_status (pending,complete,failed)
     * @uses FLPaymentHistoryIPN::payment_status_to_fl()
     */
    public $fl_payment_status = null;

    /**
     * @var string|null $txn_id varchar(50) not null
     * @transpose txn_id
     */
    public $txn_id  = null;

    /**
     * @var string|null varchar(50) not null
     * @transpose txn_type
     */
    public $txn_type = null;

    /**
     * @var string|null $payment_status varchar(50) null
     * @transpose payment_status
     */
    public $payment_status = null;

    /**
     * @var string|null $payment_method varchar(50)
     * constant from constructor
     */
    public $payment_method = null;

    /**
     * @var string|null $item_name varchar(50) null
     * @transpose item_name
     */
    public $item_name  = null;

    /**
     * @var string|null $item_number varchar(50) null
     * @transpose item_number
     */
    public $item_number  = null;

    /**
     * @var string|null $receiver_email  varchar(50) null
     * @transpose receiver_email
     */
    public $receiver_email = null;

    /**
     * @var string|null $payer_email varchar(150) null
     * @transpose payer_email
     */
    public $payer_email  = null;

    /**
     * @var string|null $first_name varchar(150) null
     * @transpose first_name
     */
    public $first_name  = null;

    /**
     * @var string|null $last_name varchar(150) null
     * @transpose last_name
     */
    public $last_name  = null;

    /**
     * @var mixed $original_data
     *
     */
    public $original_data = null;

    /**
     * @var int $custom_int
     * @transpose custom
     */
    public $custom_int = null;

    /**
     * @var object $transaction_object
     * optional slot to store transaction info
     */
    public $transaction_object = null;

    /**
     * lists errors from getting data from input
     * @var string[] $error_messages
     */
    public $error_messages = [];

    /**
     * @var int $is_duplicate
     */
    public $is_duplicate = null;


    /**
     * @var int $created_ts
     */
    public $created_ts = null;

    /**
     * FLPaymentHistoryIPN constructor.
     * @param string $payment_method : paypal
     * @param array|object $data
     */
    public function __construct($payment_method,$data)
    {
        if (empty($data)) {$data = [];}
        $this->payment_method = $payment_method;
        if (empty($this->payment_method)) {
            throw new RuntimeException("Payment method lacking");
        }

        if (array_key_exists($this->payment_method,static::$TRANSPOSE_MAPS)) {
            $map = static::$TRANSPOSE_MAPS[$this->payment_method];
        } else {
            static::log(static::LOG_WARNING,'Map key does not exist for payment method of '. $this->payment_method);
            throw new RuntimeException('FLPaymentHistoryIPN Map key does not exist for payment method of '. $this->payment_method);
        }


        foreach ($map as $our_field => $node) {
            try {
                $data_key = $node['field'];
                $data_type = $node['type'];
                switch ($data_type) {
                    case 'string':
                        {
                            $this->$our_field = (isset($data[$data_key]) ? trim($data[$data_key]) : null);
                            break;
                        }
                    case 'float':
                        {
                            $this->$our_field = (isset($data[$data_key]) ? floatval($data[$data_key]) : null);
                            break;
                        }

                    case 'int':
                        {
                            $this->$our_field = (isset($data[$data_key]) ? intval($data[$data_key]) : null);
                            break;
                        }

                    case 'null':
                        {
                            $this->$our_field = null;
                            break;
                        }

                    case 'function':
                        {
                            if (!array_key_exists('function', $node)) {
                                static::log(static::LOG_WARNING, 'function key not in function node', [$our_field, $node]);
                                break;
                            }
                            $func = $node['function'];
                            $this->$our_field = $func($data);
                            break;
                        }
                    default:
                        {
                            $this->$our_field = (isset($data[$data_key]) ? ($data[$data_key]) : null);
                        }
                }
            } catch (Exception $e) {
                static::log(static::LOG_WARNING, $e->getMessage(), [
                    $our_field,
                    $node,
                    will_get_exception_string($e),
                    $data
                ]);
                $this->error_messages[] = "$our_field <-- $data_key : ".$e->getMessage();
            }
        }

        $this->original_data = $data;
        $this->fl_payment_status = $this->payment_status_to_fl();
    }

    public function save($payment_id = null,$other_transaction_id = null) {
        global $wpdb;

        if (empty($payment_id)) {
            $payment_id = $this->payment_id;
        }

        if (empty($payment_id)) {
            static::log(static::LOG_DEBUG, 'payment ID not set', [
                'this' => $this
            ]);
            throw  new RuntimeException("Need history payment id to save payment ipn");
        }

        if (empty($other_transaction_id)) {
            $other_transaction_id = $this->other_transaction_id;
        }

        $data_serialized = maybe_serialize($this->original_data);
        if ($data_serialized === false) {
            throw new RuntimeException("Cannot encode json ". json_last_error_msg());
        }

        if ($this->payment_date) {
            $da_date = new Carbon($this->payment_date);
            $da_ts = $da_date->timestamp;
        } else {
            $da_ts = NULL;
        }
        //payment_date

        $my_error_string = implode("||",$this->error_messages);
        if(empty($my_error_string)) {$my_error_string = null;}

        if (strlen($this->country_code) > 2 ) {throw new RuntimeException("Country code is greater than 2 chars:".$this->country_code);}
        if (strlen($this->currency) > 3 ) {throw new RuntimeException("currency code is greater than 3 chars:".$this->currency);}

        $data = [
            'payment_id' => $payment_id ? (int)$payment_id: null,
            'other_transaction_id' => $other_transaction_id? (int)$other_transaction_id : null,
            'amount' => floatval($this->amount) ,
            'country' => $this->country_code,
            'currency' => $this->currency,
            'fl_payment_status' => $this->fl_payment_status,
            'txn_id' => $this->txn_id,
            'txn_type' => $this->txn_type,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'item_name' => $this->item_name,
            'item_number' => $this->item_number,
            'receiver_email' => $this->receiver_email,
            'payer_email' => $this->payer_email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'original_data' => $data_serialized,
            'error_msg' => $my_error_string
        ];

        if ($this->id) {
            $wpdb->update('wp_payment_history_ipn',$data,['id'=>$this->id]);
            will_throw_on_wpdb_error($wpdb,'update wp_payment_history_ipn');
        } else {
            $wpdb->insert('wp_payment_history_ipn',$data);
            $this->id = will_get_last_id($wpdb,'insert wp_payment_history_ipn');
        }

        if ($da_ts) {
            $sql = "UPDATE wp_payment_history_ipn SET payment_date = cast(FROM_UNIXTIME($da_ts) as datetime) WHERE id = {$this->id}";
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb,'update wp_payment_history_ipn::payment_date');
        } else {
            static::log(static::LOG_DEBUG,'Payment timestamp not set');
        }


    }

    public function mark_duplicate() {
        global $wpdb;
        if (!$this->id) {throw new LogicException("Must saved before marking as duplicate");}
        $sql = "UPDATE wp_payment_history_ipn SET is_duplicate = 1 WHERE id = ". intval($this->id);
        $wpdb->query($sql);
        will_throw_on_wpdb_error($wpdb,'marking as duplicate');
    }

    public function is_this_a_duplicate() {
        global $wpdb;
        $escaped_tx_id = esc_sql($this->txn_id);
        $escaped_payment_status = esc_sql($this->payment_status);
        if ($this->id) {
            $int_id = (int)$this->id;
            //already saved, do not count self
            $sql = "SELECT count(*) as da_count FROM wp_payment_history_ipn WHERE txn_id = '$escaped_tx_id' AND payment_status = '$escaped_payment_status' AND id <> $int_id";
        } else {
            //not saved, anything that looks like me is duplicate!
            $sql = "SELECT count(*) as da_count FROM wp_payment_history_ipn WHERE txn_id = '$escaped_tx_id' AND payment_status = '$escaped_payment_status'";
        }

        $res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb,'ipn is unique?');
        if (empty($res)) {return false;}
        $count = intval($res[0]->da_count);
        if ($count) {return true;}
        return false;
    }

    /**
     * Get a snapshot of the earlier, unaltered payment history, and update the current payment history
     * @param int $payment_history_id
     * @return object
     */
    public function set_payment_history($payment_history_id = null) {
        global $wpdb;

        $transaction_id = (int)$this->custom_int;
        $payment_history_id = (int)$payment_history_id;
        if ($payment_history_id) {
            $sql= "SELECT * FROM wp_payment_history WHERE id = $payment_history_id";
            $history_res = $wpdb->get_results($sql);
            will_throw_on_wpdb_error($wpdb,'getting history');
            if (empty($history_res)) {
                throw new LogicException("Should never get this,a non existant history, if issue would throw earlier");
            }
            $history_obj = $history_res[0];
        } else if ($transaction_id) {
            $sql= "SELECT history.* FROM wp_payment_history history 
                WHERE transaction_post_id = $transaction_id
                ";
            $res = $wpdb->get_results($sql);
            if (count($res)) {
                $history_obj = $res[0];
                $payment_history_id = $history_obj->id;
            } else {
                $history_obj = null;
                throw new LogicException("Cannot find a history from the transaction of [$transaction_id]");
            }

        } else {
            throw new LogicException("No transaction ID when getting history");
        }

        /*
         * if the payment history does not have the trx id, then fill it in
         * potentially update the payment history status (pending, completed, failed), and transaction post status (pending_transaction,failed_transaction,published)
         * if the status is changing from a pending or failed to completed, then make wp_fl_transaction and update balance
         * if the status is changing from a completed to a pending or failed, then make transaction and remove money from wallet
         */

        //code-notes do not update the payment if the status is null
        if (!empty($this->fl_payment_status)) {
            if (empty($history_obj->txn_id)) {
                $escaped_txn_id = esc_sql($this->txn_id);
                $update_sql = "UPDATE wp_payment_history SET txn_id = '$escaped_txn_id'  WHERE id = $payment_history_id";
                $wpdb->query($update_sql);
                will_throw_on_wpdb_error($wpdb, 'updating txn id for payment history');
            }

            $ipn_status = $this->fl_payment_status;
            $update_sql = "UPDATE wp_payment_history SET payment_status = '$ipn_status' WHERE id = $payment_history_id";
            $wpdb->query($update_sql);
            will_throw_on_wpdb_error($wpdb, 'updating payment history status');
        }

        $history_obj->payment_amount = floatval($history_obj->payment_amount);
        $history_obj->processing_fee_included = floatval($history_obj->processing_fee_included);
        $history_obj->user_id = intval($history_obj->user_id);
        $this->payment_id = $payment_history_id;

        $this->history_reference_object = $history_obj;
        return $history_obj;
    }

    function update_transaction_from_ipn() {
        $status = $this->fl_payment_status;
        if (empty($status)) {return;}
        $transaction_status = FLTransactionPost::payment_history_to_transaction_status($status);
        $transaction_id = $this->custom_int;
        wp_update_post( [
            'ID'           => $transaction_id,
            'post_status'   => $transaction_status
        ] );
    }

    /**
     * @return array of debugging goodness
     */
    public function do_debug_things() {
        global $_REAL_POST,$_REAL_GET;
        $history_obj = $this->history_reference_object;
        $ipn_object = $this;
        $count_process_fees = 0;
        $count_un_process_fees = 0;

        if ($history_obj) {
            $count_process_fees = FLPaymentHistory::count_transaction_type_associated($history_obj->id,FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE);
            $count_un_process_fees = FLPaymentHistory::count_transaction_type_associated($history_obj->id,FLTransactionLookup::TRANSACTION_TYPE_UNDO_PROCESSING_FEE);
        }

        $paymemt_dump = null;
        if ($ipn_object) {
            static::log(static::LOG_DEBUG,"Starting to get full payment history");
            $paymemt_dump =  FLPaymentHistory::get_full_payment_dump($ipn_object->custom_int,$this->payment_method);
        }
        $post_stuff = null;
        if (!empty($_REAL_POST)) {
            $post_stuff = $_REAL_POST;
        } else {
            $post_stuff = @file_get_contents('php://input');
        }
        return [
            'post' => $post_stuff,
            'get' => $_REAL_GET,
            '$count_process_fees' => $count_process_fees,
            '$count_un_process_fees' => $count_un_process_fees,
            '$history_obj' => $history_obj,
            '$ipn_object' => $this,
            'payment_dump ' => $paymemt_dump
        ];
    }

    /**
     * Sometimes all we have to work with is an identifier, but if we can get a transaction post from it then the code can manage
     * Here we simply look in the db for such an id, and then return the transaction post id
     * This is a helper function for some stripe object maps
     * @param string $intent_guid
     * @return int
     */
    public static function get_top_transaction_post_id_from_any_guid($intent_guid) {
        global $wpdb;
        $escaped_guid = esc_sql($intent_guid);

        $sql = "SELECT pay.transaction_post_id 
                FROM wp_payment_history_ipn ipn 
                INNER JOIN wp_payment_history pay ON pay.id = ipn.payment_id 
                WHERE ipn.txn_id = '$escaped_guid'";

        $res = $wpdb->get_results($sql);
        if (empty($res)) {throw new InvalidArgumentException("Cannot find the guid of $intent_guid in the history");}
        return intval($res[0]->transaction_post_id);
    }

}

FLPaymentHistoryIPN::generate_transpose_maps();

