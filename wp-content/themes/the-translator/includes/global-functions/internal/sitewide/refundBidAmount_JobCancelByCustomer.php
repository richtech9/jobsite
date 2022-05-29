<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      refundBidAmountJobCancelByCustomer

 * Description: In any event that the buyer cancels the job, all the bid security deposits will be refunded to all bidding Sellers.

 *

 */

function refundBidAmount_JobCancelByCustomer($job_id,$except_user=false){

    /*
     * current-php-code 2020-Dec-28
     * internal-call
     * input-sanitized :
     */


    FLPostLookupDataHelpers::delete_user_lookup_bid($job_id,FLPostLookupDataHelpers::ALL_USERS);


    $variables = array('job_link' => get_post_meta($job_id,'modified_id',true) );

    $curr_user_detail = get_userdata(get_current_user_id());

    emailTemplateForUser($curr_user_detail->user_email,CANCEL_ORDER,$variables);

}