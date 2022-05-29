<?php

function get_all_bids_of_particular_job($user_id = false){

    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */

    if($user_id == false){

        $args = array(

            'order' => 'DESC',

            'post_id' => get_the_ID(),

            'fields' =>'bid_price',

            'parent' => 0,

            'status'=>'approve',

        );

    }else{

        $args = array(

            'order' => 'DESC',

            'post_id' => get_the_ID(),

            'fields' =>'bid_price',

            'parent' => 0,

            'user_id'=> $user_id,

        );

    }

    $comments_query = new WP_Comment_Query;

    return $comments_query->query( $args );

}