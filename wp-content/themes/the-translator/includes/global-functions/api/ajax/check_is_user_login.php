<?php

add_action( 'wp_ajax_is_user_login', 'check_is_user_login'  );
add_action( 'wp_ajax_nopriv_is_user_login', 'check_is_user_login'  );

function check_is_user_login(){
    /*
   * current-php-code 2020-Oct-14
   * ajax-endpoint  is_user_login
   * input-sanitized :
   * public-api
   */
    if(is_user_logged_in()){
        echo json_encode( array( 'login' => true ) );
    }
    else{
        echo json_encode( array( 'login' => false ) );
    }
    wp_die();
}