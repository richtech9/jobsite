<?php

function check_login_redirection(){

    /*
     * current-php-code 2020-Oct-11
     * internal-call
     * input-sanitized : lang
    */
    $lang = FLInput::get('lang', 'en');
    $opts       = get_option('freeling_option_pages');

    $login_url  = (isset($opts['login_url'])) ? $opts['login_url'] : "";


    if($lang){

        $log        = get_permalink($login_url);

    }else{

        $log        = get_permalink($login_url);

    }

    if(!is_user_logged_in()){

        wp_redirect($log);

        exit;

    }

}