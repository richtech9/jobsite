<?php

/*
 * quick list of meta used in transactions, and the lookup columns they map to

numeric_modified_id  -> numeric_modified_id
 request_payment_notify  -> request_payment_notify
 _modified_transaction_id -> txn
_payment_type -> payment_type
_transactionAmount -> transaction_amount
_transactionReason -> transaction_reason
_transactionRelatedTo -> related_post_id
_transactionType ->  transaction_type
_transactionWithdrawStatus  -> withdraw_status
withdraw_approved_by -> withdraw_approved_by
withdrawal_message -> withdrawal_message
 */
class FLTransactionLookup {

    //string enum values
    const META_KEY_TRANSACTION_WITHDRAW_STATUS = '_transactionWithdrawStatus';
    const META_KEY_REQUEST_PAYMENT_NOTIFY = 'request_payment_notify';
    const META_KEY_PAYMENT_TYPE = '_payment_type';
    const META_KEY_TRANSACTION_TYPE = '_transactionType';

    //numbers
    const META_KEY_NUMERIC_MODIFIED_ID = 'numeric_modified_id';
    const META_KEY_TRANSACTION_AMOUNT = '_transactionAmount';

    //pointers
    const META_KEY_TRANSACTION_RELATED_TO = '_transactionRelatedTo';
    const META_KEY_WITHDRAW_APPROVED_BY = 'withdraw_approved_by';

    //strings
    const META_KEY_MODIFIED_TRANSACTION_ID = '_modified_transaction_id';
    const META_KEY_TRANSACTION_REASON = '_transactionReason';
    const META_KEY_WITHDRAWAL_MESSAGE = 'withdrawal_message';
    const META_KEY_WITHDRAW_CANCEL_MESSAGE = '_withdraw_cancel_message';

    const META_KEY_LOOKUP_COLUMNS = [
        self::META_KEY_NUMERIC_MODIFIED_ID => 'numeric_modified_id',
        self::META_KEY_REQUEST_PAYMENT_NOTIFY => 'request_payment_notify',
        self::META_KEY_MODIFIED_TRANSACTION_ID => 'txn',
        self::META_KEY_PAYMENT_TYPE => 'payment_type',
        self::META_KEY_TRANSACTION_AMOUNT => 'transaction_amount',
        self::META_KEY_TRANSACTION_REASON => 'transaction_reason',
        self::META_KEY_TRANSACTION_RELATED_TO => 'related_post_id',
        self::META_KEY_TRANSACTION_TYPE => 'transaction_type',
        self::META_KEY_TRANSACTION_WITHDRAW_STATUS => 'withdraw_status',
        self::META_KEY_WITHDRAW_APPROVED_BY => 'withdraw_approved_by',
        self::META_KEY_WITHDRAWAL_MESSAGE => 'withdrawal_message',
        self::META_KEY_WITHDRAW_CANCEL_MESSAGE => 'withdraw_cancel_message',
    ];

    //these values are enforced for transaction_type field when updating from the post meta
    const TRANSACTION_TYPE_NONE = 0;
    const TRANSACTION_TYPE_PROCESSING_FEE = 1;
    const TRANSACTION_TYPE_REFILL = 2;
    const TRANSACTION_TYPE_REFUND = 3;
    const TRANSACTION_TYPE_UNDO_PROCESSING_FEE = 4;
    const TRANSACTION_TYPE_WITHDRAW = 5;
    const TRANSACTION_TYPE_FREE_CREDITS = 6;
    const TRANSACTION_TYPE_FREE_CREDITS_REFUND = 7;
    const TRANSACTION_TYPE_FREE_CREDITS_USED = 8;

    // processing_fee|refill|refund|undo_processing_fee|withdraw|FREE_credits|FREE_credits_refund|FREE_credits_used
    const TRANSACTION_TYPE_VALUES = [
        self::TRANSACTION_TYPE_NONE => 'no_type',
        self::TRANSACTION_TYPE_PROCESSING_FEE => 'processing_fee',
        self::TRANSACTION_TYPE_REFILL => 'refill',
        self::TRANSACTION_TYPE_REFUND => 'refund',
        self::TRANSACTION_TYPE_UNDO_PROCESSING_FEE => 'undo_processing_fee',
        self::TRANSACTION_TYPE_WITHDRAW => 'withdraw',
        self::TRANSACTION_TYPE_FREE_CREDITS => 'FREE_credits',
        self::TRANSACTION_TYPE_FREE_CREDITS_REFUND  => 'FREE_credits_refund',
        self::TRANSACTION_TYPE_FREE_CREDITS_USED  => 'FREE_credits_used'
    ];

    //these values are enforced for payment_type field when updating from the post meta
    const PAYMENT_TYPE_NONE = 0;
    const PAYMENT_TYPE_PAYPAL = 1;
    const PAYMENT_TYPE_STRIPE = 2;
    const PAYMENT_TYPE_ALIPAY = 3;

    const PAYMENT_TYPE_VALUES = [
        self::PAYMENT_TYPE_NONE => 'no_payment',
        self::PAYMENT_TYPE_PAYPAL => 'paypal',
        self::PAYMENT_TYPE_STRIPE => 'stripe',
        self::PAYMENT_TYPE_ALIPAY => 'alipay'
    ];

    //these values are enforced for request_payment_notify field when updating from the post meta
    const REQUEST_PAYMENT_NOTIFY_NONE = 0;
    const REQUEST_PAYMENT_NOTIFY_PAYPAL = 1;
    const REQUEST_PAYMENT_NOTIFY_STRIPE = 2;
    const REQUEST_PAYMENT_NOTIFY_ALIPAY = 3;

    const REQUEST_PAYMENT_VALUES = [
        self::REQUEST_PAYMENT_NOTIFY_NONE => 'no_request',
        self::REQUEST_PAYMENT_NOTIFY_PAYPAL => 'paypal',
        self::REQUEST_PAYMENT_NOTIFY_STRIPE => 'stripe',
        self::REQUEST_PAYMENT_NOTIFY_ALIPAY => 'alipay'
    ];

    //these values are enforced for withdraw_status field when updating from the post meta
    const WITHDRAW_STATUS_NONE = 0;
    const WITHDRAW_STATUS_PENDING = 1;
    const WITHDRAW_STATUS_CANCELED= 2;
    const WITHDRAW_STATUS_COMPLETED = 3;

    const WITHDRAW_STATUS_VALUES = [
        self::WITHDRAW_STATUS_NONE => 'no_status',
        self::WITHDRAW_STATUS_PENDING => 'pending',
        self::WITHDRAW_STATUS_CANCELED => 'canceled',
        self::WITHDRAW_STATUS_COMPLETED => 'completed',
    ];

    #     post_status                 TINYINT  none=0|pending=1|publish=2|private=3|new_transaction=4|pending_transaction=5|failed_transaction=6
    const POST_STATUS_NONE = 0;
    const POST_STATUS_PENDING = 1;
    const POST_STATUS_PUBLISH = 2;
    const POST_STATUS_PRIVATE = 3;
    const POST_STATUS_NEW_TRANSACTION = 4;
    const POST_STATUS_PENDING_TRANSACTION = 5;
    const POST_STATUS_FAILED_TRANSACTION = 6;

    const POST_STATUS_VALUES = [
        self::POST_STATUS_NONE => 'no_status',
        self::POST_STATUS_PENDING => 'pending',
        self::POST_STATUS_PUBLISH => 'publish',
        self::POST_STATUS_PRIVATE => 'private',
        self::POST_STATUS_NEW_TRANSACTION => 'new_transaction',
        self::POST_STATUS_PENDING_TRANSACTION => 'pending_transaction',
        self::POST_STATUS_FAILED_TRANSACTION => 'failed_transaction'
    ];



    static function transaction_type_int_to_enum($int_type){
        if (array_key_exists($int_type,static::TRANSACTION_TYPE_VALUES)) {
            return static::TRANSACTION_TYPE_VALUES[$int_type];
        }
        throw new RuntimeException("Unknown type of $int_type");
    }

    static function post_status_int_to_enum($int_status) {
        if (array_key_exists($int_status,static::POST_STATUS_VALUES)) {
            return static::POST_STATUS_VALUES[$int_status];
        }
        throw new RuntimeException("Unknown status of $int_status");
    }

    /**
     * @var int $lookup_id
     */
    public $lookup_id;

    /**
     * @var int $related_fl_transaction_id
     */
    public $related_fl_transaction_id;

    /**
     * @var int $withdraw_approved_by
     */
    public $withdraw_approved_by;

    /**
     * @var int $post_id
     */
    public $post_id;

    /**
     * @var int $user_id
     */
    public $user_id;

    /**
     * @var int $related_post_id
     */
    public $related_post_id;

    /**
     * @var int $numeric_modified_id
     */
    public $numeric_modified_id;

    /**
     * @var int $transaction_type
     */
    public $transaction_type;

    /**
     * @var int $payment_type
     */
    public $payment_type;

    /**
     * @var int $withdraw_status
     */
    public $withdraw_status;

    /**
     * @var int $request_payment_notify
     */
    public $request_payment_notify;

    /**
     * @var int $post_status
     */
    public $post_status;

    /**
     * @var float $transaction_amount
     */
    public $transaction_amount;

    /**
     * @var int $modified_at_ts
     */
    public $modified_at_ts;

    /**
     * @var int $post_created_at_ts
     */
    public $post_created_at_ts;

    /**
     * @var string $txn
     */
    public $txn;

    /**
     * @var string $related_txn
     */
    public $related_txn;

    /**
     * @var string $transaction_reason
     */
    public $transaction_reason;

    /**
     * @var string $withdrawal_message
     */
    public $withdrawal_message ;

    /**
     * @var string $withdraw_cancel_message
     */
    public $withdraw_cancel_message ;

    /**
     * @var string $related_fl_transaction_txn
     */
    public $related_fl_transaction_txn ;

    public function __construct($row = []){
        foreach ($row as $key => $value) {
            if (property_exists($this,$key)) {$this->$key = $value;}
        }

        $this->cast_numbers();
    }

    /**
     * WP db function will return a lot of numbers as strings, which is great, until you really need them as numbers
     */
    protected function cast_numbers() {
        $this->lookup_id = (is_null($this->lookup_id)) ? null : (int)$this->lookup_id;
        $this->post_id = (is_null($this->post_id)) ? null : (int)$this->post_id;
        $this->related_fl_transaction_id = (is_null($this->related_fl_transaction_id)) ? null : (int)$this->related_fl_transaction_id;
        $this->user_id = (is_null($this->user_id)) ? null : (int)$this->user_id;
        $this->related_post_id = (is_null($this->related_post_id)) ? null : (int)$this->related_post_id;
        $this->numeric_modified_id = (is_null($this->numeric_modified_id)) ? null : (int)$this->numeric_modified_id;
        $this->transaction_type = (is_null($this->transaction_type)) ? null : (int)$this->transaction_type;
        $this->payment_type = (is_null($this->payment_type)) ? null : (int)$this->payment_type;
        $this->withdraw_status = (is_null($this->withdraw_status)) ? null : (int)$this->withdraw_status;
        $this->request_payment_notify = (is_null($this->request_payment_notify)) ? null : (int)$this->request_payment_notify;
        $this->post_status = (is_null($this->post_status)) ? null : (int)$this->post_status;
        $this->transaction_amount = (is_null($this->transaction_amount)) ? null : (float)$this->transaction_amount;
        $this->modified_at_ts = (is_null($this->modified_at_ts)) ? null : (int)$this->modified_at_ts;
        $this->post_created_at_ts = (is_null($this->post_created_at_ts)) ? null : (int)$this->post_created_at_ts;
        $this->withdraw_approved_by = (is_null($this->withdraw_approved_by)) ? null : (int)$this->withdraw_approved_by;
    }

    /**
     * @param int $user_id required, use the word 'none' if want all users
     * @param int $page_size set to -1 to not have pagination
     * @param int $page_number defaults to 1
     * @param string $sort_command  by_date_asc|by_date_desc  defaults to by_date_desc
     * @param int[] $only_transaction_types
     *      @uses FLTransactionLookup::TRANSACTION_TYPE_VALUES
     * @param int[] $only_post_status
     *      @uses FLTransactionLookup::POST_STATUS_VALUES
     *
     * @param array $search, optional searches to add
     *                  post_created_after : unix timestamp
     *                  post_created_before: unix timestamp
     *                  txn_match : string to exactly match a txn
     *                  txn_like : string to like% a txn
     *                  withdraw: string to do a full text search (non boolean)
     *                  withdraw_status: array of integers of withdraw status to filter buy @uses FLTransactionLookup::WITHDRAW_STATUS_VALUES
     *
     * @param bool $b_get_log, default false, if true then fills in the fields for related transaction logs
     * @param bool $b_calc_rows default false, if true then returns an integer to count all the rows without pagination, but use the same other limits

     * @return int|FLTransactionLookup[]
     */
    public static function get_rows($user_id, $page_size=5, $page_number=1,
                                    $sort_command=  'by_date_desc', $only_transaction_types = [],
                                    $only_post_status=[],$search = [],$b_get_log= false,$b_calc_rows= false)  {
        global $wpdb;


        $dat_user_id = (int)$user_id;
        if ($dat_user_id) {
            $where_user = "look.user_id = $user_id";
        } elseif ($user_id === 'none') {
            $where_user = '2'; //to help distinguish
        } else {
            throw new InvalidArgumentException("User id needs to be set in FLTransactionLookup::get_rows"); //no user , no service
        }

        $page_size = (int)$page_size;
        if ($page_size === 0) {$page_size = 5;}

        $page_number = (int)$page_number;
        if ($page_number <= 0) {$page_number = 1;}

        switch($sort_command) {
            case 'by_date_desc': {
                $orderby = 'look.post_created_at';
                $order = 'desc';
                break;
            }

            case 'by_date_asc': {
                $orderby = 'look.post_created_at';
                $order = 'asc';
                break;
            }

            default: {
                $orderby = 'look.post_created_at';
                $order = 'desc';
            }
        }


        if ($page_size > 0) {
            $start_count = ($page_number - 1) * $page_size;
            $page_size_calculated = $page_size;
        } else {
            $page_size_calculated = 10000;
            $start_count = 0;
        }

        if (!is_array($only_transaction_types)) {$only_transaction_types = [];}

        $cleaned_types = [];
        foreach ($only_transaction_types as $a_type) {
            if (intval($a_type)) {$cleaned_types[] = (int)$a_type;}
        }
        if (empty($cleaned_types)) {
            $where_transaction_type = 1;
        } else {
            $comma_delimited_types = implode(', ',$cleaned_types);
            $where_transaction_type = " look.transaction_type in ($comma_delimited_types) ";
        }

        if (!is_array($only_post_status)) {$only_post_status = [];}

        $cleaned_status = [];
        foreach ($only_post_status as $a_status) {
            if (intval($a_status)) {$cleaned_status[] = (int)$a_status;}
        }
        if (empty($cleaned_status)) {
            $where_post_status = 1;
        } else {
            $comma_delimited_status = implode(', ',$cleaned_status);
            $where_post_status = " look.post_status in ($comma_delimited_status) ";
        }

        if (empty($search) || !is_array(($search))) {$search = [];}

        /*
         *                  post_created_after : unix timestamp
         *                  post_created_before: unix timestamp
         *                  txn_match : string to exactly match a txn
         *                  txn_like : string to like% a txn
         *                  withdraw: string to do a full text search (non boolean)
         *                  withdraw_status: array of integers of withdraw status to filter buy @uses FLTransactionLookup::WITHDRAW_STATUS_VALUES
         */

        $start_ts = 0;
        $end_ts = 0;
        if (isset($search['post_created_after'])) {$start_ts = intval($search['post_created_after']);}
        if (isset($search['post_created_before'])) {$end_ts = intval($search['post_created_before']);}

        if ($start_ts && $end_ts) {
            $where_date = " look.post_created_at BETWEEN FROM_UNIXTIME($start_ts) AND FROM_UNIXTIME($end_ts) ";
        } elseif ($start_ts) {
            $where_date = " look.post_created_at >= FROM_UNIXTIME($start_ts)  ";
        } elseif ($end_ts) {
            $where_date = " look.post_created_at <= FROM_UNIXTIME($end_ts)  ";
        } else {
            $where_date = '1';
        }

        $txn_match = '';
        if (isset($search['txn_match'])) {$txn_match = esc_sql($search['txn_match']);}

        $txn_like = '';
        if (isset($search['txn_like'])) {$txn_like = esc_sql($search['txn_like']).'%';}

        $where_txn_match = '1';
        if ($txn_match){
            $where_txn_match = " look.txn = '$txn_match'";
        }

        $where_txn_like = '1';
        if ($txn_like){
            $where_txn_match = " look.txn LIKE '$txn_like'";
        }

        $full_text_withdraw = '';
        if (isset($search['full_text_withdraw'])) {$full_text_withdraw = esc_sql($search['withdraw']);}

        $where_withdraw = '1';
        if ($full_text_withdraw) {
            $where_withdraw = "MATCH(look.withdrawal_message) AGAINST('$full_text_withdraw' IN NATURAL LANGUAGE MODE) ";
        }

        //withdraw_status
        $where_withdraw_status = 1;
        if (isset($search['withdraw_status'])) {
            $only_withdraw_status = $search['withdraw_status'];
            if (!is_array($only_withdraw_status)) {$only_withdraw_status = [];}
            $cleaned_withdraws = [];
            foreach ($only_withdraw_status as $a_type) {
                if (intval($a_type)) {
                    $cleaned_withdraws[] = (int)$a_type;
                }
            }
            if (empty($cleaned_withdraws)) {
                $where_withdraw_status = 1;
            } else {
                $comma_delimited_types = implode(', ', $cleaned_withdraws);
                $where_withdraw_status = " look.withdraw_status in ($comma_delimited_types) ";
            }
        }


        if ($b_calc_rows) {
            $sql_for_count = "
            SELECT 
                count(look.id) as dat_count
            FROM wp_transaction_lookup look
            WHERE 
            1
            AND $where_user 
            AND $where_post_status 
            AND $where_transaction_type 
            AND $where_date
            AND $where_txn_match
            AND $where_txn_like
            AND $where_withdraw
            AND $where_withdraw_status
            ";

            $count_res = (int)$wpdb->get_var($sql_for_count);
            will_throw_on_wpdb_error($wpdb,'FLTransaction Lookup::get_rows count');
            return $count_res;
        }

        $sql_for_lookup = "
            SELECT 
                look.id as lookup_id,
                look.post_id,
                look.user_id,
                look.withdraw_approved_by,
                look.related_post_id,
                look.numeric_modified_id,
                look.transaction_type,
                look.payment_type,
                look.withdraw_status,
                look.request_payment_notify,
                look.post_status,
                look.transaction_amount,
                UNIX_TIMESTAMP (look.modified_at) as modified_at_ts,
                UNIX_TIMESTAMP(look.post_created_at) as post_created_at_ts,
                look.txn,
                look.related_txn,
                look.transaction_reason,
                look.withdrawal_message,
                look.withdraw_cancel_message
            FROM wp_transaction_lookup look
            WHERE 
            1
            AND $where_user 
            AND $where_post_status 
            AND $where_transaction_type 
            AND $where_date
            AND $where_txn_match
            AND $where_txn_like
            AND $where_withdraw
            AND $where_withdraw_status
            ORDER BY  $orderby $order 
            LIMIT $start_count, $page_size_calculated
        ";

        $lookup_res = $wpdb->get_results($sql_for_lookup); //return array of objects
        will_throw_on_wpdb_error($wpdb,'FLTransaction Lookup::get_rows');

        $ret = [];
        /**
         * @var FLTransactionLookup[] $lookup
         */
        $lookup = [];
        $post_ids = [];
        foreach ($lookup_res as $row) {
            $node = new FLTransactionLookup($row);
            $ret[] = $node;
            $lookup['p-'.$node->post_id] = $node;
            $post_ids[] = $node->post_id;
        }

        if ($b_get_log && count($post_ids)) {
            $post_ids_comma_delim = implode(',',$post_ids);
            $log_sql = "SELECT 
                            ID as related_fl_transaction_id,
                            txn_id as related_fl_transaction_txn,
                            transaction_post_id 
                        FROM wp_fl_transaction 
                        WHERE transaction_post_id in ($post_ids_comma_delim)
                        ";
            $log_res = $wpdb->get_results($log_sql);
            will_throw_on_wpdb_error($wpdb,'FLTransaction Lookup::get_rows extra log info');
            foreach ($log_res as $log) {
                $transaction_post_id = (int)$log->transaction_post_id;
                $related_fl_transaction_id = (int)$log->related_fl_transaction_id;
                $related_fl_transaction_txn = $log->related_fl_transaction_txn;
                if (in_array('p-'.$transaction_post_id,$lookup)) {
                    $lookup['p-'.$transaction_post_id]->related_fl_transaction_id = $related_fl_transaction_id;
                    $lookup['p-'.$transaction_post_id]->related_fl_transaction_txn = $related_fl_transaction_txn;
                }

            }
        }



        return $ret;
    }

}