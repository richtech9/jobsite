<?php

function fl_get_job_pay_status( $job_id, $status ){
    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */
    global $wpdb;

    $req_sum    = $wpdb->get_results("SELECT sum( amount ) as total_amount FROM wp_fl_milestones WHERE job_id = $job_id AND status = '$status'" ) ;

    return $req_sum[0]->total_amount;

}