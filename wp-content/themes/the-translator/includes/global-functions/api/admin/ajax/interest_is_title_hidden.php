<?php

// the interest id request: interest_id
// uses request: is_title_hidden
add_action( 'wp_ajax_interest_is_title_hidden', 'interest_is_title_hidden' );


function interest_is_title_hidden(){
    /*
     * current-php-code 2021-Jan-11
     * ajax-endpoint  interest_is_title_hidden
     * input-sanitized : interest_id,is_title_hidden
     */


    if (!current_user_can('manage_options')) {
        exit;
    }
    global $wpdb;
    $interest_id = (int)FLInput::get('interest_id');
    $is_title_hidden = (int)FLInput::get('is_title_hidden');

    $response = ['status'=> 0, 'message'=> 'nothing done','action'=>'interest_is_title_hidden'];
    try {
        if ($interest_id && FLInput::exists('is_title_hidden')) {

            if ($interest_id) {
                $res = $wpdb->query(/** @lang text */
                    "UPDATE wp_homepage_interest 
                                    SET is_title_hidden = $is_title_hidden WHERE id  = $interest_id");

                if ($wpdb->last_error) {
                    throw new RuntimeException("Error when updating the homepage_interest is_title_hidden of $interest_id / $is_title_hidden : " . $wpdb->last_error);
                }

                if ($res === false) {
                    throw new RuntimeException("Unknown error updating the homepage_interest is_title_hidden of $interest_id / $is_title_hidden");
                }
                $response = ['status'=> 1, 'message'=> 'updated is_title_hidden of '.$interest_id .'/'. $is_title_hidden,'action'=>'interest_is_title_hidden'];
            }
        }
    } catch (Exception $e) {
        $response = ['status'=> 0, 'message'=> $e->getMessage(),'action'=>'interest_is_title_hidden'];
    }


    echo wp_json_encode($response);
    exit;
}