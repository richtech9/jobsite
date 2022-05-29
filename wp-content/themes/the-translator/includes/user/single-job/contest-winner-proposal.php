<?php
/*
* current-php-code 2020-Oct-12
* input-sanitized : action,lang,linguist,proposalId
* current-wp-template:  customer viewing winning proposal
*/
//code-notes when customer refreshes the proposal page, and proposal is in completed status, show the rating dialog if not done already
global $wpdb;
global $post;

$action = FLInput::get('action');
$lang = FLInput::get('lang', 'en');
$proposalId = (int)FLInput::get('proposalId');
$current_user_id = (int)FLInput::get('linguist');


$prefix = $wpdb->prefix;
$post_id = get_the_ID();

//code-notes clear red dots for this proposal
$red = new FLRedDot();
$red->event_user_id_role =  FreelinguistUserLookupDataHelpers::get_logged_in_role_id();
$red->proposal_id = $proposalId;
FLRedDot::remove_red_dots(get_current_user_id(),$red);

$entry_submission = false;

$addParticipants = get_post_meta($post_id, 'all_contest_paricipants', true);

$contest_completed_proposals = get_post_meta($post_id, 'contest_completed_proposals', true);

$contest_completed_proposals_array = explode(',', $contest_completed_proposals);

$curParti = explode(',', $addParticipants);

$proposal = $wpdb->get_results(
    "
                                    SELECT prop.* , posts.post_author,
                                    UNIX_TIMESTAMP(updated_at) as updated_at_ts
                                    FROM wp_proposals prop 
                                    LEFT JOIN wp_posts posts ON posts.ID = prop.post_id
                                    WHERE prop.post_id = $post_id AND prop.id =$proposalId
                                    ");


$SiteLang = $lang;

if ($action === "winner-proposal") {
    $entry_submission = true;
}

$job_freeze = false;
if (in_array($proposal[0]->by_user, get_post_meta($post_id, 'job_freeze_user')) && !empty(get_post_meta($post_id, 'job_freeze_user'))) {
    $job_freeze = true;
}

?>
    <script>
        //code-notes Add in form-keys for winner proposal for customer to complete contest and change contest status and discussion
        if (adminAjax) {
            adminAjax.form_keys.hz_complete_contest_proposal =
                '<?= FreeLinguistFormKey::create_form_key('hz_complete_contest_proposal') ?>';
            adminAjax.form_keys.hz_change_status_contest_proposal =
                '<?= FreeLinguistFormKey::create_form_key('hz_change_status_contest_proposal') ?>';

        }
    </script>
    <div class="title-top">
        <div class="container">
            <i class="icon icon-box"></i>
            <p class="large-text">
                <?php echo get_post_meta($post_id, 'project_title', true); ?>
            </p>
        </div>
    </div>
    <section class="middle-content fl-contest-winner-proposal">
        <div class="container" id="container-body">
            <div class="dashboard-sec job_dtls">
                <div class="row">
                    <div class="job-details col-md-12" style="">


                        <div class="submissions_btns ">
                            <?php
                            $participants = $wpdb->get_results("SELECT * FROM wp_proposals WHERE `post_id` = $post_id AND by_user =$current_user_id");
                            ?>
                            <a class="hirebttn2"
                               href="<?php echo get_site_url(); ?>/job/<?php echo get_the_title($post_id); ?>/?lang=<?php
                               echo $SiteLang; ?>&action=participants-proposals"
                            >
                                Proposals
                            </a>

                            <div class="labels large-text"><?php
                                if (count($participants) != 0) {
                                    echo count($participants) . ' submissions';
                                } else {
                                    echo 'no submission.';
                                }
                                ?></div>
                            <a class="hirebttn2 fr"
                               href="<?php echo get_site_url(); ?>/job/<?php echo get_the_title($post_id); ?>/?lang=<?php echo $SiteLang; ?>"
                            >
                                Job Details
                            </a>

                            <div class="freelinguist-id-display top-right-clear" >
                                <span class="small-text">
                                    <?= $proposalId?>
                                </span>
                            </div>
                        </div>

                        <div class="avatar_img  profile_users">
                            <?php
                            //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                            $avatar = hz_get_profile_thumb($current_user_id,FreelinguistSizeImages::TINY,true);
                            ?>
                            <img width="100" style="" src="<?= $avatar ?>">
                            <?php
                            $userData = get_user_by('id', $current_user_id);
                            echo '<div class="profile_title"><h3>' . substr($userData->display_name, 0, 10) . '</h3>' .
                                //todo look at constant time date
                                '<span class="large-text">'.
                                    freelinguist_user_get_local_time($proposal[0]->by_user,true,false).
                                    '</span></div>';
                            ?>
                        </div>

                        <div class="days-left profile-page-days larger-text">
                            <?php
                            if (true) {
                                $transDay = get_post_meta($post_id, 'job_standard_delivery_date', true);
                                $today = date('Y-m-d');
                                $time1 = strtotime($transDay);
                                $time2 = strtotime($today);
                                $seconds = $time1 - $time2;
                                $days = floor($seconds / 86400);
                                if ($time1 > $time2) {
                                    echo $days . ' days left until automatic approval.';
                                }
                            }
                            ?>
                        </div>

                        <div class="cs_translator_column">
                            <div class="row">
                                <div class="approve-compl_btn">
                                    <?php

                                    if (!$job_freeze) {
                                        if (!in_array($proposalId, $contest_completed_proposals_array) &&
                                            $proposal[0]->status != 'cancelled' &&
                                            $proposal[0]->status != 'completed' &&
                                            $proposal[0]->status != 'rejected') {

                                            if ($proposal[0]->status == 'request_rejection') {

                                                echo '<p>You requested for rejection.</p>';
                                            } else if ($proposal[0]->status == 'request_revision') {

                                                echo '<p>You requested for revision.</p>';
                                            } else if ($proposal[0]->status == 'request_completion') {
                                                $da_name = get_da_name($proposal[0]->by_user); //code-notes completion request uses correct name now
                                                echo '<p>Approval of Completion Request by ' . $da_name .
                                                    ', please approve the completion or request revision within the deadline. </p>';

                                            } else if ($proposal[0]->status == 'hire_mediator') {
                                                echo '<p>Mediation in progress</p>';

                                            }

                                            echo '<p id="demo_time"></p>';

                                            if ($proposal[0]->status == 'request_completion') {

                                                echo '<a cusid="' . $post->post_author . '" id="" 
                                                class="hirebttn2 fr" href="#" 
                                                contestId="' . $post_id . '" 
                                                proposalId ="' . $proposalId . '" 
                                                data-toggle="modal" 
                                                data-target="#requestrevisionModel"> Request Revision </a>';
                                                echo '<p></p>';
                                            }

                                            echo '<a   id="" 
                                                        class="hirebttn2 "
                                                        href="#" 
                                                        data-toggle="modal"
                                                         data-target="#approvecompletionModel"> Approve Completion </a>';

                                            if ($proposal[0]->status == 'request_rejection') {
                                                echo '<a id="" class="hirebttn2 fr" href="#" > Rejection Requested</a>';
                                            } else {
                                                if ($proposal[0]->status == 'hire_mediator') {
                                                } else {

                                                    echo '<a cusid="' . $post->post_author . '" id="" 
                                                    class="hirebttn2  bg-secondary" 
                                                    href="#" 
                                                    contestId="' . $post_id . '" 
                                                    proposalId ="' . $proposalId . '" 
                                                    data-toggle="modal" 
                                                    data-target="#requestrejectionModel"> Reject </a>';
                                                }
                                            }


                                            echo '
										
										<div class="modal fade" id="approvecompletionModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

											<div class="modal-dialog">

												<div class="modal-content">

													<div class="modal-header">

													  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

													  <h4 class="modal-title">By approving completition, you acknowledge you have received the files and are satisfied with the services. </h4>
													  <h4 class="modal-title">Please note, you may not be able to raise a dispute against this job and the funds will be settled with the service provider.</h4>
													</div>

													<div class="modal-body">
														<button cusid="' . $post->post_author . '" class="button_cc bttns change_proposal_status btn btn-success" contestId="' . $post_id . '" proposalId ="' . $proposalId . '"   href="#" id="ccyes_proposal" status = "completed">Yes</button>
													</div>
												</div>
											</div>
										</div>
										
										<div class="modal fade" id="requestrevisionModel" tabindex="-1" 
										    role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

											<div class="modal-dialog">

												<div class="modal-content">

													<div class="modal-header">

													  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

													  <h4 class="modal-title">Revision for contest.</h4>

													</div>

													<div class="modal-body">
														<div class="form-group">
															<textarea  autocomplete="off" id="revision_text" class="form-control" rows="5" cols="60"></textarea>
														</div>
														<div class="form-group">
															 <a id="nop-1" class="hirebttn2 fr change_proposal_status" 
															    href="#" 
															    contestId="' . $post_id . '" 
															    proposalId ="' . $proposalId . '" 
															    status = "request_revision" 
															    cusid="' . $post->post_author . '"> Request Revision </a>
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
															 <a id="nop-2" 
															    class="hirebttn2 fr change_proposal_status" 
															    href="#" 
															    contestId="' . $post_id . '" 
															    proposalId ="' . $proposalId . '" 
															    status = "request_rejection" 
															    cusid="' . $post->post_author . '"> Request Rejection </a>
														</div>
													</div>
												</div>
											</div>
										</div>
										';


                                        } else {
                                            echo '<a id="" class="hirebttn2 fr " href="#" > ' . ucfirst($proposal[0]->status) . ' </a>';
                                        }


                                        if ($proposal[0]->status == 'completed') {

                                            if ($proposal[0]->rating_by_customer) {

                                            } else {
                                                ?>


                                                <a class="hirebttn2" href="#" data-toggle="modal"
                                                   data-target="#feedbackModel">Feedback</a>
                                                <div class="modal fade" id="feedbackModel" tabindex="-1" role="dialog"
                                                     aria-labelledby="myModalLabel" aria-hidden="true">

                                                    <div class="modal-dialog">

                                                        <div class="modal-content">

                                                            <div class="modal-header">

                                                                <button type="button" class="close huge-text"
                                                                        data-dismiss="modal">&times;
                                                                </button>

                                                                <h4 class="modal-title"><?php get_custom_string('Feedback'); ?></h4>

                                                            </div>

                                                            <div class="modal-body">

                                                                <h4>Please submit feedback only after the job has been
                                                                    completed. You can not change it after it's
                                                                    submitted. </h4>
                                                                <form class="bidform" id="hz_proposal_customer_feedback"
                                                                      method="post"
                                                                      action='<?php echo get_permalink(); ?>'
                                                                      novalidate="novalidate">

                                                                    <p class="price-form-status">

                                                                        <label for="ms_details"><?php get_custom_string('Rating'); ?></label><br>
                                                                        <input title="Rate a '1'" type="radio"
                                                                               name="rating_by_customer" class=""
                                                                               value="1" checked>&nbsp;1&nbsp;
                                                                        <input title="Rate a '2'" type="radio"
                                                                               name="rating_by_customer" class=""
                                                                               value="2">&nbsp;2&nbsp;
                                                                        <input title="Rate a '3'" type="radio"
                                                                               name="rating_by_customer" class=""
                                                                               value="3">&nbsp;3&nbsp;
                                                                        <input title="Rate a '4'" type="radio"
                                                                               name="rating_by_customer" class=""
                                                                               value="4">&nbsp;4&nbsp;
                                                                        <input title="Rate a '5'" type="radio"
                                                                               name="rating_by_customer" class=""
                                                                               value="5">&nbsp;5


                                                                    </p>

                                                                    <p class="price-form-status">

                                                                        <label for="ms_details"><?php get_custom_string('Feedback'); ?></label><br>

                                                                        <textarea title="comments by customer"
                                                                                  maxlength="10000" class="form-control"
                                                                                  aria-required="true"
                                                                                  name="comments_by_customer"  autocomplete="off"
                                                                                  id="comments_by_customer"></textarea>

                                                                    </p>


                                                                    <p class="form-submit">


                                                                        <input type="hidden" name="proposal_id"
                                                                               value="<?php echo $proposal[0]->id; ?>">


                                                                        <input type="submit"
                                                                               class="btn blue-btn bidreplysubmit"
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
                                    ?>
                                </div>
                            </div>

                            <?php if ($proposal[0]->rating_by_customer) { ?>
                                <div class="instruct-file">

                                    <span class="bold-and-blocking large-text">Customer Feedback</span>
                                    <div class="attached-doc">

                                        <?php echo convert_rating($proposal[0]->rating_by_customer, 17, NULL, $proposal[0]->post_author) .
                                            ' ' . stripslashes($proposal[0]->comments_by_customer); ?>
                                    </div>
                                </div>

                                <?php
                            }

                            if ($proposalId) {


                                $messages = $wpdb->get_results(
                                    "SELECT * FROM wp_message_history mt  LEFT JOIN wp_proposals tp ON mt.proposal_id = tp.ID 
                                                where mt.proposal_id = $proposalId order by mt.id asc");
                                if ($messages) {
                                    $j = 1;
                                    ?>
                                    <div class="instruct-file">

                                        <span class="bold-and-blocking large-text">Message History </span>
                                        <div class="attached-doc">

                                            <?php


                                            foreach ($messages as $k => $message) {
                                                echo '<div class="doc-name"><b>#' . $message->number . '</b> ' .
                                                    $message->created_at . ': ' . $message->message . '</div>';
                                                $j++;

                                            }

                                            /*********/


                                            ?>

                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>


                            <div class="instruct-file">
                                <div class="h1-title"><span class="bold-and-blocking larger-text">Files Delivered</span>
                                </div>

                                <?php
                                $tfiles = $wpdb->get_results(
                                    "SELECT * FROM wp_files WHERE `post_id` = $post_id AND type = ".FLWPFileHelper::TYPE_FREELANCER_UPLOAD." AND
                                                  proposal_id = $proposalId AND by_user = " . $current_user_id);
                                echo '<div class="attached-doc">';
                                if ($tfiles) {
                                    echo '<ul>';
                                    foreach ($tfiles as $fl) {
                                        ?>
                                        <li>
                                            <div class="col-md-12">
                                                <div class="doc-name">

                                                    <!-- code-notes [download]  new download line -->
                                                    <div class="freelinguist-download-line">

                                                        <span class="freelinguist-download-name">
                                                            <i class="text-doc-icon larger-text"></i>
                                                            <span class="freelinguist-download-name-itself enhanced-text">
                                                                <?= $fl->file_name ?>
                                                            </span>
                                                        </span> <!-- /.freelinguist-download-name -->

                                                        <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                                                           data-job_file_id = "<?= $fl->id ?>"
                                                           download = "<?= $fl->file_name ?>"
                                                           href="#">
                                                            Download
                                                        </a> <!-- /.freelinguist-download-button -->

                                                    </div><!-- /.freelinguist-download-line-->


                                                </div>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    echo '</ul>';
                                }
                                echo '</div>';
                                ?>

                            </div>
                        </div>

                        <div class="instruct-file">
                            <span class="bold-and-blocking large-text">Delivery Instructions</span>
                            <?php
                            $auth_or = $post->post_author;
                            //code-notes [contest customer private instructions]  show private files only between this user and the proposal owner
                            $tfiles = $wpdb->get_results("SELECT * FROM wp_files WHERE `post_id` = $post_id AND by_user = $auth_or AND proposal_id = $proposalId");
                            ?>
                            <div class="attached-doc">
                                <?php if ($tfiles) { ?>

                                    <ul>
                                        <?php
                                        foreach ($tfiles as $fl) {
                                            ?>
                                            <li>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <div class="doc-name">

                                                            <!-- code-notes [download]  new download line -->
                                                            <div class="freelinguist-download-line">

                                                                <span class="freelinguist-download-name">
                                                                    <span class="freelinguist-download-name-itself enhanced-text">
                                                                        <?= $fl->file_name ?>
                                                                    </span>
                                                                </span> <!-- /.freelinguist-download-name -->

                                                                <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                                                                   data-job_file_id = "<?= $fl->id ?>"
                                                                   download = "<?= $fl->file_name ?>"
                                                                   href="#">
                                                                    Download
                                                                </a> <!-- /.freelinguist-download-button -->

                                                            </div><!-- /.freelinguist-download-line-->

                                                        </div> <!-- /.col -->
                                                    </div>
                                                    <?php if (!$job_freeze) { ?>
                                                        <div class="col-md-2 text-right">
                                                            <div class="cross">
                                                                <a class="cross-icon"
                                                                   onclick="return single_remove_selected(this,<?= $fl->id ?>)"
                                                                   href="#">
                                                                </a>
                                                            </div> <!-- /.cross -->
                                                        </div>  <!-- /.col -->
                                                    <?php } ?>
                                                </div> <!-- /.row -->
                                            </li>
                                        <?php } //end foreach tfiles  ?>
                                    </ul>
                                <?php } //if tfiles  ?>
                            </div> <!-- /.attached-doc -->

                            <?php if (!$job_freeze) {
                                //code-notes [contest customer private instructions]  switch to private upload only by adding proposal id
                                ?>
                                <div class="upload-file regular-text">
                                    <i class="fa fa-upload enhanced-text"></i>
                                    Upload Files
                                    <input multiple="" name="files[]" id="hz_contest_data" class="files-data"
                                           data-id="<?php echo $post_id; ?>"
                                           data-proposal_id="<?php echo $proposalId; ?>"
                                           type="file">
                                </div>
                            <?php } ?>
                        </div> <!-- /.instruct-file -->

                        <div class="review-listing comments_lists">



                            <div class="bidding-other" style="width: 100%;">

                                <div class="hz_discussion_row">

                                    <?php
                                    echo hz_fl_discussion_list_both($post_id, $current_user_id, $post->post_author);
                                    ?>

                                </div>


                                <div class="text-box">

                                    <form id="contest_discussion">

                                        <textarea required name="comment" placeholder="Write here"  autocomplete="off" ></textarea>

                                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

                                        <input type="hidden" name="comment_to" value="<?php echo $current_user_id; ?>">

                                        <input type="submit" value="Contact Freelancer" class="enhanced-text">

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
if ($proposal[0]->status == 'request_completion'):

    $new_date = (intval($proposal[0]->updated_at_ts) +
            (60 * 60* floatval(get_option("auto_job_approvel_customer_hours"))))*1000;
    ?>

    <script>
        jQuery(function () {
            console.log("<?php echo $new_date;?>");

            // Set the date we're counting down to
            var countDownDate = new Date(<?php echo $new_date;?>).getTime();

            // Update the count down every 1 second
            var x = setInterval(function () {

                var now = new Date().getTime();
                // Get todays date and time
                //var now = new Date().getTime();

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
                        + minutes + "m " + seconds + "s  left until auto approval of completion";
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
endif;

$b_show_dialog_open = false;
if (isset($proposal) && is_array($proposal) && count($proposal) &&
    ($proposal[0]->status === 'completed') && (empty($proposal[0]->rating_by_customer))) {
    $b_show_dialog_open = true;
}


if ($b_show_dialog_open) {
    ?>
    <script>
        $(function () {
            $("a.hirebttn2[data-target='#feedbackModel']").click();
        });
    </script>
<?php } ?>