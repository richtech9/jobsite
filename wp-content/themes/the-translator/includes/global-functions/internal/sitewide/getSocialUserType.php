<?php

/**
 * This is called by the Social Login Plugin, after checking to see if this function exists, and then it unlocks more functionality in that plugin
 * @return string
 */
function getSocialUserType(){
    /*
     * current-php-code 2021-Feb-10
     * internal-call
     * input-sanitized :
     */
    return $_SESSION['social_user_type'];

}