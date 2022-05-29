<?php

/************ amount - getRefillAmount() *************************/

/**
 * @param float $amount
 * @return float
 */
function get_refill_processing_charges($amount){
    /*
     * current-php-code 2021-Jan-7
     * internal-call
     * input-sanitized :
    */
    $amount = (float)$amount;
    $refill_fee_base = get_option('refill_fee_base',0);
    if (empty($refill_fee_base)) {$refill_fee_base = 0;}
    $refill_fee_percentage = get_option('refill_fee_percentage',0);
    if (empty($refill_fee_percentage)) {$refill_fee_percentage = 0;}

    $fee = floatval($refill_fee_base + $refill_fee_percentage / 100 * $amount);
    return round($fee,2);
}