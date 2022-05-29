<?php

function getReportedSubAdminOfSuperSubAdmin(){

    /*
    * current-php-code 2021-Jan-15
    * internal-call
    * input-sanitized :
   */

    $current_user       = wp_get_current_user();
    if(in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)){
        $args = array(
            'role'         => '',
            'role__in'     => array('cashier_sub_admin','evaluation_sub_admin','message_sub_admin','mediation_sub_admin'),
            'role__not_in' => array(),
            'meta_compare' => '',
            'meta_query'   => array(),
            'date_query'   => array(),
            'include'      => array(),
            'exclude'      => array(),
            'orderby'      => 'login',
            'order'        => 'ASC',
            'offset'       => '',
            'search'       => '',
            'number'       => '',
            'count_total'  => false,
            'fields'       => array('ID','user_email','user_login'),
            'who'          => ''
        );
        $sub_Admin_user = get_users( $args );
    }else{
        $args = array(
            'role'         => '',
            'role__in'     => array(),
            'role__not_in' => array(),
            'meta_key'     => 'reported_to',
            'meta_value'   => get_current_user_id(),
            'meta_compare' => '',
            'meta_query'   => array(),
            'date_query'   => array(),
            'include'      => array(),
            'exclude'      => array(),
            'orderby'      => 'login',
            'order'        => 'ASC',
            'offset'       => '',
            'search'       => '',
            'number'       => '',
            'count_total'  => false,
            'fields'       => array('ID','user_email','user_login'),
            'who'          => ''
        );
        $sub_Admin_user = get_users( $args );
    }
    return $sub_Admin_user;
}