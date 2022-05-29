<?php
/*

    Template Name: Linguists Add Content Page Template

*/
/*
* current-php-code 2020-Oct-13
* input-sanitized : content_id,lang,mode
* current-wp-template:  freelancer create/edit content
* current-wp-top-template
*/
//code-notes when freelancer refreshes page and the content is complete, then show the rating dialog if not done already

$content_id_encoded = FLInput::get('content_id',0);
$lang = FLInput::get('lang', 'en');
$mode = FLInput::get('mode');
check_login_redirection_home($lang);

$sub_title_id_counter = 1;
$editor_title_id_counter = 1;

if (current_user_can('administrator')) {

    wp_redirect(admin_url());

}

if (!(is_user_logged_in() && (xt_user_role() == "translator"))) {
    wp_redirect('/dashboard/');
    exit();
}

get_header();

?>

<?php if (is_user_logged_in() && (xt_user_role() == "translator")) { ?>
    <script>
        //code-notes add protection to content status changes in the ajax and forms of the content customer detail page
        if (adminAjax) {
            adminAjax.form_keys.hz_change_status_content =
                '<?= FreeLinguistFormKey::create_form_key('hz_change_status_content') ?>';
        }
    </script>
<?php } ?>


    <section class="pagetitle">

        <div class="container">

            <span class="bold-and-blocking large-text"></span>

        </div>

    </section>

<?php
global $wpdb;

$content_detail = array();

$content_chapter_detail = array();

$user_id = get_current_user_id();
$content_id = 0;
$freezed = 0;
$contentTagsAr = [];
if ($mode === 'edit') {

    $content_id = FreelinguistContentHelper::decode_id($content_id_encoded);

    $sql_statement =
        "select *,
        UNIX_TIMESTAMP(rejected_at) as rejected_at_ts,
        (select (SELECT GROUP_CONCAT(tag_id)) as tag_ids
           from wp_tags_cache_job
           where job_id=$content_id and type = ".FreelinguistTags::CONTENT_TAG_TYPE."
          ) as tags
        
        from wp_linguist_content where id=$content_id AND user_id = $user_id";

    $content_detail = $wpdb->get_row($sql_statement, ARRAY_A);

    will_throw_on_wpdb_error($wpdb);

    //code-notes clear red dots for this content
    $red = new FLRedDot();
    $red->event_user_id_role =  FreelinguistUserLookupDataHelpers::get_logged_in_role_id();
    $red->content_id = $content_id;
    FLRedDot::remove_red_dots($user_id,$red);



    if (!empty($content_detail)) {

        $content_id = $content_detail['id'];

        // GET SAVED TAGS

        $sql_statement = /** @lang text */
            "select tag_name from {$wpdb->prefix}interest_tags where id IN (" . $content_detail['tags'] . ")";

        if ($content_detail['tags']) {
            $contentTags = $wpdb->get_results($sql_statement, ARRAY_A);
            $contentTagsAr = [];
            if ($contentTags) {
                $contentTagsAr = array_column($contentTags, 'tag_name');
            }
        }


        $content_chapter_detail = $wpdb->get_results(
            "select * from wp_linguist_content_chapter where user_id IS NOT NULL AND linguist_content_id=$content_id ORDER BY page_number ASC ", ARRAY_A);

        $content_file_detail = $wpdb->get_results(
            "select * from wp_content_files where content_id=$content_id", ARRAY_A);

    }


}

$prefilled = json_encode(stripslashes_deep($contentTagsAr));
?>

    <section class="single-content freelinguist-padding-to-bottom">

        <div class="container">

            <form action="" method="post" name="add-linguist-content" id="add-linguist-content"
                  class="default_txt_style" enctype="multipart/form-data">
                <!-- code-notes add in key and nonce to the linguist add content form  as the form is submitted directly -->
                <input type="hidden" name="_wpnonce"
                        value="<?= wp_create_nonce(FREELINGUIST_DEFAULT_NONCE_NAME) ?>">
                <input type="hidden" name="_form_key"
                       value="<?= FreeLinguistFormKey::create_form_key('add-linguist-content') ?>">
                <input type="hidden" name="_form_security_name" value="<?= 'add-linguist-content' ?>">
                <div class="ajax_report"></div>

                <div class="single-left">

                    <!-- code-notes moved to the top SECTION AAA and the history section-->

                    <!-- code-notes start of SECTION AAA-->
                    <?php
                    if
                        //start if freezed is set and != 1 (but the publish type is  purchased )
                    (
                        isset($content_detail['publish_type']) &&
                        $content_detail['publish_type'] === 'Purchased' &&
                        isset($content_detail['freezed']) &&
                        $content_detail['freezed'] != '1'
                    ) {

                        ?>
                        <div class="col-md-12 fl-not-freezed" style="padding-left: 0px;">

                            <div class="floatright">
                                <?php
                                $customer_id = (int)$content_detail['purchased_by'];
                                if ($customer_id) {
                                    //code-notes chat part
                                    set_query_var('job_id',$content_id);
                                    set_query_var('to_user_id', $customer_id);
                                    set_query_var('job_type', 'content');
                                    set_query_var( 'b_show_name', 0 );
                                    get_template_part('includes/user/chat/chat', 'button-area');
                                }
                                ?>
                            </div>

                            <?php


                            if (isset($content_detail['status']) && $content_detail['status'] == 'request_completion') {
                                ?>
                                <p>
                                    You have requested for completion
                                </p>
                                <?php
                            } else if (isset($content_detail['status']) && $content_detail['status'] == 'hire_mediator') {
                                echo '<p>Mediation in progress"</p>';
                            }

                            if (isset($content_detail['status']) && $content_detail['status'] != 'cancelled' &&
                                $content_detail['status'] != 'completed' &&
                                $content_detail['status'] != 'rejected' &&
                                $content_detail['status'] != 'hire_mediator'
                            ) {
                                ?>
                                <a id=""
                                   style="width:180px;text-align:center; margin-top:10px;"
                                   class="box-prement2 change_content_status"
                                   href="#"
                                   contentId="<?= $content_id ?>"
                                   status = "request_completion"
                                >
                                    Request Completion
                                </a>

                                <a id=""
                                   style="width:180px;text-align:center; margin-top:10px;"
                                   class="box-prement2 change_content_status bg-secondary"
                                   href="#"
                                   contentId="<?= $content_id ?>"
                                   status = "cancelled"
                                >
                                    Cancel
                                </a>
                                <br>
                                <?php

                                if ($content_detail['status'] == 'request_rejection') {
                                    ?>

                                    <p>Rejection requested by:
                                        <?= get_userdata($content_detail["purchased_by"])->user_nicename ?> :
                                        <?= $content_detail['rejection_txt'] ?>
                                    </p>

                                    <p>
                                        please select one from the following two options within the deadline
                                    </p>

                                    <?php
                                } //end code that is for status == request_rejection
                                else if //daisy chain that to the check of status == request_revision
                                ($content_detail['status'] == 'request_revision') {
                                    ?>
                                    <p>
                                        Revision requested by
                                        <?= get_userdata($content_detail["purchased_by"])->user_nicename ?>:
                                        <?= $content_detail['revision_text'] ?>
                                    </p>
                                    <?php
                                } else if ($content_detail['status'] == 'request_completion') {
                                    ?>
                                    <!-- You have requested for completion -->
                                    <?php
                                } else if ($content_detail['status'] == 'hire_mediator') {
                                    ?>
                                    <!--  Mediation in progress -->
                                    <?php
                                } //end if status == request_rejection


                                if ($content_detail['status'] == 'request_rejection' ||
                                    $content_detail['rejection_requested'] == '1'
                                ) {  //start if block on if status === request_rejection and rejection_requested is one
                                    ?>
                                    <a id="accept_rejection"
                                       class="box-prement2 change_content_status"
                                       href="#"
                                       contentId="<?= $content_id ?>"
                                       status = "rejected"
                                    >
                                        Accept Rejection
                                    </a>

                                    <a id=""
                                       class="box-prement2 change_content_status"
                                       href="#"
                                       contentId="<?= $content_id ?>"
                                       status = "hire_mediator"
                                    >
                                        Hire Mediator
                                    </a>

                                    <?php
                                } // end check  status === request_rejection OR rejection_requested === 1

                            }   else if  //daisy chain the if block from above: if the status is not request_rejection is it hire mediator ?
                            (isset($content_detail['status']) &&
                                $content_detail['status'] == 'hire_mediator'
                            ) {
                                ?>

                                <a  class="box-prement2 change_content_status bg-secondary"
                                    href="#"
                                    contentId="<?= $content_id ?>"
                                    status = "rejected"
                                    id="accept_rejection"
                                >
                                    Cancel Job
                                </a>

                                <?php
                            } //end top if the status == hire_mediator
                            else //start else status !== hire_mediator,
                                // all other status but hire_mediator and (request_rejection (OR rejection_requested=1) are here
                            {
                                ?>

                                <a id=""
                                   class="hirebttn2 fr"
                                   href="#"
                                >
                                    <?= ucfirst(isset($content_detail['status']) ? $content_detail['status'] : '')?>
                                </a>

                                <?php
                                //code-notes move the feedback from the read content to the edit content for freelancer
                                if (isset($content_detail['status']) &&
                                    $content_detail['status'] == 'completed'
                                ) {
                                    if (empty($content_detail['rating_by_freelancer'])) {

                                        ?>

                                        <a class="hirebttn2 fr freelinguist-space-to-right"
                                           href="#"
                                           data-toggle="modal"
                                           data-target="#feedbackModel"
                                        >
                                            Feedback
                                        </a>

                                        <?php
                                    }
                                } //end if status == completed
                                //code-notes this is the end of the modified old code for moving the feedback button
                            } //end else status !== hire_mediator, end if block for check on status hire_mediator

                            ?>

                            <p id="demo_time"></p>
                        </div> <!-- /.fl-not-freezed end buttons , statuses and controls if the content is not freezed-->
                        <?php

                    } /* end else if freezed != 1*/


                    //end section AAA
                    ?>
                    <!-- code-notes end of SECTION AAA-->



                    <!-- code-notes Start of history section -->
                    <?php

                    if ($content_id_encoded) {

                        $cid = FreelinguistContentHelper::decode_id($content_id_encoded);





                        $messages = $wpdb->get_results(
                            "SELECT * FROM wp_message_history where content_id = $cid order by id asc");
                        if ($messages) {
                            $j = 1;
                            ?>
                            <div class="buttonsec">
                                <div class="row">
                                    <h3>Message History </h3>
                                    <table class="table">
                                        <?php

                                        foreach ($messages as $k => $message) {
                                            echo '<tr><td><b>#' . $j . '</b> ' . $message->created_at . ': ' .
                                                $message->message . '</td></tr>';
                                            $j++;

                                        }

                                        ?>
                                    </table>
                                </div> <!-- /.row -->
                            </div> <!-- /.buttonsec -->
                            <?php
                        } //end if there are messages
                    } //end if there is a content_id_encoded

                    ?>
                    <!-- code-notes END of history section -->

                    <!--code-notes this is the end inserting of AAA and history blocks to the top of the page-->

                    <div class="topsec">

                        <input type="hidden" id="redirectattr"
                               value="<?php echo get_site_url() . '/linguist-content/?lang=en'; ?>"/>

                        <input type="text" id="content_title" name="content_title"
                               value="<?php echo !empty($content_detail) ? stripslashes_deep($content_detail['content_title']) : ''; ?>"
                               placeholder="Name">
                        <div class="contentTitleError"></div>

                        <input type="hidden" name="action" value="add_linguist_content">

                        <input type="hidden" name="content_id"
                               value="<?php echo ($content_id != 0) ? FreelinguistContentHelper::encode_id($content_id) : ''; ?>">

                        <textarea id="content_summary" name="content_summary" rows="10"  autocomplete="off"
                                  placeholder="Descripion of the service or digital content for sale"><?php echo
                                            !empty($content_detail) ?
                                                stripslashes_deep($content_detail['content_summary']) :
                                                '';
                                  ?></textarea>
                        <div class="contentSummaryError"></div>

                    </div> <!-- /.topsec -->

                    <div class="formcont" data-toggle="tooltip" data-placement="bottom">

                        <input type="text" name="project_tags" id="project_tags" class="tm-input  enhanced-text"
                               value="" placeholder="<?php echo get_custom_string_return('Skills or keywords'); ?>"
                               autocomplete="off"
                               <?php if (isset($content_detail['publish_type']) && $content_detail['publish_type'] == 'Purchased') { echo 'readonly';}?>
                        >

                    </div> <!-- /.formcont (keyword section) -->


                    <div class="bottmsec">

                        <?php if (!empty($content_chapter_detail)) { ?>

                            <table width="100%">

                                <tr style="background: #a2a2a2;">

                                    <th class="large-text" style="padding: 8px 12px;" width="50%">Title</th>
                                    <th class="large-text" width="20%">Visible only to buyer</th>
                                    <th class="large-text" width="10%">Page</th>
                                    <th class="large-text" width="5%">View</th>

                                    <th class="large-text" width="5%">Edit</th>

                                    <th class="large-text" width="10%" style="padding-left: 10px;">Delete</th>

                                </tr>

                                <?php foreach ($content_chapter_detail as $key => $value) { ?>

                                    <tr class="main-row">

                                        <td class="large-text" style="padding: 8px 12px;">

                                            <?= $value['title'] ?>

                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($content_detail['freezed'] != '1'): ?>
                                                <input title="visible after buy" type="checkbox"
                                                       name="visible_after_buy[<?php echo $value['id'] ?>]"
                                                       value="buy" <?php if ($value['content_visible'] == 'buy') {
                                                    echo 'checked';
                                                } ?>>
                                                <input type="hidden" name="chapters_id[]"
                                                       value="<?php echo $value['id'] ?>">

                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fl-content-page-number-display">
                                                <?= $value['page_number']?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($content_detail['freezed'] != '1'): ?>
                                                <a id="<?= $value['id'] ?>" class="click_view_conainer large-text" href="#">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            <?php endif; ?>

                                        </td>

                                        <td style="text-align: center;">
                                            <?php if ($content_detail['freezed'] != '1'): ?>
                                                <a id="<?= $value['id'] ?>" class="click_edit_conainer large-text" href="#">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            <?php endif; ?>

                                        </td>

                                        <td style="text-align: center;">
                                            <?php if ($content_detail['freezed'] != '1'): ?>

                                                <a class="delete_content_chapter large-text" id="<?= $value['id'] ?>"
                                                   href="#">

                                                    <i class="fa fa-trash"></i>

                                                </a>
                                            <?php endif; ?>

                                        </td>

                                    </tr>


                                <?php } ?>

                            </table>

                        <?php } ?>
                        <div class="buttonsec">

                            <?php if (isset($content_detail['status']) &&
                                        (   $content_detail['status'] == 'cancelled' ||
                                            $content_detail['status'] == 'completed' ||
                                            $content_detail['status'] == 'rejected'
                                        )
                            ):

                                echo '';
                            elseif (isset($content_detail['freezed']) && $content_detail['freezed'] != '1'):
                                //code-notes above is true if editing content only
                                ?>


                                <div class="upload-file regular-text"
                                     style="background: #ee2b31; color: #fff;    width: 187px;  text-align: center;">

                                    <i class="fa fa-upload enhanced-text"></i>

                                    Upload Files

                                    <input multiple="" name="files[]" type="file" id="atc_files_content_middle"
                                           data-content_id = "<?= $content_id?>"
                                           class="files-data hz_order_process" accept=".txt,application/pdf">

                                </div>


                            <?php endif; ?>
                            <div id="progress_middle" class="progress" style="margin-top: 10px;">

                                <div class="progress-bar progress-bar-success"></div>

                            </div>

                            <div class="percent_middle"></div>
                            <?php

                            $wp_upload_dir = wp_upload_dir();


                            $basepath = $wp_upload_dir['baseurl']

                            ?>

                            <?php if (!empty($content_file_detail) ) { ?>

                                <table style="margin-bottom: 50px;margin-top: 50px;" width="100%" id="content_files">
                                    <tr style="background: #a2a2a2;">
                                        <th class="default_txt_style" style="padding: 8px 12px;" width="70%">Title</th>
                                        <th class="default_txt_style" width="5%" style="padding-right: 5px;">Delete</th>
                                    </tr>

                                    <?php foreach ($content_file_detail as $key => $value) {


                                        ?>

                                        <tr class="main-row">
                                            <td class="large-text" style="padding: 8px 12px">

                                                <!-- code-notes [download]  new download line -->
                                                <div class="freelinguist-download-line">

                                                    <span class="freelinguist-download-name">
                                                        <i class="text-doc-icon larger-text"></i>
                                                        <span class="freelinguist-download-name-itself enhanced-text">
                                                            <?= $value['public_file_name'] ?>
                                                        </span>
                                                    </span> <!-- /.freelinguist-download-name -->

                                                    <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                                                       data-content_file_id = "<?=$value['id'] ?>"
                                                       download = "<?= $value['public_file_name'] ?>"
                                                       href="#">
                                                        Download
                                                    </a> <!-- /.freelinguist-download-button -->

                                                </div><!-- /.freelinguist-download-line-->
                                            </td>

                                            <td style="text-align: center;vertical-align: middle;">
                                                <a class="delete_content_file large-text" id="<?= $value['id'] ?>" href="#">

                                                    <i class="fa fa-trash"></i>

                                                </a>
                                            </td>
                                        </tr>


                                    <?php } ?>
                                </table>

                            <?php } else if ($content_id) {

                                ?>
                                <table style="margin-bottom: 50px;margin-top: 50px;" width="100%" id="content_files">
                                    <tr style="background: #a2a2a2;">
                                        <th class="default_txt_style" style="padding: 8px 12px;" width="70%">Title</th>
                                        <th class="default_txt_style" width="5%" style="padding-right: 5px;">Delete</th>
                                    </tr>
                                </table>
                            <?php } ?>
                        </div>

                        <div id="custom_table">

                            <?php foreach ($content_chapter_detail as $key => $value) { ?>

                                <div class="all_content_co" id="edit_conainer_<?= $value['id'] ?>" style="display:none">
                                    <table id="" width="100%" style="background: #f1f1f1;" class="editpor">
                                        <tr class="edit-field">
                                            <td style="padding: 15px;">

                                                <input type="hidden" name="sub_title_id[]" id="editor_title_id_<?= $editor_title_id_counter++ ?>"
                                                       value="<?= $value['id'] ?>">

                                                <input type="text" name="sub_title[]" id="editor_sub_title_<?= $sub_title_id_counter ++?>"
                                                       value="<?= $value['title'] ?>" placeholder="Title">

                                                <input type="number" name="sub_page_number[]" id="sub_page_number_<?= $sub_title_id_counter ?>" value="<?= $value['page_number'] ?>"
                                                       placeholder="Page">

                                            </td>
                                        </tr>
                                        <tr class="edit-field">
                                            <td style="padding: 15px;">
                                                <div class="fl-chapter-editor">

                                                    <?php
                                                    set_query_var( 'content_chapter_id', $value['id'] );
                                                    set_query_var( 'content_chapter_words', $value['content_bb_code'] );
                                                    get_template_part('includes/user/contentdetail/contentdetail', 'bb-code-editor');
                                                    ?>

                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>


                                <div class="all_content_co" id="view_conainer_<?= $value['id'] ?>" style="display:none">
                                    <table id="" width="100%" style="background: #f1f1f1;" class="editpor">
                                        <tr class="edit-field">

                                            <td style="padding: 15px;">

                                                <?= $value['title'] ?>
                                                <span class="fl-content-page-number-display">
                                                    Page <?= $value['page_number'] ?>
                                                </span>

                                            </td>
                                        </tr>

                                        <tr class="edit-field">

                                            <td style="padding: 15px;">

                                                <div class="fl-chapter-editor">

                                                    <?= stripslashes($value['content_html']); ?>

                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>


                            <?php } ?>

                            <table id="" width="100%" style="background: #f1f1f1;" class="editpor">
                                <tr class="edit-field">
                                    <td style="padding: 15px;">
                                        <input type="hidden" name="sub_title_id[]" id="editor_title_id_<?= $editor_title_id_counter++ ?>" value="">

                                        <input type="text" name="sub_title[]" id="editor_sub_title_<?= $sub_title_id_counter++ ?>" value=""
                                               placeholder="Title">

                                        <input type="number" name="sub_page_number[]" id="sub_page_number_<?= $sub_title_id_counter ?>" value=""
                                               placeholder="Page">

                                    </td>
                                </tr>

                                <tr class="edit-field">
                                    <td style="padding: 15px;">
                                        <?php
                                        set_query_var( 'content_chapter_id', 0 );
                                        set_query_var( 'content_chapter_words', '' );
                                        get_template_part('includes/user/contentdetail/contentdetail', 'bb-code-editor');
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div id="add_more_table_list">


                        </div>

                        <div class="buttonsec">

                            <?php

                            if (isset($content_detail['freezed']) &&
                                     $content_detail['freezed'] != '1') { ?>
                                <div style="color: #fff; float: left; margin-right: 15px;">

                                    <input name="add_more_button" id="add_more_button"
                                           class="files-data upload-file enhanced-text" style="width: 180px;"
                                           type="button" value="Add New">

                                </div> <!-- /anon -->

                            <?php } //end if  freezed exists and freezed !== 1

                            //start section to have upload for files, but display it only if not purchased and frozen
                            if (        isset($content_detail['publish_type']) &&
                                        $content_detail['publish_type'] != 'Purchased' &&
                                        isset($content_detail['freezed']) &&
                                        $content_detail['freezed'] != '1'
                            ) { //start if freezed is  set, and is not 1, and the publish_type exists and is purchased
                                ?>
                                <div class="upload-file freelinguist-no-border regular-text"
                                     style="background: #ee2b31; color: #fff;">

                                    <i class="fa fa-upload enhanced-text"></i>

                                    Add Multiple Pages using a Text File

                                    <input multiple="" name="text_files[]" type="file" id="atc_text_files_content"
                                           data-content_id = "<?= $content_id?>"
                                           class="files-data hz_order_process" accept=".txt">

                                </div>

                            <?php
                            } //end if freezed is  set, and is not 1, and the publish_type exists and is purchased, then we upload text files
                            ?>
                            <!-- code-notes this is where the old home of AAA starts-->

                            <!-- code-notes this is where the old home of the AAA ends -->


                            <!-- Progress Bar-->

                            <div id="progress" class="progress" style="margin-top: 10px;">

                                <div class="progress-bar progress-bar-success"></div>

                            </div> <!-- /.progress -->

                            <div class="percent"></div>

                            <!-- The container for the uploaded files -->

                            <div id="files_name_container" class="files"></div>

                            <ul class="document-row"></ul>

                        </div> <!-- /.buttonsec -->

                        <!-- code-notes This is where the history section old home starts -->




                        <!-- code-notes This is where the history section old home ENDS -->

                    </div> <!-- /.bottmsec -->

                </div> <!-- /.single-left -->

                <?php
                    $displayed_status = __('Pending');
                    $next_status_button_text = __('Display For Sale');
                    if (isset($content_detail['publish_type']) && (strtolower($content_detail['publish_type']) === 'publish')) {
                        $displayed_status = __('Posted');
                        $next_status_button_text = __('End Sale');
                    } else if (isset($content_detail['publish_type']) && (strtolower($content_detail['publish_type']) === 'purchased')) {
                        $displayed_status = __('Purchased');
                        $next_status_button_text = __('Purchased!');
                    } else if (isset($content_detail['publish_type']) && empty($content_detail['publish_type'])) {
                        $displayed_status = __('???');
                        $next_status_button_text = __('???');
                    }
                ?>
                <div class="single-right">

                    <div class="sidebar-col publish">

                        <div class="sidebar-head">Publish</div>

                        <div class="sidebar-col-bttm">


                            <input type="hidden" name="publish_type" id="publish_type"
                                   value="<?php echo(isset($content_detail['publish_type']) ? $content_detail['publish_type'] : ''); ?>">
                            <span class="fl-content-edit-status enhanced-text">
                                <?= $displayed_status ?>
                            </span>

                            <br>
                            <?php if ((isset($content_detail['status']) && $content_detail['status'] == 'cancelled') || (isset($content_detail['status']) && $content_detail['status'] == 'completed') || (isset($content_detail['status']) && $content_detail['status'] == 'rejected')):
                                echo '';
                            else:
                                if (isset($content_detail['publish_type']) && $content_detail['publish_type'] == 'Purchased') {
                                    ?>
                                    <button type="button" data-publish_type = "Purchased"
                                            class="publish_type_button enhanced-text"
                                            style="margin-top:5px;"
                                    >
                                        <i class="fa fa-check huge-text"></i>
                                        Save
                                    </button>
                                    <?php
                                } else {
                                ?>
                                    <button type="button" data-publish_type = "save_content"
                                             class="publish_type_button enhanced-text"
                                             style="margin-top:5px; "
                                    >
                                        <i class="fa fa-check huge-text"></i>
                                        Save
                                    </button>

                                   <button type="button"
                                            data-publish_type = "next_status_for_content"
                                            class="publish_type_button enhanced-text"
                                            style="margin-top:5px;"
                                    >
                                        <i class="fa fa-check huge-text"></i>
                                       <span style="text-align: center">
                                           <?=$next_status_button_text?>
                                       </span>

                                    </button>
                                <?php }
                            endif;
                            ?>

                        </div>

                    </div>

                    <div class="sidebar-col">

                        <div class="sidebar-head">Pricing</div>

                        <div class="sidebar-col-bttm">

                            <select title="Content Sale Type" name="content_sale_type"
                                    id="content_sale_type"
                                <?php if (isset($content_detail['publish_type']) && $content_detail['publish_type'] == 'Purchased') {
                                echo 'disabled="disabled"';
                                echo ' style="cursor:not-allowed"';
                            } ?>>

                                <option <?php echo (!empty($content_detail) && $content_detail['content_sale_type'] == 'Fixed') ? 'selected' : ''; ?>
                                        value="Fixed">Price
                                </option>

                                <option <?php echo (!empty($content_detail) && $content_detail['content_sale_type'] == 'Free') ? 'selected' : ''; ?>
                                        value="Free">Free
                                </option>

                                <option <?php echo (!empty($content_detail) && $content_detail['content_sale_type'] == 'Offer') ? 'selected' : ''; ?>
                                        value="Offer">Bid
                                </option>

                            </select>
                            <!-- code-notes hide max to be sold when the sales type is bidding. Need to hide it in js and php. And set max to be sold to 1 when the sales type changes to bidding-->
                            <?php
                            $extra_class_for_max_copies = '';
                            if(isset($content_detail['content_sale_type']) &&
                                ($content_detail['content_sale_type'] === 'Offer')
                            ) {
                                        $content_detail['max_to_be_sold'] = 1;
                                        $extra_class_for_max_copies = 'freelinguist-hide-max-copies';
                            } //end if offer
                            ?>


                            <div class="freelinguist-content-max-sold <?= $extra_class_for_max_copies ?>">
                                <label for="max_to_be_sold" >How many copies for sale? </label>
                                <input id="max_to_be_sold"
                                       name="max_to_be_sold"
                                       type="number"

                                       value = "<?=
                                            ((!empty($content_detail)&& isset($content_detail['max_to_be_sold']))?
                                                    $content_detail['max_to_be_sold']:
                                                    '1'
                                            )?>"    >
                            </div>

                            <label id="price_label">Price</label>
                            <input type="text" name="content_amount" id="content_amount"
                                   value="<?php echo (!empty($content_detail) && isset($content_detail['content_amount'])) ? $content_detail['content_amount'] : ''; ?>"
                                   placeholder="$0.00"
                                <?php if (isset($content_detail['publish_type']) && $content_detail['publish_type'] == 'Purchased') {
                                echo 'readonly';
                                echo ' style="cursor:not-allowed"';
                            } ?>>

                        </div>

                    </div>

                    <div class="sidebar-col">

                        <div class="sidebar-head">Cover Image</div>

                        <div class="sidebar-col-bttm">

                            <div class="uploadimage">

                                <?php if (!empty($content_detail) && !empty($content_detail['content_cover_image'])) {


                                    //code-notes [image-sizing]  content getting large size for edit page
                                    $cover_image_url = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
                                        $content_detail['content_cover_image'],FreelinguistSizeImages::LARGE,true);

                                    ?>

                                    <img src="<?=$cover_image_url  ?>"  id="content-cover">

                                <?php } else { ?>

                                    <!--suppress HtmlUnknownTarget -->
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/asd.jpg"
                                         id="content-cover">

                                <?php } ?>


                                <div class="custom-upload">

                                <span class="enhanced-text">
                                    <!--suppress HtmlUnknownTarget -->
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/camera.png">
                                    Update Cover Image</span>

                                    <input class="uploadimg large-text" type="file" name="content_cover_image"
                                           placeholder="Update Cover Image" onchange="previewForContent(this);">

                                </div>


                            </div>

                        </div>

                    </div>

                </div>

            </form>

        </div>
        <!--code-notes added posted feedback-->
        <?php
        if (isset($content_detail['rating_by_freelancer']) && $content_detail['rating_by_freelancer']) {
            ?>
            <div class="container linguist-content-show-customer-feedback">
                <div class="row">
                    <h3>Freelancer Feedback  </h3>
                    <div>
                        <?php


                        echo convert_rating($content_detail['rating_by_freelancer'], 17, NULL,
                                $content_detail['user_id']) . ' ' . stripslashes_deep($content_detail['comments_by_freelancer']);

                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="container fl-freelancer-content-form">
            <?php
            if (isset($content_detail['purchased_by']) ) {
                $customer_id = (int)$content_detail['purchased_by'];
                if ($customer_id && $content_detail['freezed'] != '1') {
                    set_query_var('content_id', $cid);
                    set_query_var('customer_id', $customer_id);
                    get_template_part('includes/user/contentdetail/contentdetail', 'translator-discussion');

                }//end showing discussion if purchased and not freezed
            }//end considering showing discussion if key not set
            ?>
        </div> <!-- /.end container -->

    </section>

    <!--suppress JSUnusedLocalSymbols -->
    <script>
        var editor_sub_title_id_counter = <?= $sub_title_id_counter ?>;
        var editor_title_id_counter = <?= $editor_title_id_counter ?>;
    </script>



    <script type="text/javascript">

        jQuery(function ($) {


            $(document).on('click', '.click_edit_conainer', function () {

                var id = jQuery(this).attr('id');

                jQuery(".all_content_co").hide();

                jQuery("#edit_conainer_" + id).show('slow');

            });

            $(document).on('click', '.click_view_conainer', function () {

                var id = jQuery(this).attr('id');

                jQuery(".all_content_co").hide();

                jQuery("#view_conainer_" + id).show('slow');

            });

        });



    </script>

    <script type="text/javascript">
        jQuery(function ($) {
            var tagApi = $(".tm-input").tagsManager({prefilled: <?php echo $prefilled; ?>});

            <?php if (isset($content_detail['publish_type']) && $content_detail['publish_type'] == 'Purchased') { ?>
                $('a.tm-tag-remove').hide();
            <?php } ?>



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


<?php


get_footer('homepagenew'); ?>
<?php
if (isset($content_detail['rejection_requested']) &&
    $content_detail['rejection_requested'] == '1' &&
    $content_detail['status'] != 'cancelled' &&
    $content_detail['status'] != 'completed' &&
    $content_detail['status'] != 'rejected' &&
    $content_detail['status'] != 'hire_mediator'):

    $auto_job_rejected_for_linguist_hours_minutes = floatval(get_option('auto_job_rejected_for_linguist_hours')) * 60;
    $new_date_in_milliseconds = (intval($content_detail['rejected_at_ts']) + (60 * $auto_job_rejected_for_linguist_hours_minutes))*1000;

    ?>
    <script>

        jQuery(function(){


            // Set the date we're counting down to
            var countDownDate = new Date(<?php echo $new_date_in_milliseconds;?>).getTime();

            // Update the count down every 1 second
            var x = setInterval(function () {

                // Get todays date and time
                var now = new Date().getTime();
                // Find the distance between now and the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result in the element with id="demo"
                var demo = document.getElementById("demo_time");
                if (demo) {
                    demo.innerHTML = days + "d " + hours + "h "
                        + minutes + "m " + seconds + "s  left until auto approval of rejection";
                }

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    fl_b_do_not_ask_about_content_rejection = true;
                    jQuery('#accept_rejection').click();
                    var demo_the_element = document.getElementById("demo_time");
                    if (demo_the_element) {
                        demo_the_element.innerHTML = "EXPIRED";
                    }
                }
            }, 5000);

        });


    </script>

<?php
else:

    ?>

    <script>
        var demo = document.getElementById("demo_time");
        if (demo) {
            demo.innerHTML = "";
        }
    </script>
<?php
endif;

?>

    <script>
        jQuery(function ($) {
            <?php
            if(isset($content_detail['content_sale_type']) && $content_detail['content_sale_type'] == 'Free'){
            ?>
            jQuery('#content_amount').val('');
            jQuery('#content_amount').hide();
            jQuery('#price_label').hide();

            <?php
            }
            ?>
            $('#content_title').keyup(function () {

                var titleLenght = jQuery("#content_title").val();

                if (jQuery.trim(titleLenght).length < 3) {
                    jQuery('.contentTitleError').html(
                        '<label id="content_title-error" class="error" for="content_title">Title must be of minimum 3 letters</label>');

                    return false;
                } else {

                    jQuery('.contentTitleError').html(' ');

                }
                if (jQuery.trim(titleLenght).length > 50) {
                    jQuery('#add-linguist-content').find('.ajax_report').
                        removeClass('alert-success').removeClass('alert').removeClass('alert-danger').fadeIn(200);

                    jQuery('#add-linguist-content').find('.ajax_report').addClass('alert').addClass('alert-danger');

                    jQuery(".alert").html("Don't exceed the limit").show();
                } else {
                    jQuery(".alert").html("").hide();
                    jQuery('#add-linguist-content').find('.ajax_report').
                        removeClass('alert-success').removeClass('alert').removeClass('alert-danger').hide(200);
                }
            });

            $('#content_summary').keyup(function () {
                var summaryLenght = jQuery("#content_summary").val();
                if (jQuery.trim(summaryLenght).length < 3) {
                    jQuery('.contentSummaryError').html(
                        '<label id="content_summary-error" class="error" for="content_summary">Summary must be of minimum 3 letters</label>');

                    return false;
                } else {
                    jQuery('.contentSummaryError').html(' ');

                }
                if (jQuery.trim(summaryLenght).length > 500) {
                    jQuery('#add-linguist-content').find('.ajax_report').
                        removeClass('alert-success').removeClass('alert').removeClass('alert-danger').fadeIn(200);

                    jQuery('#add-linguist-content').find('.ajax_report').addClass('alert').addClass('alert-danger');

                    jQuery(".alert").html("Don't exceed the limit").show();
                } else {
                    jQuery(".alert").html("").hide();
                    jQuery('#add-linguist-content').find('.ajax_report').
                        removeClass('alert-success').removeClass('alert').removeClass('alert-danger').hide(200);
                }
            });



            jQuery('.publish_type_button').click(function () {


                var content_amount = jQuery('#content_amount').val();
                var publish_type = jQuery(this).data('publish_type');
                let content_sale_type = jQuery('#content_sale_type').val();
                //code-notes check for tags, ,and if none return false after showing message
                let da_tags = jQuery("input[name='hidden-project_tags']").val();
                if (!da_tags) {
                    bootbox.alert("Please include at least one keyword for this content");
                    return false;
                }
                if (jQuery.trim($("#content_title").val()).length < 3) {
                    jQuery('.contentTitleError').html(
                        '<label id="content_title-error" class="error" for="content_title">Title must be of minimum 3 letters</label>');

                    return false;
                } else if (jQuery.trim($("#content_title").val()).length > 50) {
                    bootbox.alert("Title must be of maximum 50 letters");
                    return false;
                } else if (jQuery.trim($("#content_summary").val()).length < 3) {
                    jQuery('.contentSummaryError').html('<label id="content_summary-error" class="error" for="content_summary">Summary must be of minimum 3 letters</label>');

                    return false;
                } else if (jQuery("#content_summary").val().length > 500) {
                    bootbox.alert("Description must be of maximum 500 letters");
                    return false;
                } else if ((content_sale_type === 'Fixed' || content_sale_type === 'Offer') && (content_amount <= 0 || content_amount === '')) {
                    bootbox.alert("Price can not be null or zero");
                    return false;
                }
                else if (content_amount <= 0 && content_amount !== '') {

                    console.log(content_amount);
                    console.log(typeof(content_amount));
                    bootbox.alert("Price can not be negative or zero");
                    return false;
                }


                //code-notes go through each chapter title input, and if one has an empty trimmed val, then cancel the submit
                let b_cancel_due_to_empty_title = false;
                $('input[name="sub_title[]"]').each(function(){
                    let input = $(this);
                    let da_title = input.val().trim();
                    let da_body = input.closest('table').find('input[name="sub_content[]"]').val().trim();
                    if (!da_title && da_body) {
                        bootbox.alert("<?= __('Chapter Titles Cannot be Empty')?>");
                        b_cancel_due_to_empty_title = true;
                        return false;
                    }
                });

                if (b_cancel_due_to_empty_title) {
                    return false;
                }

                jQuery('#publish_type').val(publish_type);
                //code-notes security keys are added in already, as hidden input fields
                $('#add-linguist-content').submit();
            }); //end start of submit process (the submit goes to the validate which goes to to the js handler for the ajax)

            if (jQuery('#content_sale_type').val() === "Free") {
                jQuery('#content_amount').hide();
                jQuery('#price_label').hide();
            }
            jQuery('#content_sale_type').change(function () {
                var sale_type = jQuery(this).val();
                let max_input = $('div.freelinguist-content-max-sold');
                if (sale_type === 'Fixed') {
                    jQuery('#content_amount').show();
                    jQuery('#price_label').show();
                    jQuery('#price_label').text('Price');
                    max_input.show();
                } else if (sale_type === 'Offer') {
                    jQuery('#content_amount').show();
                    jQuery('#price_label').show();
                    jQuery('#price_label').text('Min Bid');
                    max_input.hide();
                } else {
                    jQuery('#content_amount').val('');
                    jQuery('#content_amount').hide();
                    jQuery('#price_label').hide();
                    max_input.show();
                }
            });
        });
    </script>


<?php if (isset($content_detail['freezed']) && $content_detail['freezed'] == '1'): ?>
    <script>
        jQuery(function () {
            jQuery('.single-content :input,.single-content :button').prop('disabled', true);


        });
    </script>
<?php endif; ?>
    <!--code-notes moved the feedback dialog to the bottom of the content details page to avoid nested forms above-->
    <div class="modal fade" id="feedbackModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

                    <h4 class="modal-title"><?php get_custom_string('Feedback'); ?></h4>

                </div>

                <div class="modal-body">
                    <h4>Please submit feedback only after the job has been completed. You can not change it after it's
                        submitted. </h4>

                    <form class="bidform" id="hz_content_translator_feedback" method="post"
                          action='<?php echo get_permalink(); ?>' novalidate="novalidate">

                        <p class="price-form-status test-here">

                            <label for="ms_details"><?php get_custom_string('Rating'); ?></label><br>
                            <input title="1" type="radio" name="rating_by_freelancer" class="" value="1" checked>&nbsp;1&nbsp;
                            <input title="2" type="radio" name="rating_by_freelancer" class="" value="2">&nbsp;2&nbsp;
                            <input title="3" type="radio" name="rating_by_freelancer" class="" value="3">&nbsp;3&nbsp;
                            <input title="4" type="radio" name="rating_by_freelancer" class="" value="4">&nbsp;4&nbsp;
                            <input title="5" type="radio" name="rating_by_freelancer" class="" value="5">&nbsp;5


                        </p>

                        <p class="price-form-status">

                            <label for="ms_details"><?php get_custom_string('Feedback'); ?></label><br>

                            <textarea title="Commments" maxlength="10000" class="form-control" aria-required="true" autocomplete="off"
                                      name="comments_by_freelancer" id="comments_by_customer"></textarea>

                        </p>


                        <p class="form-submit">


                            <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">


                            <input type="submit" class="btn blue-btn bidreplysubmit"
                                   value="<?php get_custom_string('Submit'); ?>">

                        </p>

                    </form>

                </div>

            </div>

        </div>

    </div>
<?php
$b_show_dialog_open = false;
if (
    isset($content_detail) &&
    is_array($content_detail) &&
    count($content_detail) &&
    ($content_detail['status'] === 'completed') &&
    (empty($content_detail['rating_by_freelancer']))
) {
    $b_show_dialog_open = true;
}


if ($b_show_dialog_open) {
    ?>
    <script>
        jQuery(function ($) {
            $("a.hirebttn2[data-target='#feedbackModel']").click();
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
