<?php

/**
 * @param string $message
 * @param int $milestone_id
 * @param int $proposal_id
 * @param int $content_id
 * @param int $customer
 * @param int $freelancer
 * @return bool|false|int
 *
 * insert message history
 */
function fl_message_insert( $message="",$milestone_id=null,$proposal_id=null,$content_id=null,$customer=null,$freelancer=null ){

    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */



    global $wpdb;
    if (empty($milestone_id)) {$milestone_id = null;}
    if (empty($proposal_id)) {$proposal_id = null;}
    if (empty($content_id)) {$content_id = null;}
    if (empty($customer)) {$customer = null;}
    if (empty($freelancer)) {$freelancer = null;}


    $data   = array(

        'message'            => $message,

        'milestone_id'            => $milestone_id,

        'proposal_id'    => $proposal_id,

        'content_id'              => $content_id,

        'customer'           => $customer,

        'freelancer'  => $freelancer,

        'created_at' =>date('Y-m-d'),
        'added_by' =>get_current_user_id()

    );

    $inst = $wpdb->insert( 'wp_message_history', $data );


    if( $inst !== false ){

        return $inst;

    }else{

        return false;

    }

}