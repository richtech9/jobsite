<?php

/**
 * @param $job_id
 * @return int
 */
function check_bid_exist($job_id){
    global $wpdb;
    $job_id = (int)$job_id;
    /*
     * current-php-code 2020-Jan-15
     * internal-call
     * input-sanitized :
     */

    $result         = '';

    $current_user   = wp_get_current_user();

    $current_user_id= $current_user->ID;

    $count_reply_on_bid = $wpdb->get_var(
        "SELECT COUNT(*) FROM wp_comments WHERE comment_type='job_bid' and comment_post_ID =$job_id and user_id =$current_user_id");

    if($count_reply_on_bid > 0 ){

        $result =  (int)$count_reply_on_bid;

    }

    return (int)$result;

}