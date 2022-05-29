<?php



/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_certificate_info

 * Description: delete_certificate_info

 *

 */

add_action('wp_ajax_delete_certificate_info', 'delete_certificate_info');


function delete_certificate_info(){

    /*
    * current-php-code 2021-Feb-10
    * ajax-endpoint  delete_certificate_info
    * input-sanitized :attribute
    */

    $attribute = (int)FLInput::get('attribute');
    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;


    if($attribute >= 0){

        delete_user_meta($current_user_id,'year_recieved_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'recieved_from_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'certificate_'. strip_tags($attribute));

    }

    echo 'success';

    exit;

}