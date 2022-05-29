<?php

function freeling_links($page = null){

    /*
     * current-php-code 2020-Sep-30
     * internal-call
     * input-sanitized : lang
     */

    $lang = FLInput::get('lang','en');
    $opts = get_option('freeling_option_pages');


    $link = $opts[$page];
    $_url = get_permalink($link);
    $new_url = add_query_arg(  ['lang'=>$lang],$_url);

    return $new_url;

}

function stringTrim($string){
    /*
         * current-php-code 2020-Oct-06
         * internal-call
         * input-sanitized :
         */
    return stripslashes($string);

}