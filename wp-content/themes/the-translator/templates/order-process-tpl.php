<?php

/*

Template Name: Order Process Template

*/

/*
    * current-php-code 2020-Sep-30
    * input-sanitized : lang,job_id
    * current-wp-template:  new projects
    * current-wp-top-template
    */
ob_start();

//check_login_redirection();

add_action('wp_head', 'add_meta_tags', 2); // Cache clear

if (current_user_can('administrator')) {

    wp_redirect(admin_url());

}

get_header();

$lang = FLInput::get('lang', 'en');
$job_id = FLInput::get('job_id');


$check_login = (is_user_logged_in()) ? 1 : 0;

global $wpdb;


$user_ID = get_current_user_id();

$post_files = array();


$jtype = get_post_meta($job_id, 'fl_job_type', true);
$tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
if ($jtype == 'contest') {
    $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
} else if ($jtype == 'project') {
    $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
}

$project_description = stripslashes_deep(get_post_meta($job_id, 'project_description', true));

$estimated_budgets = get_post_meta($job_id, 'estimated_budgets', true);

$project_title = stripslashes_deep(get_post_meta($job_id, 'project_title', true));



$cart_files = [];


if (!empty($cart_files)) {

    $date_delivery = !empty(get_post_meta($cart_files[0]->post_id, 'job_standard_delivery_date', true)) ? get_post_meta($cart_files[0]->post_id, 'job_standard_delivery_date', true) : date('Y-m-d');

} else if (!empty($job_id)) {

    $date_delivery = !empty(get_post_meta($job_id, 'job_standard_delivery_date', true)) ? get_post_meta($job_id, 'job_standard_delivery_date', true) : date('Y-m-d');

} else {

    $date_delivery = date('Y-m-d');

}

$userdetail = get_userdata(get_current_user_id());

$user_email = isset($userdetail->user_email) ? $userdetail->user_email : '';

$wp_interest_tags = $wpdb->prefix . "interest_tags";


$tags_name_array = array();

if ($job_id) {
    $post_tags = $wpdb->get_results(/** @lang text */
        "SELECT GROUP_CONCAT(tag_id) as tag_ids  FROM wp_tags_cache_job WHERE `job_id` = $job_id AND type = $tagType");
    foreach ($post_tags as $k => $v) {
        $post_tags_array = explode(",", $v->tag_ids);
        foreach ($post_tags_array as $v1) {
            $interest_tags = $wpdb->get_results(/** @lang text */
                "SELECT * FROM $wp_interest_tags WHERE `id` = $v1");
            foreach ($interest_tags as $k2 => $v2) {
                $tags_name_array[] = $v2->tag_name;
            }
        }
    }

}


?>

<script type="text/javascript">

    jQuery(function () {

        jQuery("#standard_delivery").datepicker({
            dateFormat: "yy-mm-dd", minDate: 0, changeMonth: true, changeYear: true, setDate: new Date(),

            onSelect: function (dateText, /* inst */) {

                var data = {'action': 'update_price_by_date', 'date': dateText};

                jQuery.post(adminAjax.url, data, function (response_raw) {


                    //code-bookmark where the calendar talks to the backend to update the ending date
                    //code-notes updating the date now works with standardized errors
                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_raw);

                    if (response.status === true) {
                        console.log(response.message);
                    } else {
                        will_handle_ajax_error('Updating Date for Project',response.message);
                    }

                });

            }

        });

    });


</script>


<?php if (!is_user_logged_in() || (xt_user_role() == "customer")) : ?>

    <div class="title-sec">

        <div class="container">

            <?php

            //$link = hz_get_template_link('templates/post-contest-tpl.php');
            $link = get_site_url() . '/post-contest/?lang=' . $lang; //code-notes stop the meta wide search to get a link

            ?>

            <div class="fancy-header">

                <div class="process-steps">

                    <span class="current last enhanced-text">Post Project (Free)</span>
                    <span class="enhanced-text">Select and Hire Expert</span>
                    <span class="enhanced-text">Receive Work</span>

                </div>


                <p>Always FREE to post your project. 100% satisfaction guaranteed and pay only when you approve the completition of the job</p>


            </div>

        </div>

    </div>

    <div class="project-post-cont">

        <div class="container">

            <div class="project-form">

                <form class="generateorderform" name="generateorderform"
                      action="<?php echo freeling_links('order_process'); ?>" method="post"
                      onsubmit="return generateOrderByCustomerNew(<?php echo $check_login; ?>,this);">

                    <input type="hidden" name="lang" value="<?php echo $lang; ?>">


                    <div class="freelinguist-change-project-type large-text"
                         data-message = "After posting the project, our freelancers will place bids. You can hire the best expert and pay only when the work is completed"
                    >

                        <label class="radio-inline">
                            <input type="radio" name="freelinguist_project_type" value="project"
                                   checked data-link="">
                            <span>Project</span>
                        </label>

                        <label class="radio-inline">
                            <input type="radio" name="freelinguist_project_type" value="contest"
                                   data-link="<?= $link ?>">
                            <span>Competition</span>
                        </label>

                    </div>



                    <div class="formcont">

                        <input class=" enhanced-text" type="text" name="project_title"
                               value="<?= (isset($project_title) && !empty($project_title)) ?
                                                        $project_title :
                                                        (isset($_SESSION['project_title']) ? $_SESSION['project_title'] : ''); ?>"
                               required id="order_page_project_title--"
                               placeholder="<?php echo get_custom_string_return('Project Title'); ?>">
                        <div class="projectTitleError"></div>

                        <textarea class="input-area  enhanced-text" maxlength="10000"
                                  placeholder="<?php echo get_custom_string_return('Type or Upload Project Description'); ?>."
                                  name="project_description"
                                  autocomplete="off"
                                  id="order_page_project_description--"><?php
                                            echo (($project_description) ? $project_description :
                                                                        ((!empty($_SESSION) && isset($_SESSION['project_description'])) ?
                                                                            $_SESSION['project_description'] :
                                                                            '')); ?></textarea>
                        <div class="projectDescriptionError"></div>
                    </div>

                    <div class="formcont" data-toggle="tooltip" data-placement="bottom">

                        <input type="text" name="project_tags" id="project_tags" class="tm-input enhanced-text" value=""
                               placeholder="<?php echo get_custom_string_return('Skills'); ?>"
                               autocomplete="off"
                        >

                    </div>



                    <div class="estimatebudget">

                        <label><?php echo get_custom_string_return('Estimated Budget'); ?>:</label>

                        <div class="selectwes">

                            <select title="Estimated Budget" name="estimated_budgets" id="estimated_budgets">

                                <option value="10_50" <?php echo $estimated_budgets == "10_50" ? "selected" : ""; ?>>
                                    Tiny project (10 - 50 USD)
                                </option>

                                <option value="50_100" <?php echo $estimated_budgets == "50_100" ? "selected" : ""; ?>>
                                    Very small project (50 - 100 USD)
                                </option>

                                <option value="100_300" <?php echo $estimated_budgets == "100_300" ? "selected" : ""; ?>>
                                    Small project (100 - 300 USD)
                                </option>
                                <option value="300_1000" <?php echo $estimated_budgets == "300_1000" ? "selected" : ""; ?>>
                                    Medium project (300 - 1000 USD)
                                </option>

                                <option value="1000_2000" <?php echo $estimated_budgets == "1000_2000" ? "selected" : ""; ?>>
                                    Big project (1000 - 2000 USD)
                                </option>

                                <option value="2000_5000" <?php echo $estimated_budgets == "2000_5000" ? "selected" : ""; ?>>
                                    Large project (2000 - 5000 USD)
                                </option>

                                <option value=">5000" <?php echo $estimated_budgets == ">5000" ? "selected" : ""; ?>>
                                    Giant project (>5000 USD)
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="estimatebudget">

                        <ul>

                            <li>

                                <label>
                                    <?php echo get_custom_string_return('Expected Delivery Date'); ?>:
                                    <span class="fl-describe-date-timezone">
                                        <?= get_option('fl_describe_date_timezone','') ?>
                                    </span>
                                </label>

                                <input type="text" value="<?php echo $date_delivery; ?>" name="standard_delivery"
                                       id="standard_delivery" placeholder="Standard delivery" readonly="readonly"
                                       autocomplete="off"
                                       class="form-control calendar-icon">

                            </li>

                        </ul>

                    </div>

                    <input type="hidden" name="project-type" value="project" id="fl-project-type">

                    <button type="submit" class="btn blue-btn next-btn postproject regular-text" name="submit_order">

                        <!--suppress HtmlUnknownTarget -->
                        <img src="<?php bloginfo('template_url'); ?>/images/post-icon.png"><?php echo get_custom_string_return('Post My Project'); ?>
                    </button>

                </form>

            </div>

        </div>

    </div>


<?php else: ?>

    <section class="middle-content">

        <div class="container">

            You are an unauthorized user.

        </div>

    </section>

<?php

endif;

?>

<?php get_footer('homepagenew'); ?>

<script type="text/javascript">
    jQuery(function ($) {


        $('.generateorderform').submit(function () {
            var project_title = jQuery("#order_page_project_title--").val();

            if (jQuery.trim(project_title) === '') {
                return false;
            }
        });
        var pausecontent = [];

        <?php
        $tags_name_array = stripslashes_deep($tags_name_array);
        if (empty($tags_name_array)) {
            $tags_name_array = [];
        }
        foreach($tags_name_array as $key => $val){ ?>
        pausecontent.push('<?php echo $val; ?>');

        <?php } ?>
        console.log('tags are: ' + pausecontent.join(','));
        var tagApi = $(".tm-input").tagsManager({
            prefilled: pausecontent
        });


        jQuery("#project_tags").typeahead({
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


        // project title key binding validation
        $('#order_page_project_title--').keyup(function () {

            var project_title = jQuery("#order_page_project_title--").val();

            if (jQuery.trim(project_title) === '') {
                jQuery('.projectTitleError').html('<label class="error" for="project_title">Please input project title</label>');

            } else {
                jQuery('.projectTitleError').html(' ');
            }
        });

        // project description key binding validation
        $('#order_page_project_description--').keyup(function () {

            var project_description = jQuery("#order_page_project_description--").val();

            if (jQuery.trim(project_description) === '') {
                jQuery('.projectDescriptionError').html('<label class="error" for="project_description">Please input project description</label>');

            } else {
                jQuery('.projectDescriptionError').html(' ');
            }
        });

    });
</script>

<script>
    jQuery(function($) {
        //code-notes show tooltip for tags
        let tag_box = $('#project_tags') ;
        freelinguist_tag_help(tag_box);
    });
</script>