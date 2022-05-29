<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_my_account

 * Description: delete_my_account

 *

 */

add_action('wp_ajax_delete_my_account', 'delete_my_account');


function delete_my_account(){


    /*
       * current-php-code 2020-Oct-16
       * ajax-endpoint  delete_my_account
       * input-sanitized :
       */

    if(get_current_user_id()){

        global $wpdb;

        $user_detail = get_userdata(get_current_user_id());
        $user_id  = get_current_user_id();
        $wpdb->update( $wpdb->prefix.'users',  array('user_status' => 1), array( 'ID' => get_current_user_id() ));

        $variables = array();

        //code-notes not queuing the delete account email, by not adding a dummy bcc
        emailTemplateForUser($user_detail->user_email,CLOSE_ACCOUNT_TEMPLATE,$variables,[],false);

        //code-notes remove any units this user has, and any content this user has
        $deese_nodes = FreelinguistContentHelper::get_original_content_ids_by_user($user_id,true);
        $node = new _FreelinguistIdType();
        $node->type = 'user';
        $node->id = $user_id;
        $deese_nodes[] = $node;

        FreelinguistUnitGenerator::remove_compiled_units_from_es_cache($log,null,$deese_nodes);
        wp_logout();

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}