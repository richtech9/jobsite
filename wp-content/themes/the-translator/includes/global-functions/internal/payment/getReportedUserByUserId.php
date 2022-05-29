<?php

function getReportedUserByUserId($roles_arr = false){
    global $wpdb;
    /*
     * current-php-code 2021-Jan-7
     * internal-call
     * task-future-work The admin side getReportedUserByUserId function may need a do over. Not sure if it would work in a larger db , needs testing if to be used later
     * input-sanitized :
    */

    $all_countries = get_countries();
    $get_processing_id = get_processing_id();
    $user_arr = array();
    $user_id = get_current_user_id();

    // get country by start and end index
    $current_user       = wp_get_current_user();
    $current_user_role    = $current_user->roles[0];
    will_send_to_error_log("Testing BAGFD",$current_user_role);
    if ( in_array('super_sub_admin',$current_user->roles) ) {
        $country_from = get_user_meta( $user_id, 'assign_country_from', true );
        $country_to = get_user_meta( $user_id, 'assign_country_to', true );

        if($country_from >= 0 && $country_to  >= 0 ){

            $user_country_slice = array_slice($all_countries, $country_from, $country_to-$country_from+1,true);

            if($roles_arr == false){
                $args = array(
                    'fields'=> array('ID'),
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'user_residence_country',
                            'value'   => array_keys($user_country_slice),
                            'type' => 'numeric',
                            'compare' => 'IN'
                        )
                    )
                );

            }else{
                $args = array(
                    'role__in' => $roles_arr,
                    'fields'=> array('ID'),
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'user_residence_country',
                            'value'   => array_keys($user_country_slice),
                            'type' => 'numeric',
                            'compare' => 'IN'
                        )
                    )
                );

            }
            $user_query = new WP_User_Query( $args );
            $result = $user_query->get_results();
            foreach ($result as $key => $value) {
                $user_arr[] = $value->ID;
            }

        }else{

            $user_arr[] = '-1000';
        }

    }else{
        $results            = $wpdb->get_results( "SELECT * FROM wp_coordination where user_id=$user_id");
        foreach ($results as $key => $value) {
            $country_from         = $value->country_from;
            $country_to         = $value->country_to;
            $from_processing_id         = $value->from_processing_id;
            $to_processing_id         = $value->to_processing_id;
            $user_country_slice = array_slice($all_countries, $country_from, $country_to-$country_from+1,true);
            $user_processing_id_slice = array_slice($get_processing_id, $from_processing_id, $to_processing_id-$from_processing_id+1,true);
            if($roles_arr == false){
                $args = array(
                    'fields'=> array('ID'),
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'user_residence_country',
                            'value'   => array_keys($user_country_slice),
                            'type' => 'numeric',
                            'compare' => 'IN'
                        ),
                        array(
                            'key'     => 'user_processing_id',
                            'value'   => array_keys($user_processing_id_slice),
                            'type'    => 'numeric',
                            'compare' => 'IN'
                        )
                    )
                );
            }else{
                $args = array(
                    'role__in' => $roles_arr,
                    'fields'=> array('ID'),
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'user_residence_country',
                            'value'   => array_keys($user_country_slice),
                            'type' => 'numeric',
                            'compare' => 'IN'
                        ),
                        array(
                            'key'     => 'user_processing_id',
                            'value'   => array_keys($user_processing_id_slice),
                            'type'    => 'numeric',
                            'compare' => 'IN'
                        )
                    )
                );
            }
            $user_query = new WP_User_Query( $args );
            $result = $user_query->get_results();

            foreach ($result as $key_result => $value_result) {
                $user_arr[] = $value_result->ID;
            }
        }
    }
    if ( in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)) {
        $user_arr = !empty($user_arr) ? array_unique($user_arr) : array();
    }else{
        $user_arr = !empty($user_arr) ? array_unique($user_arr) : array('-1000');
    }

    return $user_arr;
}