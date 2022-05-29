<?php

add_action( 'wp_ajax_hz_change_role_cus_ling',  'change_role_cus_ling'  );

function change_role_cus_ling(){
    //code-bookmark this is where the user role is changed

    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  change_role_cus_ling
    * input-sanitized :curId,curRole,gotoUrl
    */
    global $wpdb;
    $cur_id = (int)FLInput::get('curId');
    $cur_role = FLInput::get('curRole');
    $go_to_url = FLInput::get('gotoUrl');

    //will_send_to_error_log('change_role_cus_ling post ',$_POST,true,true);

    $wuser_ID = $cur_id;

    if ($wuser_ID && ($cur_role === 'customer'))

    {

        $u = new WP_User( $wuser_ID );

        $u->remove_role($cur_role );

        $u->add_role( 'translator' );


//                will_send_to_error_log('change_role_cus_ling was customer sql ',$wpdb->last_query);
//                will_send_to_error_log('change_role_cus_ling was customer sql results ',$user,true,true);


        try {
            FreelinguistUserHelper::update_elastic_index($wuser_ID);
        } catch (Exception $e) {
            will_send_to_error_log('error sending user to elastic search', $e->getMessage());
        }


        echo $go_to_url;

    }

    else if($wuser_ID && ($cur_role === 'translator'))

    {

        $u = new WP_User( $wuser_ID );
        FreelinguistDebugFramework::note('change_role_cus_ling was translator now customer. post curRole is  ',$_POST['curRole']);
        $u->remove_role( $cur_role );

        $u->add_role( 'customer' );
        FreelinguistUserHelper::update_elastic_index($wuser_ID);

        echo $go_to_url;

    } else {
        will_send_to_error_log("current role was neither customer of translator");
    }

    //code-notes in large db with many users, the user_level is recreated each time the role is changed, delete it if its zero to speed up some admin pages
    $level = (int)get_user_meta($wuser_ID, $wpdb->get_blog_prefix() . 'user_level',true);
    if (!$level) {
        delete_user_meta( $wuser_ID, $wpdb->get_blog_prefix() . 'user_level' );
    }


    wp_die();

}