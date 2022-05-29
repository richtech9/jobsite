<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_personal_info_data

 * Description: update_personal_info_data

 *

 */

add_action('wp_ajax_update_personal_info_data', 'update_personal_info_data');


function update_personal_info_data(){

    /*
    * current-php-code 2020-Oct-15
    * ajax-endpoint  update_personal_info_data
    * input-sanitized : display_name,user_address,user_city,user_hourly_rate,user_phone,user_residence_country,user_time_zone
    */


     $display_name = FLInput::get('display_name');
     $user_address = FLInput::get('user_address');
     $user_city = FLInput::get('user_city');
     $user_hourly_rate = FLInput::get('user_hourly_rate');
     $user_phone = FLInput::get('user_phone');
     $user_residence_country = FLInput::get('user_residence_country');
     $user_time_zone = FLInput::get('user_time_zone');


    if($user_phone && $display_name && $user_residence_country){

        update_user_meta( get_current_user_id(), 'user_phone', strip_tags($user_phone));

        if (isset($_REQUEST['user_address'])) {
            update_user_meta( get_current_user_id(), 'user_address', strip_tags($user_address));
        }

        update_user_meta( get_current_user_id(), 'user_city', strip_tags($user_city));
        update_user_meta( get_current_user_id(), 'user_hourly_rate', strip_tags($user_hourly_rate));
        update_user_meta( get_current_user_id(), 'user_time_zone', strip_tags($user_time_zone));

        update_user_meta( get_current_user_id(), 'user_residence_country', strip_tags($user_residence_country));

        wp_update_user( array( 'ID' => get_current_user_id(), 'display_name' => strip_tags($display_name ) ));

        //code-notes start to update es index for this
        FreelinguistUserHelper::update_elastic_index(get_current_user_id());

        //code-notes update units
        FreelinguistUnitGenerator::generate_units($log,[get_current_user_id()],[]);

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}