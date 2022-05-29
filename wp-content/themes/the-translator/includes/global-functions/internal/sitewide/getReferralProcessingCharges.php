<?php

/***** get charges with referral amount ****/
/************ amount - getRefillAmount() *************************/

/**
 * @param float|string $amount  can be numeric string castable to float
 * @return float
 */
function getReferralProcessingCharges($amount){
    /*
     * current-php-code 2020-Oct-14
     * internal-call
     * input-sanitized :
    */
    $amount_cast_as_float = floatval($amount);
    $amount_referral_fee = floatval(get_option('client_referral_fee') ?  get_option('client_referral_fee') :2);
    $client_flex_fee = floatval(get_option('client_flex_referral_fee') ?  get_option('client_flex_referral_fee') :2.5);
    $amount_processing_fee = ($amount_cast_as_float * ($client_flex_fee)) /100;
    return floatval($amount_processing_fee + $amount_referral_fee);
}