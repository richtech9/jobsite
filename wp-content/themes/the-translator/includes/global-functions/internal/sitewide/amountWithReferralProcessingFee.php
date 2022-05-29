<?php

function amountWithReferralProcessingFee($amount){
    /*
     * current-php-code 2020-Oct-14
     * internal-call
     * input-sanitized :
    */
    $amount_processing_fee = getReferralProcessingCharges($amount);
    return amount_format($amount+$amount_processing_fee);
}