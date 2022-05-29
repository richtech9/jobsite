<?php

function hz_bid_id_slug( $bid ){
    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */
    global $wpdb;

    $row    = $wpdb->get_results( "SELECT title FROM wp_fl_job WHERE `bid_id` = $bid" );

    if( $row )

        return $row[0]->title;

    else

        return '';

}