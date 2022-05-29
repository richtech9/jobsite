<?php

function hz_check_latest_milestone_status( $project_id, $user_id ){
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
                   UNIX_TIMESTAMP(rejected_at) as rejected_at_ts,
                  UNIX_TIMESTAMP(updated_at) as updated_at_ts
                  FROM wp_fl_milestones 
                  WHERE `project_id` = $project_id AND `linguist_id` = $user_id 
                  order by updated_at desc limit 1" );

    if( $row )

        return $row[0];

    else

        return false;

}