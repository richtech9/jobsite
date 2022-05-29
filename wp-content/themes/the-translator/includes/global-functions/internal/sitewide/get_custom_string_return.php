<?php

function get_custom_string_return($string){

    /*
     * current-php-code 2020-Sep-30
     * input-sanitized :
     * internal-call
     */

    $string = trim($string);

    $language = current_language();

    global $wpdb;

    $custom_string_translation_table = $wpdb->prefix.'custom_string_translation';
    $sql_query = /** @lang text */
        "SELECT * FROM $custom_string_translation_table WHERE english LIKE '".$string."' AND type = 0";
    $data = $wpdb->get_row( $sql_query,ARRAY_A);

    if($language == false){

        return $string;

    }else{

        if(isset($data[$language]) && !empty($data[$language])){

            return $data[$language];

        }else{

            return $string;

        }

    }

}












