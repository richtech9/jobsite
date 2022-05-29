<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_customer_personal_info_data

 * Description: update_customer_personal_info_data

 *

 */

add_action('wp_ajax_update_customer_personal_info_data', 'update_customer_personal_info_data');


function update_customer_personal_info_data(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_customer_personal_info_data
    * input-sanitized : display_name,user_description,user_phone,user_residence_country
    */

   // FLInput::turn_on_debugging();
    $user_phone = FLInput::get('user_phone');
    $display_name = FLInput::get('display_name');
    $user_residence_country = FLInput::get('user_residence_country');
    $user_description = FLInput::get('user_description');

    if($display_name && $user_phone){

        update_user_meta( get_current_user_id(), 'user_phone', $user_phone);


        update_user_meta( get_current_user_id(), 'user_residence_country', $user_residence_country);

        update_user_meta( get_current_user_id(), 'user_description', $user_description);

        wp_update_user( array( 'ID' => get_current_user_id(), 'display_name' => $display_name) );

        FreelinguistUserHelper::update_elastic_index(get_current_user_id());
        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}