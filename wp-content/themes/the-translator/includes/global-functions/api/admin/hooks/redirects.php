<?php


add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );
add_action('init','redirectUser');
add_filter("login_redirect", "loginRedirect", 10, 3);


function my_login_redirect( $redirect_to, $request, $user ) {
    /*
     * current-php-code 2020-Jan-11
     * current-hook
     * input-sanitized :
     */
    will_do_nothing([$request]);
    $action = FLInput::get('action');
    if ($action === 'switch_to_user') {return $redirect_to;}
    //is there a user to check?
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for admins
        if ( in_array( 'subscriber', $user->roles ) ||  in_array( 'customer', $user->roles ) || in_array( 'translator', $user->roles ) ) {
            // redirect them to the default place
            return home_url();
        }else{
            return admin_url();
        }
    }
    return $redirect_to;
}






function redirectUser(){
    /*
     * current-php-code 2020-Jan-11
     * current-hook
     * input-sanitized :
     */
    if(is_user_logged_in()){
        $user = get_userdata(get_current_user_id());
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            $url         = $_SERVER['REQUEST_URI'];
            $url_list = explode('/', $url);
            //check for admins
            if ( in_array( 'subscriber', $user->roles ) ||  in_array( 'customer', $user->roles ) || in_array( 'translator', $user->roles ) ) {
                // redirect them to the default place
                if (in_array("wp-admin", $url_list)  && !in_array('admin-ajax.php', $url_list)){
                    wp_redirect(home_url());
                }
            }
        }
    }
}


function loginRedirect( $redirect_to, $request, $user ){

    /*
     * current-php-code 2020-Jan-11
     * current-hook
     * input-sanitized :
     */
    will_do_nothing($request);
    if( is_array( $user->roles ) ) { // check if user has a role
        if ( in_array( 'cashier_sub_admin', $user->roles ) ||  in_array( 'administrator_for_client', $user->roles ) ||  in_array( 'message_sub_admin', $user->roles ) ||  in_array( 'evaluation_sub_admin', $user->roles )   ||  in_array( 'super_sub_admin', $user->roles )  || in_array( 'meditation_sub_admin', $user->roles ) ) {
            return get_site_url()."/wp-admin/profile.php?lang=en";
        }
        if ( in_array( 'administrator', $user->roles )) {
            return get_site_url()."/wp-admin?lang=en";
        }
    }

    return $redirect_to;
}
