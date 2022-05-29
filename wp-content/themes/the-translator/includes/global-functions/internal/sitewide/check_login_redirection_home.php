<?php

function check_login_redirection_home($lang = 'en'){

    /*
     * current-php-code 2020-Sep-30
     * internal-call
     * input-sanitized : lang
    */

    if (!$lang) {
        $lang = FLInput::get('lang','en') ;
    }

    if($lang){

        $is_user_logged_in = get_site_url().'?lang='.$lang;

    }else{

        $is_user_logged_in = get_site_url();

    }

    if(!is_user_logged_in()){

        wp_redirect($is_user_logged_in);

        exit;

    }

}