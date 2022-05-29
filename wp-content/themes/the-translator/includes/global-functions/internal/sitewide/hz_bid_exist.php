<?php

function hz_bid_exist( $bid ){

    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */

    global $wpdb;

    $row    = $wpdb->get_results( "SELECT * FROM wp_fl_job WHERE `bid_id` = $bid" );

    if( $row )

        return true;

    else

        return false;

}