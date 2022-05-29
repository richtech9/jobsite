<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      freeling_reset_user_password

 * Description: freeling_reset_user_password

 *

 */

add_action('wp_ajax_freeling_reset_user_password', 'freeling_reset_user_password');

add_action('wp_ajax_nopriv_freeling_reset_user_password', 'freeling_reset_user_password');

function freeling_reset_user_password(){

    /*
    * current-php-code 2021-feb-1
    * ajax-endpoint  freeling_reset_user_password
    * input-sanitized : password,reset_password_key,user_registration
     * public-api
    */

    $pass_new = FLInput::get('password','',FLInput::YES_I_WANT_CONVESION,
        FLInput::YES_I_WANT_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    $reset_password_key = FLInput::get('reset_password_key');
    $user_id = (int)FLInput::get('user_registration');
    $stored_key = get_user_meta($user_id,'forget_password_key',true);
    if($pass_new && $user_id && $reset_password_key && ($stored_key === $reset_password_key)){

        $user = get_user_by('id', $user_id);

        if(!empty($user)){

             wp_set_password( $pass_new, $user_id );


            update_user_meta($user_id,'forget_password_key',' ');

            $opts       = get_option('freeling_option_pages');

            $login_url  = (isset($opts['login_url'])) ? $opts['login_url'] : "";

            $log        = get_permalink($login_url);

            echo json_encode(array('message'=>'success', 'url' => $log));

            exit;

        }else{

            echo json_encode(array('message'=>'failed', 'url' => ''));

            exit;

        }

    }else{

        echo json_encode(array('message'=>'failed', 'url' => ''));

        exit;

    }

}