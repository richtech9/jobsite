<?php

function hz_get_pro_name( $user_id ){

    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */

    $user = get_user_by( 'ID', $user_id );

    $name = $user->first_name.' '.$user->last_name;

    if( $name != " " ){

        return $name;

    }else{

        return substr($user->display_name, 0, 10);

    }

}