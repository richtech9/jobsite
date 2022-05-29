<?php

/***** Author: Arvik *****/
add_action('wp_ajax_job_description_editable', 'job_description_editable');


function job_description_editable(){
    /*
     * current-php-code 2020-Oct-5
     * ajax-endpoint  job_description_editable
     * input-sanitized : author_id,job_id
    */

    $job_id = (int)FLInput::get('job_id');
    $author_id = (int)FLInput::get('author_id');
    $job_instruction = FLInput::get('job_instruction');

    $current_user       = wp_get_current_user();

    $current_user_id    = $current_user->data->ID;



    if( $author_id == $current_user_id){

        $job_instruction           = removePersonalInfo($job_instruction);//not used ?

        update_post_meta($job_id, 'project_description', $job_instruction);
        FreelinguistProjectAndContestHelper::update_elastic_index($job_id);
        echo 'true';

        exit;

    }else{

        echo 'false';

        exit;

    }

}