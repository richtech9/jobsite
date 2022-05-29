<?php
add_action('wp_ajax_update_local_timezone', 'update_local_timezone');

add_action('wp_ajax_nopriv_update_local_timezone', 'update_local_timezone');

function update_local_timezone(){

    /*
    * current-php-code 2020-Sep-30
    * ajax-endpoint  update_local_timezone
    * input-sanitized : session_time_zone
     * public-api
    */
    //code-notes update_local_timezone.php called too many times each page load, need to fix
    $session_time_zone = FLInput::get('session_time_zone');
    if($session_time_zone){

        $_SESSION['session_time_zone'] = $session_time_zone;

        echo 'success';

        exit;

    }

}