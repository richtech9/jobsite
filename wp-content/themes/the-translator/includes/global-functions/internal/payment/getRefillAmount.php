<?php



/************ Refill amount - 2.5% processing fee *************************/

/**
 * @param float $amount
 * @return string
 */
function getRefillAmount($amount){

    /*
     * current-php-code 2021-Jan-8
     * internal-call
     * input-sanitized :
    */
    $refill_amount = $amount - floatval(get_refill_processing_charges($amount));
    return amount_format($refill_amount);
}