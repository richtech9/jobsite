<?php
//code-notes protect buttons here if user is not customer
//code-notes when customer refreshes page and the content is complete, then show the rating dialog
/*
* current-php-code 2020-Oct-13
* input-sanitized : content_id,lang,mode,page
* current-wp-template:  customer view of content mall and single content
*/

$content_id_encoded = FLInput::get('content_id', 0);
$lang = FLInput::get('lang', 'en');
$mode = FLInput::get('mode');
$page = (int)FLInput::get('page', 0);

$content_id =  FreelinguistContentHelper::decode_id($content_id_encoded);
$content = [];
if ($content_id) {
    try {
        $content = FreelinguistContentHelper::get_content_extended_information($content_id, true);
    } catch (Exception $e) {
        //trigger 404 page and exit
        will_send_to_error_log('Trying to display missing or unowned content',will_get_exception_string($e));
        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 );
        exit();
    }
}

//code-notes clear red dots for this content
$red = new FLRedDot();
$red->event_user_id_role =  FreelinguistUserLookupDataHelpers::get_logged_in_role_id();
$red->content_id = $content_id;
FLRedDot::remove_red_dots(get_current_user_id(),$red);
;

if (current_user_can('administrator')) {

    wp_redirect(admin_url());

}

$upload_dir = wp_upload_dir();
get_header();


?>


    <link href="<?php echo get_template_directory_uri().'/css/lib/owl.carousel.css'; ?>" rel="stylesheet">


    <script>
        //code-notes add protection of content ajax and forms for content customer detail page
        if (adminAjax) {
            adminAjax.form_keys.hz_change_status_content
                = '<?= FreeLinguistFormKey::create_form_key('hz_change_status_content') ?>';
            adminAjax.form_keys.hz_complete_contest_proposal
                = '<?= FreeLinguistFormKey::create_form_key('hz_complete_contest_proposal') ?>';
            adminAjax.form_keys.hz_content_customer_feedback
                = '<?= FreeLinguistFormKey::create_form_key('hz_content_customer_feedback') ?>';

        }
    </script>
<?php

$b_show_protected_interface = true;
if ($mode === 'view' && $content_id_encoded) {


    ?>

    <link href="<?php echo get_template_directory_uri().'/css/current-code/content-customer.css?version=0.1.1'; ?>" rel="stylesheet">

    <?php

    global $wpdb;




    $tag_array = $wpdb->get_results(/** @lang text */
        "SELECT GROUP_CONCAT(tag_id) AS tag_ids FROM  wp_tags_cache_job WHERE job_id=$content_id AND type = " . FreelinguistTags::CONTENT_TAG_TYPE);
    $similar_record = [];
    $tag_ids = implode(',',$content['tag_ids']);

    if ($tag_ids) {

        $sql = "
        SELECT

          u.ID                                                         primary_id,
          u.user_nicename,
          ''                                                           user_id,
          u.display_name                                               title,
          meta_description.meta_value as  description,
          '0'                                                          price,
          meta_user_image.meta_value as  image,
        
          ''                                                           content_sale_type,
          'translator'                                                 job_type,
          '0'                                                          is_sold
        
        FROM wp_users u
          INNER JOIN (
            SELECT job_id FROM wp_tags_cache_job wtcj 
            WHERE wtcj.tag_id IN ($tag_ids) 
              AND wtcj.type = " . FreelinguistTags::USER_TAG_TYPE . "
            ORDER BY RAND()
            LIMIT 0, 8
        
                     ) as similar_users ON similar_users.job_id = u.ID
        
        
          LEFT JOIN wp_usermeta meta_description
            ON meta_description.user_id = u.ID AND meta_description.meta_key = 'description'
        
          LEFT JOIN wp_usermeta meta_user_image
            ON meta_user_image.user_id = u.ID AND meta_user_image.meta_key = 'user_image'
        
        UNION
        
        SELECT
          wlc.id                                                                            primary_id,
          ''                                                                                user_nicename,
          wlc.user_id,
          wlc.content_title                                                                 title,
          wlc.content_summary                                                               description,
          wlc.content_amount                                                                price,
          wlc.content_cover_image                                                           image,
          wlc.content_sale_type,
          'content'                                                                         job_type,
          '0' as is_sold
         FROM wp_linguist_content wlc
           INNER JOIN (
                        SELECT job_id FROM wp_tags_cache_job wtcj
                          INNER JOIN (
                                       SELECT id from wp_tags_cache_job c
                                       WHERE c.tag_id IN ($tag_ids) 
                                            AND c.type = " . FreelinguistTags::CONTENT_TAG_TYPE . "
                                       ORDER BY RAND()
                                       LIMIT 0, 8
                                     ) driver ON driver.id = wtcj.id
        
                          INNER JOIN wp_linguist_content content
                            ON content.id = wtcj.job_id AND content.publish_type = 'publish' AND content.user_id IS NOT NULL
        
                      ) as similar_content ON similar_content.job_id = wlc.id
                      
                      ORDER BY RAND()
                      LIMIT 0,8;";

        $similar_record = $wpdb->get_results($sql, ARRAY_A);

    }

    $content_detail = $wpdb->get_results(
            "SELECT * 
                     FROM wp_linguist_content_chapter 
                     WHERE user_id IS NOT NULL AND linguist_content_id = $content_id
                     ORDER BY page_number ASC
                     ", ARRAY_A);



    $user_Detail = get_userdata($content['user_id']);

    $wp_upload_dir = wp_upload_dir();

    $basepath = $wp_upload_dir['baseurl'];

    $current_user_id = get_current_user_id();

    $favContentIds = get_user_meta($current_user_id, '_favorite_content', true);

    $table = $wpdb->prefix . 'linguist_content';
    $id = $content_id;
    $view = $content['content_view'];

    $content_file_detail = $wpdb->get_results("select * from wp_content_files where content_id=$content_id", ARRAY_A);


    $result = $wpdb->update($table, array('content_view' => $view + 1), array('id' => $id), array('%d'), array('%d'));

    $row = $wpdb->get_row(
            "select *,
                  UNIX_TIMESTAMP(updated_at) as updated_at_ts,
                UNIX_TIMESTAMP(requested_completion_at) as  requested_completion_ts 
                  from wp_linguist_content 
                    where id = $content_id and publish_type='Purchased' and purchased_by=$current_user_id AND user_id IS NOT NULL",
            ARRAY_A);
    $user_id_of_purchaser = null;
    $rating_by_customer = null;
    if (!empty($content)) {
        $user_id_of_purchaser = intval($content['purchased_by']);
        $rating_by_customer = intval($content['rating_by_customer']);
    }

    //code-notes hide protected content only if there was a purchaser and the user is not the purchaser
    if ($user_id_of_purchaser) {
        if ($user_id_of_purchaser === intval($current_user_id)) {
            $b_show_protected_interface = true;
        } else {
            $b_show_protected_interface = false;
        }
    } else {
        $b_show_protected_interface = true;
    }

    ?>
    <div class="content-area fl-content-cover" data-cid= <?= $content_id ?>>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="left_content_img fl-relative-position">
                        <div class="freelingust-content-read-favorite-holder">
                            <?php
                            get_template_part('includes/user/contentdetail/contentdetail',
                                'customer-button-favorite');
                            //code-notes new favorite position
                            ?>
                        </div>
                        <?php
                        //will_dump('content',$content);
                        //code-notes [image-sizing]  content get large sized image for content cover
                        $cover_image_url = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($content['content_cover_image'],
                            FreelinguistSizeImages::LARGE,true);


                        ?>


                        <img src="<?=$cover_image_url ?>">
                    </div>
                </div> <!-- /.col -->

                <div class="col-lg-6">
                    <div class="right_content_text job_dtls">
                        <h4>
                            <?php echo stripslashes_deep($content['content_title']) ?>

                            <?php if ($content['content_sale_type'] == "Offer") {
                                if ($content['offersBy']) {

                                    $price_array = array();
                                    $allOffers = unserialize($content['offersBy']);

                                    foreach ($allOffers as $inOffers) {
                                        array_push($price_array, $inOffers['amount']);
                                    }

                                    if (count($price_array) > 0) {
                                        echo '<strong>$' . max($price_array) . '</strong>';
                                    } else {
                                        echo '<strong>$' . amount_format($content['content_amount']) . '</strong>';
                                    }


                                } else {
                                    echo '<strong>$' . amount_format($content['content_amount']) . '</strong>';
                                }
                            } else { ?>
                                <strong>$<?php echo amount_format($content['content_amount']) ?></strong>
                            <?php } ?>
                        </h4>
                        <hr>
                        <div class="inline-here">
                            Author:
                            <?php
                            if ($current_user_id == $content['user_id']) {
                                ?>
                                <a href="<?php echo freeling_links('my_account_url'); ?>"
                                   target="_blank"
                                >
                                    <?= get_da_name($user_Detail->ID) ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <a href="<?php echo freeling_links('user_account') . '&profile_type=translator&user=' . $user_Detail->user_nicename; ?>"
                                   target="_blank"
                                >
                                    <?= get_da_name($user_Detail->ID) ?>
                                </a>
                                <?php
                            }
                            ?>

                        </div>

                        <?php
                        if (is_user_logged_in()) {
                            $cusid = get_current_user_id();

                            if ($b_show_protected_interface && ($cusid != $content['user_id'])) {
                                //code-notes chat part
                                set_query_var('job_id',$content_id);
                                set_query_var('to_user_id',  $cusid);
                                set_query_var('job_type', 'content');
                                set_query_var( 'b_show_name', 0 );
                                get_template_part('includes/user/chat/chat', 'button-area');

                            }
                        }
                        ?>

                        <?php if (!empty($rating_by_customer)) {
                            ?>
                            <p class="star_icons">Rating:
                                <?php
                                for ($j = 1; $j <= $rating_by_customer; $j++) {
                                    ?>
                                    <i class="fa fa-star" aria-hidden="true"></i>

                                <?php }
                                ?>
                                <span><?php echo $rating_by_customer; ?> Star</span>
                            </p>
                            <?php
                        } ?>

                        <hr>
                        <p>
                            <?php echo stripslashes_deep(substr($content['content_summary'], 0, 500)); ?>
                        </p>


                       <!-- code-notes where the chat button used to be -->
                        <?php if (get_user_meta(get_current_user_id(), 'total_user_balance', true) >= 0) { ?>
                            <a href="<?php echo site_url() . '/content/?lang=en&mode=read&content_id=' . FreelinguistContentHelper::encode_id($content_id); ?>"
                               class="box-prement2 content-box-prement"
                            >
                                <i class="fa fa-user" aria-hidden="true"></i>
                                Read
                            </a>
                            <br>
                            <?php
                        } else {
                            echo 'Your balance is negative. Please pay for your spendings.<br>';
                        }


                        $isBuy = 0;
                        if (is_array($row)) {

                            if ($row['freezed'] != '1') {

                                if ($b_show_protected_interface && (get_user_meta(get_current_user_id(), 'total_user_balance', true) >= 0)) {

                                    echo '<div class="" style="padding: 0;">';//code-notes start of button div

                                    if ($row['status'] == 'request_rejection') {

                                        echo '<p>You requested for rejection.</p>';
                                    } else if ($row['status'] == 'request_revision') {
                                        echo '<p>You requested for revision.</p>';
                                    } else if ($row['status'] == 'request_completion') {
                                        $da_name = get_da_name($row["user_id"]); //code-notes completion request uses correct name now
                                        echo '<p>Approval of Completion Request by ' . $da_name .
                                            ', please approve the completion or request revision within the deadline.</p>';

                                    } else if ($row['purchased_by'] == get_current_user_id() &&
                                                $row['status'] != 'completed' &&
                                                $row['status'] != 'rejected' &&
                                                $row['status'] != 'cancelled')
                                    {
                                        echo 'Please either approve completion or  request revision before the deadline';
                                    }

                                    echo '<p id="demo_time"></p>';


                                    if ($row['status'] != 'cancelled' &&
                                        $row['status'] != 'completed' &&
                                        $row['status'] != 'rejected' &&
                                        $row['status'] != 'hire_mediator') {

                                        if ($row['status'] == 'request_revision') {


                                        } else {
                                            echo '<a id="" class="box-prement2 content-box-prement" href="#" '.
                                                'contentId="' . $content_id . '" data-toggle="modal" data-target="#requestrevisionModel"> Request Revision </a>';
                                            echo '<p></p>';
                                        }

                                        echo '<a id="" class="box-prement2 content-box-prement" href="#"'.
                                            ' data-toggle="modal" data-target="#approvecompletionModel"> Approve Completion </a>';


                                        if ($row['status'] == 'request_rejection') {

                                            echo '<a id="" class="box-prement2 content-box-prement" href="#" > Rejection Requested</a>';
                                        } else {

                                            echo '<a id="" class="box-prement2 bg-secondary content-box-prement" href="#" '.
                                                'contentId="' . $content_id . '" data-toggle="modal" data-target="#requestrejectionModel" '.
                                                'style="margin-left: 2px;"> Reject </a>';
                                        }
                                        if ($row['status'] == 'request_revision') {

                                            echo '<a id="" class="hirebttn2 fr content-box-prement" href="#"'.
                                                ' contentId="' . $content_id . '" >  Revision Requested</a>';
                                        }


                                        echo '<div class="modal fade" id="approvecompletionModel" tabindex="-1"'.
                                            ' role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

											<div class="modal-dialog">

												<div class="modal-content">

													<div class="modal-header">

													  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

													  <h4 class="modal-title">By approving completition, you acknowledge you have received the files and are satisfied with the services. </h4>
													  <h4 class="modal-title">Please note, you may not be able to raise a dispute against this job and the funds will be settled with the service provider.</h4>


													</div>

													<div class="modal-body">
													<!-- code-notes added dont-call-complete to the "Approve completion" button  -->
													<button  class="change_content_status btn btn-success dont-call-complete" contentId="' . $content_id .
                                                    '"    href="#" id="ccyes_proposal" status = "completed">Yes</button>
													</div>
												</div>
											</div>
										</div>
										
										<div class="modal fade" id="requestrevisionModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

											<div class="modal-dialog">

												<div class="modal-content">

													<div class="modal-header">

													  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

													  <h4 class="modal-title">Revision for content.</h4>

													</div>

													<div class="modal-body">
														<div class="form-group">
															<textarea  autocomplete="off" id="revision_text" class="form-control" rows="5" cols="60"></textarea>
														</div>
														<div class="form-group">
															<a class="hirebttn2 change_content_status" href="#" contentId="' . $content_id .
                                                                '"  status = "request_revision"> Request Revision </a>
														</div>
													</div>
												</div>
											</div>
										</div>
										
										<div class="modal fade" id="requestrejectionModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

											<div class="modal-dialog">

												<div class="modal-content">

													<div class="modal-header">

													  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

													  <h4 class="modal-title">Request for rejection.</h4>

													</div>

													<div class="modal-body">
														<div class="form-group">
															<textarea  autocomplete="off" id="rejection_txt" class="form-control" rows="5" cols="60"></textarea>
														</div>
														<div class="form-group">
															 <a  class="hirebttn2 change_content_status" href="#" contentId="' . $content_id .
                                                                '"  status = "request_rejection"> Request Rejection </a>
														</div>
													</div>
												</div>
											</div>
										</div>';


                                    } else if ($row['status'] == 'hire_mediator') {
                                        echo '<a id="" class="box-prement2" href="#" data-toggle="modal" data-target="#approvecompletionModel"> Approve Completion </a>
										
										<div class="modal fade" id="approvecompletionModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

											<div class="modal-dialog">

												<div class="modal-content">

													<div class="modal-header">

													  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

													  <h4 class="modal-title">By approving completition, you acknowledge you have received the files and are satisfied with the services.  </h4>
													  <h4 class="modal-title">Please note, you may not be able to raise a dispute against this job and the funds will be settled with the service provider. </h4>


													</div>

													<div class="modal-body">
													<button  class="change_content_status btn btn-success dont-call-complete" contentId="' . $content_id . '"    href="#" id="ccyes_proposal" status = "completed">Yes</button>
													</div>
												</div>
											</div>
										</div>';


                                    }

                                    else {
                                        echo '<a id="" class="box-prement2" href="#" > ' . ucfirst($row['status']) . ' </a>';
                                    }

                                    echo "</div><!-- code-notes end of div with buttons -->\n";
                                } else if ($b_show_protected_interface) {
                                    echo /** @lang text */
                                        '<a href="' . site_url() . '/wallet" class="box-prement2">Pay Now</a>';
                                }
                            }




                            if ($b_show_protected_interface && ($row['status'] == 'completed')) {

                                if ($row['rating_by_customer']) {


                                } else {
                                    ?>


                                    <a class="hirebttn2" href="#" data-toggle="modal"
                                       data-target="#feedbackModel">Feedback</a>


                                    <div class="modal fade" id="feedbackModel" tabindex="-1" role="dialog"
                                         aria-labelledby="myModalLabel" aria-hidden="true">

                                        <div class="modal-dialog">

                                            <div class="modal-content">

                                                <div class="modal-header">

                                                    <button type="button" class="close huge-text" data-dismiss="modal">
                                                        &times;
                                                    </button>

                                                    <h4 class="modal-title"><?php get_custom_string('Feedback'); ?></h4>

                                                </div>

                                                <div class="modal-body">

                                                    <h4>Please submit feedback only after the job has been completed.
                                                        You can not change it after it's submitted. </h4>

                                                    <form class="bidform" id="hz_content_customer_feedback"
                                                          method="post" action='<?php echo get_permalink(); ?>'
                                                          novalidate="novalidate">

                                                        <p class="price-form-status">

                                                            <label for="ms_details"><?php get_custom_string('Rating'); ?></label><br>
                                                            <input title="1" type="radio" name="rating_by_customer"
                                                                   class="" value="1" checked>&nbsp;1&nbsp;
                                                            <input title="2" type="radio" name="rating_by_customer"
                                                                   class="" value="2">&nbsp;2&nbsp;
                                                            <input title="3" type="radio" name="rating_by_customer"
                                                                   class="" value="3">&nbsp;3&nbsp;
                                                            <input title="4" type="radio" name="rating_by_customer"
                                                                   class="" value="4">&nbsp;4&nbsp;
                                                            <input title="5" type="radio" name="rating_by_customer"
                                                                   class="" value="5">&nbsp;5


                                                        </p>

                                                        <p class="price-form-status">

                                                            <label for="ms_details"><?php get_custom_string('Feedback'); ?></label><br>

                                                            <textarea title="feedback" maxlength="10000"
                                                                      class="form-control" aria-required="true"
                                                                      name="comments_by_customer"  autocomplete="off"
                                                                      id="comments_by_customer"></textarea>

                                                        </p>


                                                        <p class="form-submit">



                                                            <input type="hidden" name="content_id"
                                                                   value="<?php echo $content_id; ?>">


                                                            <input type="submit" class="btn blue-btn bidreplysubmit"
                                                                   value="<?php get_custom_string('Submit'); ?>">

                                                        </p>

                                                    </form>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <?php
                                }

                            }


                        }
                        //code-notes adding new buttons here
                        echo "<br>";
                        set_query_var( 'b_show_all_offers', 1 );
                        get_template_part('includes/user/contentdetail/contentdetail',
                            'customer-button-buy');


                        ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 freelinguist-id-area">
                    <div class="freelinguist-id-display">
                        <span class="small-text">
                            <?= $content_id?>
                        </span>
                    </div>
                    <?php
                    if (is_user_logged_in()) {
                        $cusid = get_current_user_id();

                        if ($cusid != $content['user_id']) {
                            ?>
                            <a id="report_button" class="box-prement2 grey content-box-prement"
                               href="#" data-toggle="modal" data-target="#openCCboxReport"
                            >
                                Report
                            </a>

                            <?php
                        }
                    }
                    ?>

                    <div class="modal fade" id="openCCboxReport" tabindex="-1" role="dialog"
                         aria-labelledby="myModalLabel" aria-hidden="true">

                        <div class="modal-dialog">

                            <div class="modal-content">

                                <div class="modal-header">

                                    <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

                                    <h4 class="modal-title"><?php get_custom_string('Report'); ?></h4>

                                </div>

                                <div class="modal-body">
                                    <div class="freelinguist-report-caution">
                                        <span class="large-text">Submit a report only if it contains inappropriate content</span>
                                    </div>
                                    <form id="report_form" method="post">
                                        <div class="form-group">
                                            <input type="hidden" name="reported_by"
                                                   value="<?php echo get_current_user_id(); ?>">
                                            <input type="hidden" name="content" value="<?php echo $content_id; ?>">
                                            <input type="hidden" name="linguist"
                                                   value="<?php echo $content['user_id']; ?>">
                                            <input type="hidden" name="project" value="">
                                            <input type="hidden" name="contest" value="">
                                            <input type="hidden" name="action" value="hz_submit_report">

                                        </div>
                                        <div class="form-group">
                                            <textarea class="form-control" id="report_note" placeholder="Note"  autocomplete="off"
                                                      name="report_note"></textarea>
                                        </div>

                                        <button type="button" class="btn blue-btn" onClick="return submit_report();">
                                            Submit
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
                <?php if (is_array($row)) {


                    if ($row['freezed'] != '1') {
                        if (get_user_meta(get_current_user_id(), 'total_user_balance', true) >= 0) {

                            if (!empty($content_file_detail)) { ?>

                                <table style="margin-top: 50px;" width="" id="" class="table">

                                <tr style="">

                                    <th style="padding: 8px 12px;" width="70%">Files Delivered</th>


                                </tr>

                                <?php foreach ($content_file_detail as $key => $value) { ?>

                                    <tr class="">

                                        <td class="large-text" style="padding: 8px 12px">

                                            <!-- code-notes [download]  new download line -->
                                            <div class="freelinguist-download-line">

                                                <span class="freelinguist-download-name">
                                                    <i class="text-doc-icon larger-text"></i>
                                                    <span class="freelinguist-download-name-itself enhanced-text">
                                                        <?= $value['public_file_name'] ?>
                                                    </span>
                                                </span> <!-- /.freelinguist-download-name -->

                                                <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                                                   data-content_file_id = "<?=$value['id'] ?>"
                                                   download = "<?= $value['public_file_name'] ?>"
                                                   href="#">
                                                    Download
                                                </a> <!-- /.freelinguist-download-button -->

                                            </div><!-- /.freelinguist-download-line-->

                                        </td>


                                    </tr>


                                <?php }
                            }
                        }
                    } ?>
                    </table>

                <?php } ?>
            </div>
        </div>
    </div>
    <?php if (is_array($row)) {
        if ($row['freezed'] != '1') {

            ?>
            <div class="container" style="padding-right: 0; padding-left: 0">
                <div class="review-listing job_dtls">
                    <div class="bidding-other comments_lists" style="margin-right: 0;margin-left: 0">


                        <div class="hz_discussion_row">
                            <h3 style="color: #5b676e">Delivery Discussions </h3>
                            <?php
                            echo hz_fl_content_discussion_list_both($content_id, get_current_user_id(), $content['user_id']); ?>

                        </div>

                        <div class="message-sec text-box">

                            <form id="content_discussion">

                                <textarea title="comment" required name="comment" autocomplete="off" ></textarea>

                                <input type="hidden" name="content_id" value="<?php echo $content_id;; ?>">

                                <input type="hidden" name="comment_to" value="<?php echo $content['user_id']; ?>">

                                <input type="submit" class="red-btn fr content-box-prement enhanced-text"
                                       style="float:right;" value="Contact Freelancer">

                            </form>

                        </div>

                    </div>
                </div>
            </div>
            <?php
        }
    }
    if ($b_show_protected_interface && $content_id_encoded) {

        $cid =  FreelinguistContentHelper::decode_id($content_id_encoded);


        $messages = $wpdb->get_results(
            "SELECT * FROM wp_message_history where content_id = $cid order by id asc");
        if ($messages) {
            $j = 1;
            ?>
            <div class="container">
                <div class="row">
                    <h3>Message History </h3>
                    <table class="table">
                        <?php


                        foreach ($messages as $k => $message) {
                            echo '<tr><td><b>#' . $j . '</b> ' . $message->created_at . ': ' . stripslashes_deep($message->message) . '</td></tr>';
                            $j++;

                        }

                        /*********/


                        ?>
                    </table>
                </div>
            </div>
            <?php
        }
    }
    if (is_array($row) && $row['rating_by_customer']) {
        ?>

        <div class="container">
            <div class="row">
                <h3>Customer Feedback  </h3>
                <div>
                    <?php


                    echo convert_rating($row['rating_by_customer'], 17, NULL, $row['purchased_by']) . ' ' . stripslashes_deep($row['comments_by_customer']);

                    ?>
                </div>
            </div>
        </div>
    <?php } ?>
    <section>
        <div class="main_content">
            <div class="container" style="padding-left: 0; padding-right: 0">
                <div class="row" style="margin-left: 0;margin-right: 0">
                    <div class="col-lg-12 col-md-12 col-sm-12" style="padding-left: 0; padding-right: 0">
                        <div class="left_site_bar">


                            <div class="page-area">
                                <div class="row">
                                    <div class="col-lg-4 col-md-4 col-sm-12">
                                        <a href="<?php echo site_url() . '/?lang=' . $lang; ?>"
                                           class="box-page">Home</a>
                                    </div>

                                </div>
                            </div>

                            <div class="comment-box comment-box_new-css" style="padding-left: 0; padding-right: 0">
                                <?php
                                set_query_var( 'content_id', $content_id );
                                get_template_part('includes/user/contentdetail/contentdetail', 'public-discussion');
                                ?>
                        </div> <!-- /.left_side_bar-->
                    </div> <!-- /.col-->
                </div> <!-- /.row-->
            </div> <!-- /.container-->
        </div> <!-- /.main_content-->
    </section>
    <section class="fl-content-related">
        <div class="section-padding">
            <div class="container">

                <div class="row">
                    <div class="col-lg-12 wow fadeInUp">
                        <div class="owl-carousel carousel-area">
                            <?php

                            foreach ($similar_record as $key => $value) { ?>

                                <?php
                                if ($value['job_type'] == 'content') {
                                    $userdetail = get_userdata($value['user_id']);
                                    $country = get_user_meta($value['user_id'], 'user_residence_country', true);
                                    $country = ($country ? get_countries()[$country] : 'N/A');

                                    if ($value['content_sale_type'] == 'Fixed') {
                                        $priceValue = '$' . $value['price'];
                                    } else if ($value['content_sale_type'] == 'Offer') {
                                        $priceValue = 'Best Offer';
                                    } else if ($value['content_sale_type'] == 'Free') {
                                        $priceValue = '';
                                    } else {
                                        $priceValue = '$' . $value['price'] . '/' . $value['content_sale_type'];
                                    }
                                    $q =
                                        "select content_view from wp_linguist_content where user_id IS NOT NULL AND id=" . $value['primary_id'];
                                    $content_view = $wpdb->get_row($q, ARRAY_A);
                                    ?>
                                    <?php
                                    //code-notes [image-sizing]  content get small sized image for content cover
                                    $bg_image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($value['image'],FreelinguistSizeImages::SMALL,true);


                                    $href = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($value['primary_id']);
                                    ?>
                                    <div class="single-carousel-item wow fadeInUp">
                                        <div class="user-info" style="width: 100%; display: inline-block;padding:0px;">
                                            <div class="slide-inn">
                                            <span style="position:absolute;"
                                                  class="fav add-favourited <?php echo(in_array($value['primary_id'], explode(',', $favContentIds)) ? 'favourited' : ''); ?>"
                                                  data-fav="<?php echo(in_array($value['primary_id'], explode(',', $favContentIds)) ? '1' : '0'); ?>"
                                                  data-id="<?php echo $value['primary_id']; ?>"
                                                  data-login="<?php echo(is_user_logged_in() ? '1' : '0'); ?>"></span>

                                                <a href="<?php echo $href; ?>">
                                                    <figure>
                                                        <img src="<?php echo $bg_image; ?>" alt="freelinguist"
                                                             style="width:100%;">
                                                    </figure>
                                                    <div class="description-user">
                                                    <span class="eye">
                                                        <img src="<?php echo get_template_directory_uri().'/images/eye-see.png'; ?>"
                                                             alt="freelinguist"/>
                                                    </span>
                                                        <ul>
                                                            <li class="li-1 enhanced-text">
                                                                <span><?= stripcslashes(substr($value['title'], 0, 25)); ?></span>
                                                            </li>
                                                            <li class="li-22 ">
                                                                <span class="one-line-no-overflow">
                                                                    <?php echo substr($value['description'], 0, 55); ?>
                                                                </span>
                                                            </li>

                                                            <li class="li-2 enhanced-text">
                                                                <span><?php echo $userdetail->display_name; ?></span>
                                                                <span
                                                                        class="pull-right"><?php if ($content_view['content_view'] != 0 && $content_view['content_view'] != '') {
                                                                        echo $content_view['content_view'] . ' Views';
                                                                    } ?></span></li>
                                                            <li class="li-2 enhanced-text">
                                                                <span><?php echo $country; ?></span> <span
                                                                        class="pull-right colored"><?php echo $priceValue; ?></span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </a>

                                            </div>

                                        </div>
                                        <span class=" fl-content-button-holder ">
                                                    <?php

                                                    set_query_var( 'b_show_all_offers', 0 );
                                                    set_query_var( 'content_id', $value["primary_id"] );
                                                    get_template_part('includes/user/contentdetail/contentdetail',
                                                        'customer-button-buy');
                                                    ?>
                                                </span>
                                        <!-- code-notes above was inserted content buy button-->
                                    </div>
                                <?php } else { //do user unit?>

                                    <?php $userdetail = get_userdata($value['primary_id']);
                                    $country = get_user_meta($value['primary_id'], 'user_residence_country', true);
                                    $country = ($country ? get_countries()[$country] : 'N/A');


                                    ?>
                                    <?php

                                    //code-notes [image-sizing]  get small sized image from url fragment for userprofile
                                    $bg_image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($value['image'],FreelinguistSizeImages::SMALL,true);
                                    $priceValue = (get_user_meta($value['primary_id'], 'user_hourly_rate', true)) ? '$' .
                                        get_user_meta($value['primary_id'], 'user_hourly_rate', true) . '/hours' : '';
                                    $href = site_url() . '/user-account/?lang=' . $lang . '&profile_type=translator&user=' . $value['user_nicename'];
                                    ?>
                                    <div class="single-carousel-item wow fadeInUp content-customer-user-info-holder">
                                        <div class="user-info" style="width: 100%; display: inline-block;padding:0px;">
                                            <div class="slide-inn">
                                            <span style="position:absolute;"
                                                  class="fav add-favourited
                                                  <?php echo(in_array($value['primary_id'], explode(',', $favContentIds)) ? 'favourited' : ''); ?>"
                                                  data-fav="<?php echo(in_array($value['primary_id'], explode(',', $favContentIds)) ? '1' : '0'); ?>"
                                                  data-id="<?php echo $value['primary_id']; ?>"
                                                  data-login="<?php echo(is_user_logged_in() ? '1' : '0'); ?>"></span>

                                                <a href="<?php echo $href; ?>">
                                                    <figure>
                                                        <img src="<?php echo $bg_image; ?>" alt="freelinguist"
                                                             style="width:100%;">
                                                    </figure>
                                                    <div class="description-user">
                                                    <span class="eye">
                                                        <img src="<?php echo get_template_directory_uri() .'/images/eye-see.png'; ?>"
                                                             alt="freelinguist"/>
                                                    </span>
                                                        <ul>
                                                            <li class="li-1 enhanced-text">
                                                                <span><?= stripcslashes(substr($value['title'], 0, 25)); ?></span>
                                                            </li>
                                                            <li class="li-22 ">
                                                                <span  class="one-line-no-overflow">
                                                                    <?php echo substr($value['description'], 0, 55); ?>
                                                                </span>
                                                            </li>

                                                            <li class="li-2 enhanced-text">
                                                                <span><?php echo $userdetail->display_name; ?></span>
                                                                <span
                                                                        class="pull-right" style="display: none">Placeholder for later
                                                                </span>
                                                            </li>
                                                            <li class="li-2 enhanced-text">
                                                                <span><?php echo $country; ?></span> <span
                                                                        class="pull-right colored"><?php echo $priceValue; ?></span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </a>
                                                <!-- code-notes hire button here -->
                                                <div class="hire-freelancer-button-holder">
                                                    <button class="red-btn-no-hover red-background-white-text hire-freelancer"
                                                            data-freelancer_nicename="<?= $userdetail->user_nicename ?>"
                                                            data-freelancer_id="<?= $userdetail->ID ?>"
                                                    >
                                                        <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                                                        Hire
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- /.single-carousel-item-->


                                <?php }
                            } ?>
                        </div> <!-- /.carousel-area-->
                    </div> <!-- /.col-->
                </div> <!-- /.row-->
            </div> <!-- /.container-->
        </div> <!-- /.section-padding-->
    </section>


    <?php
} elseif ($mode === 'read' && $content_id_encoded) {
    if (get_user_meta(get_current_user_id(), 'total_user_balance', true) >= 0) {
        get_template_part('includes/user/contentdetail/contentdetail', 'customer-content-read');
    } else {
        wp_redirect(site_url() . '/wallet');
    }
} else {
    $content_limit = (int)get_option('fl_page_limit_content_mall', 80);
    ?>

    <script>
        //code-notes adding js handler for scrolling
        jQuery(function () {
            freelinguist_load_content_mall_scroll_listener();
        });

    </script>

    <section
            class="dashboard-content content-mall-area"
            data-page="1"
    >

        <div class="container">
            <!--            code-notes call function to print out content units-->
            <?php
            $content_page = ($page) ? $page : 1;
            freelinguist_print_content_units($content_page, $content_limit);
            ?>
        </div>


    </section>
    <?php
}
?>
    <style>
        .li-2 {
            margin: 0 !important;
            padding: 0 !important;
            font-weight: 800 !important;
        }
    </style>

    <script src="<?php echo get_template_directory_uri(). '/js/lib/owl.carousel.min.js'?>"></script>
    <script src="<?php echo get_template_directory_uri() .'/js/current-scripts/owl-setup.js'; ?>"></script>

<?php
get_template_part('includes/user/contentdetail/contentdetail', 'customer-button-buy-dialogs');
get_template_part('includes/user/author-user-info/translator', 'hire-dialog');
get_footer('homepagenew');

?>
    <script>

        jQuery(function () {
            jQuery('body').on('click', '.add-favourited', function () {
                var elem = jQuery(this);
                var id = jQuery(this).attr('data-id');
                var login = parseInt(jQuery(this).attr('data-login'));
                var fav = parseInt(jQuery(this).attr('data-fav'));

                if (login === 0) {
                    return false;
                }
                if (id) {
                    if (fav === 1) {
                        elem.removeClass('favourited');
                        elem.attr('data-fav', 0);
                    } else {
                        elem.addClass('favourited');
                        elem.attr('data-fav', 1);
                    }
                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: adminAjax.url,
                        data: {action: 'user_add_favorite', id: id, c_type: 'content', fav: fav},
                        success: function (response) {
                            if (response.status === 1) {

                            } else if (response.status === -1) {
                            } else {
                                if (fav === 1) {
                                    elem.removeClass('favourited');
                                    elem.attr('data-fav', 1);
                                } else {
                                    elem.addClass('favourited');
                                    elem.attr('data-fav', 0);
                                }
                                alert(response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>

<?php
$requested_completion_ts = 0;
if (isset($content['requested_completion_ts'])) {
    $requested_completion_ts = intval($content['requested_completion_ts']);
}


//code-notes add in  request_revision as a condition to not do script below
if (
    isset($row)
    &&
    is_array($row)
    &&
    $requested_completion_ts
    &&
    $content['status'] !== 'request_revision'
    &&
    (
        $content['status'] == 'request_completion'
        || (
            $content['publish_type'] == 'Purchased' &&
            $content['purchased_by'] == get_current_user_id() &&
            $content['status'] != 'completed' &&
            $content['status'] != 'rejected' &&
            $content['status'] != 'cancelled'
        )
    )
):

    $new_date =  (($requested_completion_ts) +
            (60 * 60* floatval(get_option("auto_job_approvel_customer_hours"))))*1000;
    ?>

    <script>
        jQuery(function () {
            // Set the date we're counting down to
            var countDownDate = new Date(<?php echo $new_date;?>).getTime();
            console.log('js ts',countDownDate);
            // Update the count down every 1 second
            var x = setInterval(function () {

                // Get todays date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result in the element with id="demo"

                var demo = document.getElementById("demo_time");
                if (demo) {
                    demo.innerHTML = days + "d " + hours + "h "
                        + minutes + "m " + seconds + "s  left until auto approval of content";
                }

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    jQuery('#ccyes_proposal').click();
                    document.getElementById("demo").innerHTML = "EXPIRED";
                }


            }, 5000);

        });
    </script>

<?php
else:

    ?>

    <script>
        var demo = document.getElementById("demo_time");
        if (demo) {
            demo.innerHTML = "";
        }
    </script>
<?php
endif; //end if the timer countdown should run


$b_show_dialog_open = false;
if (
    isset($row) &&
    is_array($row) &&
    count($row) &&
    ($row['status'] === 'completed') &&
    (empty($row['rating_by_customer']))
) {
    $b_show_dialog_open = true;
}


if ($b_show_dialog_open) {
    ?>
    <script>
        $(function () {
            $("a.hirebttn2[data-target='#feedbackModel']").click();
        });
    </script>
<?php }
