<?php

/************ Refill amout + 2.5% processing fee *************************/
add_action('wp_ajax_fl_get_refill_processing_fee', 'fl_get_refill_processing_fee');

function fl_get_refill_processing_fee(){
    /*
       * current-php-code 2020-Jan-28
       * ajax-endpoint  fl_get_refill_processing_fee
       * input-sanitized : amount
       */
    $amount = (float)FLInput::get('amount');
    $amount_processing_fee = get_refill_processing_charges($amount);
    $total_amount = $amount+$amount_processing_fee;
    wp_send_json( [
        'status' => true,
        'message' => 'ok',
        'amount'=>$amount,
        'processing_fee'=> $amount_processing_fee,
        'total'=> $total_amount
    ]
    );
}