<?php
/*
 * current-php-code 2020-Oct-5
 * input-sanitized :
 * current-wp-template:  contest edit page (second edit page)
 */
$lang = FLInput::get('lang', 'en');
ob_start();

//check_login_redirection();

add_action('wp_head', 'add_meta_tags', 2); // Cache clear

if (current_user_can('administrator')) {

    wp_redirect(admin_url());

}

get_header();



$check_login = (is_user_logged_in()) ? 1 : 0;


global $wpdb;
global $post;


$user_ID = get_current_user_id();

$job_id = $post->ID;

$project_description = get_post_meta($job_id, 'project_description', true);
$estimated_budgets = get_post_meta($job_id, 'estimated_budgets', true);

$project_title = get_post_meta($job_id, 'project_title', true);

$prefix = $wpdb->prefix;


$date_delivery = !empty(get_post_meta($job_id, 'job_standard_delivery_date', true)) ?
    get_post_meta($job_id, 'job_standard_delivery_date', true) :
    date('Y-m-d');


$userdetail = get_userdata(get_current_user_id());

$user_email = isset($userdetail->user_email) ? $userdetail->user_email : '';


$sql_statement =
    "SELECT GROUP_CONCAT(tag_id) as tag_ids FROM wp_tags_cache_job WHERE  `job_id` = $job_id AND type = " . FreelinguistTags::CONTEST_TAG_TYPE;
$post_tags = $wpdb->get_results($sql_statement);


$tags_name_array = array();

//will_dump('post_tags',$post_tags);
foreach ($post_tags as $k => $v) {
    if (empty($v->tag_ids)) {
        continue;
    }
    $post_tags_array = explode(",", $v->tag_ids);
    foreach ($post_tags_array as $v1) {
        $interest_tags = $wpdb->get_results(
            "SELECT * FROM wp_interest_tags WHERE `id` = $v1"
        );

        foreach ($interest_tags as $k2 => $v2) {
            $tags_name_array[] = $v2->tag_name;
        }
    }
}

$auth_or = $post->post_author;
//code-notes [contest customer private instructions]  show only public files for this contest here made by this user
$post_files = $wpdb->get_results("SELECT * FROM wp_files WHERE `post_id` = $job_id AND by_user = $auth_or  AND proposal_id IS NULL");

if (!is_user_logged_in() || (xt_user_role() == "customer")) : ?>


    <div class="title-sec">

        <div class="container">


            <div class="fancy-header">

                <div class="process-steps">

                    <span class="current last enhanced-text">Post Competition and Deposit Prize </span>
                    <span class="enhanced-text">Linguist Submit Works</span>
                    <span class="enhanced-text">Choose the best Work</span>

                </div>


                <small>100% satisfaction guaranteed or full refund*</small>


            </div>

        </div>

    </div>

    <div class="project-post-cont">

        <div class="container">

            <div class="project-form">

                <form class="generateorderform" name="generateorderform"
                      action="<?php echo freeling_links('order_process'); ?>" method="post"
                      onsubmit="return updateOrderByCustomer(<?php echo $check_login; ?>,this);">

                    <input type="hidden" name="lang" value="<?php echo $lang; ?>">

                    <input type="hidden" name="project_id" value="<?php echo $job_id; ?>">
                    <input type="hidden" name="temp_id" value="" id="temp_id">

                    <div class="formcont">

                        <input type="text" class=" enhanced-text" name="project_title"
                               value="<?php echo $project_title; ?>" id="order_page_project_title"
                               placeholder="<?php echo get_custom_string_return('Competition Title'); ?>">

                        <textarea class="input-area enhanced-text" maxlength="10000"
                                  placeholder="<?php echo get_custom_string_return('Type or Upload Competition Description'); ?>."
                                  name="project_description" id="order_page_project_description" autocomplete="off"
                                  rows="20"><?php echo $project_description; ?></textarea>

                    </div>

                    <div class="formcont" data-toggle="tooltip" data-placement="bottom">

                        <input type="text" name="project_tags" id="project_tags" class="tm-input  enhanced-text"
                               value="" placeholder="<?php echo get_custom_string_return('Skills'); ?>"
                               autocomplete="off"
                        >

                        <input id="job_id" name="hidden_job_id" value="<?= $job_id; ?>" type="hidden">

                    </div>

                    <div class="item-sec" id="order_files_content">
                        <span class="bold-and-blocking large-text">Public Instruction Files</span>
                        <div class="headsec">

                            <div class="floatleft enhanced-text"><span
                                        id="count"><?php echo count($post_files); ?> </span> <?php if (count($post_files) <= 1) {
                                    get_custom_string('item');
                                } else {
                                    get_custom_string('items');
                                } ?></div>
                            <?php $conf_message = '"' . get_custom_string_return("Do you really want to remove all the files?") . '"'; ?>
                            <?php $yes = '"' . get_custom_string_return("Yes") . '"'; ?>
                            <?php $no = '"' . get_custom_string_return("No") . '"'; ?>

                            <div class="floatright small-text"><a href="#"
                                                                  onclick='return remove_all_files(<?php echo $conf_message . "," . $yes . "," . $no; ?>)'
                                                                  class="clear"><?php get_custom_string('Clear all Items'); ?></a>
                            </div>

                        </div>

                        <ul class="document-row">

                            <?php foreach ($post_files as $k => $post_file) {
                                ?>

                                <li>

                                    <div class="floatleft  enhanced-text" style="width: 90%">
                                        <!-- code-notes freehand css shifting in width to fit in file line-->

                                        <!-- code-notes [download]  new download line -->
                                        <div class="freelinguist-download-line">

                                            <span class="freelinguist-download-name">
                                                <span class="freelinguist-download-name-itself enhanced-text">
                                                    <?= $post_file->file_name ?>
                                                </span>
                                            </span> <!-- /.freelinguist-download-name -->

                                            <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                                               data-job_file_id = "<?= $post_file->id ?>"
                                               download = "<?= $post_file->file_name ?>"
                                               href="#">
                                                Download
                                            </a> <!-- /.freelinguist-download-button -->

                                        </div><!-- /.freelinguist-download-line-->


                                    </div> <!-- / anon float left div -->

                                    <div class="floatright"><a href="#"
                                                               onclick="return remove_selected_file(this,<?php echo $post_file->id; ?>)"
                                                               class="cross-icon"></a></div>

                                </li>

                                <?php

                            }

                            ?>

                        </ul>


                        <div class="upload-file regular-text">

                            <i class="fa fa-upload enhanced-text"></i>

                            <?php echo get_custom_string_return('Upload Files'); ?>

                            <input multiple="" name="files[]" id="atc_files_order" class="files-data hz_order_process"
                                   type="file" data-jid="<?php echo $job_id; ?>">

                        </div>


                        <br>

                        <!-- Progress Bar-->

                        <div id="progress" class="progress" style="margin-top: 10px;">

                            <div class="progress-bar progress-bar-success"></div>

                        </div>

                        <div class="percent"></div>

                        <!-- The container for the uploaded files -->

                        <div id="files_name_container" class="files"></div>

                    </div>

                    <div class="estimatebudget">


                        <label><?php echo get_custom_string_return('Budget'); ?>:</label>
                        <input title="Budget" maxlength="10" type="number" required name="estimated_budgets"
                               id="estimated_budgets" value="<?php echo $estimated_budgets; ?>">
                        USD

                    </div>


                    <div class="estimatebudget">

                        <ul>

                            <li>

                                <label>
                                    <?php echo get_custom_string_return('Submission Deadline'); ?>:

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

                    <!--   code-notes removed ability to edit insurance for contests  -->

                    <button type="submit" class="btn blue-btn next-btn postproject regular-text" name="submit_order">

                        <!--suppress HtmlUnknownTarget -->
                        <img src="<?php bloginfo('template_url'); ?>/images/post-icon.png"><?php echo get_custom_string_return('Update Competition'); ?>
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


<!--suppress JSUnresolvedFunction -->
<script type="text/javascript">


    jQuery(function () {
        var pausecontent = [];
        <?php
        if (empty($tags_name_array)) {
            $tags_name_array = [];
        }
        foreach($tags_name_array as $key => $val){ ?>
        pausecontent.push('<?php echo $val; ?>');

        <?php } ?>
        var tagApi = jQuery(".tm-input").tagsManager({
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
    });
</script>
<script type="text/javascript">

    jQuery(function () {

        jQuery("#standard_delivery").datepicker({

            dateFormat: "yy-mm-dd",

            minDate: 0,

            changeMonth: true,

            changeYear: true,

            setDate: new Date(),

            onSelect: function (dateText, /*inst*/) {

                var data = {'action': 'update_price_by_date', 'date': dateText, da_job_id: <?=$job_id ?>};

                jQuery.post(adminAjax.url, data, function (response_raw) {
                    //code-bookmark where the calendar talks to the backend to update the ending date
                    //code-notes js handler now works with standardized errors
                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_raw);

                    if (response.status === true) {
                        console.log(response.message);
                    } else {
                        will_handle_ajax_error('Updating Date for Content',response.message);
                    }

                });

            }

        });

    });

</script>

<script>
    jQuery(function($) {
        //code-notes show tooltip for tags
        let tag_box = $('#project_tags') ;
        freelinguist_tag_help(tag_box,true);
    });
</script>