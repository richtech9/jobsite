<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_user_profile_image

 * Description: delete_user_profile_image

 *

 */

add_action('wp_ajax_create_new_job', 'create_new_job');


function create_new_job(){

    /*
    * current-php-code 2021-Feb-10
    * ajax-endpoint  create_new_job
    * input-sanitized :
    */

    echo json_encode( array('url'=>freeling_links('order_process')) );

    exit;



}