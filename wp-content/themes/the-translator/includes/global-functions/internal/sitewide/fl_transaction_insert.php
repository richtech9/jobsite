<?php

function fl_transaction_insert( $amount = 0.0, $status = '', $type = '', $user_id = NULL,
                                $user_id_added_by = NULL, $description = '', $gateway = '', $txn_id = '',
                                $project_id = NULL, $job_id = NULL,

                                $milestone_id = NULL, $refundable = 0 ,
                                $content_id = NULL
){

    /*
      * current-php-code 2020-Oct-07
      * internal-call
      * input-sanitized :
     *
      */





    global $wpdb;

    $user_id = will_cast_emptish_to_null($user_id);
    $user_id_added_by = will_cast_emptish_to_null($user_id_added_by);
    $content_id = will_cast_emptish_to_null($content_id);
    $project_id = will_cast_emptish_to_null($project_id);
    $job_id = will_cast_emptish_to_null($job_id);
    $milestone_id = will_cast_emptish_to_null($milestone_id);

    $data   = array(

        'txn_id'            => change_transaction_id( $user_id ,false,$da_number),

        'amount'            => $amount,

        'payment_status'    => $status,

        'type'              => $type,

        'user_id'           => $user_id,

        'user_id_added_by'  => $user_id_added_by,

        'description'       => $description,

        'gateway'           => $gateway,

        'gateway_txn_id'    => $txn_id,

        'project_id'        => $project_id,

        'job_id'            => $job_id,

        'milestone_id'      => $milestone_id,

        'refundable'        => $refundable,

        'content_id'        => $content_id

    );



    $wpdb->insert( 'wp_fl_transaction', $data );
    $last_id = will_get_last_id($wpdb,'Creation of Transaction');
    return $last_id;

}
