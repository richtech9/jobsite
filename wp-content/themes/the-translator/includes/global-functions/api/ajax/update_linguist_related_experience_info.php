<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_linguist_related_experience_info

 * Description: update_linguist_related_experience_info

 *

 */

add_action('wp_ajax_update_linguist_related_experience_info', 'update_linguist_related_experience_info');


function update_linguist_related_experience_info(){

    /*
    * current-php-code 2020-Oct-15
    * ajax-endpoint  update_linguist_related_experience_info
    * input-sanitized : duties,employer,year_in_service
    */
    $duties = FLInput::get('duties',[]);
    $employer = FLInput::get('employer',[]);
    $year_in_service = FLInput::get('year_in_service',[]);

    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;

    $data = $year_in_service;

    $counter = 0;

    for($i=0;$i<count($data);$i++){

        if(!empty($year_in_service[$i]) && !empty($employer[$i]) && !empty($duties[$i])){

            update_user_meta($current_user_id,'year_in_service_'.$i, strip_tags($year_in_service[$i]));

            update_user_meta($current_user_id,'employer_'.$i, strip_tags($employer[$i]));

            update_user_meta($current_user_id,'duties_'.$i, strip_tags($duties[$i]));

            $counter++;

        }

        update_user_meta($current_user_id,'related_experience_counter',$counter );

    }

    echo 'success';

    exit;

}