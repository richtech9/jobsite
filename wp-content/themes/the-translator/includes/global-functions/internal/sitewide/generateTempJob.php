<?php

function generateTempJob(){

    /*
     * current-php-code 2020-Oct-16
     * internal-call
     * input-sanitized :
     */

    $user_ID        = get_current_user_id();

    global $wpdb;


    $wpdb->delete(  'wp_files', array( 'by_user' => $user_ID,'status' => -1 ) );

    $job_post = array(

        'post_title'    => 'test',

        'post_content'  => '',

        'post_status'   => 'pending',

        'post_author'   => get_current_user_id(),

        'post_type'     => 'job'

    );

    $job_id     = wp_insert_post( $job_post );


    $job_title  = change_the_pending_job_id($job_id,$da_number);

    update_post_meta($job_id,'modified_id',$job_title);
    update_post_meta($job_id,'numeric_modified_id',$da_number);
    $my_post = array('ID' => $job_id,'post_title' => $job_title);

    wp_update_post($my_post);

    $date = strtotime(date('Y-m-d'));

    $date = strtotime("+7 day", $date);

    $job_standard_delivery_date =  date('Y-m-d', $date);

    update_post_meta( $job_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($job_standard_delivery_date));


    return $job_id;

}