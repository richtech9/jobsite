<?php
/**
 * Used to spot check issues, not linked in regularly
 * @param null $url
 * @return bool
 */
function fl_check_ssl_bundle($url = null) {
    /*
    * current-php-code 2021-Jan-16
    * internal-call
    * input-sanitized :
    */
    $error_message = '';
    $da = new FreelinguistCurlHelper();
    $da->set_debug_mode();
    if (empty($url)) {
        $url = 'https://live.cardeasexml.com/ultradns.php'; //random url that works at the time of coding
    }
    $what = null;
    try {
        $what = $da->curl_helper($url,$http_code,null,false,'text');
    } catch (Exception $e) {
        $error_message = will_get_exception_string($e);
    }

    $log = $da->get_debug_log();
    will_send_to_error_log('Curl SSL Test', [
        'url' => $url,
        'code' => $http_code,
        'response' => $what,
        'error' => $error_message,
        'log' => $log
    ]);
    return $error_message ? false : true;
}