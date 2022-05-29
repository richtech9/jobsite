<?php

add_action('wp_ajax_fl_update_txn_description', 'fl_update_txn_description');

function fl_update_txn_description()
{

    /*
         * current-php-code 2021-March-5
         * ajax-endpoint  fl_update_txn_description
         * input-sanitized : description,txn_id
         */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }
    try {

        $txn_id = (int)FLInput::get('txn_id');
        $words = FLInput::get('description');
        $sql = "SELECT id from wp_fl_transaction WHERE id = $txn_id";
        $id_seen = (int)$wpdb->get_var($sql);
        will_throw_on_wpdb_error($wpdb,'checking txn id');
        if (!$id_seen) {throw new RuntimeException("Cannot find the id txn id of $txn_id");}

        $sql = "UPDATE wp_fl_transaction SET description = '$words' WHERE id = $txn_id"; //words already escaped and html taken care of
        $wpdb->query($sql);
        will_throw_on_wpdb_error($wpdb,'updating description');
        $rows_affected = (int)$wpdb->rows_affected;
        if (!$rows_affected) {
            throw new RuntimeException("Did not update anything");
        }


        wp_send_json( ['status' => true, 'message' => 'TXN saved','id'=>$txn_id,'words'=>$words]);

        wp_die();


    } catch (Exception $e) {
        will_send_to_error_log('admin update txn description',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }
}