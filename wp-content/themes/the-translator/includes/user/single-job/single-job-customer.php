<?php
//code-notes have this customer project page show the ratings dialog when conditions
global $wpdb;

global $post;
    /*
    * current-php-code 2020-Sep-30
    * input-sanitized : lang,job_id
    * current-wp-template:  editing projects
    */
$lang = FLInput::get('lang', 'en');
$job_id = FLInput::get('job_id');


$job_approvel_customer_hours = floatval(get_option("auto_job_approvel_customer_hours",120));
$prefix = $wpdb->prefix;

$post_id = get_the_ID();

$project_id = get_the_ID();

$jtype = get_post_meta($project_id, 'fl_job_type', true);
$tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
if ($jtype == 'contest') {
    $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
} else if ($jtype == 'project') {
    $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
}

$current_user = wp_get_current_user();

$current_user_id = $current_user->ID;

//code-notes clear red dots for this job
$red = new FLRedDot();
$red->event_user_id_role =  FreelinguistUserLookupDataHelpers::get_logged_in_role_id();
$red->project_id = $post_id;
FLRedDot::remove_red_dots($current_user_id,$red);

//code-notes add form-keys for customer management of milestones
?>
    <script>

        if (adminAjax) {
            adminAjax.form_keys.hz_create_milestone = '<?= FreeLinguistFormKey::create_form_key('hz_create_milestone') ?>';
            adminAjax.form_keys.hz_manage_milestone = '<?= FreeLinguistFormKey::create_form_key('hz_manage_milestone') ?>';
            adminAjax.form_keys.hz_approve_milestone = '<?= FreeLinguistFormKey::create_form_key('hz_approve_milestone')?>';
        }
    </script>

<?php
$wp_interest_tags = $wpdb->prefix . "interest_tags";

$post_tags = $wpdb->get_results("SELECT GROUP_CONCAT(tag_id) as tag_ids FROM wp_tags_cache_job WHERE `job_id` = $project_id AND type = $tagType");

$jdata = hz_get_job_data($job_id);
if ($job_id) {

    if (!$jdata) {
        wp_die(__("Job Not Found: "). $job_id,__("Job Not Found"));
    }
    $translator_id = $jdata->linguist_id;
    $jbStatus = $jdata->job_status;

    if (in_array($translator_id, get_post_meta($post_id, 'job_freeze_user')) && !empty(get_post_meta($post_id, 'job_freeze_user'))) {
        $job_freeze = true;
    } else {
        $job_freeze = false;
    }
} else {
    $job_freeze = false;
}
$tags_name_array = array();

?>

    <!--suppress JSUnusedLocalSymbols -->
<script>
        var countDownDate = {};
        var x = {};
        var now = {};
        var distance = {};
        var days = {};
        var hours = {};
        var minutes = {};
        var seconds = {};
    </script>
    <div class="title-top" style="position:relative">

        <div class="container">

            <i class="icon icon-box"></i>

            <p class="default_txt_style f_b large-text"><?php echo stripslashes(get_post_meta($post_id, 'project_title', true)); ?></p>

        </div>

        <?php if ( $job_id) {?>
            <div class="freelinguist-id-display freelinguist-under-customer-header " style="">
                    <span class="small-text">
                        <?= $jdata->title ?>
                    </span>
            </div>
        <?php } ?>

    </div>

    <section class="middle-content">

        <?php

        global $post;

        $prefix = $wpdb->prefix;

        $table_comment = $prefix . 'comments';

        $current_user_role = xt_user_role();

        $author_id = $post->post_author;

        if ($author_id == $current_user_id) {

        if (have_posts()): while (have_posts()) :
        the_post();

        $post_id = get_the_ID();


        $job_standard_delivery_date = get_post_meta($post_id, 'job_standard_delivery_date', true);


        ?>

        <div class="container" id="container-body">

            <div class="dashboard-sec">

                <div class="row">

                    <div class="job-details col-md-12">




                        <?php

                        /*

							#############################################

							#	MARKUP_FOR_NEW_DESIGN_WILL_START_HERE	#

							#############################################

							*/

                        if ($job_id){

                        $jdata = hz_get_job_data($job_id);

                        $currLingu = $jdata->linguist_id;

                        $ling_meta = get_userdata($jdata->linguist_id);


                        ?>

                        <div class="hire-linguist">


                            <div class="hire-linguist-top">

                                <div class="hire-linguist-top-left">

                                    <?php

                                    $pro_linguists = hz_project_asso_linguist($post_id);




                                    if ($pro_linguists) {

                                        echo '<ul>';

                                        for ($i = 0; $i < count($pro_linguists); $i++) {


                                            $prefix = $wpdb->prefix;

                                            $sql_statement = "SELECT * FROM wp_fl_job WHERE `project_id` = $post_id AND `linguist_id` = $pro_linguists[$i]";
                                            $jobsUsr = $wpdb->get_results($sql_statement);

                                            $userLink = get_permalink($post_id) . '&job_id=' . $jobsUsr[0]->title;

                                            ?>
                                            <li>
                                                <a class="regular-text" href="<?= $userLink?>">
                                                    <img src="<?= hz_get_profile_thumb($pro_linguists[$i]) ?>" >
                                                </a>
                                            </li>
                                           <?php
                                        }

                                        echo '</ul>';

                                    }

                                    ?>

                                    <a class="hireanother large-text" href="<?php the_permalink(); ?>">
                                        + Hire Another Freelancer
                                    </a>

                                </div>

                                <div class="hire-linguist-top-right">

                                    <a class="hirebttn2" href="<?php the_permalink(); ?>">Job Details & Bids</a>

                                </div>

                            </div>


                            <div class="hire-linguist-detail">

                                <a class="pro-img regular-text" href="#">
                                    <img src="<?php echo hz_get_profile_thumb($jdata->linguist_id); ?>">
                                </a>

                                <div class="pro-detail">
                                    <label class="large-text"><?php echo $ling_meta->display_name; ?></label>
                                    <br>
                                    <span class="enhanced-text">1:19 am Fri in United State</span>
                                </div>
                                <?php
                                $options = get_option('xmpp_settings');
                                $prefix = '';
                                if (array_key_exists('xmpp_prefix', $options)) {
                                    $prefix = $options['xmpp_prefix'];
                                }
                                ?>
                                <?php
                                if (!$job_freeze) {
                                    //code-notes chat part, for chatting with hired
                                    set_query_var('job_id', $jdata->project_id);
                                    set_query_var('to_user_id', $jdata->linguist_id);
                                    set_query_var( 'fl_job_id', $jdata->ID );
                                    set_query_var('job_type', 'project');
                                    set_query_var( 'b_show_name', 0 );
                                    get_template_part('includes/user/chat/chat', 'button-area');
                                 }
                                ?>

                            </div>

                            <?php


                            $ap_complition = true;

                            if (isset($mstons) && !(empty($mstons))) {

                                foreach ($mstons as $row) {


                                    if ($row->status == 'approve') {

                                        $ap_complition = false;

                                    }

                                }
                            }
                            if ($ap_complition === true && !$job_freeze) : ?>

                                <div class="approve_completion">
                                    <div style="clear: both;"></div>

                                    <?php


                                    if (get_post_meta($post_id, 'project_status', true) == 'project_completed') {

                                        if (empty($jdata->rating_by_customer)) {
                                            ?>
                                            <a style=" margin-top: 20px;" class="hirebttn2" href="#"
                                               data-toggle="modal" data-target="#feedbackModel"
                                            >
                                                Rate Freelancer
                                            </a>


                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <button style=" margin-top: 20px;" type="submit" id="approve_completion_button">
                                            Approve Completion
                                        </button>
                                    <?php } ?>


                                </div>

                            <?php endif; ?>

                        </div>

                        <div style="clear: both;"></div>

                        <div class="milestone-payment">

                            <div class="milestone-top">

                                <h3>Milestone Payments</h3>
                                <?php if (!$job_freeze): ?>
                                    <a class="hirebttn2" href="#" data-toggle="modal"
                                       data-target="#createMilestone"
                                    >+ Create Milestone
                                    </a>
                                <?php endif; ?>

                            </div>

                            <div class="milestone-payment-sec">

                                <ul>

                                    <li>

                                        <label class="large-text"><em>$</em> 0.00</label><br>

                                        <span class="">
                                            In Progress
                                            <i class="fa fa-info-circle enhanced-text"></i>
                                        </span>

                                    </li>

                                    <li>

                                        <label class="large-text">
                                            <em>$</em>
                                            <?php echo fl_get_job_pay_status($jdata->ID, 'completed'); ?>
                                        </label>
                                        <br>

                                        <span class="">Released
                                            <i class="fa fa-info-circle enhanced-text"></i>
                                        </span>

                                    </li>


                                </ul>

                            </div>


                            <div class="description-sec table-responsive">


                                <table width="100%" class="ms_data_tbl enhanced-text">

                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php

                                    $mtbl = $wpdb->prefix . 'fl_milestones';


                                    $mstons = $wpdb->get_results(
                                            "SELECT *, UNIX_TIMESTAMP(completed_at) as completed_at_ts FROM wp_fl_milestones WHERE job_id = $jdata->ID order by ID ASC ");

                                    $i = 1;

                                    $ap_complition = true;

                                    if ($mstons) {

                                        foreach ($mstons as $row) {


                                            if ($row->status == 'approve') {

                                                $ap_complition = false;

                                            }

                                            $class = (get_current_user_id() == $row->author) ? "current_user" : "";

                                            $class .= ' fl_status_' . $row->status;

                                            echo '<tr class="' . $class . '" id="milestone-' . $row->ID . '" data-status="' . $row->status . '">

					<td>' . $i . '. </td>

					<td>' . stripslashes_deep($row->content) . '</td>

					<td>$' . $row->amount . '</td>

					<td>' . $row->delivery_date . '</td>

					<td class="released-stat">';

                                            if (!$job_freeze) {
                                                if ($row->status == "approve" || $row->status == "request_completion") {
                                                    echo '<p class="enhanced-text" id="milestone_demo_time__' . $row->ID . '"></p>';
                                                    if ($row->dispute == 0) {


                                                        if ($row->status == "request_completion") {
                                                            echo 'Approval Completion Request by ' . get_userdata($row->linguist_id)->user_nicename .
                                                                ', please approve the completion or request revision within the deadline.<br>';
                                                        }

                                                        echo '<p id="demo_time_' . $row->ID . '"></p>';

                                                        if ($row->status == "request_completion") {

                                                            echo '<button id="" class="action-btns reject-btn button-small " '.
                                                                'href="#" data-toggle="modal" data-target="#requestrevisionModel_' .
                                                                $row->ID . '"> <i class="fa fa-times"></i> Request Revision </button>';
                                                            echo '<br>';

                                                        }

                                                        if (get_current_user_id() != $row->author) {

                                                            echo '<span>Working</span><br>';

                                                        } else {

                                                            echo '<span>Working</span><br>';

                                                        }

                                                        echo '<button id="" class="action-btns approve-btn button-small"' .
                                                            ' href="#" data-toggle="modal" data-target="#approvecompletionModel_' . $row->ID . '">' .
                                                            ' Approve Completion </button>';




                                                        echo '<button id="" class="action-btns reject-btn bg-secondary button-small  " ' .
                                                            'href="#" data-toggle="modal" data-target="#requestrejectionModel_' . $row->ID . '">' .
                                                            ' <i class="fa fa-times"></i> Reject </button>';


                                                    } else {
                                                        echo '<p  class="enhanced-text" id="demo_time_' . $row->ID . '"></p>';
                                                        echo '<button disabled="disabled" style="opacity:0.5;pointer-events:none;" ' .
                                                            'class=" action-btns hire-mediator-btn button-small ">' .
                                                            '<i class="fa fa-user-plus"></i> Already requested for mediator </button>';

                                                    }


                                                    if ($row->completion_requested == '1' && $row->status != "approve" &&
                                                        $row->status != "request_revision") {


                                                        ${'new_date' . $row->ID} = (intval($row->completed_at_ts) +
                                                                (60 * 60* $job_approvel_customer_hours ))*1000;


                                                        echo '<script>
						

						countDownDate[' . $row->ID . '] = new Date(' . ${'new_date' . $row->ID} . ').getTime();
						

						x[' . $row->ID . '] = setInterval(function() {

							now[' . $row->ID . '] = new Date().getTime();
                            distance[' . $row->ID . '] = countDownDate[' . $row->ID . '] - now[' . $row->ID . '];
								
                            days[' . $row->ID . '] = Math.floor(distance[' . $row->ID . '] / (1000 * 60 * 60 * 24));
                            hours[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            minutes[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60)) / (1000 * 60));
                            seconds[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60)) / 1000);

                            let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                            if (demo_time) {
                                    demo_time.innerHTML = days[' . $row->ID . '] + "d " + hours[' . $row->ID . '] + "h "
                                    + minutes[' . $row->ID . '] + "m " + seconds[' . $row->ID . '] + "s  left until auto approval of completion";
                            }

                            if (distance[' . $row->ID . '] < 0) {
                                clearInterval(x[' . $row->ID . ']);
                                jQuery("#auto_complete_' . $row->ID . '").click(); 
                                let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                                if (demo_time) {
                                    demo_time.innerHTML = "EXPIRED"; 
                                }
                            }
							

							
							
						}, 5000);
						</script>';
                                                    }


                                                } elseif ($row->status == "hire_mediator") {


                                                    echo '<p class="enhanced-text" id="demo_time_' . $row->ID . '"></p>';
                                                    if ($row->dispute == 0) {


                                                        echo '<span>Mediator is hired by Freelancer.</span><br>';

                                                        echo '<button id="" class="action-btns approve-btn button-small" href="#" data-toggle="modal" data-target="#approvecompletionModel_' . $row->ID . '"> Approve Completion </button>';


                                                    } else {

                                                        echo '<button disabled="disabled" style="opacity:0.5;pointer-events:none;" class=" action-btns hire-mediator-btn button-small "><i class="fa fa-user-plus"></i> Already requested for mediator </button>';

                                                    }


                                                    if ($row->completion_requested == '1' && $row->status != "approve" &&
                                                        $row->status != "request_revision" && $row->status != "hire_mediator") {

                                                        ${'new_date' . $row->ID} = (intval($row->completed_at_ts) +
                                                                (60 * 60* $job_approvel_customer_hours))*1000;

                                                        echo '<script>
					

						countDownDate[' . $row->ID . '] = new Date(' . ${'new_date' . $row->ID} . ').getTime();

						x[' . $row->ID . '] = setInterval(function() {
											

							now[' . $row->ID . '] = new Date(' . date('Y-m-d H:i:s') . ').getTime();

							distance[' . $row->ID . '] = countDownDate[' . $row->ID . '] - now[' . $row->ID . '];
                            days[' . $row->ID . '] = Math.floor(distance[' . $row->ID . '] / (1000 * 60 * 60 * 24));
                            hours[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            minutes[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60)) / (1000 * 60));
                            seconds[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60)) / 1000);

                            let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                                if (demo_time) {
                                    demo_time.innerHTML = days[' . $row->ID . '] + "d " + hours[' . $row->ID . '] + "h "
                                + minutes[' . $row->ID . '] + "m " + seconds[' . $row->ID . '] + "s  left until auto approval of completion";
                            }

                            if (distance[' . $row->ID . '] < 0) {
                                clearInterval(x[' . $row->ID . ']);
                                jQuery("#auto_complete_' . $row->ID . '").click(); 
                                let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                                if (demo_time) {
                                    demo_time.innerHTML = "EXPIRED";
                                }
                            }
						}, 5000);
						</script>';
                                                    }


                                                } elseif ($row->status == "request_revision") {

                                                    echo '<p class="enhanced-text" id="demo_time_' . $row->ID . '"></p>';

                                                    if ($row->dispute == 0) {


                                                        echo '<span>You have requested for revision</span><br>';


                                                        echo '<button id="" class="action-btns approve-btn button-small" '
                                                            . ' href="#" data-toggle="modal" data-target="#approvecompletionModel_' . $row->ID . '"> Approve Completion </button>';


                                                        echo '<button id="" class="action-btns reject-btn bg-secondary  button-small " ' .
                                                            'href="#" data-toggle="modal" data-target="#requestrejectionModel_' . $row->ID . '"> ' .
                                                            '<i class="fa fa-times"></i> Reject </button>';


                                                    } else {

                                                        echo '<button disabled="disabled" style="opacity:0.5;pointer-events:none;" ' .
                                                            'class=" action-btns hire-mediator-btn  button-small">' .
                                                            '<i class="fa fa-user-plus"></i> Already requested for mediator </button>';

                                                    }


                                                    if ($row->completion_requested == '1' && $row->status != "approve" &&
                                                        $row->status != "request_revision" && $row->status != "hire_mediator") {

                                                        ${'new_date' . $row->ID} = (intval($row->completed_at_ts) +
                                                                (60 * 60* $job_approvel_customer_hours))*1000;


                                                        echo '<script>
						

						countDownDate[' . $row->ID . '] = new Date(' . ${'new_date' . $row->ID} . ').getTime();

						x[' . $row->ID . '] = setInterval(function() {
											
							now[' . $row->ID . '] = new Date().getTime();
                            distance[' . $row->ID . '] = countDownDate[' . $row->ID . '] - now[' . $row->ID . '];
								
								
                            days[' . $row->ID . '] = Math.floor(distance[' . $row->ID . '] / (1000 * 60 * 60 * 24));
                            hours[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            minutes[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60)) / (1000 * 60));
                            seconds[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60)) / 1000);

                            let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                            if (demo_time) {
                                demo_time.innerHTML = days[' . $row->ID . '] + "d " + hours[' . $row->ID . '] + "h "
                                + minutes[' . $row->ID . '] + "m " + seconds[' . $row->ID . '] + "s  left until auto approval of completion";
                            }

                            if (distance[' . $row->ID . '] < 0) {
                                clearInterval(x[' . $row->ID . ']);
                                jQuery("#auto_complete_' . $row->ID . '").click();
                                let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                                if (demo_time) {
                                    demo_time.innerHTML = "EXPIRED";
                                 }
                            }
						
							
						}, 5000);
						</script>';
                                                    }


                                                } elseif ($row->status == "reject") {
                                                    echo '<p class="enhanced-text" id="demo_time_' . $row->ID . '"></p>';

                                                    echo '<button id="" class="action-btns reject-btn button-small" '
                                                        . 'href="#" data-toggle="modal" data-target="#requestrevisionModel_' . $row->ID . '"> ' .
                                                        '<i class="fa fa-times"></i> Request Revision </button><br>';

                                                    if ($row->dispute == 0) {


                                                        echo '<span>Rejection requested</span><br>';


                                                        echo '<button id="" class="action-btns approve-btn button-small"' .
                                                            ' href="#" data-toggle="modal" data-target="#approvecompletionModel_' .
                                                            $row->ID . '"> Approve Completion </button>';


                                                    } else {

                                                        echo '<button disabled="disabled" style="opacity:0.5;pointer-events:none;" ' .
                                                            'class=" action-btns hire-mediator-btn button-small">' .
                                                            '<i class="fa fa-user-plus"></i> Already requested for mediator </button>';

                                                    }

                                                    echo '<div class="modal fade" id="requestrevisionModel_' . $row->ID . '" ' .
                                                        'tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

						<div class="modal-dialog">

							<div class="modal-content">

								<div class="modal-header">

								  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

								  <h4 class="modal-title">Revision for milestone.</h4>

								</div>

								<div class="modal-body">
									<div class="form-group">
										<textarea  autocomplete="off" id="revision_text_' . $row->ID . '" class="form-control" rows="5" cols="60"></textarea>
									</div>
									<div class="form-group">
										<button class="action-btns reject-btn button-small" onclick="return hz_manage_milestone(' .
                                                        $row->ID . ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'request_revision\');"><i class="fa fa-times"></i> Request Revision</button>
									</div>
								</div>
							</div>
						</div>
					</div>';

                                                    if ($row->completion_requested == '1' && $row->status != "approve" && $row->status != "request_revision" &&
                                                        $row->status != "hire_mediator") {

                                                        ${'new_date' . $row->ID} = (intval($row->completed_at_ts) +
                                                                (60 * 60* $job_approvel_customer_hours))*1000;
                                                        echo '<script>


						countDownDate[' . $row->ID . '] = new Date(' . ${'new_date' . $row->ID} . ').getTime();

						x[' . $row->ID . '] = setInterval(function() {

							now[' . $row->ID . '] = new Date().getTime();
                            distance[' . $row->ID . '] = countDownDate[' . $row->ID . '] - now[' . $row->ID . '];
								
                            days[' . $row->ID . '] = Math.floor(distance[' . $row->ID . '] / (1000 * 60 * 60 * 24));
                            hours[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            minutes[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60 * 60)) / (1000 * 60));
                            seconds[' . $row->ID . '] = Math.floor((distance[' . $row->ID . '] % (1000 * 60)) / 1000);

                            let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                            if (demo_time) {
                                demo_time.innerHTML = days[' . $row->ID . '] + "d " + hours[' . $row->ID . '] + "h "
                                + minutes[' . $row->ID . '] + "m " + seconds[' . $row->ID . '] + "s  left until auto approval of completion";
                            }

                            if (distance[' . $row->ID . '] < 0) {
                                clearInterval(x[' . $row->ID . ']);
                                jQuery("#auto_complete_' . $row->ID . '").click();
                                let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                                if (demo_time) {
                                    demo_time.innerHTML = "EXPIRED";
                                }
                            }
							

							
							
						}, 5000);
						</script>';
                                                    }


                                                } elseif ($row->status == "requested") {

                                                    //Check {IF} this milestone is created by customer {ELSE} show accept or reject button to the linguist

                                                    if (get_current_user_id() == $row->author) {

                                                        echo "<span>Awaiting response from Freelancer.</span>";

                                                    } else {

                                                        echo '<button class="action-btns approve-btn button-small"'.
                                                            ' onclick="return wrap_wallet_hz_manage_milestone(' .
                                                            $row->ID . ', \'Do you want to Accept this milestone?\', \'Yes\', \'No\', \'approve\');">'.
                                                            '<i class="fa fa-check"></i>Accept</button>';

                                                        echo '<button class="action-btns reject-btn bg-secondary button-small" '.
                                                            'onclick="return hz_manage_milestone(' .
                                                            $row->ID . ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'rejected\');">'.
                                                            '<i class="fa fa-times"></i> Reject</button>';

                                                    }

                                                } elseif ($row->status == "approved_rejection") {

                                                    echo 'Rejection approved by Freelancer.';

                                                } elseif ($row->status == "rejected") {

                                                    echo 'Rejection approved by Customer.';

                                                } elseif ($row->status == "completed") {

                                                    echo 'Milestone Completed.';

                                                }

                                            }


                                            echo '<div class="modal fade" id="approvecompletionModel_' .
                                                $row->ID . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

						<div class="modal-dialog">

							<div class="modal-content">

								<div class="modal-header">

								  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

								  <h4 class="modal-title">By approving completition, you acknowledge you have received the files and are satisfied with the services. </h4>
								  <h4 class="modal-title">Please note, you may not be able to raise a dispute against this job and the funds will be settled with the service provider.</h4>
								  
								</div>

								<div class="modal-body">
									<button class="action-btns approve-btn button-small" id="auto_complete_' .
                                                $row->ID . '" onclick="return hz_approve_milestone(' .
                                                $row->ID . ', \'Do you want to approve this milestone.\' );"
									>
									    <i class="fa fa-check"></i>
									    Yes
									 </button>
								</div>
							</div>
						</div>
					</div>
					
					<div class="modal fade" id="requestrevisionModel_' . $row->ID .
                         '" tabindex="-1" role="dialog" 
					    aria-labelledby="myModalLabel" aria-hidden="true"
					    >

						<div class="modal-dialog">

							<div class="modal-content">

								<div class="modal-header">

								  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

								  <h4 class="modal-title">Revision for milestone.</h4>

								</div>

								<div class="modal-body">
									<div class="form-group">
										<textarea  autocomplete="off" id="revision_text_' . $row->ID . '" class="form-control" rows="5" cols="60"></textarea>
									</div>
									<div class="form-group">
										<button class="action-btns reject-btn button-small" '.
                                                'onclick="return hz_manage_milestone(' . $row->ID .
                                                ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'request_revision\');">'.
                                                '<i class="fa fa-times"></i> Request Revision</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="modal fade" id="requestrejectionModel_' . $row->ID .
                                                '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

						<div class="modal-dialog">

							<div class="modal-content">

								<div class="modal-header">

								  <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

								  <h4 class="modal-title">Request for rejection.</h4>

								</div>

								<div class="modal-body">
									<div class="form-group">
										<textarea  autocomplete="off" id="rejection_txt_' . $row->ID . '" rows="5" cols="60" class="form-control"></textarea>
									</div>
									<div class="form-group">
										<button class="action-btns reject-btn bg-secondary button-small" 
										onclick="return hz_manage_milestone(' .
                                                $row->ID . ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'reject\');">
										<i class="fa fa-times"></i> &nbsp;Reject</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					';
                                            echo '</td></tr>';

                                            $i++;

                                        }

                                    }

                                    ?>

                                    </tbody>
                                </table>

                            </div>

                            <div style="clear: both;"></div>
                            <?php

                            if ($mstons) {
                                $j = 1;
                                ?>
                                <div class="col-md-12">&nbsp;</div>


                                <div class="dash-body message-history">
                                    <h4>Message History </h4>
                                    <table class="ms_data_tbl enhanced-text">
                                        <?php
                                        foreach ($mstons as $key => $row) {

                                            $messages = $wpdb->get_results("SELECT * FROM wp_message_history WHERE milestone_id = $row->ID order by id asc");

                                            if ($messages) {
                                                foreach ($messages as $k => $message) {
                                                    echo '<tr><td><b>#' . $row->number . '</b> ' . $message->created_at . ': ' . stripslashes_deep($message->message) . '</td></tr>';

                                                }
                                            }
                                            $j++;


                                        }
                                        ?>
                                    </table>
                                </div>

                                <?php
                            }
                            ?>


                        </div>


                        <div class="dash-body">

                            <div class="dilevered-stat">


                                <h3>Files Delivered</h3>
                                <div class="item-sec">

                                    <?php
                                    $tfiles = $wpdb->get_results("SELECT * FROM wp_files WHERE `job_id` = $jdata->ID AND `by_user` = $jdata->linguist_id AND `type` = ".FLWPFileHelper::TYPE_FREELANCER_UPLOAD);

                                    if ($tfiles) {

                                        echo '<ul class="document-row">';

                                        foreach ($tfiles as $fl) {
                                            ?>
                                            <li>
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
                                            </li>
                                            <?php

                                        }

                                        echo '</ul>';

                                    }

                                    ?>

                                </div>



                                <?php


                                global $wpdb;

                                $jtbl = $wpdb->prefix . "fl_job";

                                $row = $wpdb->get_results("SELECT * FROM wp_fl_job WHERE `title` = '" . $job_id . "'");


                                ?>


                                <h3>Delivery Instructions</h3>

                                <div class="item-sec" id="order_files_content">


                                    <?php

                                    $query = "SELECT * FROM wp_files where job_id = $jdata->ID AND type = ".FLWPFileHelper::TYPE_INSTRUCTION_FILE;

                                    $trans_text_exist = $wpdb->get_results($query);

                                    ?>


                                    <div class=" ">


                                    <ul class="document-row"></ul>
                                    <?php if (!$job_freeze): ?>
                                        <div class="upload-file regular-text">
                                            <i class="fa fa-upload enhanced-text"></i>
                                            Upload Files
                                            <input multiple="" name="files[]"
                                                   id="project_single_job_file_upload"
                                                   class="files-data"
                                                   data-id="<?php echo $project_id; ?>"
                                                   data-name="<?php echo $jdata->ID; ?>"
                                                   type="file">
                                        </div>
                                    <?php endif; ?>


                                    <br>

                                    <!-- Progress Bar -->

                                    <div id="progress" class="progress" style="margin-top: 10px;">

                                        <div class="progress-bar progress-bar-success"></div>

                                    </div>

                                    <div class="percent"></div>

                                    <!-- The container for the uploaded files -->

                                    <div id="files_name_container" class="files">

                                        <div class="attached-doc will-aaa" name="translating_file_attatchment">

                                            <ul class="doc-ul">

                                                <li class="">


                                                </li>

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

                                                            <div class="cross">

                                                                <?php if ($post->post_author == $current_user_id && !$job_freeze) { ?>

                                                                    <a class="cross-icon"
                                                                       onclick="return single_remove_selected(this,<?php echo $trans_text_exist[$i]->id; ?>)"
                                                                       href="#">
                                                                    </a>

                                                                <?php } ?>

                                                            </div>

                                                        </div>

                                                    </li>

                                                <?php } ?>

                                            </ul>

                                        </div>

                                    </div>

                                </div>

                                <!-- START: Job attachment-->


                            </div><!--//dash-body-->


                            <div class="review-listing job_dtls">


                                <div class="bidding-other comments_lists">
                                    <?php
                                    if ($jdata->rating_by_customer) {
                                        ?>

                                        <h3>Rating For Freelancer</h3>

                                        <div class="item-sec" id="">
                                            <?php echo convert_rating($jdata->rating_by_customer, 17, NULL, $jdata->author) . ' ' .
                                                stripslashes_deep($jdata->comments_by_customer); ?>
                                        </div>
                                    <?php } ?>


                                    <div class="title-bg">

                                        <h3><i class="fa fa-user" aria-hidden="true"></i> Messages</h3>

                                    </div>

                                    <div class="hz_discussion_row">

                                        <?php echo hz_fl_discussion_list_both($post_id, get_current_user_id(), $currLingu); ?>

                                    </div>

                                    <div class="message-sec text-box">

                                        <form id="contest_discussion">

                                            <textarea title="comment" required name="comment"  autocomplete="off" ></textarea>

                                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

                                            <input type="hidden" name="comment_to" value="<?php echo $currLingu; ?>">

                                            <input type="submit" class="red-btn enhanced-text" value="Contact Freelancer">

                                        </form>

                                    </div>

                                </div>

                            </div>

                            <?php }else{ ?>
                            <?php

                            ?>
                            <div class="dash-body">

                                <div class="box-row select-linguist-row">
                                    <?php
                                    if (true && get_post_status($post_id) == "publish") {
                                        $comments = get_all_bids_of_particular_job(); ?>

                                        <div class="placed-bids">

                                            <div class="freelinguist-number-bidding large-text">

                                                <i aria-hidden="true" class="fa fa-user larger-text"></i>

                                                <span>
                                                    Bidding Freelancers: <?= count($comments) ?>
                                                </span>


                                            </div>

                                            <ul class="placed-bid-list fl-styled-placed-bid-list">

                                                <?php

                                                if ($comments) {

                                                    foreach ($comments as $comment) {


                                                        $hz_job_link = freeling_links('user_account') . '&profile_type=translator&user=' .
                                                            $comment->comment_author . '&b_url=' . $post_id;

                                                        if (hz_bid_exist($comment->comment_ID)) {

                                                            $hz_job_link = get_permalink($post_id) . '&job_id=' . hz_bid_id_slug($comment->comment_ID);

                                                        }


                                                        $cur_jbid = $wpdb->prefix . "fl_job";

                                                        $rowcur_jbid = $wpdb->get_results("SELECT * FROM wp_fl_job
                                                                      WHERE `linguist_id` = '" . $comment->user_id .
                                                                        "' AND `project_id` = '" . $post_id . "' ");
                                                        $jbStatus = '';
                                                        if (!empty($rowcur_jbid)) {
                                                            $jbStatus = $rowcur_jbid[0]->job_status;
                                                        }


                                                        ?>

                                                        <li>

                                                            <div class="bidder-details">

                                                                <div class="thum">

                                                                    <?php

                                                                    if ($jbStatus == 'reject_job' || $jbStatus != 'start') {

                                                                        $hz_job_link = '#';

                                                                    }

                                                                    ?>

                                                                    <a href="<?php echo freeling_links('user_account') . '&profile_type=translator&user=' . $comment->comment_author . '&b_url=' . $post_id; ?>">

                                                                        <?php
                                                                        //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                                                                        $avatar = hz_get_profile_thumb($comment->user_id,FreelinguistSizeImages::TINY,true);
                                                                        ?>
                                                                        <img style="" src="<?= $avatar ?>">

                                                                    </a>

                                                                </div>

                                                                <div class="bidder-intro">

                                                                    <div class="bidder-name">

                                                                        <?php if ($lang) { ?>

                                                                            <?php


                                                                            if ($jbStatus == 'reject_job') {

                                                                                $jbpageLink = '#';

                                                                            } else {

                                                                                $jbpageLink = freeling_links('user_account') . '&profile_type=translator&user=' . $comment->comment_author . '&b_url=' . $post_id;

                                                                            }

                                                                            ?>

                                                                            <a target="_blank"
                                                                               href="<?php echo $jbpageLink; ?>">
                                                                                <h6><?php echo substr(get_display_name($comment->user_id), 0, 25); ?></h6>
                                                                            </a>

                                                                        <?php } else { ?>

                                                                            <a target="_blank"
                                                                               href="<?php echo freeling_links('user_account');
                                                                                    ?>&profile_type=translator&user=<?php echo $comment->comment_author;
                                                                                    ?>&b_url=<?php echo $post_id; ?>"
                                                                            >
                                                                                <h6><?php echo substr(get_display_name($comment->user_id), 0, 25); ?></h6>
                                                                            </a>

                                                                        <?php } ?>

                                                                        <div class="reviews"><?php echo translater_rating($comment->user_id, 17, 'translator'); ?></div>

                                                                    </div>

                                                                    <div class="strength">

                                                                        Date:

                                                                        <div class="strength-bar">

                                                                            <?php echo date_formatted($comment->comment_date); ?>

                                                                        </div>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                            <div class="bid-description">

                                                                <a href="<?php echo $jbStatus == 'start' ? get_permalink($post_id) . '&job_id=' .
                                                                    hz_bid_id_slug($comment->comment_ID) : '#'; ?>">
                                                                    <p><?php echo stripslashes_deep(stringTrim($comment->comment_content)); ?></p>
                                                                </a>

                                                                <div class="desp-action">
                                                                    <p class="enhanced-text" id="bid_price">
                                                                        <?php



                                                                        $key = 'bid_price';
                                                                        $bid_price = get_comment_meta($comment->comment_ID, $key);
                                                                        if ($bid_price[0]) {
                                                                            echo $bid_price = '$' . $bid_price[0] . '<br>';
                                                                        }
                                                                        $options = get_option('xmpp_settings');
                                                                        $prefix = '';
                                                                        if (array_key_exists('xmpp_prefix', $options)) {
                                                                            $prefix = $options['xmpp_prefix'];
                                                                        }
                                                                        ?></p></div>
                                                            </div>
                                                            <div>
                                                                <div class="fl-bid-detail-button-holder">

                                                                    <?php
                                                                    //code-notes chat part, for chatting with bidders
                                                                    set_query_var('job_id',$post_id);
                                                                    set_query_var('to_user_id',  $comment->user_id);
                                                                    set_query_var( 'fl_job_id', NULL );
                                                                    set_query_var('job_type', 'project');
                                                                    set_query_var( 'b_show_name', 0 );
                                                                    get_template_part('includes/user/chat/chat', 'button-area');

                                                                    if (hz_bid_exist($comment->comment_ID)) {

                                                                        if ($jbStatus == 'reject_job') {

                                                                            echo '<a style="color:red; float:right; margin-right: 5%;" href="#">Rejected by Freelancer</a>';

                                                                        } else if ($jbStatus == 'start') {
                                                                            $linkk = $jbStatus == 'start' ? get_permalink($post_id) . '&job_id=' .
                                                                                hz_bid_id_slug($comment->comment_ID) : '#';
                                                                            ?>
                                                                            <a class="red-btn btn button-width button-normal regular-text"
                                                                               href="<?= $linkk ?>" style="float:right; margin-right: 5%;">
                                                                                Work Delivery
                                                                            </a>'
                                                                            <?php

                                                                        } else {
                                                                            ?>
                                                                            <a class="red-btn-no-hover" href="<?=$hz_job_link ?>" style="float:right; margin-right: 5%;">Hired </a>
                                                                            <?php
                                                                        }

                                                                    } else {

                                                                        ?>

                                                                        <button type="button"
                                                                                class="red-btn btn button-width button-normal button-hire regular-text"
                                                                                style="float:right; margin-right: 5%;"

                                                                                data-bid_id="<?php echo $comment->comment_ID; ?>"

                                                                                data-job_id="<?php echo $post_id; ?>"

                                                                                data-user_name="<?php echo get_display_name($comment->user_id); ?>"

                                                                                data-translater_id="<?php echo $comment->user_id; ?>">

                                                                            <?php get_custom_string('HIRE'); ?>

                                                                        </button>

                                                                        <button type="button"
                                                                                style="display: none;"
                                                                                id="hire_<?php echo $comment->user_id; ?>"
                                                                                data-toggle="modal"
                                                                                data-target="#hireModel">

                                                                            <?php get_custom_string('HIRE'); ?>

                                                                        </button>

                                                                        <?php

                                                                    }
                                                                    ?>

                                                                </div>

                                                                <!-- Get  bid detail -->

                                                            </div>

                                                        </li>

                                                        <?php

                                                    }

                                                }

                                                ?>

                                            </ul>

                                        </div> <!-- ./placed-bids -->
                                        <br>
                                        <?php

                                    }
                                    ?>
                                    <div class="left-sec11">

                                        <div class="order-no default_txt_style large-text"><?php echo get_post_meta($post_id, 'modified_id', true); ?> </div>
                                        <?php


                                        ?>
                                        <ul class="linguist-order enhanced-text">

                                            <li class="default_txt_style">
                                                <?php get_custom_string("Project Title"); ?>:
                                                <strong class="default_txt_style">
                                                    <?php echo stripslashes(get_post_meta($post_id, 'project_title', true)); ?>
                                                </strong>
                                            </li>

                                            <li class="expt-date default_txt_style">
                                                <?php get_custom_string("Expected delivery date"); ?>:
                                                <strong class="default_txt_style">
                                                    <?php echo date_formatted($job_standard_delivery_date); ?>
                                                </strong>
                                            </li>

                                            <li class="default_txt_style"><?php get_custom_string("Estimated budgets"); ?>
                                                :
                                                <strong class="default_txt_style">
                                                    <?php

                                                    $estimated_budgets = explode('_', get_post_meta($post_id, 'estimated_budgets', true));

                                                    if (count($estimated_budgets) > 1) {
                                                        echo $estimated_budgets[0] . ' USD to ' . $estimated_budgets[1] . ' USD';
                                                    } else {
                                                        echo $estimated_budgets[0] . ' USD';
                                                    }

                                                    ?> </strong>
                                            </li>



                                        </ul>

                                    </div>
                                    <div class="clear"></div>

                                    <div class="">

                                        <div class="job-action-btn">

                                            <?php

                                            if (true) { ?>

                                                <?php $cancel_message = '"' . get_custom_string_return("Do you want to hide this job?") . '"'; ?>

                                                <?php $publish_message = '"' . get_custom_string_return("Do you want to Publish this job?") . '"'; ?>

                                                <?php $delete_message = '"' . get_custom_string_return("Do you want to delete this job?") . '"'; ?>

                                                <?php $yes = '"' . get_custom_string_return("Yes") . '"'; ?>

                                                <?php $no = '"' . get_custom_string_return("No") . '"'; ?>

                                                <?php if (!$job_freeze): ?>
                                                    <?php if (get_post_status($post_id) == "publish") { ?>


                                                        <?php if (get_post_meta($post_id, 'hide_job', true)): ?>

                                                            <a class="red-btn btn regular-text"
                                                               onclick='return show_publish_job(<?php echo $post_id . "," . $publish_message . "," . $yes . "," . $no; ?>)'
                                                               href="javascript:;"><?php get_custom_string('Show Job'); ?></a>

                                                        <?php else: ?>
                                                            <a class="red-btn btn regular-text"
                                                               onclick='return hide_publish_job(<?php echo $post_id . "," . $cancel_message . "," . $yes . "," . $no; ?>)'
                                                               href="javascript:;"><?php get_custom_string('Hide Job'); ?></a>

                                                        <?php endif; ?>

                                                        <?php if (!isset($jbStatus)) {
                                                            $jbStatus = '';
                                                        } ?>
                                                        <?php if ($jbStatus !== 'pending' && $jbStatus !== 'reject_job' && $jbStatus !== 'start') { ?>
                                                            <a class="red-btn btn regular-text"
                                                               onclick='return delete_publish_job(<?php echo $post_id . "," . $delete_message . "," . $yes . "," . $no; ?>)'
                                                               href="javascript:;"><?php get_custom_string('Delete Job'); ?></a>
                                                        <?php } ?>

                                                    <?php } ?>

                                                <?php endif; //!job freeze ?>


                                            <?php } ?>

                                            <?php if (!$job_freeze): ?>

                                                <button class="red-btn btn regular-text"
                                                        style="background: #ee2b31;color: white;"
                                                        onclick="redirectTo('<?php echo get_site_url() . '/order-process/?lang=' . $lang . '&job_id=' . $post_id ?>');">
                                                    Duplicate Project
                                                </button>
                                                <br>
                                            <?php endif; ?>
                                        </div>


                                    </div>


                                    <!-- START: Job Instructions-->


                                    <!-- END: Job Instructions-->

                                    <div class="project-mega-jobs">
                                        <div class="job_dtls">


                                            <form id="editable_form_title">


                                                <input name="title_job_id" value="<?php echo $post_id; ?>" type="hidden">

                                                <input name="title_author" value="<?php echo $current_user_id; ?>"
                                                       type="hidden">

                                            </form>
                                            <div class="h1-title"><span class="bold-and-blocking larger-text">Project Description</span>
                                            </div>


                                            <form id="editable_form_description">

													<textarea class="default_txt_style"
                                                              maxlength="10000"
                                                              id="job_description_editable"
                                                              placeholder="Instructions goes here"
                                                              rows="20"  autocomplete="off"
                                                    ><?=
                                                        stripslashes(get_post_meta($post_id, 'project_description', true));
                                                        ?></textarea>
                                                <!-- code-notes adding new button to save description floated right-->
                                                <button class="fl-save-job-description">Save Description</button>

                                                <input id="description_job_id" value="<?php echo $post_id; ?>" type="hidden">

                                                <input id="description_author" value="<?php echo $current_user_id; ?>"
                                                       type="hidden">

                                            </form>

                                            <div class="h1-title"><span
                                                        class="bold-and-blocking larger-text">Skills</span></div>

                                            <div class="formcont" data-toggle="tooltip" data-placement="bottom" >
                                                <form id="editable_form_tags">


                                                    <input type="text" name="project_tags" id="job_instruction_tags"
                                                           class="tm-input default_txt_style  enhanced-text" value=""
                                                           placeholder="<?php echo get_custom_string_return('Skills'); ?>"
                                                           autocomplete="off"
                                                    >

                                                    <input id="tag_job_id" name="hidden_job_id"
                                                           value="<?php echo $post_id; ?>" type="hidden">

                                                    <input id="author" name="hidden_author_id"
                                                           value="<?php echo $current_user_id; ?>" type="hidden">

                                                </form>

                                            </div>

                                            <?php
                                            foreach ($post_tags as $k => $v) {
                                                $post_tags_array = explode(",", $v->tag_ids);
                                                foreach ($post_tags_array as $v1) {
                                                    if (empty($v1)) {continue;}
                                                    $interest_tags = $wpdb->get_results("SELECT * FROM wp_interest_tags WHERE `id` = $v1");
                                                    foreach ($interest_tags as $k2 => $v2) {
                                                        $tags_name_array[] = $v2->tag_name;


                                                    }
                                                }
                                            }


                                            ?>
                                        </div>

                                        <div class="item-sec default_txt_style" id="order_files_content">

                                            <div class="headsec">

                                                <?php

                                                $trans_text_exist = $wpdb->get_results("SELECT * FROM wp_files where post_id = $post_id AND type = ".FLWPFileHelper::TYPE_POST_DETAILS);

                                                ?>

                                                <div class="floatleft  enhanced-text">Instruction Files</div>

                                                <div class="floatright ">
                                                </div>

                                            </div>

                                            <!-- Progress Bar -->

                                            <div id="progress" class="progress" style="margin-top: 10px;">
                                                <div class="progress-bar progress-bar-success"></div>
                                            </div>

                                            <div class="percent"></div>

                                            <!-- The container for the uploaded files -->

                                            <div id="files_name_container" class="files">

                                                <!-- START: Job attachment-->

                                                <div class="attached-doc will-bbb" name="translating_file_attatchment">

                                                    <ul class="doc-ul">

                                                        <li class="">

                                                        </li>


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

                                                                    <?php if ($post->post_author == $current_user_id && !$job_freeze) { ?>

                                                                        <div class="cross">

                                                                            <a class="cross-icon"
                                                                               onclick="return single_remove_selected_new(this,<?php echo $trans_text_exist[$i]->id; ?>)"
                                                                               href="#"></a>

                                                                        </div>

                                                                    <?php } ?>

                                                                </div>

                                                            </li>

                                                        <?php } ?>

                                                    </ul>

                                                </div>

                                            </div>
                                            <?php if (!$job_freeze): ?>
                                                <div class="upload-file regular-text">

                                                    <i class="fa fa-upload enhanced-text"></i>Upload Instruction Files<input
                                                            multiple="" name="files[]" id="project_job_file_upload"
                                                            class="files-data hz_order_process" type="file"
                                                            data-id="<?php echo $project_id; ?>">

                                                </div>
                                            <?php endif; ?>

                                        </div>

                                    </div>

                                    <!-- END : Job attachment -->




                                        <!-- START: REview info. -->

                                        <?php

                                        $table_comments = $wpdb->prefix . 'comments';

                                        $feedback_exist = $wpdb->get_var(
                                                "SELECT count(*) FROM wp_comments 
                                                        WHERE comment_post_ID = $post_id and 
                                                        comment_type = 'feedback' and comment_approved =1");

                                        if ($feedback_exist >= 1) {

                                            $feedback_is = $wpdb->get_row("SELECT * FROM wp_comments
                                          WHERE comment_post_ID = $post_id and comment_type = 'feedback' and comment_approved =1");

                                            ?>

                                            <div class="attached-doc feedback_attached">

                                                <ul class="doc-ul">

                                                    <li class="">

                                                        <div class="col-md-9">

                                                            <div class="doc-name">

                                                                <h5><?php get_custom_string('Review'); ?></h5>

                                                            </div>

                                                        </div>

                                                        <div class="col-md-3 text-right">

                                                            <div class="download-link">

                                                                <h5><?php get_custom_string('Job Completed'); ?></h5>

                                                            </div>

                                                        </div>

                                                    </li>

                                                    <li>

                                                        <div class="col-md-9">

                                                            <div class="doc-name">

                                                                <?php echo stringTrim($feedback_is->comment_content); ?>

                                                            </div>

                                                        </div>

                                                        <div class="col-md-3 text-right">

                                                            <div class="download-link">

                                                                <?php

                                                                $feedbak_rating = get_comment_meta($feedback_is->comment_ID, 'feedback_rating', true);

                                                                echo job_rating($feedbak_rating); ?>

                                                            </div>

                                                        </div>

                                                    </li>


                                                </ul>

                                            </div>

                                            <?php

                                        }

                                        ?>

                                        <!-- END: Review info. -->


                                        <?php

                                        ?>


                                    </div>

                                </div>

                                <?php } //end not job id (else condition) ?>

                            </div>

                        </div>

                    </div>

                </div>

                <?php

                endwhile;

                else:

                    ?>

                    <div class='container'>

                        <span class="bold-and-blocking larger-text">
                            <?php _e('Sorry, nothing to display.', 'html5blank'); ?>
                        </span>

                    </div>

                <?php

                endif;

                } else {

                    echo "<div class='container unautrized_user_request'>You are an unauthorized user.</div>";

                }

                ?>

    </section>


    <script type="text/javascript">
        jQuery(function($) {

            $(document).on("click", '.button-hire', function (/*e*/) {

                var translater_id = jQuery(this).data('translater_id');

                var job_id = jQuery(this).data('job_id');

                var bid_id = jQuery(this).data('bid_id');


                var hire_username = jQuery(this).data('user_name');

                jQuery("#translater_id").val(translater_id);


                jQuery(".hire_job_id").val(job_id);

                jQuery("#bid_id").val(bid_id);

                jQuery("#hire_username").html(hire_username);

                jQuery("#hire_" + translater_id).click();


            });


        });


    </script>


    <!-- END: Hire Linguist model -->

<?php if (true) { ?>

    <div class="modal fade" id="hireModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

                    <h4 class="modal-title"><?php get_custom_string('Hire Freelancer'); ?> </h4>
                    <div style="text-align:right" id="hire_username"></div>

                </div>

                <div class="modal-body">

                    <form class="bidform" id="TranslaterHireForm" method="post" action='<?php echo get_permalink(); ?>'
                          novalidate="novalidate">

                        <p class="price-form-status">

                            <label for="status"><?php get_custom_string('Status'); ?></label><br>

                            <select title="Comment approved" class="form-control" id="comment_approved"
                                    name="comment_approved">

                                <option value="1"><?php get_custom_string('Hire'); ?></option>

                            </select>

                        </p>

                        <h4 class="modal-title">Hire Freelancer </h4>

                        <p class="comment-form-comment">

                            <label for="comment"><?php get_custom_string('Notes'); ?></label><br>

                            <input type="hidden" aria-required="true" name="translater_id" id="translater_id"
                                   value="<?php echo $comment ? $comment->user_id : ''; ?>">

                            <input type="hidden" aria-required="true" class="hire_job_id" name="job_id" id="job_id"
                                   value="<?php echo $post_id; ?>">

                            <input type="hidden" aria-required="true" name="bid_id" id="bid_id"
                                   value="<?php echo $comment ? $comment->comment_ID : ''; ?>">

                            <textarea title="bide note" maxlength="10000" class="form-control" aria-required="true"
                                      name="bid_note" id="bid_note"  autocomplete="off" ></textarea>

                        </p>

                        <p class="form-submit">

                            <input type="submit" class="btn blue-btn bidreplysubmit"
                                   value="<?php get_custom_string('Hire this Freelancer'); ?>" id="TranslaterHireButton"
                                   name="bidreplysubmit">

                            <!--Reply to this Linguist-->

                        </p>

                    </form>

                </div> <!-- ./modal-body -->

            </div> <!-- ./modal-content -->

        </div> <!-- ./modal-dialog -->

    </div> <!-- ./modal -->

<?php } ?>



    <!-- START:  Rating code start from here -->

    <script>



        function redirectTo(link) {
            window.location.href = link;
        }

        jQuery(function () {

            jQuery('#CompleteModel').on('hidden.bs.modal', function () {

                location.reload();

            });

        });

    </script>

    <script type="text/javascript" language="javascript">
        var pausecontent = [];
        <?php
        if (empty($tags_name_array)) {
            $tags_name_array = [];
        }
        foreach($tags_name_array as $key => $val){ ?>
        pausecontent.push('<?php echo $val; ?>');
        <?php } ?>
    </script>
    <script type="text/javascript">
        jQuery(function ($) {

            var tagApi = $(".tm-input").tagsManager({
                prefilled: pausecontent
            });

            jQuery(".tm-input").on('tm:spliced', function (e, tag) {
                console.log(tag + " was removed!");
                update_tags();
            });

            jQuery(".tm-input").on('tm:pushed', function (e, tag) {
                console.log(tag + " was pushed!");
                update_tags();
            });


            jQuery("#job_instruction_tags").typeahead({
                name: 'id',
                displayKey: 'name',
                source: function (query, process) {
                    return jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'get_custom_tags',
                        query: query
                    }, function (data) {
                        jQuery('#resultLoading').fadeOut(300);
                        data = JSON.parse(data);
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
            var data_post = jQuery("#editable_form_tags").serializeArray();

            data_post.push({'name': 'action', 'value': 'job_tags_editable'}, {'name': 'job_type', 'value': 'contest'});

            jQuery.ajax({

                type: 'POST',

                url: adminAjax.url,

                data: data_post,

                global: false,
                //code-notes js handler for job-tags now works with standardized errors
                success: function (response_raw) {

                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_raw);
                    if (response.status === true) {
                        console.log('tagger says', response);
                    } else {
                        will_handle_ajax_error('Project Tags',response.message);
                    }


                }

            });
        }

    </script>

<?php if ($job_freeze): ?>
    <script>
        jQuery(function () {


            jQuery('input,textarea,button').prop('disabled', true);
            jQuery('.tm-tag-remove,button').hide();
        });
    </script>
<?php endif; ?>
    <script>
        jQuery(function () {

            jQuery('#approve_completion_button').click(function () {
                <?php
                update_post_meta($post_id, 'project_status', 'project_completed');

                ?>
                window.location.reload();
            });

        });
    </script>
    <!-- END:  Rating code start from here -->
<?php

$b_show_dialog_open = false;
if (!isset($mstons) || empty($mstons) || !is_array($mstons)) {
    $b_show_dialog_open = false;
} else {
    for ($i = 0; $i < count($mstons); $i++) {
        $milestone = $mstons[$i];
        if ($milestone->status === 'completed' || $milestone->status === 'rejected') {
            $b_show_dialog_open = true;
            break;
        }
    }
}

if (!isset($jdata) || empty($jdata) || !is_object($jdata) || !empty($jdata->rating_by_customer)) {
    $b_show_dialog_open = false;
}

if ($b_show_dialog_open) {
    ?>
    <script>
        $(function () {
            $("a.hirebttn2[data-target='#feedbackModel']").click();
        });
    </script>
<?php } ?> ?>

<script>
    jQuery(function($) {
        //code-notes show tooltip for tags
        let tag_box = $('#job_instruction_tags') ;
        freelinguist_tag_help(tag_box,true);
    });
</script>