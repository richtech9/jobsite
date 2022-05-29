<?php

// To get the reported user of sub admin
function getReportedSuperSubAdmin(){

    /*
    * current-php-code 2021-Jan-15
    * internal-call
    * input-sanitized :
    */

    $user_id = get_current_user_id();

    $reported_to = get_user_meta($user_id,'reported_to',true);
    return $reported_to;
}