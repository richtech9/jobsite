<?php

function get_custom_string($string,$language=false){

    /*
     * current-php-code 2020-Sep-30
     * internal-call
     * input-sanitized :
     */

    will_do_nothing($language);
    $string = addslashes(trim($string));

    $language = current_language();

    global $wpdb;

    $data = $wpdb->get_row( "SELECT * FROM wp_custom_string_translation WHERE english LIKE '".$string."' AND type = 0",ARRAY_A);

    if($language == false){

        echo stripslashes($string);

    }else{

        if(isset($data[$language]) && !empty($data[$language])){

            echo stripslashes($data[$language]);

        }else{

            echo stripslashes($string);

        }

    }

}