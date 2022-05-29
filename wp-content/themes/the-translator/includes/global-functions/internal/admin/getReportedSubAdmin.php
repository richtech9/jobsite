<?php

// To get the reported user of sub admin
function getReportedSubAdmin(){

    /*
    * current-php-code 2021-Jan-9
    * internal-call
    * input-sanitized :
   */
    $args = array(
        'role__in'     => array('cashier_sub_admin','message_sub_admin','evaluation_sub_admin','meditation_sub_admin'),
        'meta_query' => array(
            'key' => 'reported_to',
            'value' => get_current_user_id()
        ),
        'fields'       => array('ID','user_email','user_login'),
    );
    $Users = get_users( $args );
    return $Users;
}