<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_linguist_edu_info

 * Description: update_linguist_edu_info

 *

 */

add_action('wp_ajax_update_linguist_edu_info', 'update_linguist_edu_info');


function update_linguist_edu_info(){

    /*
    * current-php-code 2020-Oct-15
    * ajax-endpoint  update_linguist_edu_info
    * input-sanitized : degree,institution,year_attended
    */

    $degree = FLInput::get('degree',[]);
    $institution = FLInput::get('institution',[]);
    $year_attended = FLInput::get('year_attended',[]);

    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;

    $data = $year_attended;

    $counter = 0;

    for($i=0;$i<count($data);$i++){

        if(!empty($year_attended[$i]) && !empty($institution[$i]) && !empty($degree[$i])){

            update_user_meta($current_user_id,'year_attended_'.$i, strip_tags($year_attended[$i]));

            update_user_meta($current_user_id,'institution_'.$i, strip_tags($institution[$i]));

            update_user_meta($current_user_id,'degree_'.$i, strip_tags($degree[$i]));

            $counter++;

        }

        update_user_meta($current_user_id,'education_counter',$counter );

    }

    echo 'success';

    exit;

}