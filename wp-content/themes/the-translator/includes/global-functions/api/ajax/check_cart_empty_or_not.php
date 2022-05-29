<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      check_cart_empty_or_not

 * Description: check_cart_empty_or_not

 *

 */

add_action('wp_ajax_check_cart_empty_or_not', 'check_cart_empty_or_not');

add_action('wp_ajax_nopriv_check_cart_empty_or_not', 'check_cart_empty_or_not');

function check_cart_empty_or_not(){

    /*
    * current-php-code 2020-Nov-3
    * ajax-endpoint  check_cart_empty_or_not
    * input-sanitized : project_title,project_description
    * public-api
    */

    print_R($_REQUEST);


    $project_title = FLInput::get('project_title');
    $project_description = FLInput::get('project_description');

    if (!$project_title) {
        $project_title = 'My Project';
    }



    $_SESSION['project_title'] = $project_title;
    $_SESSION['project_description'] = $project_description;

    echo 'true';

    exit();

}