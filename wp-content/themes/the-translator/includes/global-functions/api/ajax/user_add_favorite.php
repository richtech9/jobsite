<?php

//USER PROFILE FAV ADD/REMOVE
add_action('wp_ajax_user_add_favorite', 'user_add_favorite');

function user_add_favorite(){
    /*
    * current-php-code 2020-Oct-17
    * ajax-endpoint  user_add_favorite
    * input-sanitized :
    */

    $userId = get_current_user_id();
    $response = addToFav($userId);
    wp_send_json($response);
    exit;
}



//USER PROFILE FAV ADD/REMOVE