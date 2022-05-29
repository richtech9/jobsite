<?php


add_action( 'wp_ajax_freelinguist_user_get_local_time',  'freelinguist_user_get_local_time_ajax'  );

function freelinguist_user_get_local_time_ajax() {

    /*
       * current-php-code 2020-Nov-13
       * ajax-endpoint  freelinguist_user_get_local_time
       * input-sanitized:  user_id
       */

    $user_id = (int)FLInput::get('user_id',0);
    try {
        if (!$user_id) {
            throw new RuntimeException("Need user id for freelinguist_user_get_local_time");
        }
        $time_string = freelinguist_user_get_local_time($user_id,true,false);
        wp_send_json([
           'status' => 1,
           'message' => $time_string
        ]);
        exit;
    } catch (Exception $e) {
        wp_send_json([
            'status' => 0,
            'message' => $e->getMessage()
        ]);
        exit;
    }

}

