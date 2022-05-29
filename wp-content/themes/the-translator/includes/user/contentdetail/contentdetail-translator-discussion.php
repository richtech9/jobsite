<?php

/*
* current-php-code 2021-March-20
* input-sanitized :
* current-wp-template:  content discussion for freelancer so it does not clutter up the large template
*/

/**
 * @usage
 *  set_query_var( 'content_id', 1111 );
 *     //the content id
 *
 *  set_query_var( 'customer_id', 5555 );
 *    //the id of the customer that bought this
 *
 * get_template_part('includes/user/contentdetail/contentdetail', 'translator-discussion');
 *
 * @needs wp_linguist_content info :
 *  id,content_amount, user_id,content_sale_type, purchased_by, publish_type, number_copies_sold, max_to_be_sold
 */

if (isset($content_id)) {
    $content_id = (int)$content_id;
} else {
    $content_id = 0;
}

if (isset($customer_id)) {
    $customer_id = (int)$customer_id;
} else {
    $customer_id = 0;
}

//will_send_to_error_log('called new discussion section',['customer'=>$content_id,'content'=>$content_id]);
?>

<div class="fl-freelancer-content-discussion">
    <h4> <?= __('Delivery Discussions') ?> </h4>
    <div class="review-listing job_dtls">
        <div class="bidding-other comments_lists">

            <div class="hz_discussion_row">

                <?php
                echo hz_fl_content_discussion_list_both($content_id, get_current_user_id(), $customer_id);
                ?>

            </div>

            <div class="message-sec text-box">

                <div id="freelancer-content-discussion-input">

                    <textarea title="comment" required name="comment" autocomplete="off" ></textarea>

                    <input type="hidden" name="content_id" value="<?= $content_id; ?>">

                    <input type="hidden" name="comment_to" value="<?= $customer_id ?>">

                    <button
                            type="button"
                            class=" red-btn-no-hover red-background-white-text fr enhanced-text"
                            style="margin-top: 0.25em"
                    >
                    <?= __('Contact Customer') ?>
                </div>

            </div>

        </div>
    </div>
</div><!-- /.last div for this area -->