<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      get_display_name

 * Description: get_display_name

 *

 */

function get_display_name($user_id){
    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */
    $user_info = get_userdata($user_id);

    return $user_info->display_name;

}