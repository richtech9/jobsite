<?php/*Customer Dashboard File*//** current-php-code 2020-Oct-11* input-sanitized : lang,project_status,submit,type* current-wp-template:  Customer "My Projects" Dashboard*/if(!defined('WPINC')){die;}$type = FLInput::get('type');$submit = FLInput::get('submit');$project_status = FLInput::get('project_status');$lang = FLInput::get('lang','en');global $wpdb;$current_user_id = (int)get_current_user_id();$table_content = $wpdb->prefix.'linguist_content';$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;$paged = (int)$paged;if ($paged < 1) { $paged = 1;}$project_status_submitted="";if($submit){	$project_status_submitted = $project_status;    /*     * Start search for the customer's projects and contests. The search will be for posts that have type job and are owned by this user          * If searching for working, then select all owned jobs that either have no project status or one that has the word 'working' in the status          * If searching for completed then select all owned jobs that have the word 'completed' in the status          * If searching for others (besides working or completed), then select all owned jobs that do not have the word 'completed' or 'working' in the status          * if searching for all, then select all owned jobs          */}    $post_type_constant = FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT;    $post_status_constant = FLPostLookupDataHelpers::POST_DATA_STATUS_PUBLISH;    $working_status_flag = FLPostLookupDataHelpers::POST_DATA_NEW_STATUS_WORKING;    $no_status_flag = FLPostLookupDataHelpers::POST_DATA_NEW_STATUS_NONE;    $completed_status_flag = FLPostLookupDataHelpers::POST_DATA_NEW_STATUS_COMPLETED;    $post_id_per_page = 20;    $post_id_offset = ($paged -1) * $post_id_per_page;    switch($project_status_submitted) {        case 'all': {            //if searching for all, then select all owned jobs            $post_id_search_status = '';            break;        }        case 'working': {            //If searching for working, then select all owned jobs that either have no project status or one that has the word 'working' in the status            $post_id_search_status = "AND ( (project_new_status = $working_status_flag) OR (project_new_status = $no_status_flag)) ";            break;        }        case 'completed': {            //If searching for completed then select all owned jobs that have the word 'completed' in the status            $post_id_search_status = "AND (project_new_status = $completed_status_flag) ";            break;        }        case 'others': {            //If searching for others (besides working or completed),            // then select all owned jobs that do not have the word 'completed' or 'working' in the status            $post_id_search_status = "AND ( project_new_status NOT IN ($working_status_flag,$completed_status_flag)) ";            break;        }        default: {            //if don't know, then do not filter            $post_id_search_status = '';            if (!empty($post_id_search_status)) {                will_send_to_error_log("Unexpected verb in project_status_submitted dashboard customer (defaulting to no filters); ",                    $project_status_submitted, false, true);            }            break;        }    }    $sql_count_post_ids =        "	 SELECT count(post_id) as da_count            FROM wp_fl_post_data_lookup             WHERE                  post_status = $post_status_constant AND                   post_type = $post_type_constant AND                   author_id = $current_user_id                  $post_id_search_status            ;            ";    $da_total_job_count = $wpdb->get_var($sql_count_post_ids);//    will_log_on_wpdb_error($wpdb,'count result');    $total_pages = (int)ceil($da_total_job_count/$post_id_per_page);    $query_part = $_SERVER['QUERY_STRING'];    $url_template = get_site_url() . '/dashboard/page/%page%/?'.$query_part;    $sql_for_post_ids = "			SELECT post_id FROM wp_fl_post_data_lookup			WHERE                  post_status = $post_status_constant AND                  post_type = $post_type_constant AND                  author_id = $current_user_id                  $post_id_search_status                             ORDER BY post_id desc            LIMIT $post_id_per_page OFFSET $post_id_offset;                ";//    will_send_to_error_log('SQL I think is being passed',$sql_for_post_ids);//    will_log_on_wpdb_error($wpdb,'paged result');    $result_post_ids = $wpdb->get_results($sql_for_post_ids);//    will_send_to_error_log('results of post ids in customer dashboard',$result_post_ids);//   will_send_to_error_log("SQL for customer dashboard ids is ",$wpdb->last_query);    if ($wpdb->last_error) {        $result_post_ids = [];        will_log_on_wpdb_error($wpdb);    }    if ($result_post_ids === false) {        $result_post_ids = [];        will_send_to_error_log("Unknown error getting post ids for the customer dashboard search ");    }    if (empty($result_post_ids)) {        $result_post_ids = [];        //will_send_to_error_log('$result_post_ids is empty ');    }    $array_post_ids = [];    foreach ($result_post_ids as $res_id) {        $array_post_ids[] = $res_id->post_id;    }    if (empty($array_post_ids)) {        $array_post_ids[] = -1; //insert dummy id so the wp_query function does not dump all    }//    will_send_to_error_log('$array_post_ids',$array_post_ids);    $args = [        'post_type' 		=> 'job',        'post__in'      => $array_post_ids,        'posts_per_page' => -1    ];//    will_send_to_error_log('args in customer dashboard',$args);$loop = new WP_Query( $args ); //code-notes switching out the args in the wp query?><style>		@media (max-width: 767px){	#datatable thead {	    display: none;	}	.rr table tbody tr td.thrd-td span.tdjob_title{		width: auto !important;	}	.rr table tbody tr td.thrd-td h5 strong{		width: auto !important;	}		#datatable tbody td:nth-child(2):before{		content: "Post Date";	}	#datatable tbody td:nth-child(3):before{		content: "Project";	}	#datatable tbody td:nth-child(4):before{		content: "Description";	}	#datatable tbody td:nth-child(5):before{		content: "Price";	}	#datatable tbody td:nth-child(6):before{		content: "Status";	}	 #datatable tbody td::before {	    background-color: #e5eef3;	    border-right: 1px solid #c8d5dc;	    bottom: 0px;	    color: #000000;	    content: "";	    /* replaced fontsize 12 */	    left: 0px;	    padding: 13px 7px;	    position: absolute;	    top: 0px;	    width: 130px;	}	#datatable tbody td:first-child {    border-top: 1px solid #c8d5dc;	}	#datatable tbody td {	    min-height: 50px;	}	#datatable tbody td {	    display: block;	    float: left;	    padding-left: 140px;	    position: relative;	    width: 100%;	    border-bottom: 1px solid #c8d5dc;	}}/*code-notes temp css to make the table full width*/table.freelinguist-customer-dashboard {    width: 100% !important;    max-width: 1160px;}</style>		<section class="middle-content rr freelinguist-customer-project-dashboard">			<div class="container">				<div class="own-job-dashboard">										<div class="job-table full-width">					<div class="tabblee table-responsive enhanced-text">					   <table class="freelinguist-customer-dashboard" id="datatable">					      <thead>					         <tr>								<th class="hidden enhanced-text">Id</th>					            <th class="first-th enhanced-text">Post Date</th>					            <th class="snd-th enhanced-text">Project</th>					            <th class="trd-th enhanced-text">Description</th>													            <th class="enhanced-text">Price</th>					            <th class="forth-th" style="width: 20%">Status</th>					         </tr>					      </thead>					      <tbody>						<?php						global $wpdb;						$counter = 0;							while( $loop->have_posts() ) : $loop->the_post();							$counter ++;							$job_id 	= get_the_ID();							$job_tbl 	= hz_is_linguist_asg( $job_id, get_current_user_id() );							$ptype 		= get_post_meta( $job_id, 'fl_job_type', true );                            $tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;                            if($ptype == 'contest'){                                $tagType = FreelinguistTags::CONTEST_TAG_TYPE;                            } else if($ptype == 'project'){                                $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT                            }							$job_des = get_post_meta($job_id,'project_description',true);							$title = get_post_meta( $job_id, 'project_title', true );														$rowcur_jbid = $wpdb->get_results( "SELECT * FROM wp_fl_job WHERE  `project_id` = '".$job_id."' AND job_status='start' " );														$link = '';														if($ptype=='contest'){								$link = get_the_permalink().'&action=participants-proposals';							}else{								if(count($rowcur_jbid)>0){									$link = get_the_permalink()."&job_id=".$rowcur_jbid[0]->title;								}else{									$link = get_the_permalink();								}							}														$tags = $wpdb->get_results(                                "SELECT GROUP_CONCAT(tag_id) as tag_ids FROM wp_tags_cache_job WHERE `job_id` = $job_id AND type = $tagType" );							$tags_name_array= array();							foreach($tags as $k=>$v){								$post_tags_array =explode(",",$v->tag_ids);								foreach($post_tags_array as $v1){								    if (empty($v1)) {continue;}									$interest_tags = $wpdb->get_results( "SELECT * FROM wp_interest_tags WHERE `id` = $v1" );									foreach($interest_tags as $k2=>$v2){										$tags_name_array[] = $v2->tag_name;									}								}							}							?>					         <tr data-job-id="job-<?php echo $job_id; ?>">								<td class="hidden  enhanced-text">                                    <?php echo $job_id; ?>                                </td>					            <td class="scnd-td enhanced-text">									<?php echo get_the_date(); ?><br>									<em><?php 									if($ptype=='contest'){										echo 'Competition';									}else{										echo $ptype;									}									?></em>								</td>								<td  class="scnd-td enhanced-text">					               <p>                                       <a style="color: #666;" href="<?php echo $link; ?>">                                           <span class="break-long-words">                                               <?php echo stripslashes(mb_strimwidth($title, 0, 100, '...')); ?>                                           </span>                                       </a>                                   </p>					               <em>                                       <?php echo 'Delivery: '.get_post_meta( $job_id, 'job_standard_delivery_date', true ); ?>                                   </em>					            </td>					            <td  class="thrd-td enhanced-text">					               <a style="color: #666;" href="<?php echo $link; ?>">                                       <p>                                           <span class="break-long-words">                                              <?php echo stripslashes(mb_strimwidth($job_des, 0, 150, ' ...')); ?>                                           </span>                                       </p>                                       <h5>                                           <span class="tdjob_title">                                               <?php                                               if(isset($tags_name_array)){                                                    echo implode(',',$tags_name_array);                                               }                                               ?>                                           </span>                                       </h5>					               	</a>					            </td>																<td  class="thrd-td enhanced-text">					               	<strong>                                        $<?php echo str_replace("_", "-", get_post_meta( $job_id, 'estimated_budgets', true ) ); ?>                                    </strong>					            </td>					            <?php                                    $project_status = get_post_meta($job_id, 'project_new_status', true);                                    if ( !empty($project_status) ) {                                      $currentStatus = $project_status;                                      $currentBtn = $project_status;                                    }else{                                       $currentStatus = 'Working';                                       $currentBtn = 'Working';                                    }                                    //code-notes if this project has a a meta 'content-cancel-status', then append this to the regular status                                    $cancel_status = get_post_meta($job_id,'content-cancel-status',true) ;                                    if ($cancel_status) {                                        $currentStatus .= ' : ' . $cancel_status;                                    }                                    //code-notes Show cancelled on freelancer dashboard page for status                                    $b_is_contest_cancelled = get_post_meta($job_id,'is_cancellation_approved') ? true : false;                                    if ($b_is_contest_cancelled && $cancel_status) {                                       $currentStatus =   $cancel_status;                                    }					            ?>					            <td class="frth-td enhanced-text will_here">					            	<a style="color: #666;" href="<?php echo $link; ?>">					            	    <?php echo $currentStatus; ?>                                        <?php                                        $red_dot_type = FLRedDot::TYPE_PROJECTS;                                        if ($ptype === 'contest') {                                            $red_dot_type = FLRedDot::TYPE_CONTESTS;                                        }                                        ?>                                        <?= FLRedDot::generate_dot_html_for_user(                                            [$red_dot_type],$job_id                                        )                                        ?>					            	</a>					            </td>					         </tr>			                <?php endwhile; ?>							<?php wp_reset_postdata(); ?>    						      </tbody>					   </table>                        <?php                        freelinguist_print_pagination_bar($paged,$total_pages,$url_template,'bottom');                        ?>					</div>	<!-- /.table-responsive-->                </div> <!-- /.job-table -->            </div> <!-- /.own-job-dashboard -->        </div> <!-- /.container -->    </section> <!-- /.middle-content --><?php //} if( $loop->have_posts() ){}else{  ?><section class="noprojects_sec">	<div class="container">		<br>		<br>		<br>		<br>			<h3 style="text-align: center">No projects. Start by creating a				<a href="<?php echo get_site_url().'/order-process/?lang=en'; ?>" class="add-job-btn add-job" name="<?= $lang ?>" id="add_new_job"><i></i><?php get_header_menu_string('New Project'); ?>				</a>			</h3>		<br>		<br>		<br>		<br>	</div> <!-- /.container --></section><?php } ?>	  <style type="text/css">section.noprojects_sec {    min-height: 600px !important;}</style><script>jQuery(function($) {			var html = '<div id="form_query">' +        '<form id="filter_project_status" action="" method="post"  style="width:45%;">'+        '<table><tr>' +        '<td>' +        '<select name="project_status" class="regular-text" >' +        '<option value="all" <?php echo ($project_status_submitted == 'all') ? 'selected' : ''; ?>>All Job Posting</option> '+        '<option value="working" <?php echo ($project_status_submitted == 'working') ? 'selected' : ''; ?>>Working</option>'+        '<option value="completed" <?php echo ($project_status_submitted == 'completed') ? 'selected' : ''; ?>>Completed</option>'+        '<option value="others" <?php echo ($project_status_submitted == 'others') ? 'selected' : ''; ?>>Others</option>'+        '</select>'+        '</td>'+        '<td>'+        '<input type="submit" name="submit" class="signin-bttn login-btn-n regular-text" value="Sort">'+        '</td>'+        '</tr></table>'+        '</form></div>';		if ($(window).width() > 767) {	jQuery('#datatable').DataTable( {        "paginate": false,        "searching": false,        "bInfo": false,        "order": [[ 0, "desc" ]],		"language": {            "lengthMenu": "_MENU_ <table style='width:50%;float:left;'>" +            "<tr><td class='records-per-page regular-text'>records per page</td></tr></table>",                    },        "fnInitComplete": function(){					   jQuery('.own-job-dashboard select').css({"float":"left"});		   jQuery('#datatable_length').css({"width":"100%"});		   jQuery('#datatable_length label').css({"padding":"10px 10px","width":"40%","float":"left"});           jQuery('#datatable_length').append(html);		            } ,		 "drawCallback": function( /*settings */) {			 jQuery('#form_query').html(html);			  			 show_milestone_review_time($);		}	} );  }		   } );function show_milestone_review_time($){	jQuery('.demo_time_milestone_review').each(function(){			   		var milestone_id = jQuery(this).data('milestone_id');						var countDownDate = new Date(parseInt(jQuery(this).data('new_date'))).getTime();				console.log(jQuery(this).data('new_date'));		// Update the count down every 1 second		var x = setInterval(function() {		    // Get todays date and time            var now = new Date().getTime();            // Find the distance between now and the count down date            var distance = countDownDate - now;            // Time calculations for days, hours, minutes and seconds            var days = Math.floor(distance / (1000 * 60 * 60 * 24));            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));            // Display the result in the element with id="demo"            document.getElementById("milestone_"+milestone_id+"").innerHTML = days + "d " + hours + "h "                + "  left";            // If the count down is finished, write some text            if (distance < 0) {                clearInterval(x);                document.getElementById("milestone_"+milestone_id+"").innerHTML = "EXPIRED";            }		}, 5000);	});			$('.demo_time_proposal_review').each(function(){		var proposal_id = jQuery(this).data('proposal_id');		let stored_miscroseconds = parseInt(jQuery(this).data('new_date'));		var countDownDate = new Date(stored_miscroseconds).getTime();				console.log(jQuery(this).data('new_date'));		// Update the count down every 1 second		var x = setInterval(function() {		  // Get todays date and time		  var now = new Date().getTime();		            // Find the distance between now and the count down date          var distance = countDownDate - now;          // Time calculations for days, hours, minutes and seconds          var days = Math.floor(distance / (1000 * 60 * 60 * 24));          var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));          // Display the result in the element with id="demo"          document.getElementById("proposal_"+proposal_id+"").innerHTML = days + "d " + hours + "h "           + "  left";          // If the count down is finished, write some text          if (distance < 0) {              console.log('proposal expired',distance,countDownDate,now,jQuery(this).data('new_date'));            clearInterval(x);            document.getElementById("proposal_"+proposal_id+"").innerHTML = "EXPIRED";          }		  });		}, 5000);}</script><?php