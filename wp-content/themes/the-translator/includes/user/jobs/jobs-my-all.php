<?php

/*
* current-php-code 2020-Oct-11
* input-sanitized : job_type,lang
* current-wp-template:  freelancer contest, viewing existing proposal
*/

$job_type = FLInput::get('job_type');
$lang = FLInput::get('lang', 'en');



?>

<style>
	
	
@media (max-width: 767px){
	#datatable thead {
	    display: none;
	}

	.rr table tbody tr td.thrd-td h5 strong{
		width: auto !important;
	}
	
	#datatable tbody td:first-child:before{
		content: "Post Date";
	}
	#datatable tbody td:nth-child(2):before{
		content: "project";
	}
	
	
	 #datatable tbody td::before {
	    background-color: #e5eef3;
	    border-right: 1px solid #c8d5dc;
	    bottom: 0px;
	    color: #000000;
	    content: "";
	    /* replaced fontsize 12 */
	    left: 0px;
	    padding: 13px 7px;
	    position: absolute;
	    top: 0px;
	    width: 130px;
	}
	#datatable tbody td:first-child {
    border-top: 1px solid #c8d5dc;
	}
	#datatable tbody td {
	    min-height: 50px;
	}
	#datatable tbody td {
	    display: block;
	    float: left;
	    padding-left: 140px;
	    position: relative;
	    width: 100%;
	    border-bottom: 1px solid #c8d5dc;
	}
}
</style>

<section class="middle-content rr">

	<div class="container">

	<div class="own-job-dashboard">

	<div class="top-barr--">


		<form id="filter_project_status" action="" method="get" >
			<table style="float: right;">
				<tr>
					
					<td class=" enhanced-text">
						
						<select name="job_type" class="regular-text" title="Job Type">

							<option value="">Select Type</option>
							<option value="contest" <?php if($job_type==='contest'):echo 'selected';endif;?>>Contest</option>
							<option value="project" <?php if($job_type ==='project'):echo 'selected';endif;?>>Project</option>

							

						</select>
					</td>
					<td class=" enhanced-text">
						<input type="submit" name="submit" class="signin-bttn login-btn-n regular-text" value="Filter">
					</td>
				</tr>
			</table>
		

	   </form>

	</div>



		<div class="job-table full-width">

			<div class="table-responsive tabblee default_txt_style">	
			<?php

			$paged 		= ( get_query_var('paged') ) ? get_query_var('paged') : 1;
            $paged = (int)$paged;
            if ($paged < 1) { $paged = 1;}



            switch($job_type){
                case 'contest': {
                    $post_id_search_job_type = 'AND fl_job_type = '.FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST;
                    break;
                }
                case 'project': {
                    $post_id_search_job_type = 'AND fl_job_type = '.FLPostLookupDataHelpers::POST_DATA_POST_TYPE_JOB;
                    break;
                }
                default: {
                    $post_id_search_job_type = '';
                    break;
                }
            }
            $post_type_constant = FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT;
            $post_id_per_page  = (int) get_option('fl_page_limit_job_search',20);
            $post_id_offset = ($paged -1) * 20;

            $da_total_job_count = 0;
            //get total count of the jobs
            $job_count_sql = "
                SELECT count(*) as da_count FROM wp_fl_post_data_lookup 
			WHERE
                  hide_job = 0 AND 
                  is_cancellation_approved = 0 AND 
                  post_type = $post_type_constant
                  $post_id_search_job_type
                ;
            ";

            $job_count_res = $wpdb->get_results($job_count_sql);
            //will_send_to_error_log("SQL for total job count is ",$wpdb->last_query);
            if ($wpdb->last_error) {
                will_log_on_wpdb_error($wpdb);
            }

            if ($job_count_res === false || empty($job_count_res)) {
                will_send_to_error_log("Unknown error getting total job count for the freelancer job search ");
            } else {
                $da_total_job_count = (int)$job_count_res[0]->da_count;
            }

			$sql_post_ids = "
			SELECT post_id , job.job_status
			FROM wp_fl_post_data_lookup 
			LEFT JOIN wp_fl_job job ON job.project_id = post_id
			WHERE
                  hide_job = 0 AND 
                  is_cancellation_approved = 0 AND 
                  post_type = $post_type_constant
                  $post_id_search_job_type
            ORDER BY post_id desc
            LIMIT $post_id_per_page OFFSET $post_id_offset;
                ";
            $res_post_ids = $wpdb->get_results($sql_post_ids);
            //will_send_to_error_log("SQL for paginated post ids is ",$wpdb->last_query);
            if ($wpdb->last_error) {
                $res_post_ids = [];
                will_log_on_wpdb_error($wpdb);
            }

            if ($res_post_ids === false) {
                $res_post_ids = [];
               // will_send_to_error_log("Unknown error getting post ids for the freelancer job search ");
            }
            $array_post_ids = [];
            $job_status_per_post = [];
            foreach ($res_post_ids as $res_id) {
                if (!in_array($res_id->post_id,$array_post_ids)) {
                    $array_post_ids[] = $res_id->post_id;
                }
                $post_id_as_integer = (int) $res_id->post_id;
                $maybe_job_status = $res_id->job_status;
                if ($maybe_job_status) {
                    if (!array_key_exists($post_id_as_integer,$job_status_per_post)) {
                        $job_status_per_post[$post_id_as_integer] = [];
                    }

                    if (!array_key_exists($maybe_job_status,$job_status_per_post[$post_id_as_integer])) {
                        $job_status_per_post[$post_id_as_integer][$maybe_job_status] = 1;
                    } else {
                        $job_status_per_post[$post_id_as_integer][$maybe_job_status] ++;
                    }

                }

            }

            if (empty($array_post_ids)) {
                $array_post_ids[] = -1; //insert dummy id so the wp_query function does not dump all
            }

            $new_jobs = [
                'post_type' 		=> 'job',
                'post__in'      => $array_post_ids,
                'posts_per_page' => -1
            ];


			$wp_query = new wp_Query( $new_jobs ); //code-notes working code needs conversion
            //will_send_to_error_log("SQL for wp_query is ",$wpdb->last_query);
			
			if( $wp_query->have_posts() ){

			

			echo '<table id="datatable">

			      <thead>

			         <tr>

			            <th class="first-th">Post Date</th>

			            <th class="snd-th">Project</th>

			            <th class="trd-th"></th>

			            <th class="forth-th"></th>

			         </tr>

			      </thead>

			      <tbody>';	

			while( $wp_query->have_posts() ) : $wp_query->the_post();

			$job_id 	= get_the_ID();

			$job_tbl 	= hz_is_linguist_asg( $job_id, get_current_user_id() );

			$job_des 	= stripslashes_deep(get_post_meta($job_id,'project_description',true));

			$job_title 	= stripslashes_deep(get_post_meta($job_id,'project_title',true));

			$del_date 	= get_post_meta($job_id,'job_standard_delivery_date',true);

			$link 		= get_site_url().'/job/'.get_the_title();

			$jtype 		= get_post_meta( $job_id, 'fl_job_type', true );
            $tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
            if($jtype == 'contest'){
                $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
            } else if($jtype == 'project'){
                $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
            }

			$bid_text 	= ( $jtype == 'contest' ) ? "Participate" : "Bid";

			$projectType 	= ( $jtype == 'contest' ) ? "Competition" : "";

			if($jtype == 'contest'){

                //code-notes hide contest if customer's wallet is negative
                $is_wallet_negative = FreelinguistContestCancellation::is_contest_and_negative_wallet($job_id,$log,$customer_balance);

                $participants = get_post_meta($job_id,'all_contest_paricipants',true);
                $participants_array = explode(',',$participants);
                if(!in_array(get_current_user_id(),$participants_array)){
                    if ($is_wallet_negative) {continue;} //code-notes skip showing  if not participated
                } else {
                    if ($is_wallet_negative) {continue;}//code-notes skip showing regardless, if wallet is negative
                }


	            $contStatus = get_post_meta($job_id,'all_contest_paricipants',true);

	           	if($contStatus != ''){ $currentStatus = 'Approve Completion'; $currentBtn = 'Recieve Content'; }

	           	else{ $currentStatus = 'In Progress'; $currentBtn = 'In Progress'; }

	        }else{

                  $project_status = get_post_meta($job_id, 'project_status', true);

                  if ( !empty($project_status) ) {

                  	 if ($project_status == 'project_in_progress') {

                      	 $currentStatus = 'In Progress'; 

                      	 $currentBtn = 'In Progress';

                  	 }

                   	 if ($project_status == 'pending') {

                      	 $currentStatus = 'Select Freelancer';

                      	 $currentBtn = 'Select Freelancer';

                  	 }

                  }else{

                     $currentStatus = 'NA'; 

                     $currentBtn = 'NA';

                  }

	        }



                $from_to = '';
			    $from_to_array = [];
                $from  = '';
                if ($from) { $from_to_array[] = $from;}
                $to  = '';
                if ($to) { $from_to_array[] = $to;}
                if ($from && $to) {
                    $from_to = implode(' to ',$from_to_array);
                } elseif ($from) {
                    $from_to = 'from '. $from;
                } elseif ($to) {
                    $from_to = 'to '. $to;
                }

                //code-notes change wording of prize assured if insurance or not
                $guaranted_span = '';
                if ($jtype === 'contest') {
                   $guaranted_phrase =  get_post_meta($job_id, 'is_guaranted', true)? '(Client Insurance)' : '(Prize Assured)';
                    $guaranted_span = '<span class="freelinguist-secondary-info freelinguist-contest-guaranted-phrase">'.$guaranted_phrase.'</span>';
                }

                $wp_interest_tags = $wpdb->prefix."interest_tags";
                $tags = $wpdb->get_results(
                    "SELECT GROUP_CONCAT(tag_id) as tag_ids FROM wp_tags_cache_job WHERE `job_id` = $job_id AND type = $tagType" );
                $tags_name_array=[];
                $tag_name_span = '';
                foreach($tags as $k=>$v){
                    $post_tags_array =explode(",",$v->tag_ids);
                    foreach($post_tags_array as $v1){
                        if (empty($v1)) {continue;}
                        $interest_tags = $wpdb->get_results( /** @lang text */
                            "SELECT * FROM $wp_interest_tags WHERE `id` = $v1" );
                        foreach($interest_tags as $k2=>$v2){
                            $tags_name_array[] = $v2->tag_name;
                        }
                    }
                }
                if (!empty($tags_name_array)) {
                    $tag_name_string = implode(', ',$tags_name_array);
                    $tag_name_span = '<span class="default_txt_style freelinguist-tag-list">'.$tag_name_string.'</span>';
                }


                //code-notes in second column scnd-td add in tags on next line
				echo '<tr data-job-id="job-'.$job_id.'">

			            <td class="frst-td">'.get_the_date().'</td> <!-- remove id displayed below date-->

			            <td class="scnd-td">

			               <span class="break-long-words">'.mb_strimwidth($job_title, 0, 100, ' ...').'</span>

			               <span></span>
                            '.$tag_name_span.'
			            </td>

			            <td class="thrd-td"><a class="default_txt_style" href="'.$link.'">

			               <span class="break-long-words">'.mb_strimwidth($job_des, 0, 150, ' ...').'</span>
			               <br>
			               
			               <span class="freelinguist-secondary-info">'.$from_to.'</span>
			               <br>
			               <span class="freelinguist-secondary-info">'.
                                '$'. str_replace("_","-",get_post_meta($job_id,'estimated_budgets',true)).'</span>
			                <span class="freelinguist-secondary-info">'.$del_date.'</span>
			                <br>
			               <span class=" freelinguist-secondary-highlight" style="text-transform: capitalize;">'.$projectType.'</span> '.
                            $guaranted_span .
			            '</td>



			            <td class="forth-td">

			            	';



							if($bid_text=='Bid'){
								
								if(in_array(get_current_user_id(),get_post_meta($job_id,'_bid_placed_by'))){
								    $button_words_array = [];
									if (isset($job_status_per_post[intval($job_id)])) {
									    if (isset($job_status_per_post[intval($job_id)]['reject_job'])) {
                                            $button_words_array[] = 'Declined';
                                            if (count($job_status_per_post[intval($job_id)] ) > 1) {
                                                $button_words_array[] = 'Bidden';
                                            }
                                        } else {
                                            $button_words_array[] = 'Bidden';
                                        }

                                    } else {
                                        $button_words_array[] = 'Bidden';
                                    }
                                    $button_words = implode(' ',$button_words_array);
									echo '<a class="hirebttn2 bid-btn"  href="#">'.$button_words.'</a>';
								}else{

								    if(get_current_user_id() != get_post()->post_author) {
                                        echo '<a class="hirebttn2 bid-btn"  href="#" data-target="#placeBidModel_' . $job_id . '" data-toggle="modal">' . $bid_text . '</a>';
                                    }
								    else {
                                        echo '<a class="hirebttn2 bid-btn disabled-action"  href="#" >' . $bid_text . '</a>';
                                    }
                                    ?>

									<div role="dialog" id="placeBidModel_<?=$job_id?>" class="modal fade">

                                        <div class="modal-dialog">

                                            <div class="modal-content">

                                                <div class="modal-header">

                                                    <button data-dismiss="modal" class="close huge-text" type="button">×</button>

                                                    <h4 class="modal-title">Apply to this job</h4>

                                                </div>

                                                <div class="modal-body">

                                                    <div id="alert_message_model"></div>
														
                                                    <form class="comment-form"  onsubmit="return place_the_bid(this)" method="post" action="<?= $link ?>" novalidate="novalidate">
                                                        <p class="comment-form-comment">
                                                            <label for="bidPrice">Bid Price</label>
																
                                                            <input type="number" class="form-control" name="bidPrice" min="1" value="" title="Bid Amount">
                                                            <input type="hidden" value="" name="comment_ID">
																
                                                        </p>

                                                        <p class="comment-form-comment">

                                                            <label for="comment">Notes</label><br>

                                                            <textarea maxlength="10000" class="form-control"  style="height:200px" aria-required="true" name="comment"
                                                                        title="Comment" autocomplete="off"
                                                            ></textarea>

                                                        </p>

                                                        <p class="form-submit">

                                                            <input type="submit" value="Apply to this job" class="btn blue-btn"  name="submit">

                                                            <input type="hidden"  value="<?=$job_id?>" name="comment_post_ID">

                                                            <input type="hidden"  value="<?=$lang?>" name="lang">

                                                            <input type="hidden" value="0"  name="comment_parent">

                                                        </p>

                                                    </form>

                                                </div> <!-- ./modal-body -->

                                            </div> <!-- ./modal-content -->

										</div> <!-- ./modal-dialog -->

                                    </div> <!-- ./modal -->
                                     <?php
								}
							}else{
								//echo '<a class="hirebttn2" href="'.$link.'" >'.$bid_text.'</a>';
								
								$participants = get_post_meta($job_id,'all_contest_paricipants',true);
								$participants_array = explode(',',$participants);
								if(in_array(get_current_user_id(),$participants_array)){
									echo '<a class="hirebttn2 bid-btn"  href="'.$link.'" >Participated</a>';
								}else{
                                    $author = get_post_field( 'post_author', $job_id );
                                    //code-notes Participate button is expired after the deadline time is pasted
                                    $participate_word = 'PARTICIPATE';
                                    $is_expired = false;
                                    try {
                                        $is_expired = FreelinguistContestCancellation::is_after_deadline_date($job_id, $log, $award_ended_ts,$diff_in_seconds);

                                        if ($is_expired) {
                                            $participate_word = 'EXPIRED';
                                        }
                                    } catch(RuntimeException $r) {}

									if($author == get_current_user_id() || $is_expired)
								        echo '<a  class="hirebttn2 bid-btn disabled-action" href="#">'.$participate_word.'</a>';
								    else
								        echo '<a  linguid="'.get_current_user_id().'" contestid="'.$job_id.'" lang="'.$lang.'"  class="hirebttn2 prt_accept bid-btn" href="#">'.$participate_word.'</a>';
								}
							}

			            echo '</td>

			        </tr>';

			endwhile;

			echo '</tbody>

			   </table>';

			}else{

				echo '<h3>Sorry, No Job Found.</h3>';

			}

			?>

             <?php
                #code-notes added calculated pagination links to the page
                $total_pages = (int)ceil($da_total_job_count/$post_id_per_page);
                $url_template = get_site_url() . '/jobs/page/%page%/?lang=en';
                freelinguist_print_pagination_bar($paged,$total_pages,$url_template,'bottom');
             ?>

		</div></div>

		</div>

	</div>

</section>

<script>
    jQuery(function($) {
        // return;
        if ($(window).width() > 767) {
            jQuery('#datatable').DataTable( {
                "paginate": false,
                "searching": false,
                "bInfo": false,
                "order": [[ 0, "desc" ]],
                //"ordering": false,

                "fnInitComplete": function(){


                    jQuery('.own-job-dashboard select').css({"float":"left"});
                    jQuery('#datatable_length').css({"width":"100%"});
                    jQuery('#datatable_length label').css({"padding":"10px 10px","width":"40%","float":"left"});


                } ,
                "drawCallback": function( settings ) {

                }
            } );
        }
    });
</script>