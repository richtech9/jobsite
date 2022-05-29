<?php

/**
 * @param $title
 * @return object
 */
function hz_get_job_data( $title ){

    /*
     * current-php-code 2020-Sep-30
     * internal-call
     * input-sanitized :
     */
    global $wpdb;
    if (!$title) {return null;}

    $row    = $wpdb->get_results( "SELECT * FROM wp_fl_job WHERE `title` = '".$title."'" );

    if( $row )

        return $row[0];

    else

        return null;

}