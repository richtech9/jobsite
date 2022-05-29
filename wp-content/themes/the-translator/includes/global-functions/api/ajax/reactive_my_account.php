<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      reactive_my_account

 * Description: reactive_my_account

 *

 */

add_action('wp_ajax_reactive_my_account', 'reactive_my_account');

add_action('wp_ajax_nopriv_reactive_my_account', 'reactive_my_account');

function reactive_my_account(){
    /*
        * current-php-code 2020-Sep-30
        * ajax-endpoint  reactive_my_account
        * input-sanitized : email
         * public-api
        */
    $email =   FLInput::get('email','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    if($email){

        global $wpdb;

        $account_detail = get_user_by( 'email', $email );

        $account = $account_detail->ID;

        if(!empty($account)){

            $variables = array();

            $wpdb->update( $wpdb->prefix.'users',  array('user_status' => 0), array( 'user_email' => "$email" ));

            //code-notes not queuing the re-activation account email, by not adding a dummy bcc
            emailTemplateForUser($email,ACCOUNT_ACTIVATED_TEMPLATE,$variables,[],false);

            wp_logout();

            echo 'success';

            exit;

        }else{

            echo 'failed';

            exit;

        }

    }else{

        echo 'failed';

        exit;

    }

}