<?php

add_action( 'wp_ajax_newUserResendConfirmationEmail', 'newUserResendConfirmationEmail' );

add_action( 'wp_ajax_nopriv_newUserResendConfirmationEmail', 'newUserResendConfirmationEmail' );



function newUserResendConfirmationEmail(){
    //code-bookmark this is where the resending of the email happens

    $email = FLInput::get('email','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    /*
    * current-php-code 2020-Sep-30
    * ajax-endpoint  newUserResendConfirmationEmail
    * input-sanitized : email
     * public-api
    */
    if (email_exists($email) != false ) {

        $user = get_user_by( 'email', $email );

        $user_id = $user->ID;



        if( $user_id ){

            $opts = get_option('freeling_option_pages');

            $code = get_user_meta($user_id, 'has_to_be_activated', true);

            $act_val    = (isset($opts['activation_url'])) ? $opts['activation_url'] : 7;



            $activation_link = add_query_arg( array( 'key' => $code, 'user' => $user_id ), get_permalink($act_val));
            will_send_to_error_log("activation link",$activation_link);

            add_user_meta( $user_id, 'has_to_be_activated', $code, true );

            /* get Email template and send email */



            $variables = array();

            $variables['activation_link'] = $activation_link;

            //echo $_REQUEST['email'].'-'. ACCOUNT_ACTIVATION_TEMPLATE .'-'.$variables;
            //code-notes not queuing the resend confirmation activation email, by not adding a dummy bcc
            emailTemplateForUser($email, ACCOUNT_ACTIVATION_TEMPLATE ,$variables,[],false);

            echo 'success';

        }



    }else{

        echo 'email does not exist'.email_exists($email);

    }

    wp_die();

}