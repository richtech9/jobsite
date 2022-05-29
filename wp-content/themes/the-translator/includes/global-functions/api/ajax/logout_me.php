<?php

add_action('init','logout_me');

function logout_me() {
    /*
     * current-php-code 2020-Oct-5
     * ajax-endpoint  logout_me (not a true ajax)
     * public-api
     * input-sanitized : action,lang
    */

    $action = FLInput::get('action');
    $lang = FLInput::get('lang','en');
    if($action === 'logout_me'){

        /* Force no-cache headers on *ALL* front pages and in *ALL* cases */

        /* Proof of concept, do not use */

        header( 'Cache-Control: no-cache, no-store, must-revalidate' );

        header( 'Pragma: no-cache' );

        header( 'Expires: 0' );

        /* Do same for admin_init to get dashboard to not cache */

        wp_logout();

        if($lang){

            $log = freeling_links("dashboard_url").'&user_redirect=home';

        }else{

            $log = freeling_links('dashboard_url').'?user_redirect=home';

        }



        wp_redirect($log);

        exit;

    }

}