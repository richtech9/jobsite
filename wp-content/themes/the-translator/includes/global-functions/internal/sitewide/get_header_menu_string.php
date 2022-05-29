<?php

function get_header_menu_string($string){

    /*
    * current-php-code 2020-Sep-30
     * internal-call
    * input-sanitized :
    */

    $string = trim($string);

    $language = current_language();

    global $wpdb;

    $custom_string_translation_table = $wpdb->prefix.'custom_string_translation';

    $sql_statement = /** @lang text */
        "SELECT * FROM $custom_string_translation_table WHERE english LIKE '".$string."' AND type = 1";
    $data = $wpdb->get_row( $sql_statement,ARRAY_A);

    if($language == false){

        echo $string;

    }else{

        if(isset($data[$language]) && !empty($data[$language])){

            echo $data[$language];

        }else{

            echo $string;

        }

    }

}