<?php

/**
 * Gets the user's display name,
 * if that is empty, or just spaces, will get the first and/or last name.
 * If neither name is set , returns the user's screen name
 * @param int $user_id
 * @return string
 *
 * @throws InvalidArgumentException if the user id is not recognized
 */
function get_da_name($user_id) {
    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */
    if (empty(intval($user_id))) {return '';}
    $user_data = get_userdata($user_id);
    if (!$user_data) {
        //log that not valid id
        $message = "The user id [$user_id] passed in not a valid user id";
        will_send_to_error_log($message);
        throw new InvalidArgumentException($message);
    }

    if (trim(get_userdata($user_id)->display_name)) {
        return get_userdata($user_id)->display_name;
    }
    $names = [];
    if (get_userdata($user_id)->first_name) { $names[] = get_userdata($user_id)->first_name; }
    if (get_userdata($user_id)->last_name) { $names[] = get_userdata($user_id)->last_name; }
    $da_name = implode(' ',$names);
    if (empty($da_name)) {$da_name = get_userdata($user_id)->user_nicename;}
    return $da_name;
}