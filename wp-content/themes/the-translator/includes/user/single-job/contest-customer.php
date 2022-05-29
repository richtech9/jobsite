<?php
    /*
     * current-php-code 2020-Sep-30
     * input-sanitized : lang,action,linguist,proposalId
     * current-wp-template:  contest main page for customer
     */
global $wpdb, $post;

$lang = FLInput::get('lang', 'en');
$action = FLInput::get('action');
$linguist = FLInput::get('linguist');
$proposalId = FLInput::get('proposalId');

$post_id = get_the_ID();
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;//task-future-work  make sure non logged in users in all pages are doing what they are supposed to
if (!$current_user_id) {return;}
$projectTitle = get_post_meta($post_id, 'project_title', true);
$SiteLang = $lang;

if (($action === "proposal") && $proposalId && $linguist) {
    get_template_part('includes/user/single-job/contest', 'participant-proposal');
} elseif ($action === "participants-proposals") {
    get_template_part('includes/user/single-job/contest', 'participants-proposals');
} elseif (($action === "winner-proposal") && $proposalId && $linguist) {
    get_template_part('includes/user/single-job/contest', 'winner-proposal');
} elseif ($action === "edit-contest") {
    get_template_part('includes/user/single-job/contest', 'edit-contest');
}  else { ?>

    <div class="title-top">
        <div class="container"><i class="icon icon-box"></i>
            <p class="default_txt_style f_b large-text">

                <?php
                echo get_post_meta($post_id, 'project_title', true);
                ?>

            </p>
        </div>
    </div>

    <section class="middle-content fl-contest-customer">

        <div class="container" id="container-body">

            <div class="dashboard-sec job_dtls default_txt_style">

                <div class="row">

                    <div class="job-details col-md-12" style="">

                        <?php

                        $auth_or = $post->post_author;
                        $participants = $wpdb->get_results(
                            "SELECT * FROM wp_files WHERE `post_id` = $post_id AND by_user != $auth_or");
                        $wp_interest_tags = $wpdb->prefix . "interest_tags";
                        $sql_statement =
                            "SELECT GROUP_CONCAT(tag_id) as tag_ids 
                            FROM wp_tags_cache_job 
                            WHERE `job_id` = $post_id AND type = ". FreelinguistTags::CONTEST_TAG_TYPE;
                        will_throw_on_wpdb_error($wpdb);
                        $post_tags = $wpdb->get_results($sql_statement);

                        $tags_name_array = array();
                        $totalsubmission = 0;
                        $total_participants = 0;
                        $all_participants = [];

                        $auth_or = $post->post_author;

                        $proposal_sql =
                            "SELECT * FROM wp_proposals wp WHERE wp.post_id = $post_id  AND wp.by_user != $auth_or";

                        $all_proposals = $wpdb->get_results( $proposal_sql   );

                        $totalsubmission += count($all_proposals);
                        foreach ($all_proposals as $proposal_detail) {
                            $user_id = intval($proposal_detail->by_user);
                            $this_proposal_id = intval($proposal_detail->id);
                            if (!isset($all_participants[$user_id])) {
                                $all_participants[$user_id] = [];
                            }

                            $all_participants[$user_id][] = $this_proposal_id;
                        }
                        $total_participants = count(array_keys($all_participants));

                        $winning_proposals = FreelinguistProjectAndContestHelper::get_winning_proposals_and_users($post_id);


                        if (!empty($winning_proposals)) { ?>

                            <span class="bold-and-blocking large-text">Winners</span>

                            <div class="comment-row fl-winning-proposals">

                                <?php

                                foreach ($winning_proposals as $winning_proposal_id => $user_dets) {
                                    if (!is_object($user_dets)) {continue;}
                                    ?>

                                    <figure class="column fl-winning-proposal">

                                        <a href="<?=  get_site_url(); ?>/job/<?=
                                                        get_the_title($post_id); ?>/?lang=<?=
                                                        $SiteLang; ?>&action=winner-proposal&linguist=<?=
                                                        $user_dets->id; ?>&proposalId=<?=
                                                        $winning_proposal_id; ?>"
                                        >

                                            <?php
                                            //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                                            $avatar = hz_get_profile_thumb($user_dets->id,FreelinguistSizeImages::TINY,true);
                                            ?>
                                            <img class="" style="" src="<?= $avatar ?>">
                                        </a>

                                        <p class="enhanced-text">
                                            <a href="<?= freeling_links('user_account') . '&profile_type=translator&user=' . $user_dets->user_login . '&b_url=' . $post_id?>">
                                                <?= substr(get_da_name($user_dets->id), 0, 10) ?>
                                            </a>
                                        </p>

                                    </figure>

                                <?php } // end foreach ?>
                            </div>

                        <?php } // end if ?>

                        <div class="submissions_btns ">

                            <a class="hirebttn2"
                               href="<?php    echo get_site_url(); ?>/job/<?php
                                                echo get_the_title($post_id); ?>/?lang=<?php
                                                echo $SiteLang; ?>&action=participants-proposals">
                                Proposals
                            </a>

                            <div class="fl-contest-participant-proposal-count large-text ">


                                <span>
                                    <?php
                                    if ($total_participants > 1) {
                                        echo $total_participants . ' Participants';
                                    } elseif ($total_participants === 1) {
                                        echo '1 Participant';
                                    }
                                    else {
                                        echo 'No Participants.';
                                    }
                                    ?>
                                </span>


                                <span>
                                    <?php
                                    if ($totalsubmission > 1) {
                                        echo $totalsubmission . ' Submissions';
                                    } elseif ($totalsubmission === 1) {
                                        echo '1 Submission';
                                    }
                                    else {
                                        echo 'No Submissions.';
                                    }
                                    ?>
                                </span>

                            </div> <!-- /.fl-contest-participant-proposal-count -->

                            <div class="top-btns">

                                <?php $cancel_message = '"' . get_custom_string_return("Do you want to hide this job?") . '"'; ?>

                                <?php $publish_message = '"' . get_custom_string_return("Do you want to Publish this job?") . '"'; ?>

                                <?php $delete_message = '"' . get_custom_string_return("Do you want to delete this job?") . '"'; ?>

                                <?php $yes = '"' . get_custom_string_return("Yes") . '"'; ?>

                                <?php $no = '"' . get_custom_string_return("No") . '"'; ?>

                                <?php if (get_post_meta($post_id, 'hide_job', true)): ?>

                                    <a class="hirebttn2 fr"
                                       onclick='return show_publish_job(<?php echo $post_id . "," . $publish_message . "," . $yes . "," . $no; ?>)'
                                       href="javascript:"><?php get_custom_string('Show Job'); ?>
                                    </a>
                                    <br>

                                <?php else: ?>
                                    <a class="hirebttn2 fr"
                                       onclick='return hide_publish_job(<?php echo $post_id . "," . $cancel_message . "," . $yes . "," . $no; ?>)'
                                       href="javascript:"><?php get_custom_string('Hide Job'); ?>
                                    </a>
                                    <br>

                                <?php endif; ?>

                                <a class="hirebttn2  fr"
                                   href="<?php echo get_site_url(); ?>/job/<?php echo get_the_title($post_id); ?>/?lang=<?php echo $SiteLang; ?>&action=edit-contest">
                                    Edit
                                </a>

                                <!--suppress HtmlUnknownTarget -->
                                <a class="hirebttn2  fr"
                                   href="<?php echo get_site_url(); ?>/post-contest/?lang=<?php echo $SiteLang; ?>&job_id=<?php echo $post_id; ?>">
                                    Duplicate
                                </a>

                            </div>

                        </div>

                        <div class="linguist-header">

                            <div class="col-33">

                                <span class="bold-and-blocking large-text"><?php echo get_the_title($post_id); ?></span>

                                <?php

                                $delviTime = get_post_meta($post_id, 'job_standard_delivery_date', true);

                                $formatDelvi = date("d/m/Y", strtotime($delviTime));

                                ?>

                                <p class="large-text">Deadline: <?php echo $formatDelvi; ?></p>


                                <p class="large-text">Client Insurance:<?php echo get_post_meta($post_id, 'is_guaranted', true) == 1 ? 'Yes' : 'No'; ?>
                                </p>

                            </div>

                            <div class="col-33">

                                <?php

                                $budget = get_post_meta($post_id, 'estimated_budgets', true);

                                $arrBudget = explode('_', $budget);

                                $from = $arrBudget[0];
                                $end = '';
                                if (count($arrBudget) > 1) {
                                    $end = $arrBudget[1];
                                }

                                if ($end != '') {
                                    $totalBudget = '$' . $from . ' to ' . '$' . $end;
                                } else {
                                    $totalBudget = '$' . $from;
                                }

                                ?>


                                <p class="large-text">Price: <?php echo $totalBudget; ?></p>

                            </div>

                        </div><!--#linguist-header-->

                        <div class="h1-title"><span class="bold-and-blocking larger-text">Instructions</span></div>

                        <div id="result_reposne" style="color: green;"></div>

                        <form id="editable_form_instruction">

                            <textarea maxlength="10000" id="job_instruction_editable"
                                      placeholder="Instructions goes here" autocomplete="off"
                                      rows="20"><?php echo get_post_meta($post_id, 'project_description', true); ?></textarea>

                            <!-- code-notes adding new button to save instrutions floated right-->
                            <button class="fl-save-contest-description">Save</button>

                            <input id="instruction_job_id" value="<?php echo $post_id; ?>" type="hidden">

                            <input id="instruction_author" value="<?php echo $current_user_id; ?>" type="hidden">

                        </form>

                        <div class="h1-title"><span class="bold-and-blocking larger-text">Skills</span></div>

                        <div class="formcont">
                            <form id="editable_form_tags">

                                <input type="text" name="project_tags" id="job_instruction_tags"
                                       class="tm-input  enhanced-text" value="" autocomplete="off"
                                       placeholder="<?php echo get_custom_string_return('Skills'); ?>">

                                <input id="job_id" name="hidden_job_id" value="<?php echo $post_id; ?>" type="hidden">

                                <input id="author" name="hidden_author_id" value="<?php echo $current_user_id; ?>"
                                       type="hidden">

                            </form>
                        </div> <!-- /.formcont -->

                        <?php


                        foreach ($post_tags as $k => $v) {

                            $post_tags_array = explode(",", $v->tag_ids);
//                            will_dump('k,v',['k'=>$k, 'v' =>$v,'post_tags'=>$post_tags_array]);
                            foreach ($post_tags_array as $v1) {
                                if (!empty($v1)) {
                                    $interest_tags = $wpdb->get_results(/** @lang text */
                                        "SELECT * FROM $wp_interest_tags WHERE `id` = $v1");
                                    will_throw_on_wpdb_error($wpdb);
                                    foreach ($interest_tags as $k2 => $v2) {
                                        $tags_name_array[] = $v2->tag_name;
                                    }
                                }
                            }
                        }

                        ?>

                        <div class="instruct-file">

                            <span class="bold-and-blocking large-text">Public Instruction Files</span>

                            <?php


                            $auth_or = $post->post_author;
                            //code-notes [contest customer private instructions]  show only public files for this contest here made by this user
                            $tfiles = $wpdb->get_results(
                                "SELECT * FROM wp_files WHERE `post_id` = $post_id AND by_user = $auth_or AND proposal_id IS NULL");
                            ?>

                            <div class="attached-doc">
                                <?php if ($tfiles) { ?>

                                    <ul>
                                        <?php foreach ($tfiles as $fl) {
                                            ?>

                                            <li>
                                                <div class="row">

                                                    <div class="col-md-10">
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

                                                    <div class="col-md-2 text-right">

                                                        <div class="cross">
                                                            <a class=" enhanced-text cross-icon"
                                                               onclick="return single_remove_selected(this, <?= $fl->id ?> )"
                                                               href="#"></a>
                                                        </div> <!-- /.cross -->
                                                    </div> <!-- /.text-right -->

                                                </div>
                                            </li>
                                        <?php } //end loop to fill in li ?>
                                    </ul>

                                <?php } // end if ?>

                            </div> <!-- /.attached-doc-->

                            <div class="upload-file regular-text">

                                <i class="fa fa-upload enhanced-text"></i>
                                Upload Files
                                <input multiple="" name="files[]"
                                       id="hz_contest_data"
                                       class="files-data"
                                       data-id="<?php echo $post_id; ?>"
                                       type="file">

                            </div>

                        </div>

                        <div class="fl-award-period-notice">
<!--                            code-notes Adding in the notice about freelancers claiming prizes-->
                            <?php
                            $log = [];
                            $b_is_past_award_time = false; $count_proposals = 0;$customer_balance = 0.0;$total_self_awarded_amount = 0.0;
                            try {
                                $b_relavent = FreelinguistContestCancellation::get_claim_info_for_contest($post_id, $log,
                                    $b_is_past_award_time, $count_proposals,
                                    $customer_balance, $total_self_awarded_amount,
                                    $count_self_awarded_people,$award_period_ends_at_ts);

                                if ($b_relavent) {
                                    //something to show
                                    $da_date = date("F d Y ",$award_period_ends_at_ts);
                                    ?>
                                    <p class="large-text bold-and-blocking">
                                        <?= $da_date ?>
                                        : the award deadline has passed. The prize money has been shared equally among all participants.
                                    </p>
                                    <?php
                                } else {
                                    print "<!-- Nothing to show -->";
                                }

                            } catch (RuntimeException $r) {
                                will_send_to_error_log("Issue with getting claim info", will_get_exception_string($r));
                            }

                            ?>
                        </div>
                        <div class="cancel-section">
                            <!--                        code-notes do logic to decide what kind of cancel button to show-->
                            <?php
                            $info_can_show_cancel = FreelinguistContestCancellation::can_cancel_button_be_shown($post_id, $reason_not_shown,$log);
                            $str_extra_class = '';
                            $cancel_button_text = '';
                            if ($info_can_show_cancel === false) {
                                $debug_cancel = 'not showing button || ' . $reason_not_shown;
                                $str_extra_class = 'hide-cancel-contest-button';
                            } elseif (is_string($info_can_show_cancel)) {
                                $debug_cancel = 'showing disabled button || ' . $str_extra_class . ' || ' . $reason_not_shown;
                                $str_extra_class = 'disable-cancel-contest-button';
                                $cancel_button_text = $info_can_show_cancel;
                            } elseif ($info_can_show_cancel === true) {
                                $cancel_button_text = 'Request Cancellation';
                                $debug_cancel = 'showing button to press' ;
                            }
                            will_do_nothing($debug_cancel);
                            ?>

                            <span data-contestid="<?= $post_id ?>"
                                  class="cancel-contest-button small-text fr <?= $str_extra_class ?>"><?= $cancel_button_text ?></span>
                            <!--                code-notes: show button if post meta has is_guaranted as truthful value and contest is ended-->

                        </div> <!-- /.cancel-section -->

                        <div class="comment-section comments_lists">

                            <div class="container">

                                <div class="title">Public Clarifications</div>

                                <div class="comments">

                                    <div class="hz_discussion_row">

                                        <?php // echo hz_fl_discussion_list( $post_id,$current_user_id,0 );

                                        echo hz_fl_discussion_list_public($post_id); ?>

                                    </div>

                                    <div class="text-box">

                                        <form id="contest_discussion">

                                            <textarea class="default_txt_style" required name="comment" autocomplete="off"
                                                      placeholder="Write here..."></textarea>

                                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

                                            <input type="hidden" name="comment_to" value="0">

                                            <input type="submit" value="Post Comment" class="enhanced-text">

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div> <!-- /.job-details -->

                </div> <!-- /.row -->

            </div> <!-- /.dashboard -->

        </div> <!-- /.container -->

    </section> <!-- /.middle-content -->

<?php } //end else from top php block ?>

<script type="text/javascript" language="javascript">
    var pausecontent = [];
    <?php
    if (empty($tags_name_array)) {
        $tags_name_array = [];
    } //end if
    foreach($tags_name_array as $key => $val){ ?>
    pausecontent.push('<?php echo $val; ?>');
    <?php } //end foreach  ?>
</script>
<!--suppress JSValidateTypes -->
<script type="text/javascript">
    jQuery(function ($) {

        var tagApi = $(".tm-input").tagsManager({
            prefilled: pausecontent
        });

        jQuery(".tm-input").on('tm:spliced', function(e, tag) {
            console.log(tag + " was removed!");
            update_tags();
        });

        jQuery(".tm-input").on('tm:pushed', function(e, tag) {
            console.log(tag + " was pushed!");
            update_tags();
        });


        $("#job_instruction_tags").typeahead({
            name: 'id',
            displayKey: 'name',
            source: function (query, process) {
                return $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'get_custom_tags',
                    query: query
                }, function (data) {
                    $('#resultLoading').fadeOut(300);
                    data = $.parseJSON(data);
                    return process(data);
                });
            },
            afterSelect: function (item) {

                console.log(item);
                tagApi.tagsManager("pushTag", item.name);
            }
        }).bind("typeahead:selected", function (obj, datum, name) {
            console.log(obj, datum, name);
        });


    });

    function update_tags() {
        let $ = jQuery;
        var data_post = $("#editable_form_tags").serializeArray();
        let job_id_hidden = $("#editable_form_tags").find('input[name="hidden_job_id"]');
        if (job_id_hidden.length === 0) {
            let job_id = $('input[name="hidden_job_id"]').val();
            data_post.push({'name': 'hidden_job_id', 'value': job_id});
        }

        let project_tags_input = $("#editable_form_tags").find('input[name="hidden-project_tags"]');
        if (project_tags_input.length === 0) {
            let project_tags_input = $('input[name="hidden-project_tags"]');
            if (project_tags_input.length ) {
                data_post.push({'name': 'hidden-project_tags', 'value': project_tags_input.val()});

            } else {
                project_tags_input = $('input[name="project_tags"]');
                data_post.push({'name': 'hidden-project_tags', 'value': project_tags_input.val()});
            }

        }
        data_post.push({'name': 'action', 'value': 'job_tags_editable'});

        /**
         * @var {DaAjaxObject} adminAjax
         */
        $.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: data_post,

            global: false,

            //code-notes js handler for job-tags
            success: function (response_raw) {
                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status === true) {
                    console.log('tagger says', response);
                } else {
                    will_handle_ajax_error('Contest Tags',response.message);
                }
            }

        });
    }


</script>

<script>
    jQuery(function($) {
        //code-notes show tooltip for tags
        let tag_box = $('#job_instruction_tags') ;
        freelinguist_tag_help(tag_box,true);
    });
</script>
