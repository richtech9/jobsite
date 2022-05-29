<?php
/*
    * current-php-code 2020-Oct-07
    * input-sanitized : lang,job_id
    * current-wp-template:  for freelancers doing projects
*/
global $wpdb;
$lang = FLInput::get('lang', 'en');
$job_id = FLInput::get('job_id');

//code-notes update freelancer project page here to display rating dialog

$jdata = hz_get_job_data($job_id);
if (!$jdata) {
    wp_die(__("Job Not Found: "). $job_id,__("Job Not Found"));
}

$post_id = get_the_ID();

$jtype = get_post_meta($post_id, 'fl_job_type', true);
$tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
if ($jtype == 'contest') {
    $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
} else if ($jtype == 'project') {
    $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
}

$currLingu = $jdata->author;

$auto_job_rejected_for_linguist_hours_minutes = floatval(get_option('auto_job_rejected_for_linguist_hours')) * 60;


$translator_id = get_current_user_id();

//code-notes clear red dots for this job
$red = new FLRedDot();
$red->event_user_id_role =  FreelinguistUserLookupDataHelpers::get_logged_in_role_id();
$red->project_id = $post_id;
FLRedDot::remove_red_dots($translator_id,$red);

if (in_array($translator_id, get_post_meta($post_id, 'job_freeze_user')) &&
    !empty(get_post_meta($post_id, 'job_freeze_user')))
{
    $job_freeze = true;
} else {
    $job_freeze = false;
}
?>
    <script>
        if (adminAjax) {
            adminAjax.form_keys.hz_create_milestone = '<?= FreeLinguistFormKey::create_form_key('hz_create_milestone') ?>';
            adminAjax.form_keys.hz_manage_milestone = '<?= FreeLinguistFormKey::create_form_key('hz_manage_milestone') ?>';
            adminAjax.form_keys.hz_approve_milestone = '<?= FreeLinguistFormKey::create_form_key('hz_approve_milestone')?>';
        }
    </script>

<?php


$sql_statement =
    "SELECT GROUP_CONCAT(tag_id) as tag_ids FROM wp_tags_cache_job WHERE `job_id` = $post_id AND type = $tagType";
$tags = $wpdb->get_results($sql_statement);
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

    $tags_name_array = stripslashes_deep($tags_name_array);
}

$current_translater_bid = get_all_bids_of_particular_job($translator_id);

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

$current_user_role = xt_user_role();
?>
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


    <div class="linguist-header">

        <div class="">

            <div class="freelinguist-id-display" style="" data-jid="<?= $jdata->ID?>" data-pid="<?= $post_id?>">
                <span class="small-text">
                    <?= $jdata->title ?>
                </span>
            </div>
<!--            <span class="bold-and-blocking large-text">--><?php //echo $jdata->title; ?><!--</span>-->
            <?php
            if ($jdata->job_status == 'start') {

                //code-notes chat part
                set_query_var( 'job_id', $jdata->project_id );
                set_query_var( 'to_user_id', $jdata->linguist_id );
                set_query_var( 'fl_job_id', $jdata->ID );
                set_query_var( 'job_type', 'project' );
                set_query_var( 'b_show_name', 0 );
                get_template_part('includes/user/chat/chat','button-area');

            }

            if (get_post_meta($post_id, 'project_status', true) == 'project_completed'){

            if (empty($jdata->rating_by_freelancer)) { ?>
                <a class="hirebttn2" href="#" data-toggle="modal"
                   data-target="#feedbackTranslatorModel"
                >
                    Rate Customer
                </a>

            <?php } else {
                ?>

                <div class="dash-body">

                    <h3>Rating for customer</h3>

                    <div class="item-sec" id="">
                        <?php echo convert_rating($jdata->rating_by_freelancer, 17, 'hide', $jdata->linguist_id) . ' ' .
                            stripslashes_deep($jdata->comments_by_freelancer); ?>
                    </div>
                </div>


            <?php } ?>
            <div class="break-one-line"></div>
            <p class="enhanced-text">
                <strong>Project Title:</strong>
                <?php echo stripslashes(get_post_meta($jdata->project_id, 'project_title', true)); ?>
            </p>

            <p class="enhanced-text">
                <strong>Expected delivery date:</strong>
                <?php echo date_formatted(get_post_meta($jdata->project_id, 'job_standard_delivery_date', true)); ?>
            </p>

            <?php $budget = get_post_meta($jdata->project_id, 'estimated_budgets', true); ?>

            <p class="enhanced-text">
                <strong>Budget:</strong>
                <?php echo budget_formatter($budget); ?>
            </p>

            <p class="enhanced-text">
                <strong>Project Description: </strong>
                <?php echo stripslashes(get_post_meta($jdata->project_id, 'project_description', true)); ?>
            </p>

            <p class="enhanced-text">
                <strong>Skills:</strong>
                <?php if (isset($tags_name_array)) {
                    echo implode(',', $tags_name_array);
                } ?>
            </p>

        </div>

    </div>


    <!-- END: Bidding Statement -->


    <div class="h1-title"><span class="bold-and-blocking larger-text">Delivery Instructions</span>

        <?php


        $inst_filesPP = $wpdb->get_results("SELECT * FROM wp_files where post_id = $jdata->project_id AND type = ".FLWPFileHelper::TYPE_INSTRUCTION_FILE);


        if ($inst_filesPP) {

            echo '<ul>';

            foreach ($inst_filesPP as $rowPP) {
                ?>
                <li>
                    <div class="col-md-12">
                        <div class="doc-name">
                            <!-- code-notes [download]  new download line -->
                            <div class="freelinguist-download-line">

                                <span class="freelinguist-download-name">
                                    <i class="text-doc-icon larger-text"></i>
                                    <span class="freelinguist-download-name-itself enhanced-text">
                                        <?= $rowPP->file_name ?>
                                    </span>
                                </span> <!-- /.freelinguist-download-name -->

                                <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                                   data-job_file_id = "<?= $rowPP->id ?>"
                                   download = "<?= $rowPP->file_name ?>"
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

        ?>

    </div>

    <div class="instruct-file">

        <span class="blocking-and-black large-text">Instruction Files</span>

        <?php


        $inst_files = $wpdb->get_results("SELECT * FROM wp_files where post_id = $jdata->project_id AND type = ".FLWPFileHelper::TYPE_POST_DETAILS);


        if ($inst_files) {

            echo '<ul>';

            foreach ($inst_files as $row) {
                ?>
                <li>
                    <!-- code-notes [download]  new download line -->
                    <div class="freelinguist-download-line">

                        <span class="freelinguist-download-name">
                            <span class="freelinguist-download-name-itself enhanced-text">
                                <?= $row->file_name ?>
                            </span>
                        </span> <!-- /.freelinguist-download-name -->

                        <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                           data-job_file_id = "<?= $row->id ?>"
                           download = <?= $row->file_name ?>
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


    <div style="clear: both"></div>


    <div class="milestone-payment">

        <div class="milestone-top"><h3>Milestone Payments</h3>
            <?php if (!$job_freeze): ?>
            <a class="hirebttn2 " href="#" data-toggle="modal" data-target="#createMilestone"><i
                        class="fa fa-plus"></i> Create Milestone</a></div>
        <?php endif;
        ?>

        <div class="milestone-payment-sec">

            <ul>

                <li><label class="large-text"><em>$</em> 0.00</label><br><span class="">In Progress <i
                                class="fa fa-info-circle enhanced-text"></i></span></li>

                <li><label class="large-text"><em>$</em> <?php echo fl_get_job_pay_status($jdata->ID, 'completed'); ?>
                    </label><br><span class="">Released <i class="fa fa-info-circle enhanced-text"></i></span>
                </li>



            </ul>

        </div>

        <div class="description-sec table-responsive">

            <table width="100%" class="ms_data_tbl freelinguist-source-ind-transaltor enhanced-text">

                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>

                <?php


                $mstons = $wpdb->get_results(
                        "SELECT * , UNIX_TIMESTAMP(rejected_at) as rejected_at_ts FROM wp_fl_milestones WHERE job_id = $jdata->ID ORDER BY ID ASC ");

                $class = '';

                $i = 1;

                if ($mstons) {

                    foreach ($mstons as $key => $row) {

                        $class = (get_current_user_id() == $row->author) ? "current_user" : "";

                        $class .= ' fl_status_' . $row->status;

                        echo '<tr class="' . $class . '" id="milestone-' . $row->ID . '" data-status="' . $row->status . '">

					<td>' . $i . '. </td>

					<td width="220px">' . stripslashes_deep($row->content) . '</td>

					<td>$' . $row->amount . '</td>

					<td>' . $row->delivery_date . '</td>

					<td class="released-stat">';

                        if ($row->status == "approve") {

                            //Check if this milestone created by linguist or customer

                            if ($row->dispute == 0) {

                                if (get_current_user_id() != $row->author) {

                                    echo '<span>In Progress</span> <br>';

                                } else {

                                    echo '<span>In Progress</span> ';

                                }
                                echo '<button class="action-btns approve-rejection-btn button-small2 " '.
                                    'onclick="return hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'request_completion\');">'.
                                    '<i class="fa fa-check"></i>Request Completion</button>';

                                echo '<button class="action-btns reject-btn bg-secondary button-small2 " '.
                                    ' onclick="return hz_manage_milestone(' . $row->ID .
                                    ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'approved_rejection\');">'.
                                    '<i class="fa fa-times"></i> Cancel Job</button>';


                            } else {

                                echo '<button disabled="disabled" style="opacity:0.5;pointer-events:none;" '.
                                    'class="action-btns hire-mediator-btn button-small2 ">'.
                                    '<i class="fa fa-user-plus"></i> Already requested for mediator </button>';

                            }


                        } elseif ($row->status == "reject") {


                            if ($row->dispute != '1') {

                                echo '<button class="action-btns approve-rejection-btn button-small2" '.
                                    'onclick="return hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'request_completion\');">'.
                                    '<i class="fa fa-check"></i>Request Completion</button></br>';

                                echo '<p class="enhanced-text">Your job was rejected by customer.  You have up to ' .
                                    get_option('auto_job_rejected_for_linguist_hours') . ' hours to choose one of the following.. '.
                                    '</p>';

                                echo '<p class="enhanced-text" id="demo_time_' . $row->ID . '"></p>';

                                echo '<p class="enhanced-text">' . $row->rejection_txt . '</p>';


                                echo '<button class="action-btns hire-mediator-btn button-small2" '.
                                    'onclick="return wrap_wallet_hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'hire_mediator\');">'.
                                    '<i class="fa fa-user-plus"></i> Hire an independent mediator</button>';

                                echo '<button class="action-btns approve-rejection-btn button-small2 " id="auto_reject_' .
                                    $row->ID . '" onclick="return hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'approved_rejection\');">'.
                                    '<i class="fa fa-check"></i>Approve Rejection</button>';


                            } else {

                                echo '<button disabled="disabled" style="opacity:0.5;pointer-events:none;" '.
                                    'class="action-btns hire-mediator-btn button-small2">'.
                                    '<i class="fa fa-user-plus"></i> Already requested for mediator </button>';

                            }

                            if ($row->rejection_requested == "1") {

                                ${'new_date' . $row->ID} = (intval($row->rejected_at_ts) + (60 * $auto_job_rejected_for_linguist_hours_minutes))*1000;

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
                                            + minutes[' . $row->ID . '] + "m " + seconds[' . $row->ID . '] +
                                             "s  left until auto approval of rejection";
                                         }
                                        if (distance[' . $row->ID . '] < 0) {
                                            clearInterval(x[' . $row->ID . ']);
                                            adminAjax.flags.b_ignore_milestone_cancel_dialog = true;
                                            jQuery("#auto_reject_' . $row->ID . '").click(); 
                                            let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                                            if (demo_time) {
                                                demo_time.innerHTML = "EXPIRED"; 
                                            }
                                            
                                        }
										
									}, 5000);
									</script>';
                            }


                        } elseif ($row->status == "request_completion") {


                            if ($row->dispute != '1') {

                                echo '<p class="enhanced-text">You requested for completion to customer.';

                                if ($row->rejection_requested == "1") {
                                    echo '  You can choose options below. ';
                                }
                                echo '</p>';

                                echo '<button class="action-btns reject-btn bg-secondary button-small2 " '.
                                    'onclick="return hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'approved_rejection\');">'.
                                    '<i class="fa fa-times"></i> Cancel Job</button>';

                                echo '<button class="action-btns approve-rejection-btn button-small2 " '.
                                    'onclick="return hz_manage_milestone(' . $row->ID .
                                    ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'request_completion\');">'.
                                    '<i class="fa fa-check"></i>Request Completion</button></br>';


                                if ($row->rejection_requested == "1") {

                                    echo '<p class="enhanced-text">Your job was rejected by customer.  You have up to ' .
                                        get_option('auto_job_rejected_for_linguist_hours') . ' hours to choose one of the following.. </p>';

                                    echo '<p class="enhanced-text" id="demo_time_' . $row->ID . '"></p>';
                                    echo '<button class="action-btns approve-rejection-btn button-small2" id="auto_reject_' .
                                        $row->ID . '" onclick="return hz_manage_milestone(' . $row->ID .
                                        ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'approved_rejection\');">'.
                                        '<i class="fa fa-check"></i>Approve Rejection</button>';

                                    echo '<button  class="action-btns hire-mediator-btn button-small2 " '.
                                        'onclick="return wrap_wallet_hz_manage_milestone(' .
                                        $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'hire_mediator\');">'.
                                        '<i class="fa fa-user-plus"></i> Hire an independent mediator</button>';
                                }

                            }

                            if ($row->rejection_requested == "1") {

                                ${'new_date' . $row->ID} = (intval($row->rejected_at_ts) +
                                        (60 * $auto_job_rejected_for_linguist_hours_minutes))*1000;

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
                                            + minutes[' . $row->ID . '] + "m " + seconds[' . $row->ID . '] +
                                             "s  left until auto approval of rejection";
                                        }
    
                                        if (distance[' . $row->ID . '] < 0) {
                                            clearInterval(x[' . $row->ID . ']);
                                            adminAjax.flags.b_ignore_milestone_cancel_dialog = true;
                                            jQuery("#auto_reject_' . $row->ID . '").click(); 
                                            let demo_time = document.getElementById("demo_time_' . $row->ID . '");
                                            if (demo_time) {
                                                demo_time.innerHTML = "EXPIRED"; 
                                            }
                                        }
									}, 5000);
									</script>';
                            }


                        } elseif ($row->status == "request_revision") {


                            // Check {IF} this milestone is created by linguist {ELSE} show Hire mediator or Approve Rejection button for the linguist


                            if ($row->dispute != '1') {

                                $project_author_id = get_post_field('post_author', $row->project_id);
                                $author_name = get_da_name($project_author_id);
                                echo '<p class="enhanced-text"> Revision Request by ' . $author_name . ': ' . $row->revision_text . '</p>';


                                echo '<button class="action-btns reject-btn bg-secondary button-small2 " '.
                                    'onclick="return hz_manage_milestone(' . $row->ID .
                                    ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'approved_rejection\');">'.
                                    '<i class="fa fa-times"></i> Cancel Job</button>';

                                echo '<button class="action-btns approve-rejection-btn button-small2 " '.
                                    'onclick="return hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'request_completion\');">'.
                                    '<i class="fa fa-check"></i>Request Completion</button>';


                                if ($row->rejection_requested == "1") {

                                    echo '<p class="enhanced-text">Your job was rejected by customer.  You have up to ' .
                                        get_option('auto_job_rejected_for_linguist_hours') .
                                        ' hours to choose one of the following.. '.
                                        '</p><br>';

                                    echo '<p class="enhanced-text" id="demo_time_' . $row->ID . '"></p>';

                                    echo '<button  class="action-btns hire-mediator-btn button-small2" '.
                                        'onclick="return wrap_wallet_hz_manage_milestone(' .
                                        $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'hire_mediator\');">'.
                                        '<i class="fa fa-user-plus"></i> Hire an independent mediator</button>';

                                    echo '<button class="action-btns approve-rejection-btn button-small2" id="auto_reject_' . $row->ID .'"'.
                                        ' onclick="return hz_manage_milestone(' .
                                        $row->ID . ', \'Do you want to Approve this rejection?\', \'Yes\', \'No\', \'approved_rejection\');">'.
                                        '<i class="fa fa-check"></i>Approve Rejection</button>';

                                }


                            }

                            if ($row->rejection_requested == "1") {
                                //code-notes milliseconds
                                ${'new_date' . $row->ID} = (intval($row->rejected_at_ts) +
                                        (60 * $auto_job_rejected_for_linguist_hours_minutes))*1000;

                                ?>

                               <script>
									countDownDate[<?= $row->ID ?>] = new Date(<?= ${'new_date' . $row->ID} ?>).getTime();

									x[<?= $row->ID ?>] = setInterval(function() {
											
										now[<?= $row->ID ?>] = new Date().getTime();

                                        distance[<?= $row->ID ?>] = countDownDate[<?= $row->ID ?>] - now[<?= $row->ID ?>];

                                        days[<?= $row->ID ?>] = Math.floor(distance[<?= $row->ID ?>] / (1000 * 60 * 60 * 24));
                                        hours[<?= $row->ID ?>] = Math.floor((distance[<?= $row->ID ?>] % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                        minutes[<?= $row->ID ?>] = Math.floor((distance[<?= $row->ID ?>] % (1000 * 60 * 60)) / (1000 * 60));
                                        seconds[<?= $row->ID ?>] = Math.floor((distance[<?= $row->ID ?>] % (1000 * 60)) / 1000);

                                        let demo_time = document.getElementById("demo_time_<?= $row->ID ?>");
                                        if (demo_time) {
                                            demo_time.innerHTML = days[<?= $row->ID ?>] + "d " + hours[<?= $row->ID ?>] + "h "
                                                + minutes[<?= $row->ID ?>] + "m " + seconds[<?= $row->ID ?>] +
                                                "s  left until auto approval of rejection";
                                        }

                                        if (distance[<?= $row->ID ?>] < 0) {
                                            clearInterval(x[<?= $row->ID ?>]);
                                            adminAjax.flags.b_ignore_milestone_cancel_dialog = true;
                                            jQuery("#auto_reject_<?= $row->ID ?>").click();
                                            let demo_time = document.getElementById("demo_time_<?= $row->ID ?>");
                                            if (demo_time) {
                                                demo_time.innerHTML = "EXPIRED";
                                            }
                                        }
									}, 5000);
									</script>
                                <?php
                            }



                        } elseif ($row->status == "dispute") {

                            echo '<span class="status-label status-dispute"><i class="' .
                                freelinguist_fa_icons($row->status) . '"></i>Disputed</span>';

                        } elseif ($row->status == "requested") {


                            // Check {IF} this milestone is created by linguist {ELSE} show accept or reject button to the linguist

                            if (get_current_user_id() == $row->author) {

                                echo "<span>Awaiting response from Customer.</span>";

                            } else {

                                echo '<button class="action-btns approve-btn button-small2 " onclick="return hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Accept this milestone?\', \'Yes\', \'No\', \'approve\');">'.
                                    '<i class="fa fa-check"></i>Accept</button>';

                                echo '<button class="action-btns reject-btn bg-secondary button-small2 " onclick="return hz_manage_milestone(' .
                                    $row->ID . ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'reject\');">'
                                    .'<i class="fa fa-times"></i> Cancel Job</button>';

                            }

                        } elseif ($row->status == "approved_rejection") {

                            echo 'Cancelled by Freelancer.';

                        } elseif ($row->status == "rejected") {

                            echo 'Rejection by Customer.';

                        } elseif ($row->status == "hire_mediator") {

                            echo '<p class="enhanced-text">Mediation in progress.</p>';
                            echo '<button class="action-btns reject-btn bg-secondary button-small2 " '.
                                'onclick="return hz_manage_milestone(' .
                                $row->ID . ', \'Do you want to Reject this milestone?\', \'Yes\', \'No\', \'approved_rejection\');">'.
                                '<i class="fa fa-times"></i> Cancel Job</button>';

                        } elseif ($row->status == "completed") {

                            echo 'Milestone Completed, payment has been released.';

                        }


                        echo '</td></tr>';

                        $i++;

                    }

                }

                ?>


            </table>

        </div>

        <div style="clear: both;"></div>
        <?php

        if ($mstons) {
            $j = 1;
            ?>
            <div class="col-md-12">&nbsp;</div>
            <div class="dash-body">
                <span class="bold-and-blocking large-text">Message History </span>
                <table class="ms_data_tbl enhanced-text">
                    <?php
                    foreach ($mstons as $key => $row) {

                        $messages = $wpdb->get_results("SELECT * FROM wp_message_history WHERE milestone_id = $row->ID order by id asc");

                        if ($messages) {
                            foreach ($messages as $k => $message) {
                                echo '<tr data-msg_id="' . $message->id . '"><td><b>#' .
                                    $row->number . '</b> ' . $message->created_at . ': ' . $message->message . '</td></tr>';

                            }
                        }
                        /*********/
                        $j++;


                    }
                    ?>
                </table>
            </div>
            <?php
        }

        ?>


    </div>
    <?php

}
?>

    <div class="dash-body">

        <h3>Files Delivered</h3>

        <div class="item-sec" id="order_files_content">


            <?php

            $tfiles = $wpdb->get_results("SELECT * FROM wp_files WHERE `job_id` = $jdata->ID AND type = ".FLWPFileHelper::TYPE_FREELANCER_UPLOAD);

            if ($tfiles) {

                echo '<ul class="document-row">';

                foreach ($tfiles as $fl) {
                    ?>
                    <li>
                        <div class="floatleft  enhanced-text" style="width: 90%;display: inline-block">
                            <!-- code-notes freehand adjusting width to fit new file line-->

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
                        <div class="floatright">
                            <a href="#"
                               class="cross-icon large-text"
                               onclick="return single_remove_selected(this,<?= $fl->id ?>)" ></a>
                        </div>
                    </li>
                    <?php

                }

                echo '</ul>';

            } else {

                echo '<ul class="document-row"></ul>';

            }

            ?>


            <?php if (!$job_freeze): ?>
                <div class="upload-file regular-text"><i class="fa fa-upload enhanced-text"></i>Upload Files<input
                            multiple="" name="files[]" id="atc_files_order_by_linguist"
                            class="files-data hz_order_process" type="file" data-jid="<?php the_ID(); ?>"

                            data-indjob="<?php echo $jdata->ID; ?>" ></div>

            <?php endif; ?>

            <br>

            <!--Progress Bar-->

            <div id="progress" class="progress" style="margin-top: 10px;">
                <div class="progress-bar progress-bar-success"></div>
            </div>

            <div class="percent"></div>

            <!-- The container for the uploaded files -->

        </div>

    </div><!--//dash-body-->


    <div class="review-listing job_dtls">

        <div class="placed-bids">

            <div class="bid-header">

                <i aria-hidden="true" class="fa fa-user enhanced-text"></i>
                <label><?php get_custom_string('BIDDING STATEMENT'); ?></label>

            </div>

            <ul class="placed-bid-list">

                <?php

                if (isset($current_translater_bid[0]->comment_approved) && $current_translater_bid[0]->comment_approved == 1) {

                    ?>

                    <li>

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

                            <p class="enhanced-text"
                               id="bid_statement"><?php echo stripslashes(stringTrim($current_translater_bid[0]->comment_content)); ?></p>

                            <p class="enhanced-text" id="bid_price">
                                <?php
                                echo '$' . $bid_price;
                                ?>
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

        </div>
        <div class="linguist-footer no-pad">

            <?php

            if ($current_user_role == 'translator') {

                if (true) {

                    if (true &&
                        true &&
                        true)
                    { ?>


                        <!-- Start: Model view(PLace Bid)-->

                        <button id="placebidbutton" data-target="#placeBidModel" data-toggle="modal"
                                class="btn btn-green button-normal red-btn red-background-white-text regular-text"
                                style="width: 158px;"
                        >
                            <?php get_custom_string(
                                    !isset($current_translater_bid[0]->comment_content) ?
                                        'Place Bid' :
                                        'Update Bid');
                            ?>
                        </button>



                        <div role="dialog" id="placeBidModel" class="modal fade">

                            <div class="modal-dialog">

                                <!-- Modal content-->

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <button data-dismiss="modal" class="close huge-text" type="button">×</button>

                                        <h4 class="modal-title"><?php get_custom_string('Apply to this job'); ?></h4>

                                    </div>

                                    <div class="modal-body">

                                        <div id="alert_message_model"></div>

                                        <form class="comment-form" id="commentform"
                                              onsubmit="return place_the_bid(this)" method="post"
                                              action="<?php echo get_permalink(); ?>" novalidate="novalidate"><p
                                                    class="comment-form-comment">
                                                <label for="bidPrice"><?php get_custom_string('Bid Price'); ?></label>

                                                <input title="Bid Price" type="number" class="form-control"
                                                       name="bidPrice" min="1" value="<?php echo $bid_price; ?>">
                                                <input type="hidden"
                                                       value="<?php if (isset($current_translater_bid[0]->comment_ID)) {
                                                           echo $current_translater_bid[0]->comment_ID;
                                                       } ?>" name="comment_ID">

                                            </p>

                                            <p class="comment-form-comment">

                                                <label for="comment"><?php get_custom_string('Notes'); ?></label><br>

                                                <textarea maxlength="10000" class="form-control" style="height:200px"
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
                                                       class="btn blue-btn" id="submit" name="submit">

                                                <input type="hidden" id="comment_post_ID"
                                                       value="<?php echo get_the_ID(); ?>" name="comment_post_ID">

                                                <input type="hidden" id="" value="<?= $lang ?>" name="lang">

                                                <input type="hidden" value="0" id="comment_parent"
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

                } else {

                        echo '<span class="removed_msg  enhanced-text">' . get_custom_string('You have rejected this job') . '.</span>';

                }

            } ?>

        </div>


    </div>
    <div class="review-listing job_dtls">
        <div class="bidding-other comments_lists">

            <div class="title-bg">

                <h3><i class="fa fa-user" aria-hidden="true"></i> Message</h3>

            </div>

            <div class="hz_discussion_row">

                <?php
                echo hz_fl_discussion_list_both($post_id, get_current_user_id(), $currLingu); ?>

            </div>

            <div class="message-sec text-box">

                <form id="contest_discussion">

                    <textarea title="comment" required name="comment"  autocomplete="off" ></textarea>

                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

                    <input type="hidden" name="comment_to" value="<?php echo $currLingu; ?>">

                    <input type="submit" title="Contact customer" class="red-btn enhanced-textenhanced-text"
                           value="Contact Customer">

                </form>

            </div>

        </div>
    </div>


<?php if ($job_freeze): ?>
    <script>
        jQuery(function () {
            jQuery('.middle-content input,.middle-content textarea,.middle-content button').prop('disabled', true);
            jQuery('.middle-content .tm-tag-remove,.middle-content button').hide();
        });
    </script>
<?php endif;

$b_show_dialog_open = false;
if (!isset($mstons) || empty($mstons) || !is_array($mstons)) {
    $b_show_dialog_open = false;
} else {
    for ($i = 0; $i < count($mstons); $i++) {
        $milestone = $mstons[$i];
        if (($milestone->status === 'completed' || $milestone->status === 'rejected')) {
            $b_show_dialog_open = true;
            break;
        }
    }
}

if (!isset($jdata) || empty($jdata) || !is_object($jdata) || !empty($jdata->rating_by_freelancer)) {
    $b_show_dialog_open = false;
}


if ($b_show_dialog_open) {
    ?>
    <script>
        $(function () {
            $("a.hirebttn2[data-target='#feedbackTranslatorModel']").click();
        });
    </script>
<?php }
