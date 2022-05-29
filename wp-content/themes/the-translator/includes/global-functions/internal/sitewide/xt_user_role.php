<?php


/**
 * Logic:
 * If the user has no roles, add subscriber and return that
 * If the user has either role of translator or customer, but not both, then return one of those
 * If the user has both, then return the first role to make it obvious there is something wrong without crashing the page
 * If the user has neither, then return the first role
 * @return string
 */
function xt_user_role(){

    /*
    * current-php-code 2020-Sep-30
    * internal-call
    * input-sanitized :
   */

    if(!is_user_logged_in()){ return ''; }

    $user = get_userdata(get_current_user_id());

    $roles_to_check = ['customer','translator'];

    if(!isset($user->roles[0])){

        $u = new WP_User( get_current_user_id() );

        $u->add_role( 'subscriber' );

        $role = 'subscriber';

    }else{
        if (count($user->roles) === 1) {
            $role = $user->roles[0];
        } else {
            $intersect = array_intersect($user->roles,$roles_to_check);
            if (count($intersect) > 1) {
                $role = $user->roles[0];
            }
            else if (in_array('customer',$user->roles)) {$role =  'customer';}
            else if (in_array('translator',$user->roles)) {$role =  'translator';}
            else {
                $role = $user->roles[0];
            }
        }

    }

    return $role;





}