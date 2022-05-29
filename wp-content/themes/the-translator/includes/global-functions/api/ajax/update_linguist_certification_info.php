<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_linguist_certification_info

 * Description: update_linguist_certification_info

 *

 */

add_action('wp_ajax_update_linguist_certification_info', 'update_linguist_certification_info');


function update_linguist_certification_info(){

    /*
    * current-php-code 2020-Oct-15
    * ajax-endpoint  update_linguist_certification_info
    * input-sanitized : certificate,recieved_from,year_recieved
    */

    $year_recieved = FLInput::get('year_recieved',[]);
    $recieved_from = FLInput::get('recieved_from',[]);
    $certificate = FLInput::get('certificate',[]);


    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;

    $data = $year_recieved;

    $counter = 0;

    for($i=0;$i<count($data);$i++){

        if(!empty($year_recieved[$i]) && !empty($recieved_from[$i]) && !empty($certificate[$i])){

            update_user_meta($current_user_id,'year_recieved_'.$i, strip_tags($year_recieved[$i]));

            update_user_meta($current_user_id,'recieved_from_'.$i, strip_tags($recieved_from[$i]));

            update_user_meta($current_user_id,'certificate_'.$i, strip_tags($certificate[$i]));

            $counter++;

        }

        update_user_meta($current_user_id,'certification_counter',$counter );

    }

    echo 'success';

    exit;

}