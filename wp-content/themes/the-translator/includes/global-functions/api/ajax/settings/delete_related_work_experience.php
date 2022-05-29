<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_related_work_experience

 * Description: delete_related_work_experience

 *

 */

add_action('wp_ajax_delete_related_work_experience', 'delete_related_work_experience');


function delete_related_work_experience(){

    /*
    * current-php-code 2021-Feb-10
    * ajax-endpoint  delete_related_work_experience
    * input-sanitized :attribute
    */

    $attribute = (int)FLInput::get('attribute');

    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;



    if($attribute >= 0){

        delete_user_meta($current_user_id,'year_in_service_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'employer_'. strip_tags($attribute));

        delete_user_meta($current_user_id,'duties_'. strip_tags($attribute));

    }

    echo 'success';

    exit;

}