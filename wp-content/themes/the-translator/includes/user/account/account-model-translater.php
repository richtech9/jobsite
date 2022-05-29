	
   <?php
   /*
    * current-php-code 2020-Oct-31
    * input-sanitized :
    * current-wp-template:  for freelancers
    */
   FreelinguistDebugFramework::note('called account-model-translater.php');
   ?>

		<div class="modal fade in" id="profiledetailModel" role="dialog" >
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close huge-text" data-dismiss="modal">×</button>
						<h4 class="modal-title"><?php get_custom_string("Account Information"); ?></h4>
					</div>
					<div class="modal-body">
						<label id="personal_info_message"></label>
						<form class="profiledetail" id="profiledetail" method="post" action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate">
								<?php $user = get_user_by( 'id', $current_user_id );?>					
							<p class="account-form-account">
								<label for="display_name"><?php get_custom_string("Name"); ?></label>
								<input class="form-control" type="text" name="display_name" id="display_name" value="<?php echo $user->display_name; ?>"> 
								<label class="error"></label>								
								<label for="user_phone"><?php get_custom_string("Phone number"); ?></label>
								<input class="form-control" type="text" name="user_phone" id="user_phone" value="<?php echo empty(get_user_meta($current_user_id,'user_phone',true)) ? '' : get_user_meta($current_user_id,'user_phone',true); ?>"> 
								<label class="error"></label>								
								<label for="user_residence_country"><?php get_custom_string("Residence country"); ?>.</label><br>
								<!-- <input class="form-control" type="text" name="user_residence_country" value="<?php //echo empty(get_user_meta($current_user_id,'user_residence_country',true)) ? '' : get_user_meta($current_user_id,'user_residence_country',true); ?>">							 -->
								<select  title="Country" class="selectpicker" name="user_residence_country">
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
								<label for="user_city"><?php get_custom_string("City"); ?></label>
                                <input class="form-control" type="text" name="user_city" id="user_city" value="<?php echo empty(get_user_meta($current_user_id,'user_city',true)) ? '' : get_user_meta($current_user_id,'user_city',true); ?>"> 
								
								<label class="error"></label>
								<label for="user_hourly_rate"><?php get_custom_string("Hourly Rate"); ?></label>
                                <input class="form-control" type="text" name="user_hourly_rate" id="user_hourly_rate" value="<?php echo empty(get_user_meta($current_user_id,'user_hourly_rate',true)) ? '' : get_user_meta($current_user_id,'user_hourly_rate',true); ?>" onkeypress="return isNumberKey(event)" > 
								
								<label class="error"></label>
								<label for="user_hourly_rate"><?php get_custom_string("Time Zone"); ?></label>
                                <select  title="Time Zone" class="selectpicker" name="user_time_zone">
								<?php 
								$timezones = timezone_identifiers_list();
								
								foreach ($timezones as $key) { 
									if(get_user_meta($current_user_id,'user_time_zone',true) == $key){
										?>
										<option selected value="<?php echo $key; ?>"><?php echo $key; ?></option>
										<?php
									}else{ ?>
										<option value="<?php echo $key; ?>"><?php echo $key; ?></option>
										<?php
									}
									
								}
								?>
								</select>
								
                                <label class="error"></label>    
								<!-- <label for="Address"><?php //get_custom_string("Address"); ?></label>
								<input class="form-control" type="text" name="user_address" value="<?php //echo empty(get_user_meta($current_user_id,'user_address',true)) ? '' : get_user_meta($current_user_id,'user_address',true); ?>">							
								<label class="error"></label> -->
							</p>
							<p class="form-submit">
								<input type="submit" name="submit" id="submit" class="btn blue-btn" value="<?php get_custom_string('Update'); ?>">
							</p>				
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade in" id="summaryModel" role="dialog" >
<?php
global $wpdb;
$contentTagsAr = [];
$content_detail = $wpdb->get_row( "SELECT GROUP_CONCAT(tag_id) as tags FROM wp_tags_cache_job WHERE `job_id` = $current_user_id AND type = ". FreelinguistTags::USER_TAG_TYPE,ARRAY_A);
if($content_detail['tags']){
	// echo  "select tag_name from {$wpdb->prefix}interest_tags where id IN (".$content_detail['tags'].")";
$contentTags = $wpdb->get_results( "select tag_name from wp_interest_tags where id IN (".$content_detail['tags'].")",ARRAY_A);

$contentTagsAr = [];
if($contentTags){
	$contentTagsAr = array_column($contentTags, 'tag_name');
}
}
$prefilled = json_encode($contentTagsAr);
?>
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close huge-text" data-dismiss="modal">×</button>
						<h4 class="modal-title"><?php get_custom_string("Summary"); ?></h4>
					</div>
					<div class="modal-body">
						<label id="form_error_message" style="color:red"></label>
						<label id="form_success_message" style="color:green"></label>
						<form class="summaryinformation" id="summaryinformation" method="post"
                              action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate"
                        >
							<?php $user = get_user_by( 'id', $current_user_id );?>					
							<p class="account-form-account">	
								<label for="user_description"><?php get_custom_string("Description"); ?></label>
								<textarea class="form-control editors"  autocomplete="off" maxlength="10000" id="user_description" name="user_description"><?php echo empty(get_user_meta($current_user_id,'description',true)) ? '' : get_user_meta($current_user_id,'description',true); ?></textarea>
								<label class="error"></label>
							</p>
							<p class="formcont" style="" data-toggle="tooltip" data-placement="bottom">
								<input type="text" name="project_tags" id="project_tags" class="tm-input enhanced-text" autocomplete="off"
                                       value="" placeholder="<?php echo get_custom_string_return('Skills'); ?>">

							</p>
							<p class="form-submit">
								<input type="submit" name="submit" id="submit" class="btn blue-btn" value="<?php get_custom_string('Update'); ?>">
								<input type="hidden" value="update_account_info" name="action" id="action"> 
							</p>				
						</form>
					</div>
				</div>
			</div>
		</div>

        <!-- task-future-work This is the only way to start an evaluation, but there is no way to make it show up in the pages. Are evaulation admin and this still a thing?-->
		<div class="modal fade in" id="RequestEvaluationModel" role="dialog" >
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close huge-text" data-dismiss="modal">×</button>
						<h4 class="modal-title"><?php get_custom_string("Request Evaluation"); ?></h4>
					</div>
					<div class="modal-body">
						<label id="form_error_message" style="color:red"></label>
						<label id="form_success_message" style="color:green"></label>
						<form class="RequestEvaluation" id="RequestEvaluation" method="post" action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate">											
							<label for="user_description"><?php get_custom_string("Please complete the update of your profile before sending this request. Your public profile can no longer be edited once the request is sent"); ?>.</label>
							<p class="account-form-account">	
								<textarea placeholder="<?php get_custom_string('Type your reason here'); ?>"  autocomplete="off"  class="form-control" id="RequestEvaluation_description" name="RequestEvaluation_description"></textarea>
								<label class="error"></label>
							</p>
							<p class="form-submit">
								<button type="button" class="btn blue-btn" data-dismiss="modal"><?php get_custom_string("Cancel"); ?></button>
								<input type="submit" name="submit" id="submit" class="btn blue-btn" value="<?php get_custom_string("SEND REQUEST"); ?>">
								<input type="hidden" value="update_account_info" name="action" id="action"> 
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

						

						<h4 class="modal-title"><?php get_custom_string("Please upload a profile pic for better services."); ?></h4>

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
		<div class="modal fade in" id="educationModel" role="dialog" >
			<div class="modal-dialog" style="width:70%">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close huge-text" data-dismiss="modal">×</button>
						<h4 class="modal-title"><?php get_custom_string("Education Information"); ?></h4>
					</div>
					<div class="modal-body">
						<label id="form_error_message" style="color:red"></label>
						<label id="form_success_message" style="color:green"></label>

						<form class="educationDetailForm" id="educationDetailForm" method="post" action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate">
							<?php $user = get_user_by( 'id', $current_user_id );?>
							<div class="education_mod" id="education_mod">	
								<div class="validate_error">
									<div class="row">
										<label id="year_attended[]-error" class="error" for="year_attended[]"></label>
										<label id="institution[]-error" class="error" for="institution[]"></label>
										<label id="degree[]-error" class="error" for="degree[]"></label>
									</div>
								</div>
								<?php 
								$total_edu = empty(get_user_meta($current_user_id,'education_counter',true)) ? 0 : get_user_meta($current_user_id,'education_counter',true);						
								for($i=0;$i<=$total_edu;$i++){ 
									$institution 	= get_user_meta($current_user_id,'institution_'.$i,true);
									$year_attended 	= get_user_meta($current_user_id,'year_attended_'.$i,true);
									$degree 		= get_user_meta($current_user_id,'degree_'.$i,true);
									if(!empty($institution) || !empty($year_attended) || !empty($degree)){
									?>

									<div class="row">
										<div class="col-md-3">
											<label for="year_attended"><?php get_custom_string("Years Attended"); ?></label><br>
											<input class="form-control" type="text" name="year_attended[]" id="year_attended[]" value="<?php echo $year_attended; ?>"> 
											<label class="error"></label>
										</div>
										<div class="col-md-4">
											<label for="institutuin"><?php get_custom_string("Institution"); ?></label><br>
											<input class="form-control" type="text" name="institution[]" id="institution[]" value="<?php echo $institution; ?>"> 
<label class="error"></label>											
										</div>
										<div class="col-md-4">
											<label for="degree"><?php get_custom_string("Degree"); ?></label><br>
											<input class="form-control" type="text" name="degree[]" id="degree[]" value="<?php echo $degree; ?>"> 
											<label class="error"></label>
										</div>		
										<div class="col-md-1">
											<label for="areas_expertise">&nbsp;</label><br>
											<a href="#" id="delete_education_info" name="<?php echo $i; ?>" class="delete_education_info glyphicon glyphicon-remove"></a>
										</div>							
									</div>
									<?php
									}
								}	
								?>
								
							</div>

							<p class="form-submit">
								<input type="submit" name="submit" id="submit" class="btn blue-btn" value="<?php get_custom_string("Update"); ?>">
								<a style="float:right" id="add_more_education" class="add_more_education add_more_data" href="#"><?php get_custom_string("Add New"); ?></a>
							</p>				
						</form>
					</div>
				</div>
			</div>
		</div>


		<div class="modal fade in" id="CertificatesModel" role="dialog" >
			<div class="modal-dialog" style="width:70%">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close huge-text" data-dismiss="modal">×</button>
						<h4 class="modal-title"><?php get_custom_string("Licenses/Certificates/Awards"); ?></h4>
					</div>
					<div class="modal-body">
						<label id="form_error_message" style="color:red"></label>
						<label id="form_success_message" style="color:green"></label>

						<form class="certificationDetailForm" id="certificationDetailForm" method="post" action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate">
							<?php $user = get_user_by( 'id', $current_user_id );?>
							<div class="certification_mod" id="certification_mod">	
								<div class="validate_error">
									<div class="row">
										<label id="year_recieved[]-error" class="error" for="year_recieved[]"></label>
										<label id="recieved_from[]-error" class="error" for="recieved_from[]"></label>
										<label id="certificate[]-error" class="error" for="certificate[]"></label>
									</div>
								</div>
								<?php 
								$total_cert = empty(get_user_meta($current_user_id,'certification_counter',true)) ? 0 : get_user_meta($current_user_id,'certification_counter',true);						
								for($i=0;$i<=$total_cert;$i++){ 
									$year_recieved 	= get_user_meta($current_user_id,'year_recieved_'.$i,true);
									$recieved_from 	= get_user_meta($current_user_id,'recieved_from_'.$i,true);
									$certificate 		= get_user_meta($current_user_id,'certificate_'.$i,true);
									if(!empty($year_recieved) || !empty($recieved_from) || !empty($certificate)){
									?>

									<div class="row">
										<div class="col-md-3">
											<label for="year_recieved"> <?php get_custom_string("Year Received"); ?></label><br>
											<input class="form-control" type="text" name="year_recieved[]" id="year_recieved[]" value="<?php echo $year_recieved; ?>"> 
											<label class="error"></label>
										</div>
										<div class="col-md-4">
											<label for="employer"><?php get_custom_string("Received from"); ?></label><br>
											<input class="form-control" type="text" name="recieved_from[]" id="recieved_from[]" value="<?php echo $recieved_from; ?>"> 
											<label class="error"></label>
										</div>
										<div class="col-md-4">
											<label for="duties"><?php get_custom_string("certificate"); ?></label><br>
											<input class="form-control" type="text" name="certificate[]" id="certificate[]" value="<?php echo $certificate; ?>"> 
											<label class="error"></label>
										</div>	
										<div class="col-md-1">
											<label for="areas_expertise">&nbsp;</label><br>
											<a href="#" id="delete_certificate_info" name="<?php echo $i; ?>" class="delete_certificate_info glyphicon glyphicon-remove"></a>
										</div>								
									</div>
									<?php
									}
								}	
								?>
								
							</div>

							<p class="form-submit">
								<input type="submit" name="submit" id="submit" class="btn blue-btn" value="<?php get_custom_string('Update'); ?>">
								<a style="float:right" id="add_more_certification" class="add_more_certification add_more_data" href="#"><?php get_custom_string('Add New'); ?></a>
							</p>				
						</form>
					</div>
				</div>
			</div>
		</div>


		<div class="modal fade in" id="relatedWorkExperienceModel" role="dialog" >
			<div class="modal-dialog" style="width:70%">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close huge-text" data-dismiss="modal">×</button>
						<h4 class="modal-title"><?php get_custom_string("Related work experiences"); ?></h4>
					</div>
					<div class="modal-body">
						<label id="form_error_message" style="color:red"></label>
						<label id="form_success_message" style="color:green"></label>

						<form class="relatedExperinceDetailForm" id="relatedExperinceDetailForm" method="post" action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate">
							<?php $user = get_user_by( 'id', $current_user_id );?>
							<div class="related_experience_mod" id="related_experience_mod">	
								<div class="validate_error">
									<div class="row">
										<label id="year_in_service[]-error" class="error" for="year_in_service[]"></label>
										<label id="employer[]-error" class="error" for="employer[]"></label>
										<label id="duties[]-error" class="error" for="duties[]"></label>
										<label id="areas_expertise[]-error" class="error" for="areas_expertise[]"></label>
									</div>
								</div>
								<?php 
								$total_cert = empty(get_user_meta($current_user_id,'related_experience_counter',true)) ? 0 : get_user_meta($current_user_id,'related_experience_counter',true);						
								for($i=0;$i<=$total_cert;$i++){ 
									$year_in_service 	= get_user_meta($current_user_id,'year_in_service_'.$i,true);
									$employer 	= get_user_meta($current_user_id,'employer_'.$i,true);
									$duties 		= get_user_meta($current_user_id,'duties_'.$i,true);
									if(!empty($year_in_service) || !empty($employer) || !empty($duties)){
									?>

									<div class="row">
										<div class="col-md-3">
											<label for="year_in_service"> <?php get_custom_string("Year in service"); ?></label><br>
											<input class="form-control" type="text" name="year_in_service[]" id="year_in_service[]" value="<?php echo $year_in_service; ?>"> 
											<label class="error"></label>
										</div>
										<div class="col-md-4">
											<label for="employer"><?php get_custom_string("Employer"); ?></label><br>
											<input class="form-control" type="text" name="employer[]" id="employer[]" value="<?php echo $employer; ?>"> 
											<label class="error"></label>
										</div>
										<div class="col-md-4">
											<label for="duties"><?php get_custom_string("Duties"); ?></label><br>
											<input class="form-control" type="text" name="duties[]" id="duties[]" value="<?php echo $duties; ?>"> 
											<label class="error"></label>
										</div>	
										<div class="col-md-1">
											<label for="areas_expertise">&nbsp;</label><br>
											<a href="#" id="delete_related_work_experience" name="<?php echo $i; ?>" class="delete_related_work_experience glyphicon glyphicon-remove"></a>
										</div>								
									</div>
									<?php
									}
								}	
								?>
								
							</div>

							<p class="form-submit">
								<input type="submit" name="submit" id="submit" class="btn blue-btn" value="<?php get_custom_string('Update'); ?>">
								<a style="float:right" id="add_more_related_work" class="add_more_related_work add_more_data" href="#"><?php get_custom_string('Add New'); ?></a>
							</p>				
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade in" id="languagesModel" role="dialog" >
			<div class="modal-dialog" style="width:80%">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close huge-text" data-dismiss="modal">×</button>
						<h4 class="modal-title"><?php get_custom_string("Language"); ?></h4>
					</div>
					<div class="modal-body">
						<label id="form_error_message" style="color:red"></label>
						<label id="form_success_message" style="color:green"></label>

						<form class="languageDetailForm" id="languageDetailForm" method="post" action="<?php echo freeling_links('my_account_url'); ?>" novalidate="novalidate">
							<?php $user = get_user_by( 'id', $current_user_id );?>
							<div class="language_mod" id="language_mod">	
								<div class="validate_error">
									<div class="row">
										<label id="language[]-error" class="error" for="language[]"></label>
										<label id="language_level[]-error" class="error" for="language_level[]"></label>
										<label id="year_of_experince[]-error" class="error" for="year_of_experince[]"></label>
										<label id="areas_expertise[]-error" class="error" for="areas_expertise[]"></label>
									</div>
								</div>
								<?php 
								$language_counter = empty(get_user_meta($current_user_id,'language_counter',true)) ? 0 : get_user_meta($current_user_id,'language_counter',true);						
								for($i=0;$i<=$language_counter;$i++){ 
									$language 		= get_user_meta($current_user_id,'language_'.$i,true);
									$language_level 	= get_user_meta($current_user_id,'language_level_'.$i,true);
									$year_of_experince 	= get_user_meta($current_user_id,'year_of_experince_'.$i,true);
									$areas_expertise 		= get_user_meta($current_user_id,'areas_expertise_'.$i,true);
									if(!empty($language_level)){
									?>
									<div class="row">
										<div class="col-md-3">
											<label for="Language"><?php get_custom_string("Experince"); ?></label><br>
											<input  title="Languagee" class="form-control" type="text" name="language[]" id="" value="<?php echo $language; ?>">
											<label class="error"></label>
										</div>
										<div class="col-md-2">
											<label for="language_level"> <?php get_custom_string("Level"); ?></label><br>
											<!--select class="selecter_trans" id="language_level[]" name="language_level[]">
												<option value="native" <?php if($language_level =='native') { echo 'selected'; } ?>>Native</option>
												<option value="fluent" <?php if($language_level =='fluent') { echo 'selected'; } ?>>Fluent</option>
												<option value="learner" <?php if($language_level =='learner') { echo 'selected'; } ?>>Learner</option>
											</select-->
											<select class="selecter_trans" id="language_level[]" name="language_level[]">
												<option value="pro" <?php if($language_level =='pro') { echo 'selected'; } ?>>Pro</option>
												<option value="intermediate" <?php if($language_level =='intermediate') { echo 'selected'; } ?>>Intermediate</option>
												<option value="beginner" <?php if($language_level =='beginner') { echo 'selected'; } ?>>Beginner</option>
											</select>
											<label class="error"></label>
										</div>
										
										<div class="col-md-3">
											<label for="areas_expertise"><?php get_custom_string("Areas/expertise"); ?></label><br>
											<input type="text" id="areas_expertise[]" class="form-control" name="areas_expertise[]" value="<?php echo $areas_expertise; ?>">
											
											<label class="error"></label>
										</div>	
										<div class="col-md-3">
											<label for="year_of_experince"><?php get_custom_string("Years of experience"); ?></label><br>
											<input class="form-control" type="text" name="year_of_experince[]" id="year_of_experince[]" value="<?php echo $year_of_experince; ?>"> 
											<label class="error"></label>
										</div>												
										<div class="col-md-1">
											<label for="areas_expertise">&nbsp;</label><br>
											<a href="#"  name="<?php echo $i; ?>" class="delete_language glyphicon glyphicon-remove"></a>
										</div>							
									</div>
									<?php
									}
								}	
								?>
								
							</div>

							<p class="form-submit">
								<input type="submit" name="submit" id="submit" class="btn blue-btn" value="<?php get_custom_string('Update'); ?>">
								<a style="float:right" id="add_language" class="add_language add_more_data" href="#"><?php get_custom_string('Add New'); ?></a>
							</p>	
							<p id="custom_data_val"><input type="hidden" name="form_type" value="language"></p>			
						</form>
					</div>
				</div>
			</div>
		</div>





<script type="text/javascript" src="<?php echo get_template_directory_uri(). '/js/ckeditor/ckeditor/ckeditor.js' ?>"></script>
<script type="text/javascript">
  jQuery(function($) {
	  
	for (let instance in CKEDITOR.instances) {
	CKEDITOR.instances[instance].updateElement();
	}
    var tagApi = $(".tm-input").tagsManager({prefilled: <?php echo $prefilled; ?>});


    jQuery("#project_tags").typeahead({
      name: 'id',
      displayKey: 'name',
      source: function (query, process) {
        return jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {action:'get_custom_tags', query: query }, function (data) {
			jQuery('#resultLoading').fadeOut(300);
          data = JSON.parse(data);
          return process(data);
        });
      },
      afterSelect :function (item){
		 
		 console.log(item);
        tagApi.tagsManager("pushTag", item.name);
      }
    }).bind("typeahead:selected", function(obj, datum, name) {
		console.log(obj, datum, name);
	});
	
	
	jQuery('.editors').each(function(){

		CKEDITOR.replace( jQuery(this).attr('id') );

	});
  });
	function isNumberKey(evt)
	{
	 var charCode = (evt.which) ? evt.which : event.keyCode;
	 return !(charCode > 31 && (charCode < 48 || charCode > 57));

	}
</script>

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

<script>
   jQuery(function($) {
       //code-notes show tooltip for tags
       let tag_box = $('#project_tags') ;
       freelinguist_tag_help(tag_box);
   });
</script>

