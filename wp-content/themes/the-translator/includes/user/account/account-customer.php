<?php

if(!defined('WPINC')){die;}
/*

Customer account File

*/


//update_user_meta(get_current_user_id(),'total_user_balance',5000);
?>
<style>
    /*noinspection CssUnusedSymbol*/
.modal-backdrop.in {
	opacity: 0.7 !important;
}
@media (max-width: 767px){
	#customer_review_table thead {
	    display: none;
	}

	.rr table tbody tr td.thrd-td h5 strong{
		width: auto !important;
	}
	#customer_review_table tbody td:first-child:before {
		content: "Rating";
	}
	#customer_review_table tbody td:nth-child(2):before{
		content: "Comments";
	}
	#customer_review_table tbody td:nth-child(3):before{
		content: "Freelancer";
	}
	#customer_review_table tbody td:nth-child(4):before{
		content: "Job Type";
	}
	#customer_review_table tbody td:nth-child(5):before{
		content: "Job";
	}
	#customer_review_table tbody td:nth-child(6):before{
		content: "Date";
	}
	 #customer_review_table tbody td::before {
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
	#customer_review_table tbody td:first-child {
    border-top: 1px solid #c8d5dc;
	}
	#customer_review_table tbody td {
	    min-height: 50px;
	}
	#customer_review_table tbody td {
	    display: block;
	    float: left;
	    padding-left: 140px;
	    position: relative;
	    width: 100%;
	    border-bottom: 1px solid #c8d5dc;
	}
}

    /*code-notes display user id on the account page (top right)*/
    span.fl-account-customer-user-id-display{
        display: inline-block;
        width: fit-content;
        float: right;
        color: lightgray;
    }

</style>

<!-- get society css-->
<?php get_template_part('includes/user/society/society', 'style');?>
<!-- end society css-->


	<?php

	global $wpdb;


	$current_user 		= wp_get_current_user(); 

	$current_user_id 	= get_current_user_id();

	// Count the job categories-> completed, pending and ongoing


	?>
	<link href="<?php echo get_template_directory_uri() . '/css/current-code/content-customer.css';?>" rel="stylesheet">
	<div class="content-area">
		<div class="container">
			<div class="row">
				<div class="page-title col-md-12"><h3><i class="icon icon-userlg"></i><?php  get_custom_string("My Account"); ?></h3></div>
			</div>
			<div class="row">
				<div class="col-lg-6">
					<div class="left_content_img">
						<div class="thumnil upload-file new regular-text">

							<div class="fig">
                                <?php
                                //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                                $avatar = hz_get_profile_thumb($current_user_id,FreelinguistSizeImages::LARGE,false);
                                ?>

								<?php if($avatar){

								 	echo '<img style="" src="'.$avatar.'">';

								 }else{
									echo '<img style="" src="'.get_template_directory_uri().'/images/user-profile.png">';
								 }
								 ?>

								 <div class="edit-profile-pic">

								 	<div class="profile_change_icon">

										<input class="user_image_btn" title=" " type="file" name="user_image" id="user_image">

									</div>

									<?php $conf_message = '"'.get_custom_string_return("Do you want to remove your profile image?").'"'; ?>									

									<?php $yes = '"'.get_custom_string_return("Yes").'"'; ?>									

									<?php $no = '"'.get_custom_string_return("No").'"'; ?>	

									<?php if(!empty(get_user_meta($current_user_id,'user_image',true))){ ?>

											<a class="user_image_delete_cl glyphicon glyphicon-remove" onclick='return delete_profile_image(<?php echo $conf_message.",".$yes.",".$no; ?>)' id="delete_profile_image" href="#"></a>

									<?php } ?>

								</div>

							</div>


						</div>
					</div>
				</div>
				<div class="col-lg-6">

					<div class="right_content_text">
						<div class="edit-acc enhanced-text">
							<a class=small-text" href="javascript:void()" data-toggle="modal" data-target="#profiledetailModel"><?php  get_custom_string("Update Account Info"); ?></a>
						</div>
						<h4>
                            <strong><?php echo ucfirst($current_user->display_name); ?></strong>
                            <span class="fl-account-customer-user-id-display small-text"><?= $current_user_id ?></span>
                        </h4>

						<hr>
						
						<p>
                            <b><?php  get_custom_string("Email"); ?>:</b>
							<?php echo $current_user->user_email; ?>
                        </p>
						<p>
							<b><?php  get_custom_string("Phone"); ?>:</b>
							<?php echo empty(get_user_meta($current_user_id,'user_phone',true)) ? '' : get_user_meta($current_user_id,'user_phone',true); ?></p>
						
						<p>
							<b><?php get_custom_string("Residence country"); ?>: </b>
							<?php echo (get_user_meta($current_user_id,'user_residence_country',true) < 0) ? 'Not Exist' : get_country_by_index(get_user_meta($current_user_id,'user_residence_country',true)); ?></p>	
						<hr>
						
						<p>
							<b><?php get_custom_string("Description"); ?>:</b>
							<?php if(empty(get_user_meta($current_user_id,'user_description',true))){ get_custom_string('Not Available'); } else { echo get_user_meta($current_user_id,'user_description',true); } ?>
						</p>

                        <div style="margin-top: 1em">
                            <?php get_template_part('includes/user/society/society', 'referral-code');?>
                        </div>


					</div>
				</div>
			</div>

			<div class="row freelinguist-last-row">
				<div class="accdetal-table">

                    <h4><?php get_custom_string("Freelancer reviews"); ?></h4>

                    <table class="data-table" id="customer_review_table">

                    <thead>

                    <tr>

                        <th width="14%"><?php echo get_custom_string_return("Rating"); ?></th>

                        <th width="40%"><?php  get_custom_string("Comments"); ?> </th>

                        <th><?php  get_custom_string("Client"); ?> </th>
						<th><?php  get_custom_string("Job Type"); ?></th>
                        <th width="7%"><?php  get_custom_string("Job"); ?></th>
                        
                        <th><?php  get_custom_string("Date"); ?></th>

                       

                    </tr>

                    </thead>

                    <tbody>

                    <?php

						$new_feedback_array = array();
						$content_table = $wpdb->prefix.'linguist_content';
                        $feedback_is = $wpdb->get_results("SELECT * FROM wp_linguist_content WHERE purchased_by = $current_user_id and rating_by_customer IS NOT NULL AND rating_by_freelancer IS NOT NULL AND user_id IS NOT NULL ORDER by id desc limit 10",ARRAY_A);
						
							
                        for ($i = 0; $i < count($feedback_is); $i++) {
							
							array_push($new_feedback_array,$feedback_is[$i]);

						}
						$proposal_table = $wpdb->prefix.'proposals';
                        $feedback_is_2 = $wpdb->get_results("SELECT * FROM wp_proposals WHERE customer = $current_user_id and rating_by_customer IS NOT NULL  AND rating_by_freelancer IS NOT NULL order by id desc limit 10",ARRAY_A);

                        for ($i = 0; $i < count($feedback_is_2); $i++) {
							array_push($new_feedback_array,$feedback_is_2[$i]);
						}
						
						$jobs_table = $wpdb->prefix.'fl_job';
						$feedback_is_3 = $wpdb->get_results("SELECT * FROM wp_fl_job WHERE author = $current_user_id and rating_by_customer IS NOT NULL  AND rating_by_freelancer IS NOT NULL order by id desc limit 10",ARRAY_A);

						for ($i = 0; $i < count($feedback_is_3); $i++) {
							array_push($new_feedback_array,$feedback_is_3[$i]);
						}
                           
						$new_feedback_array = array_sort($new_feedback_array, 'updated_at', SORT_DESC);
						foreach($new_feedback_array as $k=>$v){
							
						?>
							 <tr>
								<td>

									<?php

									$feedbak_rating = $v['rating_by_freelancer'];

									echo job_rating($feedbak_rating);

									?>

								</td>

								<td> <?php echo stripslashes($v['comments_by_freelancer']); ?></td>

								<td>

									<?php

								  //$post_data = get_post($feedback_is_2[$i]->post_id);
									$customer_id = '';
									$job_type = '';
									$job_title = '';
									if(isset($v['content_title']) && $v['content_title']) {
										$customer_id = $v['purchased_by'] ;
										$job_type = 'Content';
										$job_title = $v['content_title'];
										
									}elseif(isset($v['customer']) && $v['customer']){
										$customer_id = $v['by_user'];
										$job_type = 'Proposal';
										//$job_title =get_the_title($v['post_id']);
										$job_title =get_post_meta( $v['post_id'], 'project_title', true );
										 
									}else if(isset($v['author']) && $v['author']){
										$customer_id = $v['linguist_id'] ;
										$job_type = 'Project';
										//$job_title =get_the_title($v['project_id']);
										$job_title =get_post_meta( $v['project_id'], 'project_title', true );
									}
									$post_author = get_userdata($customer_id);

									echo $post_author->display_name;

									?>

								</td>

								<td><?php echo $job_type;?></td>
								<td><?php echo $job_title;?></td>
								<td><?php echo date_formatted($v['updated_at']); ?></td>
								   

								   

								
							 </tr>
						<?php
						 }
							
						

                     ?>

                    </tbody>

                </table>

                </div>
			</div>
		</div>
	<div class="modal fade in" id="profiledetailModel" role="dialog" >

		<div class="modal-dialog">

			<!-- Modal content-->

			<div class="modal-content">

				<div class="modal-header">

					<button type="button" class="close huge-text" data-dismiss="modal">×</button>

					<h4 class="modal-title"><?php  get_custom_string("Account Information"); ?></h4>

				</div>

				<div class="modal-body">

					<label id="form_error_message" style="color:red"></label>

					<label id="form_success_message" style="color:green"></label>

					<form class="customerProfiledetail" id="customerProfiledetail" method="post" action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate">

						<?php $user = get_user_by( 'id', $current_user_id );?>					

						<p class="account-form-account">

							<label for="display_name"><?php  get_custom_string("Name"); ?></label><br>

							<input class="form-control" type="text" name="display_name" id="display_name" value="<?php echo $user->display_name; ?>"> 

							<label for="user_phone"><?php  get_custom_string("Phone number"); ?></label><br>

							<input class="form-control" type="text" name="user_phone" id="user_phone" value="<?php echo empty(get_user_meta($current_user_id,'user_phone',true)) ? '' : get_user_meta($current_user_id,'user_phone',true); ?>"> 



							<label for="user_residence_country"><?php  get_custom_string("Residence country"); ?>.</label><br>							


								<select class="selectpicker" name="user_residence_country" title="country">

								<?php 

								$countries = get_countries();

								$i = 0;

								foreach ($countries as $key) { 

									if(get_user_meta($current_user_id,'user_residence_country',true) == $i){

										?>

										<option selected value="<?php echo $i; ?>"><?php echo $key; ?></option>

										<?php

									}else{ ?>

										<option value="<?php echo $i; ?>"><?php echo $key; ?></option>

										<?php

									}

									$i++;

								}

								?>

								</select>



								<label class="error"></label>

							<label for="user_description"><?php  get_custom_string("Description"); ?></label><br>

							<textarea maxlength="2000" class="form-control" id="user_description" autocomplete="off" name="user_description"><?php echo empty(get_user_meta($current_user_id,'user_description',true)) ? '' : get_user_meta($current_user_id,'user_description',true); ?></textarea><br>

						</p>

						<p class="form-submit">

							<input type="submit" name="submit" id="submit" class="btn blue-btn my-account-btn small-text" value="<?php  get_custom_string("Update"); ?>">

						</p>				

					</form>

				</div>

			</div>

		</div>

	</div>
	
	
	
	<!--****** profile image ******* -->
	<div class="modal fade in" id="profilepicreminder" role="dialog" >

		<div class="modal-dialog">

			<!-- Modal content-->

			<div class="modal-content">

				<div class="modal-header">

					

					<h4 class="modal-title"><?php  get_custom_string("Please upload a profile pic for better services."); ?></h4>

				</div>

				<div class="modal-body">
						<form id="profilepicreminder_form">
							
								<input type="hidden" name="action" value="user_image_file_reminder">
								<input class="user_image_btn" title=" " type="file" name="user_image" id="" accept="image/*">
							
							
								<button class="btn btn-success" id="profilepicreminder_button" type="button">Ok</button>
							
						</form>

							

				</div>

			</div>

		</div>

	</div>
	
	
	<!------------------------------------->
	</div>
<?php
if(!empty(get_user_meta($current_user_id,'user_image',true)) || get_avatar( get_the_author_meta( $current_user_id  ), 100 )){

}else{				 	

?>
<script>
jQuery({
	jQuery('#profilepicreminder').modal({
                        backdrop: 'static',
                        keyboard: true, 
                        show: true
                });

});
</script>
<?php }?>
<?php
if(empty(get_user_meta($current_user_id,'user_image',true))){

    ?>
    <script>
        jQuery(function(){
            jQuery('#profilepicreminder').modal({
                backdrop: 'static',
                keyboard: true,
                show: true
            });

        });
    </script>
<?php } ?>
