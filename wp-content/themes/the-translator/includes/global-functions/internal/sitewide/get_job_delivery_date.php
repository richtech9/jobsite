<?php

function get_job_delivery_date($job_id){

    /*
     * current-php-code 2020-Oct-06
     * internal-call
     * input-sanitized :
     */

    $data = new stdClass();

    $job_standard_delivery_date = get_post_meta($job_id, 'job_standard_delivery_date', true);

    $data->delivery_date_only = $job_standard_delivery_date;

    $data->delivery_date_time = $job_standard_delivery_date.'23:59:59';

    return $data;

}