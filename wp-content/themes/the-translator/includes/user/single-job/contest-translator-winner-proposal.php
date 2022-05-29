<?php/** current-php-code 2020-Oct-12* input-sanitized : lang* current-wp-template:  translator viewing winning proposal*///code-notes when freelancer refreshes this page, and the proposal is complete, but no rating, then show rating dialogglobal $wpdb;global $post;$lang = FLInput::get('lang', 'en');$proposal_id = (int)FLInput::get('proposal_id');$post_id = get_the_ID();$current_user = wp_get_current_user();$SiteLang = $lang;$currLingu = $current_user->ID;$job_freeze = false;if (in_array($currLingu, get_post_meta($post_id, 'job_freeze_user')) && !empty(get_post_meta($post_id, 'job_freeze_user'))) {    $job_freeze = true;}//code-notes clear red dots for this proposal$red = new FLRedDot();$red->event_user_id_role =  FreelinguistUserLookupDataHelpers::get_logged_in_role_id();$red->proposal_id = $proposal_id;FLRedDot::remove_red_dots($currLingu,$red);$author_id = $post->post_author;$author = get_user_by('id', $author_id);$addParticipants = get_post_meta($post_id, 'all_contest_paricipants', true);$curParti = explode(',', $addParticipants);$getBalance = get_user_meta($currLingu, 'total_user_balance', true);$proposal = $wpdb->get_results(        "SELECT *, UNIX_TIMESTAMP(rejected_at) as rejected_at_ts FROM wp_proposals WHERE `post_id` = $post_id AND id =$proposal_id");?>    <script>        //code-notes Add in form-keys for winner proposal for freelancer        if (adminAjax) {            adminAjax.form_keys.hz_change_status_contest_proposal =                '<?= FreeLinguistFormKey::create_form_key('hz_change_status_contest_proposal') ?>';        }    </script>    <div class="title-top">        <div class="container">            <i class="icon icon-box"></i>            <p class="large-text">                <?php echo get_post_meta($post_id, 'project_title', true); ?>            </p>        </div>    </div>    <section class="middle-content">        <div class="container" id="container-body">            <div class="dashboard-sec sing-ling job_dtls">                <div class="row">                    <div class="job-details col-md-12 hello" style="">                        <div class="hire-linguist">                            <div class="hire-linguist-top" style="margin-top: 0.5em;">                                <div class="hire-linguist-top-left">                                    <?php                                    if (!$job_freeze) {                                        if ($proposal[0]->status != 'cancelled' && $proposal[0]->status != 'completed' && $proposal[0]->status != 'rejected' && $proposal[0]->status != 'hire_mediator') {                                            if ($proposal[0]->status == 'request_rejection') {                                            } else if ($proposal[0]->status == 'request_revision') {                                                echo '<p>Revision Request by  ' . get_the_author() . ': ' . $proposal[0]->revision_text . '</p>';                                            } else if ($proposal[0]->status == 'request_completion') {                                                echo '<p>You have requested for completion</p>';                                            } else if ($proposal[0]->status == 'hire_mediator') {                                                echo '<p>Mediation in progress"</p>';                                            }                                            echo '<a id="" class="hirebttn2  change_proposal_status" href="#" contestId="' . $post_id . '" proposalId ="' . $proposal_id . '" status = "request_completion"> Request Completion </a>';                                            echo '<a id="" class="hirebttn2  change_proposal_status bg-secondary" href="#" contestId="' . $post_id . '" proposalId ="' . $proposal_id . '" status = "cancelled" style="width: 207px;text-align: center;"> Cancel </a>';                                            if ($proposal[0]->status != 'cancelled' && $proposal[0]->status != 'completed' && $proposal[0]->status != 'rejected' && $proposal[0]->status != 'hire_mediator') {                                                if (($proposal[0]->status == 'request_rejection' || $proposal[0]->rejection_requested == '1') && $proposal[0]->status != 'hire_mediator') {                                                    echo '<p>Rejection requested by: ' . get_the_author() . ': ' . $proposal[0]->rejection_txt . '</p>';                                                    echo '<p>please select one from the following two options within the deadline.</p>';                                                }                                            }                                            if (($proposal[0]->status == 'request_rejection' || $proposal[0]->rejection_requested == '1') && $proposal[0]->status != 'hire_mediator') {                                                echo '<a class="hirebttn2  change_proposal_status" href="#" contestId="' .                                                    $post_id . '" proposalId ="' . $proposal_id . '" status = "rejected" id="accept_rejection"> Accept Rejection </a>';                                                echo '<a id="" class="hirebttn2  change_proposal_status" href="#" contestId="' .                                                    $post_id . '" proposalId ="' . $proposal_id . '" status = "hire_mediator">Hire Mediator </a>';                                            }                                        } else if ($proposal[0]->status == 'hire_mediator') {                                            echo '<p>Mediation in progress</p>';                                            echo '<a class="hirebttn2 fr change_proposal_status bg-secondary" href="#" contestId="' .                                                $post_id . '" proposalId ="' . $proposal_id . '" status = "rejected" id="accept_rejection"> Cancel Job </a>';                                        } else {                                            echo '<a id="" class="hirebttn2 fr " href="#" > ' . ucfirst($proposal[0]->status) . ' </a>';                                        }                                        echo '<p id="demo_time"></p>';                                        if ($proposal[0]->status == 'completed') {                                            if ($proposal[0]->rating_by_freelancer) {                                            } else {                                                ?>                                                <p>&nbsp;</p>                                                <a class="hirebttn2" href="#" data-toggle="modal"                                                   data-target="#feedbackModel">Feedback</a>                                                <div class="modal fade" id="feedbackModel" tabindex="-1" role="dialog"                                                     aria-labelledby="myModalLabel" aria-hidden="true">                                                    <div class="modal-dialog">                                                        <div class="modal-content">                                                            <div class="modal-header">                                                                <button type="button" class="close huge-text"                                                                        data-dismiss="modal">&times;                                                                </button>                                                                <h4 class="modal-title"><?php get_custom_string('Feedback'); ?></h4>                                                            </div>                                                            <div class="modal-body">                                                                <h4>Please submit feedback only after the job has been                                                                    completed. You can not change it after it's                                                                    submitted. </h4>                                                                <form class="bidform"                                                                      id="hz_proposal_freelancer_feedback" method="post"                                                                      action='<?php echo get_permalink(); ?>'                                                                      novalidate="novalidate">                                                                    <p class="price-form-status">                                                                        <label for="ms_details"><?php get_custom_string('Rating'); ?></label><br>                                                                        <input title="Rate a '1'" type="radio"                                                                               name="rating_by_freelancer" class=""                                                                               value="1" checked>&nbsp;1&nbsp;                                                                        <input title="Rate a '2'" type="radio"                                                                               name="rating_by_freelancer" class=""                                                                               value="2">&nbsp;2&nbsp;                                                                        <input title="Rate a '3'" type="radio"                                                                               name="rating_by_freelancer" class=""                                                                               value="3">&nbsp;3&nbsp;                                                                        <input title="Rate a '4'" type="radio"                                                                               name="rating_by_freelancer" class=""                                                                               value="4">&nbsp;4&nbsp;                                                                        <input title="Rate a '5'" type="radio"                                                                               name="rating_by_freelancer" class=""                                                                               value="5">&nbsp;5                                                                    </p> <!-- /.price-form-status -->                                                                    <p class="price-form-status">                                                                        <label for="ms_details"><?php get_custom_string('Feedback'); ?></label><br>                                                                        <textarea title="Comments by freelancer"                                                                                  maxlength="10000" class="form-control"                                                                                  aria-required="true"                                                                                  name="comments_by_freelancer"  autocomplete="off"                                                                                  id="comments_by_freelancer"></textarea>                                                                    </p> <!-- /.price-form-status -->                                                                    <p class="form-submit">                                                                        <input type="hidden" name="proposal_id"                                                                               value="<?php echo $proposal[0]->id; ?>">                                                                        <?php                                                                        $post_data = get_post($proposal[0]->post_id);                                                                        $post_author = get_userdata($post_data->post_author);                                                                        ?>                                                                        <input type="hidden" name="customer"                                                                               value="<?php echo $post_author->ID; ?>">                                                                        <input type="submit"                                                                               class="btn blue-btn bidreplysubmit"                                                                               value="<?php get_custom_string('Submit'); ?>">                                                                    </p> <!-- /.form-submit -->                                                                </form> <!-- /.bidform -->                                                            </div> <!-- /.modal-body -->                                                        </div> <!-- /.modal-content -->                                                    </div> <!-- /.modal-dialog -->                                                </div> <!-- /.modal -->                                                <?php                                            } //end else                                        } //end if proposal completed                                    } //end if not job freeze                                    ?>                                </div>                                <div class="fr">                                    <!-- code-notes added proposal button here -->                                    <a class="hirebttn2" href="<?= get_permalink() ?>&action=proposals">PROPOSALS</a>                                </div>                            </div>                        </div>                        <div class="linguist-header">                            <div class="col-33">                                <span class="bold-and-blocking large-text"><?php echo get_the_title($post_id); ?></span>                                <?php                                $delviTime = get_post_meta($post_id, 'job_standard_delivery_date', true);                                $formatDelvi = date("d/m/Y", strtotime($delviTime));                                ?>                                <p class="large-text"><strong>Deadline:</strong> <?php echo $formatDelvi; ?></p>                                <p class="large-text"><strong>Client Self                                        Insurance:</strong><?php echo get_post_meta($post_id, 'is_guaranted', true) == 1 ? 'Yes' : 'No'; ?>                                </p>                            </div>                            <div class="col-33">                                <?php                                $budget = get_post_meta($post_id, 'estimated_budgets', true);                                $arrBudget = explode('_', $budget);                                $from = $arrBudget[0];                                $end = '';                                if (count($arrBudget) > 1) {                                    $end = $arrBudget[1];                                }                                if ($end != '') {                                    $totalBudget = '$' . $from . ' to ' . '$' . $end;                                } else {                                    $totalBudget = '$' . $from;                                }                                ?>                                <p class="large-text">Price: <?php echo $totalBudget; ?></p>                            </div>                            <div class="col-33">                            </div>                        </div><!--#linguist-header-->                        <?php if ($proposal[0]->rating_by_freelancer && (intval($proposal[0]->by_user))) { ?>                            <div class="instruct-file">                                <span class="bold-and-blocking large-text">Freelancer Feedback</span>                                <div class="attached-doc">                                    <?php echo convert_rating($proposal[0]->rating_by_freelancer, 17, NULL,                                            $proposal[0]->by_user) . ' ' . stripslashes($proposal[0]->comments_by_freelancer); ?>                                </div>                            </div>                            <?php                        }                        if ($proposal_id) {                            $messages = $wpdb->get_results(                                    "SELECT * FROM wp_message_history mt                                            LEFT JOIN wp_proposals tp ON mt.proposal_id = tp.ID                                             where mt.proposal_id = $proposal_id order by mt.id asc"                            );                            if ($messages) {                                $j = 1;                                ?>                                <div class="instruct-file">                                    <span class="bold-and-blocking large-text">Message History </span>                                    <div class="attached-doc">                                        <?php                                        foreach ($messages as $k => $message) {                                            echo '<div class="doc-name"><b>#' . $message->number . '</b> ' .                                                $message->created_at . ': ' . $message->message . '</div>';                                            $j++;                                        }                                        /*********/                                        ?>                                    </div>                                </div>                                <?php                            }                        }                        ?>                        <div class="cs_customer_column">                            <div class="h1-title"><span class="bold-and-blocking larger-text">Instructions</span></div>                            <p>                                <?php echo  get_post_meta($post_id, 'project_description', true); ?>                            </p>                            <div class="h1-title"><span class="bold-and-blocking larger-text">Skills</span></div>                            <?php                            if (!empty($post_tags)) {                                foreach ($post_tags as $k => $v) {                                    $post_tags_array = explode(",", $v->tag_ids);                                    foreach ($post_tags_array as $v1) {                                        if (empty($v1)) {continue;}                                        $interest_tags = $wpdb->get_results("SELECT * FROM wp_interest_tags WHERE `id` = $v1");                                        foreach ($interest_tags as $k2 => $v2) {                                            $tags_name_array[] = $v2->tag_name;                                        }                                    }                                }                            }                            if (isset($tags_name_array) && sizeof($tags_name_array) > 0) {                                echo '<p>';                                foreach ($tags_name_array as $key => $value) {                                    if ($key == sizeof($tags_name_array) - 1) {                                        echo $value;                                    } else {                                        echo $value . ',';                                    }                                }                                echo '</p>';                            }                            ?>                            <p>                            </p>                            <div class="instruct-file">                                <span class="bold-and-blocking large-text">Delivery Instructions</span>                                <?php                                $auth_or = $post->post_author;                                //code-notes [contest customer private instructions]  show private files only between this user and the contest author                                $tfiles = $wpdb->get_results("SELECT * FROM wp_files WHERE `post_id` = $post_id AND by_user = $auth_or  AND proposal_id = $proposal_id AND type = ".FLWPFileHelper::TYPE_INSTRUCTION_FILE." ");                                echo '<div class="attached-doc">';                                if ($tfiles) {                                    echo '<ul>';                                    foreach ($tfiles as $fl) {                                        ?>                                        <li>                                            <div class="col-md-12">                                                <div class="doc-name">                                                    <!-- code-notes [download]  new download line -->                                                    <div class="freelinguist-download-line">                                                        <span class="freelinguist-download-name">                                                            <i class="text-doc-icon larger-text"></i>                                                            <span class="freelinguist-download-name-itself enhanced-text">                                                                <?= $fl->file_name ?>                                                            </span>                                                        </span> <!-- /.freelinguist-download-name -->                                                        <a class="red-btn-no-hover freelinguist-download-button enhanced-text"                                                           data-job_file_id = "<?= $fl->id ?>"                                                           download = "<?= $fl->file_name ?>"                                                           href="#">                                                            Download                                                        </a> <!-- /.freelinguist-download-button -->                                                    </div><!-- /.freelinguist-download-line-->                                                </div>                                            </div>                                        </li>                                        <?php                                    }                                    echo '</ul>';                                }                                echo '</div><!-- /.attached-doc -->';                                ?>                            </div> <!-- /.instruct-file -->                        </div> <!-- /.cs_customer-column -->                        <div class="instruct-file">                            <span class="bold-and-blocking large-text">Delivery Files</span>                            <?php                            $auth_or = $post->post_author;                            $tfiles = $wpdb->get_results("SELECT * FROM wp_files WHERE `post_id` = $post_id AND proposal_id = $proposal_id AND type = ".FLWPFileHelper::TYPE_FREELANCER_UPLOAD." ");                            echo '<div class="attached-doc">';                            if ($tfiles) {                                echo '<ul>';                                foreach ($tfiles as $fl) {                                    echo '																		';                                    ?>                                    <li>                                        <div class="col-md-12">                                            <div class="doc-name col-md-10">                                                <!-- code-notes [download]  new download line -->                                                <div class="freelinguist-download-line">                                                    <span class="freelinguist-download-name">                                                        <span class="freelinguist-download-name-itself enhanced-text">                                                            <?= $fl->file_name ?>                                                        </span>                                                    </span> <!-- /.freelinguist-download-name -->                                                    <a class="red-btn-no-hover freelinguist-download-button enhanced-text"                                                       data-job_file_id = "<?= $fl->id ?>"                                                       download = "<?= $fl->file_name ?>"                                                       href="#">                                                        Download                                                    </a> <!-- /.freelinguist-download-button -->                                                </div><!-- /.freelinguist-download-line-->                                            </div> <!-- /.doc-name.col-md-10-->                                            <div class="col-md-2 text-right">                                                <?php                                                if (!$job_freeze) {                                                    ?>                                                    <div class="cross">                                                        <a class="cross-icon enhanced-text"                                                           onclick="return single_remove_selected_contest_file_handler(this,<?= $fl->id ?>)"                                                           href="#">                                                        </a>                                                    </div>                                                    <?php                                                }                                                ?>                                            </div> <!-- /.col-md-2.text-right-->		    						</div> <!-- /.col-md-12-->									                                </li>                                <?php                                }//for each file                                echo '</ul>';                            }//end if files                            echo '</div>';                            if ($proposal[0]->status != 'cancelled' && $proposal[0]->status != 'completed' && !$job_freeze) {                                ?>                                <div class="attached-doc">                                    <div class="upload-file regular-text">                                        <i class="fa fa-upload enhanced-text"></i>                                        Upload Files                                        <input  multiple=""                                                 name="files[]"                                                 id="hz_contest_proposal_data"                                                 class="files-data hz_order_process"                                                 data-id="<?php echo $post_id; ?>"                                                 data-proposal_id="<?= $proposal_id; ?>"                                                 type="file"                                                 accept="*/*"                                        >                                    </div> <!-- /.upload-file -->                                    <div id="progress" class="progress" style="margin-top: 10px;">                                        <div class="progress-bar progress-bar-success"></div>                                    </div>                                    <div class="percent"></div>                                    <!-- The container for the uploaded files -->                                    <div id="files_name_container" class="files"></div>                                </div> <!-- /.attached-doc -->                            <?php } else {                                echo ucfirst($proposal[0]->status);                            } ?>                        </div> <!-- /.instruct-file -->                        <div class="review-listing comments_lists">                            <div class="bidding-other" style="width: 100%;">                                <div class="hz_discussion_row">                                    <?php                                    echo hz_fl_discussion_list_both($post_id, $currLingu, $post->post_author);                                    ?>                                </div> <!-- hz_discussion_row -->                                <div class="text-box">                                    <form id="contest_discussion">                                        <textarea required name="comment" placeholder="Write here" autocomplete="off" ></textarea>                                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">                                        <input type="hidden" name="comment_to"                                               value="<?php echo $post->post_author; ?>">                                        <input type="submit" value="Contact Customer" class="enhanced-text">                                    </form> <!-- /.contest_discussion-->                                </div> <!-- text-box -->                            </div> <!-- ./bidding-other -->                        </div> <!-- review-listing.comment-lists-->                    </div> <!-- /.job-details-->                </div> <!-- /.row-->            </div> <!-- /.dashboard-sec-->        </div> <!-- /.container-->    </section> <!-- /.middle-content--><?phpif ($proposal[0]->rejection_requested == '1' && $proposal[0]->status != 'cancelled' && $proposal[0]->status != 'completed' && $proposal[0]->status != 'rejected' && $proposal[0]->status != 'hire_mediator'):    $auto_job_rejected_for_linguist_hours_minutes = floatval(get_option('auto_job_rejected_for_linguist_hours')) * 60;    $new_date = (intval($proposal[0]->rejected_at_ts) + (60 * $auto_job_rejected_for_linguist_hours_minutes))*1000;    ?>    <script>        jQuery(function () {            // Set the date we're counting down to            var countDownDate = new Date(<?php echo $new_date;?>).getTime();            // Update the count down every 1 second            var x = setInterval(function () {                // Get todays date and time                var now = new Date().getTime();                // Find the distance between now and the count down date                var distance = countDownDate - now;                // Time calculations for days, hours, minutes and seconds                var days = Math.floor(distance / (1000 * 60 * 60 * 24));                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));                var seconds = Math.floor((distance % (1000 * 60)) / 1000);                // Display the result in the element with id="demo"                var demo = document.getElementById("demo_time");                if (demo) {                    demo.innerHTML = days + "d " + hours + "h "                        + minutes + "m " + seconds + "s  left until auto approval of rejection";                }                // If the count down is finished, write some text                if (distance < 0) {                    clearInterval(x);                    jQuery('#accept_rejection').click();                    var demo_the_element = document.getElementById("demo_time");                    if (demo_the_element) {                        demo_the_element.innerHTML = "EXPIRED";                    }                }            }, 5000);        });    </script><?phpelse:    ?>    <script>        var demo = document.getElementById("demo_time");        if (demo) {            demo.innerHTML = "";        }    </script><?phpendif;$b_show_dialog_open = false;if (isset($proposal) && is_array($proposal) && count($proposal) &&    ($proposal[0]->status === 'completed') && (empty($proposal[0]->rating_by_freelancer))) {    $b_show_dialog_open = true;}if ($b_show_dialog_open) {    ?>    <script>        $(function () {            $("a.hirebttn2[data-target='#feedbackModel']").click();        });    </script><?php } ?>