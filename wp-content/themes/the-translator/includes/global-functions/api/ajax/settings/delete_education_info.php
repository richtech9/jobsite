<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_education_info

 * Description: delete_education_info

 *

 */

add_action('wp_ajax_delete_education_info', 'delete_education_info');


function delete_education_info(){

    /*
    * current-php-code 2021-Feb-10
    * ajax-endpoint  delete_education_info
    * input-sanitized :attribute
    */

    $attribute = (int)FLInput::get('attribute');

    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;


    if($attribute >= 0){

        delete_user_meta($current_user_id,'year_attended_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'institution_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'degree_'. strip_tags($attribute));

    }

    $msg ='success';
    echo trim($msg);

    exit;

}