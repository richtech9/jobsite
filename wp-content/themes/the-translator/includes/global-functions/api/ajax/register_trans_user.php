<?php

add_action( 'wp_ajax_reg_user', 'register_trans_user' );

add_action( 'wp_ajax_nopriv_reg_user', 'register_trans_user' );


//code-bookmark the php function for registering users in this theme
function register_trans_user(){

    /*
     * current-php-code 2020-Oct-16
     * ajax-endpoint  lang,reg_user
     * input-sanitized : data keys --> email,password,usr_type
    */

    global $wpdb;

    $data_string 	= FLInput::get('data','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    $data = [];
    parse_str($data_string, $data);

    $email =   FLInput::clean_data_key($data,'email','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    $password =  FLInput::clean_data_key($data,'password','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

    $usr_type = FLInput::clean_data_key($data,'usr_type');
    $lang = FLInput::get('lang','en');

    if ( email_exists($email) == false ) {

        $parts = explode("@", $email);

        $username = $parts[0];

        if(username_exists($username)){

            $username = $username.'_'.rand(10,999);

        }

        $user_id = wp_create_user( $username, $password,$email );

        update_user_meta($user_id, '_user_type', $usr_type);

        if( $user_id && !is_wp_error($user_id) ){

            $code = sha1( $user_id . time() );

            $opts = get_option('freeling_option_pages');

            $act_val    = (isset($opts['activation_url'])) ? $opts['activation_url'] : 7;



            $activation_link = add_query_arg( array( 'key' => $code, 'user' => $user_id ), get_permalink($act_val));
            //code-bookmark here is the user activation code generation and storage
            add_user_meta( $user_id, 'has_to_be_activated', $code, true );

            $random_processing_id = rand(0,100);

            update_user_meta( $user_id, 'user_processing_id', $random_processing_id, true );

            $ip_info = getLocationInfoByIp();

            update_user_meta($user_id,'user_residence_country', get_index_by_country($ip_info['country']));

            /* get Email template and send email $result['city'] */



            $variables = array();

            $variables['activation_link'] = $activation_link;

            //echo $_REQUEST['email'].'-'. ACCOUNT_ACTIVATION_TEMPLATE .'-'.$variables;
            //code-notes not queuing the account activation email, by not adding a dummy bcc
            emailTemplateForUser($email, ACCOUNT_ACTIVATION_TEMPLATE ,$variables,[],false);


            $redirect_to =add_query_arg(  ['reg'=> 'true','lang'=>$lang], get_permalink($opts["login_url"]));


            $result = array('msg'=>'success','redirect_to'=> $redirect_to);

            //code-notes in large db with many users, the user_level is recreated each time the role is changed, delete it if its zero to speed up some admin pages
            $level = (int)get_user_meta($user_id, $wpdb->get_blog_prefix() . 'user_level',true);
            if (!$level) {
                delete_user_meta( $user_id, $wpdb->get_blog_prefix() . 'user_level' );
            }

            echo json_encode($result);

            exit;

        }


    }else{

        echo 'already_exist';

        exit;

    }

    wp_die();

}