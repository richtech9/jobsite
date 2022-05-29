<?php

// the interest id request: interest_id
// uses request: is_per_id
add_action( 'wp_ajax_per_id_is_using', 'per_id_is_using' );

/**
 * @deprecated
 * code-notes  Anything that is set per id is now merged into the top templates
 * code-notes : the column of wp_homepage_interest->is_per_id is not used anywhere
 * code-notes not included
 */
function per_id_is_using(){
    /*
      * current-php-code 2021-Jan-11
      * ajax-endpoint  per_id_interest_create
      * input-sanitized : interest_id is_per_id
      */


    if (!current_user_can('manage_options')) {
        exit;
    }
    global $wpdb;
    $interest_id = (int)FLInput::get('interest_id');
    $is_per_id = (int)FLInput::get('is_per_id');
    $response = ['status'=> 0, 'message'=> 'nothing done','action'=>'per_id_is_using'];
    try {
        if ($interest_id && array_key_exists('is_per_id',$_REQUEST)) {

            if ($interest_id) {
                $res = $wpdb->query(/** @lang text */
                    "UPDATE wp_homepage_interest 
                                    SET is_per_id = $is_per_id WHERE id  = $interest_id");

                if ($wpdb->last_error) {
                    throw new RuntimeException("Error when updating the homepage_interest is_per_id of $interest_id / $is_per_id : " . $wpdb->last_error);
                }

                if ($res === false) {
                    throw new RuntimeException("Unknown error updating the homepage_interest is_per_id of $interest_id / $is_per_id");
                }
                $response = ['status'=> 1, 'message'=> 'updated is_per_id of '.$interest_id .'/'. $is_per_id,'action'=>'per_id_is_using'];
            }
        }
    } catch (Exception $e) {
        $response = ['status'=> 0, 'message'=> $e->getMessage(),'action'=>'per_id_is_using'];
    }


    echo wp_json_encode($response);
    exit;
}