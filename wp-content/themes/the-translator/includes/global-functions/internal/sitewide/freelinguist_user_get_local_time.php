<?php
use Carbon\Carbon;
/**
 * @param int $user_id
 * @param bool $b_throw_exception
 * @return string  (date time string)
 */
function freelinguist_user_get_local_time($user_id,$b_default_utc = false, $b_throw_exception = true) {

    /*
       * current-php-code 2020-Nov-13
       * internal-call
       * input-sanitized:  user_id_for_time
       */
    if (empty(intval($user_id))) {return '';}
    $user_time_zone = get_user_meta($user_id, 'user_time_zone', true);
    if (!$user_time_zone) {
        if ($b_default_utc) {
            $user_time_zone = 'UTC';
        }
        if (!$user_time_zone) {
            if ($b_throw_exception) {
                throw new RuntimeException("User $user_id does not have a timezone set");
            } else {
                return '';
            }
        }

    }
    $carbon = Carbon::now($user_time_zone);
    $base = $carbon->toDayDateTimeString();
    $offset = $carbon->getOffsetString();
    $timezone_a = $carbon->getTimezone();
    $timezone_b = $carbon->rawFormat('T');
    $timezone_name = $timezone_b;
    if (strlen($timezone_b) > strlen($timezone_a)) {
        $timezone_name = $timezone_a;
    }
    $ret = $base . ' (' . $timezone_name   .' '.$offset.')';
    return $ret;

}