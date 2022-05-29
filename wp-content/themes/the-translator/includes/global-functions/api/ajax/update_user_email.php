<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      update_user_email

 * Description: update_user_email

 *

 */

add_action('wp_ajax_update_user_email', 'update_user_email');


function update_user_email(){
    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_user_email
    * input-sanitized : new_email
    */


    $new_email = FLInput::get('new_email','',FLInput::YES_I_WANT_CONVESION,
        FLInput::YES_I_WANT_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    if (email_exists($new_email) == false ) {

        $user_id = get_current_user_id();

        update_user_meta($user_id, 'user_new_email', $new_email);

        if( $user_id && !is_wp_error($user_id) ){

            $code = sha1( $user_id . time() );

            $opts = get_option('freeling_option_pages');

            $act_val    = (isset($opts['activation_url'])) ? $opts['activation_url'] : 7;

            $activation_link = add_query_arg( array( 'email_activitation_key' => $code, 'user' => $user_id ), get_permalink($act_val));

            update_user_meta( $user_id, 'has_to_be_activated_email', $code );

            $variables = array();

            $variables['activation_link'] = $activation_link;

            emailTemplateForUser($new_email,ACTIVATION_LINK_EMAIL_CHANGE_TEMPLATE,$variables);
            //code-notes link processed in wp-content/themes/the-translator/templates/acc-active-tpl.php

            echo 'success';

            exit;

        }

    }else{

        echo 'already_exist';

        exit;

    }

}

