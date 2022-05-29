<?php
/*

Translater Account File

*/

/*
* current-php-code 2020-Oct-17
* input-sanitized : lang,user
* current-wp-template:  freelancer profile view
*/
#code-notes In the freelancer profile, the hire buttons for the other freelancer (found in related users in the right column)do anything right now. Will set the links to go to their profiles
#code-notes The rate per hour for related users is now not hard-coded to be $15

$b_url = (int)FLInput::get('b_url');
$lang = FLInput::get('lang','en');

$user_login_asked_for = FLInput::get('user');
?>
    <!--code-notes download and use local version of slick js and css-->
<link href="<?php echo get_template_directory_uri().'/css/current-code/content-customer-read.css' ?>" rel="stylesheet">
<style>
    @media (max-width: 767px) {

        #skills_table tbody td::before, #sale_table tbody td::before, #public_table tbody td::before {
            display: none;
            width: 0px;

        }

        #skills_table tbody td, #sale_table tbody td, #public_table tbody td {
            padding-left: 20px;
        }

        #customer_review_table thead, #skills_table thead {
            display: none;
        }

        .rr table tbody tr td.thrd-td h5 strong {
            width: auto !important;
        }

        #customer_review_table tbody td:first-child:before {
            content: "Rating";
        }

        #customer_review_table tbody td:nth-child(2):before {
            content: "Comments";
        }

        #customer_review_table tbody td:nth-child(3):before {
            content: "Customer";
        }

        #customer_review_table tbody td:nth-child(4):before {
            content: "Job Type";
        }

        #customer_review_table tbody td:nth-child(5):before {
            content: "Job";
        }

        #customer_review_table tbody td:nth-child(6):before {
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

        #language_table tbody td:nth-child(2):before {
            content: "LEVEL";
        }

        #language_table tbody td:nth-child(3):before {
            content: "YEARS OF EXPERIENCE";
        }

        #language_table tbody td:nth-child(4):before {
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

        #work_table tbody td:nth-child(2):before {
            content: "EMPLOYER";
        }

        #work_table tbody td:nth-child(3):before {
            content: "DUTIES";
        }

        #work_table tbody td:nth-child(4):before {
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

        #certificates_table tbody td:nth-child(2):before {
            content: "RECEIVED FROM";
        }

        #certificates_table tbody td:nth-child(3):before {
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

        #education_table tbody td:nth-child(2):before {
            content: "INSTITUTION";
        }

        #education_table tbody td:nth-child(3):before {
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

    span.fl-profile-linguist-id {
        color: lightgray;
        position: absolute;
        right: 0;
        top: 60%
    }
</style>

<!-- get society css-->
<?php get_template_part('includes/user/society/society', 'style');?>
<!-- end society css-->

<?php

if (!defined('WPINC')) {
    die;
}



global $wpdb;



$translator = get_user_by('slug',$user_login_asked_for);

if (empty($translator) || empty($translator->ID)) {
    //trigger 404 page and exit
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}
$translator_id = $translator->ID;

$user_role = $translator->roles[0];

$author_id = $translator->ID;

$current_user_id = get_current_user_id();
$favContentIds = get_user_meta($current_user_id, '_favorite_content', true);
$favTranslatorIds = get_user_meta($current_user_id, '_favorite_translator', true);

$contentTagsAr = [];
$content_detail = $wpdb->get_row(
        "SELECT GROUP_CONCAT(tag_id) as tags FROM wp_tags_cache_job 
                  WHERE `job_id` = $translator_id AND type = ".FreelinguistTags::USER_TAG_TYPE, ARRAY_A);

will_log_on_wpdb_error($wpdb,'AAA0');
if ($content_detail['tags']) {
    $contentTags = $wpdb->get_results(
            "SELECT tag_name FROM wp_interest_tags WHERE id IN (" . $content_detail['tags'] . ")", ARRAY_A);
    will_log_on_wpdb_error($wpdb,'AAA1');
    $contentTagsAr = [];
    if ($contentTags) {
        $contentTagsAr = array_column($contentTags, 'tag_name');
    }
}
$prefilled = json_encode($contentTagsAr);


?>

<section class="landing-page" style="margin-bottom: 10em">

    <div class="container">

        <div class="row">

            <div class="col-xs-12 col-sm-8 col-md-9 landing-left">


                <div class="upper-deciption">

                    <div class="figure fl-relative-position">
                        <div class="freelingust-translator-info-favorite-holder">
                            <?php
                            set_query_var( 'for_user_id', $translator_id );
                            get_template_part('includes/user/author-user-info/translator', 'button-favorite');
                            //code-notes new favorite position
                            ?>
                        </div>

                        <?php

                        //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                        $avatar = hz_get_profile_thumb($translator_id,FreelinguistSizeImages::LARGE,true);

                        ?>
                        <img class="" style="" src="<?= $avatar ?>">
                    </div>


                    <div class="description-right">

                        <span class="bold-and-blocking large-text"><?php echo ucfirst($translator->display_name); ?></span>

                        <ul>

                            <li class="enhanced-text">
                                <strong>Residence Country: </strong>
                                <?php echo (get_user_meta($translator_id, 'user_residence_country', true) < 0) ?
                                    'Not Exist' :
                                    get_country_by_index(get_user_meta($translator_id, 'user_residence_country', true));
                                ?>
                            </li>

                            <li class="enhanced-text">
                                <strong>City: </strong>
                                <?php echo empty(get_user_meta($translator_id, 'user_city', true)) ?
                                    '' :
                                    get_user_meta($translator_id, 'user_city', true); ?>
                            </li>

                            <li class="opensans enhanced-text">
                                <?php
                                    echo translater_rating($author_id, 17,'translator');
                                ?>
                            </li>

                            <li class="enhanced-text">
                                <strong>Local Time: </strong> <br>
                                <?= freelinguist_user_get_local_time($translator_id,true,false) ?>
                            </li>

                            <li class="opensans enhanced-text">Success Rate: 98%</li>

                            <li class="opensans">Rate:
                                <?php echo (get_user_meta($translator_id, 'user_hourly_rate', true)) ?
                                    '$' . get_user_meta($translator_id, 'user_hourly_rate', true) . ' USD/hr' :
                                    ''; ?>
                            </li>

                            <li class="opensans enhanced-text">Project Completed: 97%</li>

                        </ul>


                        <?php if (get_user_meta($author_id, 'approve_description', true)) : ?>
                            <div class="description-lowest">
                                <?php echo stripslashes(get_user_meta($author_id, 'approve_description', true)); ?>
                                <br>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 1em">
                            <?php
                            set_query_var( 'referral_code_of_user',$author_id );
                            get_template_part('includes/user/society/society', 'referral-code');
                            ?>
                        </div>

                    </div>


                </div>

                <div class="accdetal-table">


                    <div class="acc-description">

                        <h4><?php get_custom_string("Public Profile"); ?></h4>


                        <table class="data-table education-data" id="public_table">

                            <thead>
                                <tr>
                                    <th><?php get_custom_string("Summary"); ?></th>
                                </tr>
                            </thead>

                            <tbody>

                            <tr>
                                <td><?php echo empty(get_user_meta($translator_id, 'description', true)) ?
                                        'Not exist' :
                                        stripslashes(get_user_meta($translator_id, 'description', true)); ?>
                                </td>
                            </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- END: Public Profile-->

                <div class="accdetal-table">
                    <div class="acc-description">

                        <h4><?php get_custom_string("List of Skills"); ?></h4>


                        <table class="data-table" id="skills_table">

                            <tbody>

                            <tr>
                                <td><?php echo trim(implode(",", $contentTagsAr), ','); ?></td>
                            </tr>

                            </tbody>

                        </table>

                    </div>

                </div>
                <!------------------->

                <?php

                $all_conts = array();

                $content_detail = $wpdb->get_results("SELECT * FROM wp_linguist_content WHERE user_id = $translator_id", ARRAY_A);
                will_log_on_wpdb_error($wpdb,'AAA3');
                $all_conts_new_slider = [];
                foreach ($content_detail as $inside_content) {

                    $noOfDone = '';
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

                    $href = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($inside_content['id']);

                    $all_conts[] = /** @lang text */
                        '<li>

					<a href="' . $href . '">

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

                                    <div class="floatright">Number for Sale- ' . $inside_content['max_to_be_sold'] . '</div>

                                </li>

                            </ul>

                        </div>

                    </li>';
                    $class = "";
                    $fav = '0';
                    $data_login = "0";

                    if (is_user_logged_in()) {
                        $data_login = "1";
                    }
                    if (in_array($inside_content['id'], explode(',', $favContentIds))) {
                        $class = "favourited";
                        $fav = "1";
                    }
                    //code-notes adding in dynamic view

                    if (isset($inside_content['content_view']) && $inside_content['content_view']) {
                        $number_views = intval($inside_content['content_view']);
                        $view_word = 'View';
                        if ($number_views > 1) {
                            $view_word = 'Views';
                        }
                        $noOfDone = "$number_views $view_word";
                    }

                    // end dynamic view


                    $all_conts_new_slider[] = /** @lang text */
                        '<div class="user-info">

							<div class="slide-inn">

							<span class="fav add-favourited ' . $class .
                                '" data-fav="' . $fav . '" data-id="' . $inside_content['id'] .
                                '" data-login="' . $data_login . '" data-c_type = "content"></span>
							<a href="' . $href . '">

								<figure>

									<img src="' . $imageCunt . '" alt="freelinguist" class="freelinguist-max-width">

									

									

								</figure>

								<div class="description-user">

								<span class="eye"><img src="' . get_stylesheet_directory_uri() . '/images/eye-see.png" alt="freelinguist"></span>

								<ul>

								
								
								<li class="li-1"><span>' . stripcslashes(substr($inside_content['content_title'], 0, 25)) . '</span></li>
								<li class="li-22"><span  class="one-line-no-overflow">' . substr($inside_content['content_summary'], 0, 55) . '</span></li>
								<li class="li-2">' . $country . '</li>
								<li class="li-2"><span>' . $userdetail->display_name . '</span> <span class="pull-right">'.$noOfDone.'</span></li>
								<li class="li-2"><span>' . str_word_count(stripcslashes($inside_content['content_summary'])) .
                                    'Words</span> <span class="pull-right colored">' . $priceValue . '</span></li>

								</ul>

								

								</div>

								</a></div></div>';


                }

                $all_conts[] = /** @lang text */
                    '<li>

                       <span class="u_image"><a class="addcont" href="' .
                    freeling_links("linguist_add_content_url") . '"><img style="" src="' .
                    get_stylesheet_directory_uri() . '/images/add_conta.jpeg" draggable="false"></a></span>

                    </li>';

                $allCunts = implode('', $all_conts);
                $allCunts_new = implode('', $all_conts_new_slider);


                ?>


                <div class="accdetal-table no-border">
                    <div class="acc-description">
                        <h4>Content for Sale</h4>

                        <table class="data-table education-data" id="sale_table">

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


                <div class="accdetal-table">
                    <div class="table-responsive">


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

                            $total_edu = empty(get_user_meta($translator_id, 'education_counter', true)) ?
                                0 :
                                get_user_meta($translator_id, 'education_counter', true);

                            for ($i = 0; $i <= $total_edu - 1; $i++) {

                                $year_attended = get_user_meta($translator_id, 'year_attended_' . $i, true);
                                $institution = get_user_meta($translator_id, 'institution_' . $i, true);
                                $degree = get_user_meta($translator_id, 'degree_' . $i, true);
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
                </div>
                <div class="accdetal-table">

                    <h4> <?php get_custom_string("Licenses/Certificates/Awards"); ?></h4>

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

                        $total_edu = empty(get_user_meta($author_id, 'certification_counter', true)) ?
                            0 :
                            get_user_meta($author_id, 'certification_counter', true);

                        for ($i = 0; $i <= $total_edu - 1; $i++) {

                            $year_recieved_ = get_user_meta($author_id, 'year_recieved_' . $i, true);
                            $recieved_from_ = get_user_meta($author_id, 'recieved_from_' . $i, true);
                            $certificate = get_user_meta($author_id, 'certificate_' . $i, true);
                            if (!empty($year_recieved_) || !empty($recieved_from_) || !empty($certificate)) { ?>

                                <tr>

                                    <td><?php echo $year_recieved_; ?></td>

                                    <td><?php echo $recieved_from_; ?></td>

                                    <td><?php echo $certificate; ?></td>

                                </tr>

                                <?php

                            }

                        } ?>

                        </tbody>

                    </table>

                </div>

                <!---->


                <!-- END: My Licenses/Certificates/Awards/ Area -->


                <!-- START: Related work experiences Area -->

                <div class="accdetal-table">
                    <div class="table-responsive">


                        <h4><?php get_custom_string("Related work experiences"); ?></h4>

                        <table id="work_table">

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

                                $total_edu = empty(get_user_meta($translator_id, 'related_experience_counter', true)) ?
                                    0 :
                                    get_user_meta($translator_id, 'related_experience_counter', true);
                                for ($i = 0;
                                $i <= $total_edu - 1;
                                $i++){

                                $year_in_service = get_user_meta($translator_id, 'year_in_service_' . $i, true);
                                $employer = get_user_meta($translator_id, 'employer_' . $i, true);
                                $duties = get_user_meta($translator_id, 'duties_' . $i, true);
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
                </div>


                <div class="accdetal-table">
                    <div class="table-responsive">

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

                                $total_lang = empty(get_user_meta($translator_id, 'language_counter', true)) ?
                                    0 :
                                    get_user_meta($translator_id, 'language_counter', true);

                                for ($i = 0;
                                $i < $total_lang;
                                $i++){

                                $language = get_user_meta($translator_id, 'language_' . $i, true);
                                $language_level = get_user_meta($translator_id, 'language_level_' . $i, true);
                                $year_of_experince = get_user_meta($translator_id, 'year_of_experince_' . $i, true);
                                $areas_expertise = get_user_meta($translator_id, 'areas_expertise_' . $i, true);
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
                <!-- END: Related work experiences Area -->



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
                        $feedback_is = $wpdb->get_results(
                                "SELECT * FROM wp_linguist_content 
                                        WHERE user_id = $author_id and rating_by_customer IS NOT NULL 
                                        AND rating_by_freelancer IS NOT NULL order by id desc limit 10", ARRAY_A);

                        will_log_on_wpdb_error($wpdb,'AAA4');
                        for ($i = 0; $i < count($feedback_is); $i++) {

                            array_push($new_feedback_array, $feedback_is[$i]);

                        }
                        $feedback_is_2 = $wpdb->get_results(
                                "SELECT * FROM wp_proposals WHERE by_user = $author_id 
                                          AND rating_by_customer IS NOT NULL  
                                          AND rating_by_freelancer IS NOT NULL order by id desc limit 10", ARRAY_A);

                        will_log_on_wpdb_error($wpdb,'AAA5');
                        for ($i = 0; $i < count($feedback_is_2); $i++) {
                            array_push($new_feedback_array, $feedback_is_2[$i]);
                        }

                        $feedback_is_3 = $wpdb->get_results(
                                "SELECT * FROM wp_fl_job WHERE linguist_id = $author_id and rating_by_customer IS NOT NULL
                                            AND rating_by_freelancer IS NOT NULL order by id desc limit 10", ARRAY_A);

                        will_log_on_wpdb_error($wpdb,'AAA6');
                        for ($i = 0; $i < count($feedback_is_3); $i++) {
                            array_push($new_feedback_array, $feedback_is_3[$i]);
                        }

                        $new_feedback_array = array_sort($new_feedback_array, 'updated_at', SORT_DESC);
                        foreach ($new_feedback_array as $k => $v) {

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

                                    $customer_id = '';
                                    $job_type = '';
                                    $job_title = '';
                                    if (isset($v['content_title'])) {
                                        $customer_id = $v['purchased_by'];
                                        $job_type = 'Content';
                                        $job_title = $v['content_title'];

                                    } elseif (isset($v['customer'])) {
                                        $customer_id = $v['customer'];
                                        $job_type = 'Proposal';
                                        $job_title = get_post_meta($v['post_id'], 'project_title', true);

                                    } else if (isset($v['author'])) {
                                        $customer_id = $v['author'];
                                        $job_type = 'Project';
                                        $job_title = get_post_meta($v['project_id'], 'project_title', true);
                                    }
                                    if ($customer_id) {
                                        $post_author = get_userdata($customer_id);

                                        echo $post_author->display_name;
                                    }


                                    ?>

                                </td>

                                <td><?php echo $job_type; ?></td>
                                <td><?php echo $job_title; ?></td>
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


            <div class="col-xs-12 col-sm-4 col-md-3 landing-right">

                <div class="upper-right">

                    <h4><span>Hire <?php echo substr($translator->display_name, 0, 10) ?></span></h4>

                    <h6>
                        <?php
                        $da_name = '';
                        if ($user_login_asked_for) {
                            $username = $user_login_asked_for;
                            $user_class = get_user_by('slug', $username);
                            $da_name = get_da_name($user_class->ID);
                        }
                        ?>
                        Hi <?php echo  $da_name ?>, I noticed your profile and would like to offer you my project.We
                        can discus any
                        details over chat.

                    </h6>
                    <?php if (is_user_logged_in()) { ?>


                        <div class="form-group enhanced-text">

                            <label>My Budget</label>

                            <!-- code-notes redo money input row -->
                            <div class="input-group regular-input-group">
                                <span class="input-group-addon regular-input-group">$</span> <!-- doller-sign  large-text -->
                                <input id = "amount-for-hire" title="Amount" type="text" class="form-control regular-input-group"   value="100.00">
                                <span class=" input-group-addon regular-input-group">USD</span> <!--   large-text -->
                            </div>

                        </div>
                        <!--                        data-target="#hireModel" data-toggle="modal"-->
                        <button id="placebidbutton"
                                class="btn blue-btn next-btn postproject regular-text" type="button"><img
                                    src="<?php echo get_stylesheet_directory_uri() .'/images/post-icon.png' ?>">Hire
                        </button>
                        <script>
                            jQuery(function($){
                                jQuery('button#placebidbutton').click(function() {
                                    let entered_amount = $('input#amount-for-hire').val();
                                    $('input#estimated_budgets').val(entered_amount);
                                    $('div#hireModel').modal();
                                });
                            });
                        </script>
                        <div role="dialog" id="hireModel" class="modal fade">

                            <div class="modal-dialog">

                                <!-- Modal content-->

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <button data-dismiss="modal" class="close huge-text" type="button">×</button>

                                        <h4 class="modal-title"><?php echo "Hire " . $da_name; ?></h4>

                                    </div>

                                    <div class="modal-body">

                                        <div id="alert_message_model"></div>

                                        <form id="hire_linguist_by_customer">


                                            <div class="form-group">


                                                <textarea maxlength="10000" class="form-control" style="height:200px"
                                                          name="description" id="hire_linguist_description" autocomplete="off"
                                                          placeholder="Description" required></textarea>

                                            </div>
                                            <div class="form-group">
                                                <label for="budget">Budget($)</label>
                                                <input title="Budget" type="number" name="estimated_budgets" id="estimated_budgets"
                                                       value="" class="form-control" maxlength="10000" required>


                                            </div>
                                            <div class="form-group">
                                                <label for="budget">Delivery Date</label>
                                                <input type="text" name="delivery_date" id="delivery_date" value=""
                                                       class="form-control" maxlength="10000" placeholder="" readonly
                                                       required>


                                            </div>
                                            <div class="form-group">&nbsp;</div>

                                            <p class="form-submit">

                                                <button type="button" value="" class="btn blue-btn" id="submit"
                                                        name="submit_hire"
                                                        onclick="return hire_linguist();"><?php get_custom_string('Hire'); ?> </button>

                                                <input type="hidden" id="linguist_id" value="<?php echo $author_id; ?>"
                                                       name="linguist_id">

                                                <input type="hidden" id="user" value="<?php echo $user_login_asked_for; ?>"
                                                       name="user">

                                                <input type="hidden" id=""
                                                       value="<?= $lang; ?>"
                                                       name="lang">


                                            </p>

                                        </form>

                                    </div>

                                </div>


                            </div>

                        </div>

                        <p>By clicking the button , you have read and agree to our <a class="colored"
                                                                                      href="<?php echo site_url() . '/terms-of-service'; ?>">

                                Terms & Conditions </a> and <a class="colored"
                                                               href="<?php echo site_url() . '/privacy-peerok'; ?>">

                                Privacy Policy </a>.


                        </p>






                        <div style="padding: 0;margin: 0; position: relative">
                            <a id="report_button" class="box-prement2 grey report-linguist-extra enhanced-text report-linguist-button"
                               href="#"
                               data-toggle="modal" data-target="#openCCboxReport"
                            >
                                Report
                            </a>
                            <span class="fl-profile-linguist-id"><?= $translator_id?></span>
                        </div>


                    <?php } else { ?>
                        <form>

                            <div class="form-group">

                                <label>My Budget</label>

                                <!-- code-notes redid money input row -->
                                <div class="input-group regular-input-group">
                                    <span class="input-group-addon regular-input-group">$</span> <!-- doller-sign  large-text -->
                                    <input id = "amount-for-hire" title="Amount" type="text" class="form-control regular-input-group"   value="100.00">
                                    <span class=" input-group-addon regular-input-group">USD</span> <!--   large-text -->
                                </div>
                            </div>

                            <a class="btn blue-btn next-btn postproject regular-text " name="submit_order"
                               href="<?php echo site_url() . '/login'; ?>"><img
                                        src="<?php echo get_stylesheet_directory_uri().'/images/post-icon.png' ?>">Hire
                            </a>


                            <p>By clicking the button , you have read and agree to our <a class="colored"
                                                                                          href="<?php echo site_url() . '/terms-of-service'; ?>">

                                    Terms & Conditions </a> and <a class="colored"
                                                                   href="<?php echo site_url() . '/privacy-peerok'; ?>">

                                    Privacy Policy </a>.


                            </p>


                            <a class="btn blue-btn next-btn postproject regular-text" name="submit_order"
                               href="<?php echo site_url() . '/login'; ?>"
                            >
                                <i class="fa fa-heart  large-text" aria-hidden="true"></i>
                                Save to Favorite
                            </a>



                            <a id="report_button" class="box-prement2 grey report-linguist-button"
                               href="<?php echo site_url() . '/login'; ?>"
                            >
                                Report
                            </a>


                        </form>


                        <?php
                    }
                    ?>
                    <div class="job_dtls">
                        <div class="modal fade" id="openCCboxReport" tabindex="-1" role="dialog"
                             aria-labelledby="myModalLabel" aria-hidden="true">

                            <div class="modal-dialog">

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

                                        <h4 class="modal-title"><?php get_custom_string('Report'); ?></h4>

                                    </div>

                                    <div class="modal-body">
                                        <div class="freelinguist-report-caution">
                                            <span class="large-text">Submit a report only if it contains inappropriate content</span>
                                        </div>
                                        <form id="report_form" method="post">
                                            <div class="form-group">
                                                <input type="hidden" name="reported_by"
                                                       value="<?php echo get_current_user_id(); ?>">
                                                <input type="hidden" name="content" value="">
                                                <input type="hidden" name="linguist"
                                                       value="<?php echo $translator_id; ?>">
                                                <input type="hidden" name="project" value="">
                                                <input type="hidden" name="contest" value="">
                                                <input type="hidden" name="action" value="hz_submit_report">

                                            </div>
                                            <div class="form-group">
                                                <textarea class="form-control" id="report_note" placeholder="Note"  autocomplete="off"
                                                          name="report_note"></textarea>
                                            </div>

                                            <button type="button" class="btn blue-btn"
                                                    onClick="return submit_report();">Submit
                                            </button>
                                            <div class="freelinguist-after-submit-report">
                                                <span class="small-text"></span>
                                            </div>
                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>


                    </div>
                    <?php
                    global $wpdb;

                    $tag_array = $wpdb->get_results(
                            "SELECT GROUP_CONCAT(tag_id) AS tag_ids FROM  wp_tags_cache_job 
                                      WHERE job_id=$translator_id AND type=". FreelinguistTags::USER_TAG_TYPE);
                    will_log_on_wpdb_error($wpdb,'AAA7');
                    if ((count($tag_array) > 0) && (!empty($tag_array[0]->tag_ids))) {
                        $tag_ids = $tag_array[0]->tag_ids;
                    } else {
                        $tag_ids = "-1111";
                    }

                    $sql = "
                         SELECT
                        
                              u.ID                                                         primary_id,
                              u.user_nicename,
                              ''                                                           user_id,
                              u.display_name                                               title,
                              meta_description.meta_value as  description,
                              '0'                                                          price,
                              ''                                               as  description_image,
                              meta_user_image.meta_value as  image,
                    
                              ''                                                           content_sale_type,
                              'translator'                                                 job_type,
                              '0'                                                          is_sold,
                             look.user_hourly_rate as                               user_hourly_rate 
                    
                            FROM wp_users u
                            
                             INNER JOIN (
                                   SELECT limu.ID as job_id
                                   FROM wp_users limu
                                     INNER JOIN (
                                                  SELECT job_id
                                                  FROM wp_tags_cache_job wtcj
                                                  WHERE wtcj.tag_id IN ($tag_ids)
                                                  AND wtcj.type = ".FreelinguistTags::USER_TAG_TYPE."
                                                  LIMIT 0, 64
                                       ) as inner_life ON inner_life.job_id = limu.ID
                    
                                   ORDER BY RAND()
                                   LIMIT 0, 8 ) as similar_users
                              ON similar_users.job_id = u.ID
    
                    
                              LEFT JOIN wp_usermeta meta_description
                                ON meta_description.user_id = u.ID AND meta_description.meta_key = 'description'
                    
                          
                              LEFT JOIN wp_usermeta meta_user_image
                                ON meta_user_image.user_id = u.ID AND meta_user_image.meta_key = 'user_image'
                           
                              LEFT JOIN   wp_fl_user_data_lookup look ON look.user_id = u.ID
                            ORDER BY RAND() LIMIT 0,8
                        ";
                    $similar_users = $wpdb->get_results($sql, ARRAY_A);
                    will_throw_on_wpdb_error($wpdb);
                    ?>


                    <div class="hire-div">
                        <?php
                        //code-bookmark generate units for the profile page
                        foreach ($similar_users as $key => $user) {

                            $bg_image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($user['image'],FreelinguistSizeImages::SMALL,true);
                            //code-notes [image-sizing]  now using small profile pic for avatar

                            $hourly_rate = (int)$user['user_hourly_rate'];

                            $country = get_user_meta($user['primary_id'], 'user_residence_country', true);
                            $country = ($country ? get_countries()[$country] : 'N/A');

                            $href = site_url() . "/user-account/?lang=$lang&profile_type=translator&user=" . $user['user_nicename'];
                            $user_id = $user['primary_id'];
                            $project_tags = $wpdb->get_row(
                                    "SELECT GROUP_CONCAT(tag_id) as tags FROM wp_tags_cache_job 
                                              WHERE job_id=$user_id AND type = ".FreelinguistTags::USER_TAG_TYPE, ARRAY_A);
                            will_log_on_wpdb_error($wpdb,'AAA11');
                            if ($project_tags['tags']) {

                                $contentTags = $wpdb->get_results(
                                        "SELECT tag_name FROM wp_interest_tags WHERE id IN (" . $project_tags['tags'] . ")",
                                        ARRAY_A);
                                will_log_on_wpdb_error($wpdb,'AAA12');
                                $contentTagsAr = [];
                                if ($contentTags) {
                                    $contentTagsAr = array_column($contentTags, 'tag_name');
                                }
                            }

                            ?>
                            <div style="padding:0;margin-bottom:20px">
                                <div class="user-info" style="width: 100%; display: inline-block;">
                                    <div class="slide-inn">
                                        <a href="<?php echo $href; ?>">
                                            <figure>
                                                <img class="freelinguist-max-width" src="<?php echo $bg_image; ?>" alt="freelinguist"
                                                     style="">
                                            </figure>
                                            <div class="description-user">
                                                <span class="eye">
                                                    <img src="<?php echo get_template_directory_uri().'/images/eye-see.png' ?>"
                                                         alt="freelinguist"/>
                                                </span>
                                                <ul>
                                                    <li class="li-1 enhanced-text">
                                                        <span>
                                                            <?php
                                                            if (isset($contentTagsAr)) {
                                                                echo trim(implode(",", $contentTagsAr), ',');
                                                            }
                                                            ?>
                                                        </span>
                                                    </li>
                                                    <li class="li-22">
                                                        <?php echo substr($user['description'], 0, 55); ?>
                                                    </li>

                                                    <li class="li-2 enhanced-text">
                                                        <span><?= $user['title']; ?></span>
                                                        <span class="pull-right"></span>
                                                    </li>

                                                    <li class="li-2 enhanced-text">
                                                        <span><?php echo $country; ?></span>
                                                        <span class="pull-right colored">
                                                            <?php
                                                            if ($hourly_rate) {
                                                                echo "$hourly_rate/hour";
                                                            } else {
                                                                echo "<!-- rate not set -->";
                                                            }
                                                            ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </a>
                                        <a href="<?= $href ?>"
                                           class="hirebttn hireLinguisthome box-prement2 enhanced-text"
                                        ><i  class="fa fa-user-circle-o" aria-hidden="true"></i>
                                            Hire
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <?php
                        }
                        ?>

                    </div>


                </div>


            </div>

        </div>
        <?php if ($b_url) { ?>

            <a href="<?php echo get_permalink($b_url); ?>" class="btn go_back_to_job blue-btn">Go back to
                job</a>

        <?php } ?>

</section>




<script>


    jQuery(function () {
        jQuery('body').on('click', '.add-favourited', function () {
            var elem = jQuery(this);
            var id = jQuery(this).attr('data-id');
            var c_type = jQuery(this).attr('data-c_type');
            var login = parseInt(jQuery(this).attr('data-login'));
            var fav = parseInt(jQuery(this).attr('data-fav'));

            if (login === 0) {
                window.location.href = devscript_getsiteurl.getsiteurl + "/login/?redirect_to=" + devscript_getsiteurl.getsiteurl + '/content';
                return false;
            }
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
                    data: {action: 'user_add_favorite', id: id, c_type: c_type, fav: fav},
                    success: function (response) {

                        console.log(response);

                        if (parseInt(response.status.toString()) === 1) {
                            if (elem.hasClass('postproject')) {
                                window.location.reload();
                            }
                        } else if (parseInt(response.status.toString()) === -1) {
                            window.location.href = devscript_getsiteurl.getsiteurl + "/login/?redirect_to=" + devscript_getsiteurl.getsiteurl + '/content';
                        } else {


                            if (fav === 1) {
                                elem.removeClass('favourited');
                                elem.attr('data-fav', 1);
                                elem.text("Remove from favourite");
                            } else {
                                elem.addClass('favourited');
                                elem.attr('data-fav', 0);
                                elem.text("Save to favourite");
                            }
                            alert(response.message);
                        }
                    }
                });
            }
        });
    });
</script>

<script>
    jQuery(function() {
        jQuery('.responsive').slick({

            dots: false,

            infinite: false,

            speed: 300,

            slidesToShow: 3,

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
        jQuery("#delivery_date").datepicker({minDate: 0});
    });

</script>