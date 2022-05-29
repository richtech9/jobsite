<?php



/*

 * Author Name: Lakhvinder Singh

 * Method:      update_user_password_change

 * Description: update_user_password_change

 *

 */

add_action('wp_ajax_update_user_password_change', 'update_user_password_change');


function update_user_password_change(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_user_password_change
    * input-sanitized : old_password,password
    */


    $password = FLInput::get('password','',FLInput::YES_I_WANT_CONVESION,
        FLInput::YES_I_WANT_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    $old_password = FLInput::get('old_password','',FLInput::YES_I_WANT_CONVESION,
        FLInput::YES_I_WANT_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);
    
    if($password){

        $user = get_user_by('id', get_current_user_id());

        $pass = $old_password;

        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID) ){

            $user_id = get_current_user_id();

            $pass_new = $password;

            wp_set_password( trim($pass_new), $user_id );
            //code-notes the above function changes the password

            echo 'success';

            exit;

        }else{

            echo 'wrong_old_password';

            exit;

        }

    }else{

        echo 'failed';

        exit;

    }

}