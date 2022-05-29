<?php

/*
* current-php-code 2021-March-20
* input-sanitized :
* current-wp-template:  content discussion for freelancer so it does not clutter up the large template
*/

/**
 * @usage

 *
 *  set_query_var( 'content_id', $content_id );
 *  get_template_part('includes/user/contentdetail/contentdetail', 'public-discussion');
 *
 * @needs wp_linguist_content info :
 *  id,content_amount, user_id,content_sale_type, purchased_by, publish_type, number_copies_sold, max_to_be_sold
 */

if (isset($content_id)) {
    $content_id = (int)$content_id;
} else {
    $content_id = 0;
}

$content = FreelinguistContentHelper::get_content_extended_information($content_id);
$parent_id = null;
if (intval($content['parent_content_id'])) {
    $parent_id = intval($content['parent_content_id']);
    $content_id = $parent_id;
}

//will_send_to_error_log('called new discussion section',['customer'=>$content_id,'content'=>$content_id]);
?>

<h3>Comments </h3>
<div class="comment-box comment-box_new-css" data-parent_content_id="<?= $parent_id?>">

    <?php
    if (is_user_logged_in()) {
        ?>
        <i class="fa col-md-1 giant-text thumb-img">


            <img src="<?php echo hz_get_profile_thumb(get_current_user_id()); ?>"
                 class="wow fadeInUp">

        </i>
    <?php } else {
        echo '<i class="fa fa-user giant-text col-md-1" aria-hidden="true"></i>';
    }

    ?>
    <form id="content_public_discussion" class="col-md-11">
        <input type="text" name="comment" placeholder="Join the discussion" style="width: 100%">
        <span class="commentEmptyMessageMain" style="color:red"></span>

        <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">


        <button type="submit"  class="red-btn-no-hover red-background-white-text fr enhanced-text">
            <?= __('Comment') ?>
        </button>

    </form>

    <div class="comment-box comments-list">
        <?php


        echo hz_fl_content_discussion_list_public($content_id);


        ?>

    </div> <!-- /.comment-box -->
</div> <!-- /.comment-box -->



