<?php


add_action('wp_ajax_socialUserTypeLoginChange', 'socialUserTypeLoginChange');

add_action('wp_ajax_nopriv_socialUserTypeLoginChange', 'socialUserTypeLoginChange');

function socialUserTypeLoginChange(){

    /*
      * current-php-code 2021-Feb-10
      * ajax-endpoint  socialUserTypeLoginChange
      * input-sanitized :usr_type
      * public-api
      */
    $user_type = FLInput::get('usr_type');
    $_SESSION['social_user_type'] = $user_type;

    echo 'success';

    exit;

}