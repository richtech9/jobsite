<?php

function gen_pjob_title( $pro_id ){
    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */
    global $wpdb;

    $row    = $wpdb->get_results( "SELECT * FROM wp_fl_job WHERE `project_id` = $pro_id" );


    $jlast  = 0;

    if( $row ){

        $jlast    = $wpdb->get_results(  "SELECT job_seq FROM wp_fl_job WHERE `project_id` = $pro_id  ORDER BY job_seq DESC LIMIT 1"  );

        $jlast    = $jlast[0]->job_seq;

    }

    return $jlast + 1;

}


/*
Author: Sandeep
Method: update_project_status
Description: Update project status
*/
function update_project_status($job_id, $status){
    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */
    update_post_meta($job_id, 'project_status', $status);
}