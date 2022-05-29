<?php

class FLPaymentHistory extends  FreelinguistDebugging
{
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const DEFAULT_CURRENCY_ISO_CODE = 'USD';

    //task-future-work make sure the postmeta value key part is reasonable, phpstorm does not show key length

    /**
     * @param int $payment_id
     * @param int $transaction_type (code from transaction table lookup)
     * @return int
     */
    public static function count_transaction_type_associated($payment_id,$transaction_type) {
        global $wpdb;
        $payment_id = (int)$payment_id;
        $transaction_type = (int)$transaction_type;
        $sql = "SELECT count(*) as da_count 
                FROM wp_payment_history_ipn ipn 
                INNER JOIN wp_posts post on ipn.other_transaction_id = post.ID
                
                INNER JOIN wp_transaction_lookup lookup on post.ID = lookup.post_id AND lookup.transaction_type = $transaction_type
                WHERE ipn.payment_id = $payment_id
                ";
        $count = $wpdb->get_var($sql);
        return (int)$count;
    }


    /**
     * Gets all the data for the payment history, its transaction's meta, and each row of the ipn with any other transaction info
     * @param int $top_transaction_id
     * @param string $payment_method
     * @return array
     */
    public static function get_full_payment_dump($top_transaction_id,$payment_method) {
        global $wpdb;
        $top_transaction_id = (int)$top_transaction_id;
        $sql = "SELECT 
                  id, user_id, refill_by, transaction_post_id, created_time, payment_amount, 
                  processing_fee_included, currency, payment_status, txn_id, payment_type, item_name 
                FROM  wp_payment_history hist 
                WHERE hist.transaction_post_id = $top_transaction_id
                ";
        $history_res = $wpdb->get_results($sql);
        will_log_on_wpdb_error($wpdb,'debug payment history');
        if (empty($history_res)) return ['error'=> 'payment history not found'];

        $history_obj = $history_res[0];
        $history_obj->ipn = [];
        $post_ids = [];
        $post_ids[$top_transaction_id] = $history_obj;

        $sql = "
            select * FROM wp_payment_history_ipn WHERE payment_id = {$history_obj->id} ORDER BY id asc
        ";

        $ipn_res = $wpdb->get_results($sql);
        will_log_on_wpdb_error($wpdb,'debug payment ipn history');


        foreach ($ipn_res as $index => $row) {
            $maybe_serialized_data = $row->original_data;
            $maybe_unserialized_data = maybe_unserialize($maybe_serialized_data);
            $ipn = new FLPaymentHistoryIPN($payment_method,$maybe_unserialized_data);
            $ipn->payment_id = $row->payment_id;
            $ipn->other_transaction_id = $row->other_transaction_id;
            $history_obj->ipn[] = $ipn;
            if ($ipn->other_transaction_id) {
                $post_ids[$ipn->other_transaction_id] = $ipn ;
            }
        }

        $unique_post_ids = array_keys($post_ids);
        $post_ids_comma_delimited = implode(', ',$unique_post_ids);
        $sql= "SELECT
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
                  INNER JOIN wp_transaction_lookup look ON look.post_id = post.ID
                  
                WHERE post.ID in ($post_ids_comma_delimited)
                 ";
        $trans_res = $wpdb->get_results($sql);
        will_log_on_wpdb_error($wpdb,'debug payment history for transactions');
        foreach ($trans_res as $row) {
            $da_transaction_id = (int)$row->post_id;
            if (array_key_exists($da_transaction_id,$post_ids)) {
                $rube = $post_ids[$da_transaction_id];
            } else {
                static::log(static::LOG_WARNING,'Cannot find post id in $post_ids!',[$da_transaction_id,$post_ids]);
                continue;
            }
            if ($rube instanceof FLPaymentHistoryIPN) {
                $rube->transaction_object = $row;
            } else {
                $rube->transaction_object = $row; //same things !!
            }
        }

        return $history_obj;
    }

    /**
     * @param FLPaymentHistoryIPN $ipn_object
     * @throws Exception
     * @return bool, true if something done, false if nothing done
     */
    protected static function add_refill($ipn_object) {
        global $wpdb;
        $history_obj = $ipn_object->history_reference_object;
        if (empty($history_obj)) {
            throw new RuntimeException("History Object is Empty");
        }
        //if the status is changing from a pending or failed to completed, then make wp_fl_transaction and update balance
        //its okay if we have done this before on the transaction, because this can be a double reverse, or other esoteric combo, and we will have already done other things
        //BUT need to check to make sure we have either no complete earlier with a other transaction,
        // or there is an equal number of failed with other transactions and complete with other transactions

        $count_process_fees = FLPaymentHistory::count_transaction_type_associated($history_obj->id,FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE);
        $count_un_process_fees = FLPaymentHistory::count_transaction_type_associated($history_obj->id,FLTransactionLookup::TRANSACTION_TYPE_UNDO_PROCESSING_FEE);

        if (($count_un_process_fees === $count_process_fees) || ($count_un_process_fees > $count_process_fees) ) {
            try {
                $wpdb->query("START TRANSACTION;");

                $net_amount_to_add = $history_obj->payment_amount - $history_obj->processing_fee_included;
                $user_balance = get_user_meta($history_obj->user_id, 'total_user_balance', true);
                $new_balance = $user_balance + $net_amount_to_add;
                update_user_meta($history_obj->user_id, 'total_user_balance', amount_format($new_balance));
                //task-future-work remove making fee posts, only make posts when there is movement in and out of gateways
                $fee_transaction_id = transaction_updated('Refill Processing Fee',
                    $history_obj->user_id,
                    - $history_obj->processing_fee_included, //code-notes if fee taken out then negative
                    'Payment processing fee ', //code-notes do not put in raw value into reason as it gets printed out to screen and hard to format
                    FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE],
                    $history_obj->transaction_post_id);

                $update_sql = "UPDATE wp_payment_history_ipn SET other_transaction_id = $fee_transaction_id WHERE id = " . $ipn_object->id;
                $wpdb->query($update_sql);
                will_throw_on_wpdb_error($wpdb, 'updating $fee_transaction_id for history ipn');


                $new_transaction_row_id = fl_transaction_insert(amount_format($net_amount_to_add), 'done', 'refill',
                    $history_obj->user_id, get_current_user_id(), 'Payment (net) Applied for refill',$history_obj->payment_type,$history_obj->txn_id);
                will_throw_on_wpdb_error($wpdb, 'creating new wp_fl_transaction row for refill');
                $top_transaction_id = $history_obj->transaction_post_id;
                $sql_update_transaction = "UPDATE wp_fl_transaction SET transaction_post_id = $top_transaction_id WHERE id = $new_transaction_row_id";
                $wpdb->query($sql_update_transaction,'Setting wp_fl_transction to have be connected to top post');

                $new_transaction_row_id = fl_transaction_insert(-amount_format( $history_obj->processing_fee_included), 'done', 'refill_fee',
                    $history_obj->user_id, get_current_user_id(), 'Refill Fee',$history_obj->payment_type,$history_obj->txn_id);
                will_throw_on_wpdb_error($wpdb, 'creating new wp_fl_transaction row for refill fee');
                $sql_update_transaction = "UPDATE wp_fl_transaction SET transaction_post_id = $fee_transaction_id WHERE id = $new_transaction_row_id";
                $wpdb->query($sql_update_transaction,'Setting wp_fl_transction to have be connected to fee post');


                will_throw_on_wpdb_error($wpdb);

                $wpdb->query("COMMIT;"); //make sure to set everything only if no errors, we do not want half done transactions/history here
            } catch (Exception $e) {
                $wpdb->query("ROLLBACK;");
                $error_message_raw = will_get_exception_string($e);
                $error_message = esc_sql($error_message_raw);
                $update_sql = "UPDATE wp_payment_history_ipn SET error_msg = '$error_message' WHERE id = " . $ipn_object->id;
                $wpdb->query($update_sql);
                will_throw_on_wpdb_error($wpdb, 'updating error message on hisory ipn');
                throw $e;
            }


            return true;
        }
        return false;
    }

    /**
     * @param FLPaymentHistoryIPN $ipn_object
     * @throws Exception
     * @return bool, true if we did something
     */
    protected static function maybe_remove_refill( $ipn_object){
        global $wpdb;
        $history_obj = $ipn_object->history_reference_object;
        if (empty($history_obj)) {
            throw new RuntimeException("History Object is Empty");
        }
        //if the status is changing from a completed to a pending or failed, then make transaction and remove money from wallet
        // BUT ONLY IF we have already given money to the wallet, or there is one more complete transaction than total failed ipn status with other transaction id
        $count_process_fees = FLPaymentHistory::count_transaction_type_associated($history_obj->id,FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE);
        $count_un_process_fees = FLPaymentHistory::count_transaction_type_associated($history_obj->id,FLTransactionLookup::TRANSACTION_TYPE_UNDO_PROCESSING_FEE);
        if ($count_process_fees > $count_un_process_fees) {

            //we remove total amount (minus fees) from user balance

            //make a transaction to undo the fee

            try {
                $wpdb->query( "START TRANSACTION;");
                $net_amount_to_remove = $history_obj->payment_amount - $history_obj->processing_fee_included;
                $user_balance = get_user_meta($history_obj->user_id, 'total_user_balance', true);
                $new_balance = $user_balance - $net_amount_to_remove;
                update_user_meta($history_obj->user_id, 'total_user_balance', amount_format($new_balance));

                $fee_transaction_id = transaction_updated('Undo Refill Processing Fee ',
                    $history_obj->user_id,
                    $history_obj->processing_fee_included, //code-notes positive amount because we are refunding (adding)
                    'Undoing Payment processing fee ' , //code-notes do not put in raw value into reason as it gets printed out to screen and hard to format
                    FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_UNDO_PROCESSING_FEE]);

                $update_sql = "UPDATE wp_payment_history_ipn SET other_transaction_id = $fee_transaction_id WHERE id = ".$ipn_object->id;
                $wpdb->query($update_sql);
                will_throw_on_wpdb_error($wpdb,'updating $fee_transaction_id for history ipn');

                $wpdb->query( "COMMIT;"); //make sure to set everything only if no errors, we do not want half done transactions/history here
            } catch (Exception $e) {
                $wpdb->query( "ROLLBACK;");
                $error_message_raw = will_get_exception_string($e);
                $error_message = esc_sql($error_message_raw);
                $update_sql = "UPDATE wp_payment_history_ipn SET error_msg = '$error_message' WHERE id = ".$ipn_object->id;
                $wpdb->query($update_sql);
                will_throw_on_wpdb_error($wpdb,'updating error message on hisory ipn');
                throw $e;
            }



            return true;
        }//if okay to remove
        return false;
    }

    public static function make_new_payment($amount,$processing_fee,$type_payment,$item_name,$transaction_title,
                                            &$new_transaction_id,$initial_status =FLTransactionPost::TRANSACTION_PENDING ) {
        global $wpdb;
        $user_id = get_current_user_id();
        $new_transaction_id = transaction_updated($transaction_title,$user_id,$amount,$transaction_title,
            FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_REFILL],
            false,$initial_status,true);
        update_post_meta($new_transaction_id,FLTransactionLookup::META_KEY_PAYMENT_TYPE,$type_payment);
        //insert a new payment history, with the post attached
        $full_amount = $amount + $processing_fee;

        $payment_data = array(
            'txn_id'            => null,
            'payment_amount'    => $full_amount,
            'processing_fee_included' => $processing_fee,
            'payment_status'    => 'pending',
            'item_name'         => $item_name,
            'user_id'           => $user_id,
            'payment_type'      => $type_payment,
            "transaction_post_id" => $new_transaction_id
        );

        $wpdb->insert( 'wp_payment_history', $payment_data );
        will_throw_on_wpdb_error($wpdb,'insert payment history');
        $new_payment_history_id = $wpdb->insert_id;
        static::log(static::LOG_DEBUG,'made new payment history',[
            'id' => $new_payment_history_id,
            'data' => $payment_data
        ]);
        return $new_payment_history_id;

    }

    /**
     * @param FLPaymentHistoryIPN $ipn_object
     * @throws
     */
    public static function payment_actions($ipn_object) {

        static::log(static::LOG_DEBUG,"finishing up actions with the IPN Object of ",$ipn_object);
        $status = $ipn_object->fl_payment_status;
        $history_obj = $ipn_object->history_reference_object;
        $b_is_duplicate = $ipn_object->is_this_a_duplicate();
        if (
            !$b_is_duplicate &&
            ($status === FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE) &&
            (
                ($history_obj->payment_status === FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED) ||
                ($history_obj->payment_status === FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING)
            )
        ) {
            $b_did_add = FLPaymentHistory::add_refill($ipn_object);
            if ($b_did_add) {
                /******************* Send email to user ******************************************************************************/
                $variables = array();
                $user_detail = get_userdata($history_obj->user_id);
                $variables['credit'] = $history_obj->payment_amount;
                $variables['processing_fee'] = $history_obj->processing_fee_included;
                $total_amount = $history_obj->payment_amount - $history_obj->processing_fee_included;
                $variables['total_amount'] = $total_amount;
                emailTemplateForUser($user_detail->user_email, EMAIL_TO_USER_WHEN_REFILL_CREDIT, $variables);
                static::log(static::LOG_DEBUG,'Got callback and completed refill');
            } else {
                static::log(static::LOG_DEBUG,'Got callback that was successful, but refill already done, so did nothing');
            }

        } //if going to complete from something else
        else if (
            !$b_is_duplicate &&
            ($status === FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED) &&
            (
                ($history_obj->payment_status === FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE) ||
                ($history_obj->payment_status === FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING)
            )
        )
        {
            $b_did_undo = FLPaymentHistory::maybe_remove_refill($ipn_object);
            if ($b_did_undo) {
                /******************* Send email to user ******************************************************************************/
                $variables = array();
                $user_detail = get_userdata($history_obj->user_id);
                $variables['credit'] = $history_obj->payment_amount;
                $variables['processing_fee'] = $history_obj->processing_fee_included;
                $total_amount = $history_obj->payment_amount - $history_obj->processing_fee_included;
                $variables['total_amount'] = $total_amount;
                emailTemplateForUser($user_detail->user_email,EMAIL_TO_USER_WHEN_UNDO_CREDIT,$variables);

                static::log(static::LOG_DEBUG,'Got callback and undid refill');
            } else {
                //send failed email
                $variables = array();
                $user_detail = get_userdata($history_obj->user_id);
                $variables['credit'] = $history_obj->payment_amount;
                $variables['processing_fee'] = $history_obj->processing_fee_included;
                $total_amount = $history_obj->payment_amount - $history_obj->processing_fee_included;
                $variables['total_amount'] = $total_amount;
                emailTemplateForUser($user_detail->user_email,EMAIL_TO_USER_WHEN_CANNOT_CREDIT,$variables);
                static::log(static::LOG_DEBUG,'Got callback and sent message about failing');
            }

        }//if going to failed from something else
        else {
            static::log(static::LOG_DEBUG,'Got callback but was pending, or not a transition, so did nothing');
        } //if not doing any action
        if ($b_is_duplicate) {
            $ipn_object->mark_duplicate();
        }
        if (static::is_at_level(static::LOG_DEBUG)) {
            try {
                $debug_info = $ipn_object->do_debug_things();
                static::log(static::LOG_DEBUG, 'debugging info', $debug_info);
            } catch (Exception $ef) {
                static::log(static::LOG_WARNING,'Problem showing debug dump',$ef);
            }
        }


    }


}