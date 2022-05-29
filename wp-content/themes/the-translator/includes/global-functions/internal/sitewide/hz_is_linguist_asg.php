<?php

function hz_is_linguist_asg( $project_id, $user_id ){
    /*
     * current-php-code 2020-Oct-18
     * internal-call
     * input-sanitized :
     */
    global $wpdb;

    $user_id = (int)$user_id;
    $project_id = (int)$project_id;

    $row    = $wpdb->get_results( "SELECT * FROM wp_fl_job WHERE `project_id` = $project_id AND `linguist_id` = $user_id" );

    if( $row )

        return $row[0];

    else

        return false;

}