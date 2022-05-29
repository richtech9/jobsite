<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_language_info

 * Description: delete_language_info

 *

 */

add_action('wp_ajax_delete_language_info', 'delete_language_info');


function delete_language_info(){

    /*
    * current-php-code 2021-Feb-10
    * ajax-endpoint  delete_language_info
    * input-sanitized :attribute
    */

    $attribute = (int)FLInput::get('attribute');
    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;


    if($attribute >= 0){

        delete_user_meta($current_user_id,'language_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'language_level_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'year_of_experince_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'areas_expertise_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'language_counter'. strip_tags($attribute));

    }

    echo 'success';

    exit;

}