<?php

/*
 * Author Name: Lakhvinder Singh
 * Method:      check_linguist_email_id_exist_or_not
 * Description: check_linguist_email_id_exist_or_not
 *
 */
add_action('wp_ajax_check_linguist_email_id_exist_or_not', 'check_linguist_email_id_exist_or_not');
function check_linguist_email_id_exist_or_not(){

    /*
      * current-php-code 2020-Jan-11
      * ajax-endpoint  check_linguist_email_id_exist_or_not
      * input-sanitized : action
      */

    if ( !current_user_can( 'manage_options' ) ) {  exit;}

    $user_email = FLInput::get('user_email_is');

    $user = get_user_by( 'email', $user_email );
    if(!empty($user)){
        $current_user       = wp_get_current_user();
        $users_list = getReportedUserByUserId();
        if ( in_array('administrator',$current_user->roles) || in_array('admin',$current_user->roles) || in_array($user->ID,$users_list)) {

            echo $user->ID;
            exit;

        }else{
            echo 'unauthorized';
            exit;
        }
    }else{
        echo 'false';
        exit;
    }
}