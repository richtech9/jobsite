<?php

add_filter('user_contactmethods', 'new_contact_methods', 10, 1);

/*
*   Description : Add column in user table admin
*/
function new_contact_methods($contactmethods)
{
    /*
    * current-php-code 2020-Jan-11
    * current-hook
    * input-sanitized :
    */

    $contactmethods[FreelinguistUserHelper::META_KEY_NAME_TAX_FORM] = 'Tax form';
    $contactmethods['display_name'] = 'Name';
    return $contactmethods;
}

