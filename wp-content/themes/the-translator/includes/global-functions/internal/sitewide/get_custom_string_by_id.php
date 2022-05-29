<?php

function get_custom_string_by_id($string,$id){

    /*
     * current-php-code 2020-Oct-10
     * internal-call
     * input-sanitized :
     */

    $string = addslashes(trim($string));

    $language = current_language();

    global $wpdb;

    $data = $wpdb->get_row( "SELECT * FROM wp_custom_string_translation WHERE id=$id",ARRAY_A);



    if(isset($data[$language]) && !empty($data[$language])){

        echo stripslashes($data[$language]);

    }else{

        echo stripslashes($string);

    }



}