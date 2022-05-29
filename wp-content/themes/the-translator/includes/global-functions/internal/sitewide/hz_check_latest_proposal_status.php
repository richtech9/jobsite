<?php

/**
 * @param $project_id
 * @param $user_id
 * @return object
 */
function hz_check_latest_proposal_status( $project_id, $user_id ){
    /*
     * current-php-code 2020-Nov-15
     * internal-call
     * input-sanitized :
     */
    global $wpdb;
    $project_id = (int)$project_id;
    $user_id = (int)$user_id;
    $row    = $wpdb->get_results(
        "SELECT * , 
                UNIX_TIMESTAMP(created_at) as da_start_ts,
                UNIX_TIMESTAMP(rejected_at) as rejected_at_ts,
                UNIX_TIMESTAMP(updated_at) as updated_at_ts
              FROM wp_proposals 
              WHERE `post_id` = $project_id AND `by_user` = $user_id 
              ORDER BY updated_at DESC LIMIT 1" );

    if( $row )

        return $row[0];

    else

        return (object)[];

}
