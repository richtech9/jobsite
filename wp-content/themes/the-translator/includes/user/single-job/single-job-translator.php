<?php
//echo get_permalink();

/*
* current-php-code 2020-Oct-6
* input-sanitized : job_id,lang,redirect_to
* current-wp-template:  freelancer project page
*/
$lang = FLInput::get('lang', 'en');
$job_fetch = FLInput::get('job_fetch');
$job_id = FLInput::get('job_id');
$redirect_to = FLInput::get('redirect_to');

global $post;


$translator_id = get_current_user_id();

if (in_array($translator_id, get_post_meta($post->ID, 'job_freeze_user')) &&
    !empty(get_post_meta($post->ID, 'job_freeze_user'))
) {
    $job_freeze = true;
} else {
    $job_freeze = false;
}
if ($lang) {

    $curr_url = get_site_url() . '/job/' . $post->post_title . '&lang=' . $lang;

} else {

    $curr_url = get_site_url() . '/job/' . $post->post_title;

}

?>


<script>

    jQuery(function () {

        jQuery('#uploadModel').on('hidden.bs.modal', function () {

            location.reload();

        });

    });

</script>





<?php

global $wpdb;

global $post;


$current_user = wp_get_current_user();

$current_user_id = $current_user->ID;


?>

<section class="middle-content">

    <?php

    global $post;


    $current_user_role = xt_user_role();

    $author_id = $post->post_author;

    $current_translater_bid = get_all_bids_of_particular_job($current_user_id);

    $bid_price = '';
    if (isset($current_translater_bid[0]->comment_ID)) {
        $comment_id = $current_translater_bid[0]->comment_ID;
        $key = 'bid_price';
        $bid_price_array = get_comment_meta($comment_id, $key);
        if ($bid_price_array[0]) {
            $bid_price = $bid_price_array[0];
        }
        //$bid_price= $bid_price[0];
    }


    if (have_posts()): while (have_posts()) : the_post();

        $post_id = get_the_ID();

        $jtype = get_post_meta($post_id, 'fl_job_type', true);
        $tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
        if ($jtype == 'contest') {
            $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
        } else if ($jtype == 'project') {
            $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
        }


        $delivery_date_only = get_job_delivery_date($post_id);

        $job_standard_delivery_date = $delivery_date_only->delivery_date_only;

        $is_expired = FreelinguistContestCancellation::is_after_deadline_date($post_id, $log, $award_ended_ts,$diff_in_seconds);

        $job_standard_delivery_date = get_post_meta($post_id, 'job_standard_delivery_date', true);

        $security_amount = get_option('bid_security_amount');


        $wp_interest_tags = $wpdb->prefix . "interest_tags";


        $tags = $wpdb->get_results("SELECT GROUP_CONCAT(tag_id) as tag_ids FROM wp_tags_cache_job WHERE `job_id` = $post_id AND type = $tagType");
        $tags_name_array = array();
        foreach ($tags as $k => $v) {
            $post_tags_array = explode(",", $v->tag_ids);
            foreach ($post_tags_array as $v1) {
                if (empty($v1)) {continue;}
                $interest_tags = $wpdb->get_results("SELECT * FROM wp_interest_tags WHERE `id` = $v1");
                foreach ($interest_tags as $k2 => $v2) {
                    $tags_name_array[] = $v2->tag_name;
                }
            }
        }
        $tags_name_array = stripslashes_deep($tags_name_array);

        ?>


        <div class="container" id="container-body">

            <div class="dashboard-sec">

                <div class="row">

                    <div class="job-details col-md-12">

                        <?php


                        if ($job_id) {

                            get_template_part('includes/user/single-job/ind', 'translator');

                        } else {

                            ?>

                            <div class="page-title">

                                <h3 class="default_txt_style f_b"><i
                                            class="icon icon-box"></i><?php get_custom_string('Job Details'); ?></h3>


                            </div>

                            <div class="dash-body">

                                <div class="box-row select-linguist-row default_txt_style row ">

                                    <div class="left-sec col-sm-12 col-md-6">

                                        <div class="order-no  large-text">
                                            <?php echo get_post_meta($post_id, 'modified_id', true); ?>
                                        </div>

                                        <ul class="linguist-order enhanced-text">

                                            <li class="default_txt_style">
                                                <?php get_custom_string("Project Title"); ?>:
                                                <strong class="default_txt_style">
                                                    <?php echo stripslashes(get_post_meta($post_id, 'project_title', true)); ?>
                                                </strong>
                                            </li>

                                            <li class="expt-date default_txt_style">
                                                <?php get_custom_string("Expected delivery date"); ?>
                                                : <strong class="default_txt_style">
                                                    <?php echo date_formatted($job_standard_delivery_date); ?>
                                                </strong>
                                            </li>




                                            <li class="price default_txt_style">
                                                <?php get_custom_string("Budget"); ?>:
                                                <strong class="default_txt_style">
                                                    <?php echo str_replace("_", "-", get_post_meta($post_id, 'estimated_budgets', true)) . ' USD'; ?>
                                                </strong>
                                            </li>


                                            <li class="default_txt_style">
                                                <?php get_custom_string("Project Description"); ?>
                                                : <strong  class="default_txt_style">
                                                    <?php echo stripslashes_deep(get_post_meta($post_id, 'project_description', true)); ?>
                                                </strong>
                                            </li>

                                            <li class="price default_txt_style">
                                                <?php get_custom_string("Skills"); ?>:
                                                <strong class="default_txt_style">
                                                    <?php
                                                    if (isset($tags_name_array)) {
                                                        echo implode(',', $tags_name_array);
                                                    }
                                                    ?>
                                                </strong>
                                            </li>

                                        </ul>

                                    </div>
                                    <div class="col-sm-12 col-md-6" style="text-align: right">
                                        <?php
                                            $author_user = get_userdata($post->post_author);
                                            $nice_name = $author_user->user_nicename;
                                            $author_avatar = hz_get_profile_thumb($post->post_author,FreelinguistSizeImages::TINY,true);
                                            $display_name = get_da_name($post->post_author);
                                            $profile_link = freeling_links('user_account') . '&profile_type=customer&user=' . $nice_name
                                        ?>
                                        <div>
                                            <a href="<?= $profile_link ?>"> <?=  $display_name?></a>
                                            <img style="height: 4em" src="<?= $author_avatar ?>">
                                        </div>
                                        <hr>
                                        <div class="right-btnss">


                                            <div class="job_dtls">
                                                <div class="modal fade" id="openCCboxReport" tabindex="-1" role="dialog"
                                                     aria-labelledby="myModalLabel" aria-hidden="true">

                                                    <div class="modal-dialog">

                                                        <div class="modal-content">

                                                            <div class="modal-header">

                                                                <button type="button" class="close huge-text"
                                                                        data-dismiss="modal">&times;
                                                                </button>

                                                                <h4 class="modal-title">
                                                                    <?php get_custom_string('Report project'); ?>
                                                                </h4>

                                                            </div>

                                                            <div class="modal-body">
                                                                <div class="freelinguist-report-caution">
                                                                    <span class="large-text">Submit a report only if it contains inappropriate content</span>
                                                                </div>
                                                                <form id="report_form" method="post">
                                                                    <div class="form-group">
                                                                        <input type="hidden" name="reported_by"
                                                                               value="<?php echo get_current_user_id(); ?>">
                                                                        <input type="hidden" name="content" value="">
                                                                        <input type="hidden" name="linguist"
                                                                               value="<?php echo get_current_user_id(); ?>">
                                                                        <input type="hidden" name="project"
                                                                               value="<?php echo $post_id; ?>">
                                                                        <input type="hidden" name="contest" value="">
                                                                        <input type="hidden" name="action"
                                                                               value="hz_submit_report">

                                                                    </div>
                                                                    <div class="form-group">
                                                                    <textarea class="form-control" id="report_note"
                                                                              placeholder="Note"  autocomplete="off"
                                                                              name="report_note"></textarea>
                                                                    </div>

                                                                    <button type="button" class="btn blue-btn"
                                                                            onClick="return submit_report();">Submit
                                                                    </button>
                                                                    <div class="freelinguist-after-submit-report">
                                                                        <span class="small-text"></span>
                                                                    </div>
                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                            <?php
                                            $current_user_id = get_current_user_id();

                                            $rowcur_jbid = $wpdb->get_results("SELECT * FROM wp_fl_job WHERE `linguist_id` = '" . $current_user_id . "' AND `project_id` = '" . $post_id . "' ");
                                            $jbStatus = '';
                                            if (!empty($rowcur_jbid)) {
                                                $jbStatus = $rowcur_jbid[0]->job_status;
                                            }


                                            if ($jbStatus == 'pending') {


                                                ?>
                                                <button class="box-prement2 grey"
                                                        onclick="return hz_start_job( 'reject_job', '<?= $post_id ?>', 'Are you sure, you want to cancel this job?');"
                                                        style="float: right;width: 150px;">Reject
                                                </button>


                                                <button class="box-prement2"
                                                        onclick="return wrap_wallet_hz_start_job( 'start', '<?= $post_id ?>', 'Are you sure, you want to start this job?');"
                                                        style="float: right; width: 150px;">Start
                                                </button>

                                                <?php
                                            } elseif ($job_fetch == 'success') {
                                                will_do_nothing('job_fetch');

                                            }


                                            ?>

                                        </div>
                                    </div>


                                    <!-- START: Job Instructions-->

                                    <div class="attached-doc" id="attached_translated_files">

                                        <ul class="doc-ul">


                                            <li>

                                                <div class="col-md-9">

                                                    <div class="doc-name">

                                                        <h5 class="default_txt_style f_b"><?php get_custom_string('Job Instructions'); ?>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;

                                                            <?php

                                                            $trans_text_exist = $wpdb->get_results("SELECT * FROM wp_files where post_id = $post_id AND type = ".FLWPFileHelper::TYPE_POST_DETAILS);

                                                            ?>

                                                        </h5>



                                                        <ul>

                                                            <?php for ($i = 0; $i < count($trans_text_exist); $i++) {
                                                                ?>

                                                                <li>

                                                                    <div class="col-md-9">

                                                                        <div class="doc-name">

                                                                            <!-- code-notes [download]  new download line -->
                                                                            <div class="freelinguist-download-line">

                                                                                <span class="freelinguist-download-name">
                                                                                    <i class="text-doc-icon larger-text"></i>
                                                                                    <span class="freelinguist-download-name-itself enhanced-text">
                                                                                        <?= $trans_text_exist[$i]->file_name ?>
                                                                                    </span>
                                                                                </span> <!-- /.freelinguist-download-name -->

                                                                                <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                                                                                   data-job_file_id = "<?= $trans_text_exist[$i]->id ?>"
                                                                                   download = "<?= $trans_text_exist[$i]->file_name ?>"
                                                                                   href="#">
                                                                                    Download
                                                                                </a> <!-- /.freelinguist-download-button -->

                                                                            </div><!-- /.freelinguist-download-line-->

                                                                        </div>

                                                                    </div>

                                                                    <div class="col-md-3 text-right">

                                                                        <?php if ($post->post_author == $current_user_id) { ?>

                                                                            <div class="cross">

                                                                                <a class="cross-icon"
                                                                                   onclick="return single_remove_selected(this,<?php echo $trans_text_exist[$i]->id; ?>)"
                                                                                   href="#"></a>

                                                                            </div>

                                                                        <?php } ?>

                                                                    </div>

                                                                </li>

                                                            <?php } ?>

                                                        </ul>

                                                    </div>

                                                </div>



                                            </li>


                                            <li>
                                                <?php echo stringTrim(get_post_meta($post_id, 'project_description', true)); ?>
                                            </li>


                                        </ul>

                                    </div>

                                    <!-- END: Job Instructions-->






                                    <!-- Start: Bidding Statement -->

                                    <div class="placed-bids">

                                        <div class="bid-header">

                                            <i aria-hidden="true" class="fa fa-user enhanced-text"></i>
                                            <label><?php get_custom_string('BIDDING STATEMENT'); ?></label>

                                        </div>

                                        <ul class="placed-bid-list">

                                            <?php

                                            if (isset($current_translater_bid[0]->comment_approved) && $current_translater_bid[0]->comment_approved == 1) {

                                                ?>

                                                <li style="float: none !important;margin-top: 1em">

                                                    <div class="bidder-details">

                                                        <div class="thum">

                                                            <?php
                                                            //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                                                            $avatar = hz_get_profile_thumb($current_translater_bid[0]->user_id,FreelinguistSizeImages::TINY,true);
                                                            ?>
                                                            <img style="" src="<?= $avatar ?>">


                                                        </div>

                                                        <div class="bidder-intro">

                                                            <div class="bidder-name">


                                                                <h6><?php echo get_display_name($current_translater_bid[0]->user_id); ?></h6>

                                                                <div class="reviews">
                                                                    <?php echo translater_rating($current_translater_bid[0]->user_id, 17, 'translator'); ?>
                                                                </div>

                                                            </div>

                                                            <div class="strength">

                                                                <?php get_custom_string('Date'); ?>:

                                                                <div class="strength-bar">

                                                                    <?php echo date_formatted(($current_translater_bid[0]->comment_date)); ?>

                                                                </div>


                                                            </div>


                                                        </div>

                                                    </div>

                                                    <div class="bid-description ">

                                                        <p class="enhanced-text" id="bid_statement">
                                                            <?php echo stripslashes(stringTrim($current_translater_bid[0]->comment_content)); ?>
                                                        </p>

                                                        <p class="enhanced-text" id="bid_price">
                                                            <?= '$' . $bid_price; ?>
                                                        </p>

                                                    </div>

                                                    <?php

                                                    // messages part start from here

                                                    $childcomments = get_comments(array(

                                                        'post_id' => $post_id,

                                                        'order' => 'DESC',

                                                        'parent' => $current_translater_bid[0]->comment_ID,

                                                        'comment_approved' => 0

                                                    ));

                                                    //code-unused no child comments used in current code
                                                    if (count($childcomments) > 0) {

                                                        ?>

                                                        <ul class="placed-bid-list bid-discussion">

                                                            <div class="bid-header bid-header-messages">

                                                                <i aria-hidden="true" class="fa fa-user"></i>
                                                                <label><?php get_custom_string('Messages'); ?></label>

                                                            </div>

                                                            <?php

                                                            foreach ($childcomments as $bidreply) {

                                                                ?>

                                                                <li>

                                                                    <div class="bidder-details">

                                                                        <div class="thum">

                                                                            <?php
                                                                            //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                                                                            $avatar = hz_get_profile_thumb($bidreply->user_id,FreelinguistSizeImages::TINY,true);
                                                                            ?>
                                                                            <img style="" src="<?= $avatar ?>">
                                                                        </div>

                                                                        <div class="bidder-intro">

                                                                            <div class="bidder-name">

                                                                                <h6><?php echo get_display_name($bidreply->user_id); ?></h6>

                                                                            </div>

                                                                            <div class="strength">

                                                                                <?php echo date_formatted($bidreply->comment_date); ?>

                                                                            </div>

                                                                        </div>

                                                                    </div>

                                                                    <div class="bid-description "><p
                                                                                class="enhanced-text"><?php echo stringTrim($bidreply->comment_content); ?></p>
                                                                    </div>

                                                                </li>

                                                                <?php

                                                            }

                                                            ?>

                                                            <li class="rejection_txt_container">


                                                            </li>

                                                        </ul>

                                                        <?php

                                                    } ?>

                                                </li>

                                                <?php

                                            }

                                            ?>

                                        </ul>

                                        <!--    code-notes move report button to end of bids div-->
                                        <a id="report_button" class="box-prement2 grey fr " href="#"
                                           data-toggle="modal" data-target="#openCCboxReport" style=""> Report
                                            project </a>
                                    </div>

                                    <!-- END: Bidding Statement -->


                                </div>


                                <div class="linguist-footer no-pad">

                                    <?php

                                    if ($current_user_role == 'translator') {



                                            if (true)
                                            { ?>

                                                <!-- Start: Model view(PLace Bid)-->

                                                <?php if (count($rowcur_jbid) > 0) {
                                                } else {
                                                    if ($author_id != $current_user_id) {
                                                        ?>

                                                        <button id="placebidbutton" data-target="#placeBidModel"
                                                                data-toggle="modal"
                                                                class="btn btn-green red-btn red-background-white-text regular-text"
                                                        >
                                                            <?php get_custom_string(!isset($current_translater_bid[0]->comment_content) ? 'Place Bid' : 'Update Bid'); ?>
                                                        </button>
                                                        <?php
                                                    }
                                                }
                                                ?>


                                                <div role="dialog" id="placeBidModel" class="modal fade">

                                                    <div class="modal-dialog">

                                                        <!-- Modal content-->

                                                        <div class="modal-content">

                                                            <div class="modal-header">

                                                                <button data-dismiss="modal" class="close huge-text"
                                                                        type="button">×
                                                                </button>

                                                                <h4 class="modal-title"><?php get_custom_string('Apply to this job'); ?></h4>

                                                            </div>

                                                            <div class="modal-body">

                                                                <div id="alert_message_model"></div>

                                                                <form class="comment-form" id="commentform"
                                                                      onsubmit="return place_the_bid(this)"
                                                                      method="post"
                                                                      action="<?php echo get_permalink(); ?>"
                                                                      novalidate="novalidate"><p
                                                                            class="comment-form-comment">
                                                                        <label for="bidPrice"><?php get_custom_string('Bid Price'); ?></label>

                                                                        <input title="Bid Price" type="number"
                                                                               class="form-control" name="bidPrice"
                                                                               min="1"
                                                                               value="<?php echo $bid_price; ?>">
                                                                        <input type="hidden"
                                                                               value="<?php if (isset($current_translater_bid[0]->comment_ID)) {
                                                                                   echo $current_translater_bid[0]->comment_ID;
                                                                               } ?>" name="comment_ID">

                                                                    </p>

                                                                    <p class="comment-form-comment">

                                                                        <label for="comment"><?php get_custom_string('Notes'); ?></label><br>

                                                                        <textarea
                                                                                maxlength="10000" class="form-control"
                                                                                style="height:200px"
                                                                                aria-required="true" name="comment"  autocomplete="off"
                                                                                id="comment"><?php
                                                                                                echo isset($current_translater_bid[0]->comment_content) ?
                                                                                                    stripslashes(stringTrim($current_translater_bid[0]->comment_content)) :
                                                                                                    '';
                                                                                ?></textarea>

                                                                    </p>

                                                                    <p class="form-submit">

                                                                        <input type="submit"
                                                                               value="<?php get_custom_string('Apply to this job'); ?>"
                                                                               class="btn blue-btn" id="submit"
                                                                               name="submit">

                                                                        <input type="hidden" id="comment_post_ID"
                                                                               value="<?php echo get_the_ID(); ?>"
                                                                               name="comment_post_ID">

                                                                        <input type="hidden" id="" value="<?= $lang; ?>"
                                                                               name="lang">

                                                                        <input type="hidden" value="0"
                                                                               id="comment_parent"
                                                                               name="comment_parent">

                                                                    </p>

                                                                </form>

                                                            </div>

                                                        </div>


                                                    </div>

                                                </div>

                                                <!-- END : Model view(PLace Bid)-->

                                                <?php

                                            }



                                    } ?>

                                </div>

                            </div>

                        <?php } ?>

                    </div>

                </div>

            </div>

        </div>

    <?php

    endwhile;

    else: ?>

        <div class='container'>

            <span class="bold-and-blocking larger-text"><?php _e('Sorry, nothing to display.', 'html5blank'); ?></span>

        </div>

    <?php

    endif;

    ?>


</section>






<!-- START:  Rating code start from here -->

<script>



    jQuery(function () {

        jQuery('#CompleteModel').on('hidden.bs.modal', function () {

            location.reload();

        });

    });


    // function redirectToPage(link){
    // 	window.location.href=link;
    // }
</script>
<?php if ($job_freeze): ?>
    <script>
        jQuery(function () {

            jQuery('.middle-content input,.middle-content textarea,.middle-content button').prop('disabled', true);
            jQuery('.middle-content .tm-tag-remove,.middle-content button').hide();
        });
    </script>

<?php endif; ?>

