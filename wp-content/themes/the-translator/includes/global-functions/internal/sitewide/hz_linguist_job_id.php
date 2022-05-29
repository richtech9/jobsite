<?php


function hz_linguist_job_id( $linguist_id, $project_id ){
    /*
     * current-php-code 2020-Oct-18
     * internal-call
     * input-sanitized :
     */
    global $wpdb;


    $jid = $wpdb->get_row( "SELECT * FROM wp_fl_job WHERE  project_id =".$project_id ." AND linguist_id =".$linguist_id );

    if( $jid )

        return $jid->ID;

    else
        will_send_to_error_log("hz_linguist_job_id",
            ["could not find a job for ",'$linguist_id'=>$linguist_id,'$project_id'=>$project_id]);
        return 0;

}
