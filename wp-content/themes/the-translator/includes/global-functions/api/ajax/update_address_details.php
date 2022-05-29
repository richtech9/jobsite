<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_address_details

 * Description: update_address_details

 *

 */

add_action('wp_ajax_update_address_details', 'update_address_details');


function update_address_details(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_address_details
    * input-sanitized : address_line_1,address_line_2,country,full_name,state,telephone_number,town_city,zip_postal_code
    */


    $address_line_1 = FLInput::get('address_line_1');
    $address_line_2 = FLInput::get('address_line_2');
    $country = FLInput::get('country');
    $full_name = FLInput::get('full_name');
    $state = FLInput::get('state');
    $telephone_number = FLInput::get('telephone_number');
    $town_city = FLInput::get('town_city');
    $zip_postal_code = FLInput::get('zip_postal_code');


    if($full_name){


        update_user_meta( get_current_user_id(), 'user_full_name', strip_tags($full_name));

        wp_update_user( array( 'ID' => get_current_user_id(), 'display_name' => strip_tags($full_name ) ));

        update_user_meta( get_current_user_id(), 'user_address_line_1', strip_tags($address_line_1));

        update_user_meta( get_current_user_id(), 'user_address_line_2', strip_tags($address_line_2));

        update_user_meta( get_current_user_id(), 'user_town_city', strip_tags($town_city));

        update_user_meta( get_current_user_id(), 'user_state', $state);

        update_user_meta( get_current_user_id(), 'user_zip_postal_code', strip_tags($zip_postal_code));

        update_user_meta( get_current_user_id(), 'user_residence_country', $country);

        update_user_meta( get_current_user_id(), 'user_phone', $telephone_number);

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}