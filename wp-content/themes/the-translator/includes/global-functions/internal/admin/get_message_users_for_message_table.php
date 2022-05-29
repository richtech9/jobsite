<?php

function get_message_users_for_message_table($current_page,$number_per_page){
    /*
        * current-php-code 2021-Jan-9
        * internal-call
        * input-sanitized :
       */

    $user_info = get_userdata(get_current_user_id());
    $user_list = array();
    if(in_array('administrator',$user_info->roles) || in_array('administrator_for_client',$user_info->roles)){
        // START: get all administrator_for_client users
        $args = array(
            'role__in'     => array(),
            'fields'       => array('ID','user_email','user_login'),
            'number' => $number_per_page, // How many per page
            'paged' => $current_page // What page to get, starting from 1.
        );

        $administrator_for_client_array = get_users( $args );

        foreach ($administrator_for_client_array as $key => $value) {
            $user_list[] = $value->ID;
        }

        // END: get all administrator_for_client users
    }elseif(in_array('super_sub_admin',$user_info->roles)){

        //$role__in   = array('administrator_for_client','cashier_sub_admin','evaluation_sub_admin');
        $user_list[] = get_current_user_id();
        // START: get all reported sub admin users
        $users = getReportedSubAdminOfSuperSubAdmin();
        foreach ($users as $key => $value) {
            $user_list[] = $value->ID;
        }
        // END: get all reported sub admin users

        // START: get all administrator_for_client users
        $args = array(
            'role__in'     => array('administrator_for_client'),
            'fields'       => array('ID','user_email','user_login'),
        );
        $administrator_for_client_array = get_users( $args );
        foreach ($administrator_for_client_array as $key => $value) {
            $user_list[] = $value->ID;
        }
        // END: get all administrator_for_client users
    }elseif(   in_array('cashier_sub_admin',$user_info->roles) ||
                in_array('meditation_sub_admin',$user_info->roles) ||
                in_array('evaluation_sub_admin',$user_info->roles) ||
                in_array('message_sub_admin',$user_info->roles)
    ){
        //$role__in = array('super_sub_admin');
        $user_list[] = get_current_user_id();
        if(!empty(getReportedSuperSubAdmin())){
            $user_list[] = getReportedSuperSubAdmin();
        }
    }else{
        // START: get all administrator_for_client users
        $args = array(
            'role__in'     => array('administrator_for_client'),
            'fields'       => array('ID','user_email','user_login'),
        );
        $administrator_for_client_array = get_users( $args );
        foreach ($administrator_for_client_array as $key => $value) {
            $user_list[] = $value->ID;
        }
        // END: get all administrator_for_client users
    }
    return $user_list;
}