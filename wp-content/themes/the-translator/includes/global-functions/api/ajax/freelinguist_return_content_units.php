<?php

/**
 * @whoami:
 * @description:
 *  this file is used to show content units in the content mall
 *  it allows for pagination so as the user scrolls down, the js calls this for another page of content units
 *
 *  Unlike the top tag units on the homepage, this does not use use twig templates yet, although that will be nice to add later
 *
 */
add_action( 'wp_ajax_nopriv_get_content_units', 'freelinguist_return_content_units' );
add_action( 'wp_ajax_get_content_units', 'freelinguist_return_content_units' );

/**
 * INPUTS
 *  $_GET  int page (required)
 *  $_GET  int content_limit (optional, will default to wp option of fl_page_limit_content_mall)
 *
 * OUTPUTS html for the next few rows of content units
 * along with some status flags
 * in a json response
 *
 * it calls @see freelinguist_print_content_units, and has it print the html to a buffer,
 * then it reads and clears the buffer, putting the html into a string
 * @uses freelinguist_print_content_units
 */
function freelinguist_return_content_units() {

    /*
    * current-php-code 2020-Jan-5
    * ajax-endpoint  get_content_units
    * public-api
    * input-sanitized : page
    */

    $page = (int)FLInput::get('page');
    $log = [];
    $response = [
        'status' => false, 'message' => 'nothing done',
        'action' => 'get_content_units', 'log' => [],'html'=>'', 'units'=> 0
    ];

    try {

        if ($page < 1) {
            throw new RuntimeException("Page must be 1 or greater");
        }
        $content_limit = null;
        if (!empty($_REQUEST['limit'])) {
            $content_limit = intval($_REQUEST['limit']);
            if ($content_limit < 0) {
                throw new RuntimeException("Content Limit must be 0 or greater");
            }
        }
        $log[] = ['page' => $page];
        $log[] = ['content_limit' => $content_limit];

        ob_start();
        $response['units'] =freelinguist_print_content_units($page,$content_limit);
        $response['html'] = ob_get_clean();
        $response['status'] = true;
        $response['message'] = 'generated html units';

    } catch (Exception $e) {
        $response['status'] = false;
        $response['message'] = $e->getMessage();
        $response['log'] = $log;
    }


    echo wp_json_encode($response);
    wp_die();
}