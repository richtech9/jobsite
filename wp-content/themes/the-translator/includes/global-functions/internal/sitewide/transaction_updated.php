<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      transaction_updated

 * Description: Its using for transaction related enteries

 *

 */

function transaction_updated($transaction_title,$user_id,$transactionAmount,$transactionReason,$transactionType,
                             $job_id=false,$status= FLTransactionPost::TRANSACTION_COMPLETE,$b_error_checking = false){

    /*
     * current-php-code 2020-Dec-28
     * internal-call
     * input-sanitized :
     */



        $transaction        = array(

            'post_title'    => $transaction_title,

            'post_content'  => '',

            'post_status'   => $status,

            'post_author'   => $user_id,

            'post_type'     => 'wallet'

        );

        $transaction_id = wp_insert_post( $transaction );
        if ($b_error_checking) {
            if ((! intval($transaction_id)) || is_wp_error($transaction_id)) {
                throw new RuntimeException("Could not create transaction post");
            }
        }

        update_post_meta( $transaction_id, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, amount_format($transactionAmount));

        update_post_meta( $transaction_id, FLTransactionLookup::META_KEY_TRANSACTION_REASON, $transactionReason);

        update_post_meta( $transaction_id, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, $transactionType);

        if($transactionType == FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW]){
            update_post_meta( $transaction_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS,
                FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_PENDING]);
        }

        if($job_id != false){

            update_post_meta( $transaction_id, FLTransactionLookup::META_KEY_TRANSACTION_RELATED_TO, $job_id);

            $modified_transaction_id = change_transaction_id($user_id,$job_id,$da_number);

            update_post_meta($transaction_id,FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID,$modified_transaction_id);
            update_post_meta($transaction_id,FLTransactionLookup::META_KEY_NUMERIC_MODIFIED_ID,$da_number);
        }else{

            $modified_transaction_id = change_transaction_id($user_id,false,$da_number);

            update_post_meta($transaction_id,FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID,$modified_transaction_id);
            update_post_meta($transaction_id,FLTransactionLookup::META_KEY_NUMERIC_MODIFIED_ID,$da_number);
        }

        return $transaction_id;



}