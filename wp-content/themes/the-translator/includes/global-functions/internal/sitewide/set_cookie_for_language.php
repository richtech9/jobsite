<?php

function set_cookie_for_language(){

    /*
    * current-php-code 2021-Feb-9
    * internal-call
    * input-sanitized : lang
    */

    $lang = FLInput::get('lang','en');

    $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http"); //$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $actual_link = "$actual_link://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    setcookie('_is_language', $lang, time() + (86400 * 90), "/");

}