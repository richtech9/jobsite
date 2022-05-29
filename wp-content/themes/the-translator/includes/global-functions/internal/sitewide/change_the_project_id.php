<?php

function change_the_project_id( $prefix,&$da_number ){

    /*
     * current-php-code 2020-Oct-16
     * internal-call
     * input-sanitized :
     */

    $custom_job_id = empty(get_option('custom_pro_id')) ? 20000 : get_option('custom_pro_id');

    $custom_job_id = $custom_job_id + 1;
    $da_number = $custom_job_id;
    update_option( 'custom_pro_id', $custom_job_id, true );

    return $prefix.''.$custom_job_id;

}