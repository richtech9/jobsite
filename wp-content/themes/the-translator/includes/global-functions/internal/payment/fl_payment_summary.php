<?php

class FLPaymentSummary extends FreelinguistDebugging
{
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    public $id = null;
    public $user_id = null;
    public $refill_by = null;
    public $transaction_post_id = null;
    public $created_time = null;
    public $payment_amount = null;
    public $processing_fee_included = null;
    public $currency = null;
    public $payment_status = null;
    public $txn_id = null;
    public $description = null;
    public $payment_type = null;
    public $item_name = null;
    public $transaction_post_txn = null;

    /**
     * @var FLPaymentHistoryIPN[] $ipn
     */
    public $ipn = [];

    public function __construct($object_from_query)
    {
        foreach ($object_from_query as $key=>$val) {
            if (property_exists($this,$key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * returns a hash with information for any of these post ids which was a top level payment transaction
     * the posts do not have to be top transaction posts, we filter
     * @param int[] $post_ids_of_interest
     * @return FLPaymentSummary[]
     */
    public static function make_summaries($post_ids_of_interest)
    {
        global $wpdb;
        $top_post_ids = [];
        foreach ($post_ids_of_interest as $raw_post_id) {
            $top_post_ids[] = (int)$raw_post_id;
        }
        if (empty($top_post_ids)) {return [];}
        $post_id_comma_delimited = implode(', ',$top_post_ids);

        $sql = "SELECT 
                  hist.id, hist.user_id, hist.refill_by, hist.transaction_post_id, hist.created_time, hist.payment_amount, 
                  hist.processing_fee_included, hist.currency, hist.payment_status, hist.txn_id, hist.payment_type, hist.item_name ,
                  look.txn     as transaction_post_txn
                FROM  wp_payment_history hist 
                INNER JOIN wp_transaction_lookup look ON look.post_id = hist.transaction_post_id
                WHERE hist.transaction_post_id in ($post_id_comma_delimited)
                ";
        $history_res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb, 'getting payment summaries');
        if (empty($history_res)) return [];

        $ret = [];
        $history_ids = [];

        /**
         * @var FLPaymentSummary[] $my_histories
         */
        $my_histories = [];
        foreach($history_res as $row) {
            $my_history_id = (int)$row->id;
            $history_ids[] = $my_history_id;
            $my_post_id = (int)$row->transaction_post_id;
            $node = new FLPaymentSummary($row);
            $ret[$my_post_id] = $node;
            $my_histories[$my_history_id] = $node; //each way to update its ipn list
        }
        $history_id_comma_delimited = implode(', ',$history_ids);
        $other_transaction_post_ids = [];

        $sql = "
            SELECT ipn.* , UNIX_TIMESTAMP(ipn.create_date) as created_ts
             FROM wp_payment_history_ipn ipn
             WHERE ipn.payment_id in ($history_id_comma_delimited) AND ipn.is_duplicate = 0 
             ORDER BY id asc
        ";

        $ipn_res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb, 'debug payment ipn history');

        foreach ($ipn_res as $index => $row) {
            $my_history_id = (int)$row->payment_id;
            $my_history_thing = $my_histories[$my_history_id];
            $maybe_serialized_data = $row->original_data;
            $maybe_unserialized_data = maybe_unserialize($maybe_serialized_data);
            $ipn = new FLPaymentHistoryIPN($row->payment_method, $maybe_unserialized_data);
            $ipn->payment_id = (int)$row->payment_id;
            $ipn->id = (int)$row->id;
            $ipn->is_duplicate = (intval($row->is_duplicate)) ;
            $ipn->created_ts = (intval($row->created_ts)) ;

            $ipn->other_transaction_id = (intval($row->other_transaction_id)? (int)$row->other_transaction_id: null) ;
            //hack for dealing with most data not marked as duplicates at this time
            $b_flag_is_unmarked_duplicate = false;
            foreach ($my_history_thing->ipn as $check_ipn) {
                if (empty($ipn->other_transaction_id)) {
                    if (($ipn->txn_id === $check_ipn->txn_id) && ($ipn->payment_status === $check_ipn->payment_status) ) {
                        //is duplicate
                        $b_flag_is_unmarked_duplicate = true;
                    }
                }
            }
            if ($b_flag_is_unmarked_duplicate) {continue;}
            //end checking for unmarked duplicates
            $my_history_thing->ipn[] = $ipn;
            if ($ipn->other_transaction_id) {
                $other_transaction_post_ids[$ipn->other_transaction_id] = $ipn;
            }
        }

        $unique_post_ids = array_keys($other_transaction_post_ids);
        if (count($unique_post_ids)) {
            $post_ids_comma_delimited = implode(', ', $unique_post_ids);
            $sql = "SELECT
                  post.ID as post_id,
                  post.post_title,
                  post.post_status,
                  post.post_author,
                  post.post_type,
                  look.payment_type       as payment_type,
                  look.transaction_amount as transaction_amount,
                  look.transaction_reason as transation_reason,
                  look.transaction_type   as transaction_type,
                  look.txn        as modified_id,
                  look.numeric_modified_id          as number_id
                FROM wp_posts post
                  INNER JOIN wp_transaction_lookup look on post.ID = look.post_id
                 
                WHERE post.ID in ($post_ids_comma_delimited)
                 ";
            $trans_res = $wpdb->get_results($sql);
            will_throw_on_wpdb_error($wpdb, 'debug payment history for transactions');
            foreach ($trans_res as $row) {
                $da_transaction_id = (int)$row->post_id;
                if (array_key_exists($da_transaction_id, $other_transaction_post_ids)) {
                    $rube = $other_transaction_post_ids[$da_transaction_id];
                } else {
                    static::log(static::LOG_WARNING, 'Cannot find post id in $post_ids!', [$da_transaction_id, $other_transaction_post_ids]);
                    continue;
                }
                $row->post_id = (int)$row->post_id;
                if ($rube instanceof FLPaymentHistoryIPN) {
                    $rube->transaction_object = $row;
                } else {
                    $rube->transaction_object = $row; //same things !!
                }
            }
        }

        return $ret;
    }
}