<?php
/*
* current-php-code 2020-Oct-07
* input-sanitized : save-user-data
* current-wp-template:  for freelancers doing projects
*/
global $wpdb;
?>
<!--code-notes download and use local version of slick js and css-->

<link href="<?php echo get_template_directory_uri().'/css/current-code/content-customer.css'; ?>" rel="stylesheet">
<style>
    /*noinspection CssUnusedSymbol*/
.modal-backdrop.in {
	opacity: 0.7 !important;
}
@media (max-width: 767px){
	
	#skills_table tbody td::before, #sale_table tbody td::before,#public_table tbody td::before
	{
		display:none;
		width:0px;
		
	}
	#skills_table tbody td, #sale_table tbody td,#public_table tbody td{
		padding-left:20px;
	}
	#customer_review_table thead,#skills_table thead{
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
		content: "Customer";
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
	
	
	
	#language_table thead {
	    display: none;
	}
	
	#language_table tbody td:first-child:before {
		content: "LANGUAGE";
	}
	#language_table tbody td:nth-child(2):before{
		content: "LEVEL";
	}
	#language_table tbody td:nth-child(3):before{
		content: "YEARS OF EXPERIENCE";
	}
	#language_table tbody td:nth-child(4):before{
		content: "AREAS/EXPERTISE";
	}
	
	 #language_table tbody td::before {
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
	#language_table tbody td:first-child {
    border-top: 1px solid #c8d5dc;
	}
	#language_table tbody td {
	    min-height: 50px;
	}
	#language_table tbody td {
	    display: block;
	    float: left;
	    padding-left: 140px;
	    position: relative;
	    width: 100%;
	    border-bottom: 1px solid #c8d5dc;
	}
	
	
	#work_table thead {
	    display: none;
	}
	
	#work_table tbody td:first-child:before {
		content: "YEAR IN SERVICE";
	}
	#work_table tbody td:nth-child(2):before{
		content: "EMPLOYER";
	}
	#work_table tbody td:nth-child(3):before{
		content: "DUTIES";
	}
	#work_table tbody td:nth-child(4):before{
		content: "SKILLS";
	}
	
	 #work_table tbody td::before {
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
	#work_table tbody td:first-child {
    border-top: 1px solid #c8d5dc;
	}
	#work_table tbody td {
	    min-height: 50px;
	}
	#work_table tbody td {
	    display: block;
	    float: left;
	    padding-left: 140px;
	    position: relative;
	    width: 100%;
	    border-bottom: 1px solid #c8d5dc;
	}
	
	#certificates_table thead {
	    display: none;
	}
	
	#certificates_table tbody td:first-child:before {
		content: "YEAR RECEIVED";
	}
	#certificates_table tbody td:nth-child(2):before{
		content: "RECEIVED FROM";
	}
	#certificates_table tbody td:nth-child(3):before{
		content: "LICENSE/CERTIFICATE/AWARDS";
	}
	
	
	 #certificates_table tbody td::before {
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
	#certificates_table tbody td:first-child {
    border-top: 1px solid #c8d5dc;
	}
	#certificates_table tbody td {
	    min-height: 50px;
	}
	#certificates_table tbody td {
	    display: block;
	    float: left;
	    padding-left: 140px;
	    position: relative;
	    width: 100%;
	    border-bottom: 1px solid #c8d5dc;
	}
	
	#education_table thead {
	    display: none;
	}
	
	#education_table tbody td:first-child:before {
		content: "YEARS ATTENDED";
	}
	#education_table tbody td:nth-child(2):before{
		content: "INSTITUTION";
	}
	#education_table tbody td:nth-child(3):before{
		content: "DEGREE";
	}
	
	
	 #education_table tbody td::before {
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
	#education_table tbody td:first-child {
    border-top: 1px solid #c8d5dc;
	}
	#education_table tbody td {
	    min-height: 50px;
	}
	#education_table tbody td {
	    display: block;
	    float: left;
	    padding-left: 140px;
	    position: relative;
	    width: 100%;
	    border-bottom: 1px solid #c8d5dc;
	}
}

    /*code-notes display user id on the account page (top right)*/
    div.fl-account-freelancer-user-id-display{
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

if (!defined('WPINC')) {
    die;
}

/*

Translater Account File

*/


global $wpdb;



$current_user = wp_get_current_user();

$current_user_id = get_current_user_id();

//update_user_meta(get_current_user_id(),'total_user_balance',5000);

//updateTranslatorRating(get_current_user_id()); // code-notes stopped using deprecated updateTranslatorRating

$contentTagsAr = [];
$content_detail = $wpdb->get_row("SELECT GROUP_CONCAT(tag_id) as tags FROM wp_tags_cache_job WHERE `job_id` = $current_user_id AND type = ".FreelinguistTags::USER_TAG_TYPE, ARRAY_A);
if ($content_detail['tags']) {
    $contentTags = $wpdb->get_results("select tag_name from wp_interest_tags where id IN (" . $content_detail['tags'] . ")", ARRAY_A);
//    will_send_to_error_log('$contentTags',[$contentTags,$wpdb->last_query]);
    $contentTagsAr = [];
    if ($contentTags) {
        $contentTagsAr = array_column($contentTags, 'tag_name');
    }
}
$prefilled = json_encode($contentTagsAr);


$completed_jobs = [];


?>
<div class="content-area">
    <div class="container">
        <div class="row" style="padding-top: 0.25em">
            <div class="col-lg-6">
                <div class="left_content_img">
                    <div class="thumnil upload-file new regular-text">
                        <div class="fig">
                        <?php
                        //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                        $avatar = hz_get_profile_thumb($current_user_id,FreelinguistSizeImages::LARGE,false);
                        if ($avatar) {
                            echo '<img style="" src="' . $avatar . '">';
						 }else{
							echo '<img style="" src="'.get_template_directory_uri().'/images/user-profile.png">';
						 }
                            ?>
                            <div class="edit-profile-pic">

                                <div class="profile_change_icon">

                                    <input class="user_image_btn" type="file" title=" " name="user_image"
                                           id="user_image">

                                </div>

                                <?php if (!empty(get_user_meta($current_user_id, 'user_image', true))) { ?>

                                    <?php $conf_message = '"' . get_custom_string_return("Do you want to remove your profile image?") . '"'; ?>

                                    <?php $yes = '"' . get_custom_string_return("Yes") . '"'; ?>

                                    <?php $no = '"' . get_custom_string_return("No") . '"'; ?>

                                    <a class="user_image_delete_cl glyphicon glyphicon-remove"
                                       onclick='return delete_profile_image(<?php echo $conf_message . "," . $yes . "," . $no; ?>)'
                                       id="delete_profile_image" href="javascript:;"></a>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="fl-account-freelancer-user-id-display"><?= $current_user_id ?></div>
                <div class="right_content_text">
                    <h4><?php echo ucfirst($current_user->display_name); ?></h4>

                    <hr>
                    <p>
                        <b>Residence Country: </b>
                        <?php echo (get_user_meta($current_user_id, 'user_residence_country', true) < 0) ? 'Not Exist' : get_country_by_index(get_user_meta($current_user_id, 'user_residence_country', true)); ?>
                    </p>
                    <p>
                        <b>City: </b>
                        <?php echo empty(get_user_meta($current_user_id, 'user_city', true)) ? '' : get_user_meta($current_user_id, 'user_city', true); ?>
                    </p>
                    <p><?php echo translater_rating($current_user_id, 17,'translator'); ?></p>
                    <?php
                    $user_time_zone = get_user_meta($current_user_id, 'user_time_zone', true);
                    if ($user_time_zone) {
                        date_default_timezone_set($user_time_zone);
                    }
                    $local_time = date('h:i:s a');

                    ?>
                    <p>
                        <b>Local Time: </b>
                        <?php echo (get_user_meta($current_user_id, 'user_time_zone', true)) ? $local_time : ''; ?></p>
                    <p><b>Success Rate:</b> 97%</p>
                    <p><b>My
                            Rate:</b> <?php echo (get_user_meta($current_user_id, 'user_hourly_rate', true)) ? '$' . get_user_meta($current_user_id, 'user_hourly_rate', true) . ' USD/hr' : ''; ?>
                    </p>
                    <p><b>Project Completed:</b> 98%</p>

                    <div class="edit-acc enhanced-text">
                        <a class=small-text"  href="" data-toggle="modal"
                           data-target="#profiledetailModel"><?php get_custom_string("Update Info (Private)"); ?></a>
                    </div>

                    <div style="margin-top: 3em">
                        <?php get_template_part('includes/user/society/society', 'referral-code');?>
                    </div>

                </div>
            </div>
        </div>
        <div class="row table-container">

            <!-- START: Public Profile-->

            <div class="accdetal-table">


                <!-- <?php echo ' ' . translater_rating($current_user_id, 20,'translator'); ?> -->

                <div class="acc-description">

                    <h4><?php get_custom_string("Public Profile"); ?></h4>

                    <div class="edit-acc enhanced-text">

                        <a class=small-text"  href="" data-toggle="modal"
                           data-target="#summaryModel"><?php get_custom_string("Update Summary"); ?></a>

                    </div>

                    <?php //} ?>

                    <table class="data-table education-data" id="public_table">

                        <thead>
                        <tr>
                            <th><?php get_custom_string("Summary"); ?></th>
                        </tr>
                        </thead>

                        <tbody>

                        <tr>
                            <td><?php echo empty(get_user_meta($current_user_id, 'description', true)) ? 'Not exist' : stripslashes(get_user_meta($current_user_id, 'description', true)); ?></td>
                        </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- END: Public Profile-->

            <div class="accdetal-table">
                <div class="acc-description">

                    <h4><?php get_custom_string("List of Skills"); ?></h4>


                    <table class="data-table education-data" id="skills_table">

                        <thead>
                        <tr>
                            <th><?php get_custom_string("List of Skills"); ?></th>
                        </tr>
                        </thead>

                        <tbody>

                        <tr>
                            <td><?php echo trim(implode(",", $contentTagsAr), ','); ?></td>
                        </tr>

                        </tbody>

                    </table>

                </div>

            </div>
            <!------------------->


            <!---->


            <?php

            $all_conts = [];
            $all_conts_new_slider = [];

            $content_detail = $wpdb->get_results("select * from wp_linguist_content where user_id = $current_user_id", ARRAY_A);
            foreach ($content_detail as $inside_content) {


                if (strlen($inside_content['content_summary']) > 25) {

                    $pos = strpos($inside_content['content_summary'], ' ', 25);

                    $desc_all = substr($inside_content['content_summary'], 0, $pos) . '...';

                } else {

                    $desc_all = $inside_content['content_summary'];

                }

                //code-notes [image-sizing]  content getting small cover image
                $imageCunt = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
                    $inside_content['content_cover_image'],FreelinguistSizeImages::SMALL,true);

                $userdetail = get_userdata($inside_content['user_id']);
                $country = get_user_meta($inside_content['user_id'], 'user_residence_country', true);
                $country = ($country ? get_countries()[$country] : 'N/A');

                if ($inside_content['content_sale_type'] == 'Fixed') {
                    $priceValue = '$' . $inside_content['content_amount'];
                } else if ($inside_content['content_sale_type'] == 'Offer') {
                    $priceValue = 'Best Offer';
                } else if ($inside_content['content_sale_type'] == 'Free') {
                    $priceValue = '';
                } else {
                    $priceValue = '$' . $inside_content['content_amount'] . '/' . $inside_content['content_sale_type'];
                }

                //code-notes adding in dynamic view
                $noOfDone = '';
                if (isset($inside_content['content_view']) && $inside_content['content_view']) {
                    $number_views = intval($inside_content['content_view']);
                    $view_word = 'View';
                    if ($number_views > 1) {
                        $view_word = 'Views';
                    }
                    $noOfDone = "$number_views $view_word";
                }

                // end dynamic view


                $all_conts[] = /** @lang text */
                    '<li>

					<a href="' . freeling_links('linguist_add_content_url') . '&mode=edit&content_id=' . FreelinguistContentHelper::encode_id($inside_content['id']) . '">

                       <span class="u_image"><img style="" src="' . $imageCunt . '" draggable="false"></span>

                    </a>

                       <div class="grid-con">

                            <ul>

                            	<li>

                                    <h4>' . $inside_content['content_title'] . '</h4>

                                    <p>' . $desc_all . '</p>

                                </li>

                                <li>

                                    <div class="floatleft">Amount- ' . $inside_content['content_amount'] . '</div>

                                    <div class="floatright">Type- ' . $inside_content['content_type'] . '</div>

                                </li>

                                <li>

                                    <div class="floatleft">Sale- ' . $inside_content['content_sale_type'] . '</div>

                                    <div class="floatright">Number For Sale- ' . $inside_content['max_to_be_sold'] . '</div>

                                </li>

                            </ul>

                        </div>

                    </li>';
                $favIds = get_user_meta($current_user_id, '_favorite_content', true);
                $all_conts_new_slider[] = /** @lang text */
                    '<div class="user-info">

							<div class="slide-inn">

							<span class="fav add-favourited' . (in_array($inside_content['id'], explode(',', $favIds)) ? ' favourited' : '') . '" data-fav="' . (in_array($inside_content['id'], explode(',', $favIds)) ? '1' : '0') . '" data-c_type="content" data-id="' . $inside_content['id'] . '"></span>
							<a href="' . freeling_links('linguist_add_content_url') . '&mode=edit&content_id=' . FreelinguistContentHelper::encode_id($inside_content['id']) . '">

								<figure>

									<img src="' . $imageCunt . '" alt="freelinguist">

									

									

								</figure>

								<div class="description-user">

								<span class="eye"><img src="' . get_stylesheet_directory_uri() . '/images/eye-see.png" alt="freelinguist"></span>

								<ul>

								
								
								<li class="li-1"><span>' . stripcslashes(substr($inside_content['content_title'], 0, 25)) . '</span></li>
								<li class="li-22">' . substr($inside_content['content_summary'], 0, 55) . '</li>
								<li class="li-2"><span>' . $userdetail->display_name . '</span> <span class="pull-right">'.$noOfDone.'</span></li>
								<li class="li-2"><span>' . $country .'</span> <span class="pull-right colored">' . $priceValue . '</span></li>
								<li class="li-2" style="display:none">
								  <!-- code-notes hiding delete link -->  
								  <span>Usage</span> 
								  <span class="pull-right colored delete-content" data-id="' . $inside_content['id'] . '">Delete Content</span></li>
								</ul>

								

								</div>

								</a></div></div>';


            }

            $all_conts[] = /** @lang text */
                '<li>

                       <span class="u_image"><a class="addcont" href="' . freeling_links("linguist_add_content_url") . '"><img style="" src="' . get_stylesheet_directory_uri() . '/images/add_conta.jpeg" draggable="false"></a></span>

                    </li>';

            $allCunts = implode('', $all_conts);
            $allCunts_new = implode('', $all_conts_new_slider);


            $completed_jobs_array = [];


            $completed_jobs_html = implode('', $completed_jobs_array);
            ?>

            <!-- END: Demo file-->


            <!-- START: Sample Work-->

            <div class="accdetal-table no-border">
                <div class="acc-description">
                    <h4>Content for Sale</h4>

                    <table class="data-table education-data" id="sale_table">

                        <thead>
                        <tr>
                            <th>Content for Sale</th>
                        </tr>
                        </thead>

                        <tbody>

                        <tr>
                            <td>
                                <div class="slider">

                                    <div class="responsive">

                                        <?php echo $allCunts_new; ?>

                                    </div>

                                </div>
                            </td>
                        </tr>

                        </tbody>

                    </table>


                </div>


            </div>

            <!-- END: Sample Work-->

            <!-- START: My Education Area -->

            <div class="accdetal-table">


                <div class="edit-acc enhanced-text">

                    <a class=small-text"  href="" data-toggle="modal"
                       data-target="#educationModel"><?php get_custom_string("Update Education Info"); ?></a>

                </div>

                <?php //} ?>

                <h4><?php get_custom_string("Education"); ?></h4>

                <table class="data-table education-data" id="education_table">

                    <thead>

                    <tr>

                        <th><?php get_custom_string("Years attended"); ?></th>

                        <th><?php get_custom_string("Institution"); ?> </th>

                        <th><?php get_custom_string("Degree"); ?></th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    $total_edu = empty(get_user_meta($current_user_id, 'education_counter', true)) ? 0 : get_user_meta($current_user_id, 'education_counter', true);

                    for ($i = 0; $i <= $total_edu - 1; $i++) {

                        $year_attended = get_user_meta($current_user_id, 'year_attended_' . $i, true);

                        $institution = get_user_meta($current_user_id, 'institution_' . $i, true);

                        $degree = get_user_meta($current_user_id, 'degree_' . $i, true);

                        if (!empty($institution) || !empty($year_attended) || !empty($degree)) {

                            ?>

                            <tr>

                                <td><?php echo $year_attended; ?></td>

                                <td><?php echo $institution; ?></td>

                                <td><?php echo $degree; ?></td>

                            </tr>

                        <?php }

                    } ?>

                    </tbody>

                </table>

            </div>

            <!-- END: My Education Area -->


            <!-- START: My Licenses/Certificates/Awards/ Area -->

            <div class="accdetal-table">


                <div class="edit-acc enhanced-text">

                    <a class=small-text"  href="" data-toggle="modal"
                       data-target="#CertificatesModel"><?php get_custom_string('Update Certificates Info'); ?></a>

                </div>

                <?php //} ?>

                <h4><?php get_custom_string("Licenses/Certificates/Awards"); ?></h4>

                <table class="data-table license-data" id="certificates_table">

                    <thead>

                    <tr>

                        <th><?php get_custom_string("Year received"); ?></th>

                        <th><?php get_custom_string("Received From"); ?></th>

                        <th><?php get_custom_string("License/Certificate/Awards"); ?></th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    $total_edu = empty(get_user_meta($current_user_id, 'certification_counter', true)) ? 0 : get_user_meta($current_user_id, 'certification_counter', true);

                    for ($i = 0; $i <= $total_edu - 1; $i++) {

                        $year_recieved_ = get_user_meta($current_user_id, 'year_recieved_' . $i, true);

                        $recieved_from_ = get_user_meta($current_user_id, 'recieved_from_' . $i, true);

                        $certificate = get_user_meta($current_user_id, 'certificate_' . $i, true);


                        ?>

                        <tr>

                            <td><?php echo $year_recieved_; ?></td>

                            <td><?php echo $recieved_from_; ?></td>

                            <td><?php echo $certificate; ?></td>

                        </tr>

                        <?php //}

                    } ?>

                    </tbody>

                </table>

            </div>

            <!-- END: My Licenses/Certificates/Awards/ Area -->


            <!-- START: Related work experiences Area -->

            <div class="accdetal-table">


                <div class="edit-acc enhanced-text">

                    <a class=small-text"  href="" data-toggle="modal"
                       data-target="#relatedWorkExperienceModel"><?php get_custom_string('Update Experience Info'); ?></a>

                </div>

                <?php //} ?>

                <h4><?php get_custom_string("Related work experiences"); ?></h4>

                <table class="data-table experience-data" id="work_table">

                    <thead>

                    <tr>

                        <th><?php get_custom_string("Year in service"); ?></th>

                        <th><?php get_custom_string("Employer"); ?></th>

                        <th><?php get_custom_string("Duties"); ?></th>
                        <th><?php get_custom_string("Skills"); ?></th>
                    </tr>

                    </thead>

                    <tbody>



                        <?php

                        $total_edu = empty(get_user_meta($current_user_id, 'related_experience_counter', true)) ? 0 : get_user_meta($current_user_id, 'related_experience_counter', true);

                        for ($i = 0;
                        $i <= $total_edu - 1;
                        $i++){

                        $year_in_service = get_user_meta($current_user_id, 'year_in_service_' . $i, true);

                        $employer = get_user_meta($current_user_id, 'employer_' . $i, true);

                        $duties = get_user_meta($current_user_id, 'duties_' . $i, true);

                        if (!empty($year_in_service) || !empty($employer) || !empty($duties)){

                        ?>

                    <tr>

                        <td><?php echo $year_in_service; ?></td>

                        <td><?php echo $employer; ?></td>

                        <td><?php echo $duties; ?></td>
                        <td><?php echo $duties; ?></td>
                    </tr>

                    <?php }

                    } ?>


                    </tbody>

                </table>

            </div>

            <!-- END: Related work experiences Area -->


            <!-- START: Language Area -->
            <div class="accdetal-table">
                <div class="table-responsive">

                    <div class="edit-acc enhanced-text">

                        <a class=small-text"  href="" data-toggle="modal" name="language"
                           data-target="#languagesModel"><?php get_custom_string("Update"); ?></a>

                    </div>

                    <h4><?php get_custom_string("Languages"); ?></h4>

                    <table id="language_table">

                        <thead>

                        <tr>

                            <th><?php get_custom_string("Language"); ?></th>

                            <th><?php get_custom_string("Level"); ?></th>

                            <th><?php get_custom_string("Years of experience"); ?></th>
                            <th><?php get_custom_string("Areas/expertise"); ?></th>
                        </tr>

                        </thead>

                        <tbody>


                            <?php

                            $total_lang = empty(get_user_meta($current_user_id, 'language_counter', true)) ? 0 : get_user_meta($current_user_id, 'language_counter', true);


                            for ($i = 0;
                            $i < $total_lang;
                            $i++){

                            $language = get_user_meta($current_user_id, 'language_' . $i, true);

                            $language_level = get_user_meta($current_user_id, 'language_level_' . $i, true);

                            $year_of_experince = get_user_meta($current_user_id, 'year_of_experince_' . $i, true);

                            $areas_expertise = get_user_meta($current_user_id, 'areas_expertise_' . $i, true);

                            if (!empty($language_level)){
                            ?>

                        <tr>

                            <td><?php echo $language; ?></td>

                            <td><?php echo $language_level; ?></td>

                            <td><?php echo $year_of_experince; ?></td>
                            <td><?php echo $areas_expertise; ?></td>
                        </tr>

                        <?php }

                        } ?>

                        </tbody>

                    </table>

                </div>
            </div>
            <div class="accdetal-table">


                <!-- END: Language Area -->


                <!-- START: Experience in translation area -->

                <!---->

                <!-- END: Experience in translation area -->


                <!-- START: Experience in editing/proofreading  -->

                <!---->

                <!-- END: Experience in editing/proofreading -->


                <!-- START: Experience in writing -->

                <!---->
            </div>
            <!-- END: Experience in writing -->


            <!-- START: Customer reviews -->

            <div class="accdetal-table">



                <h4><?php get_custom_string("Customer reviews"); ?></h4>

                <table class="data-table" id="customer_review_table">

                    <thead>

                    <tr>

                        <th width="14%"><?php echo get_custom_string_return("Rating"); ?></th>

                        <th width="40%"><?php get_custom_string("Comments"); ?> </th>

                        <th><?php get_custom_string("Client"); ?> </th>
						<th><?php get_custom_string("Job Type"); ?></th>
                        <th width="7%"><?php get_custom_string("Job"); ?></th>

                        <th><?php get_custom_string("Date"); ?></th>

                       

                    </tr>

                    </thead>

                    <tbody>

                    <?php

						$new_feedback_array = array();
                        $feedback_is = $wpdb->get_results("SELECT * FROM wp_linguist_content WHERE user_id = $current_user_id and rating_by_customer IS NOT NULL AND rating_by_freelancer IS NOT NULL order by id desc limit 10",ARRAY_A);
						
							
                        for ($i = 0; $i < count($feedback_is); $i++) {
							
							array_push($new_feedback_array,$feedback_is[$i]);

						}
                        $feedback_is_2 = $wpdb->get_results("SELECT * FROM wp_proposals WHERE by_user = $current_user_id and rating_by_customer IS NOT NULL  AND rating_by_freelancer IS NOT NULL order by id desc limit 10",ARRAY_A);

                        for ($i = 0; $i < count($feedback_is_2); $i++) {
							array_push($new_feedback_array,$feedback_is_2[$i]);
						}
						
						$feedback_is_3 = $wpdb->get_results("SELECT * FROM wp_fl_job WHERE linguist_id = $current_user_id and rating_by_customer IS NOT NULL  AND rating_by_freelancer IS NOT NULL order by id desc limit 10",ARRAY_A);

						for ($i = 0; $i < count($feedback_is_3); $i++) {
							array_push($new_feedback_array,$feedback_is_3[$i]);
						}
                           
						$new_feedback_array = array_sort($new_feedback_array, 'updated_at', SORT_DESC);
						foreach($new_feedback_array as $k=>$v){
							
						?>
							 <tr>
								<td>

									<?php

									$feedbak_rating = $v['rating_by_customer'];

									echo job_rating($feedbak_rating);

									?>

								</td>

								<td><?php echo stripslashes($v['comments_by_customer']); ?></td>

								<td>

									<?php

								  //$post_data = get_post($feedback_is_2[$i]->post_id);
									$customer_id = '';
									$job_type = '';
									$job_title = '';
									if(isset($v['content_title'])) {
										$customer_id = $v['purchased_by'] ;
										$job_type = 'Content';
										$job_title = $v['content_title'];
										
									}elseif(isset($v['customer'])){
										$customer_id = $v['customer'];
										$job_type = 'Proposal';
										//$job_title =get_the_title($v['post_id']);
										$job_title =get_post_meta( $v['post_id'], 'project_title', true );
										 
									}else if($v['author']){
										$customer_id = $v['author'] ;
										$job_type = 'Project';
										//$job_title =get_the_title($v['project_id']);
										$job_title =get_post_meta( $v['project_id'], 'project_title', true );
									}
									$post_author = get_userdata($customer_id);
                                    if ($post_author && $post_author->ID) {
                                        echo $post_author->display_name;
                                    } else {
                                        echo "<!-- no post author -->";
                                    }


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

            <!-- END: Customer reviews -->

        </div>

    </div>

</div>


<!-- Include code for popup -->

<?php include('account-model-translater.php'); ?>
<script>
    jQuery(function() {
        jQuery('.delete-content').click(function () {
            var c = confirm("Are you sure !");
            if (c) {
                var content_id = jQuery(this).data('id');

                jQuery.ajax({

                    url: adminAjax.url,

                    type: "POST",

                    data: {'action': 'delete_linguist_content', 'delete_content_id': content_id},

                    dataType: 'json',

                    success: function (response) {

                        if (response.status === true) {
                            window.location.reload();
                        } else {
                            alert(response.message);
                        }

                    }

                });

                return false;
            }
        });
        jQuery('.responsive').slick({

            dots: false,

            infinite: false,

            speed: 300,

            slidesToShow: 4,

            slidesToScroll: 1,

            responsive: [

                {

                    breakpoint: 1199,

                    settings: {

                        slidesToShow: 2,

                        slidesToScroll: 1,

                        infinite: true,

                        dots: true

                    }

                },

                {

                    breakpoint: 991,

                    settings: {

                        slidesToShow: 2,

                        slidesToScroll: 1

                    }

                },

                {

                    breakpoint: 639,

                    settings: {

                        slidesToShow: 1,

                        slidesToScroll: 1

                    }

                }

                // You can unslick at a given breakpoint now by adding:

                // settings: "unslick"

                // instead of a settings object

            ]

        });
    });

</script>
<script>
    jQuery(function () {
        // alert(3);
        jQuery('body').on('click', '.add-favourited', function () {
            ajaxindicatorstart('loading data.. please wait..');
            var elem = jQuery(this);
            var id = jQuery(this).attr('data-id');
            var fav = parseInt(jQuery(this).attr('data-fav'));

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
                        ajaxindicatorstop();
                        if (response.status !== 1) {
                            will_handle_ajax_error("favorite error",response.message);
                        }
                    }
                });
            }
        });
    });
</script>
