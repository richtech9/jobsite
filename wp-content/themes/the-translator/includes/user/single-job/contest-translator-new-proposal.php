<?php

/*
* current-php-code 2020-Oct-10
* input-sanitized : lang,linguist
* current-wp-template:  freelancer contest, making a new proposal
*/

$lang = FLInput::get('lang', 'en');
$linguist = FLInput::get('linguist');
$sealcont = (int)FLInput::get('sealcont');


$check_login = (is_user_logged_in()) ? 1 : 0;

global $wpdb;

global $post;


$post_id = get_the_ID();

$current_user = wp_get_current_user();

$SiteLang = $lang;

$currLingu = $current_user->ID;

$job_freeze = false;
if (in_array($currLingu, get_post_meta($post_id, 'job_freeze_user')) &&
    !empty(get_post_meta($post_id, 'job_freeze_user')))
{
    $job_freeze = true;
    echo "Job is freezed";
    exit;
}


$author_id = $post->post_author;

$seal_fee = (float)get_option('seal_fee') ? get_option('seal_fee') : 10;


$addParticipants = get_post_meta($post_id, 'all_contest_paricipants', true);

$curParti = explode(',', $addParticipants);




$getBalance = (float)get_user_meta($currLingu, 'total_user_balance', true);
if (!(empty($_POST)) && ($getBalance < 0)) {
    //redirect to the same page again to show wallet
    $my_url_here = get_site_url() . '/job/' . get_the_title($post_id) . '/?lang=' . $SiteLang . '&action=new-proposal';
    wp_redirect($my_url_here);
    exit;

}


?>
<script>
    //code-notes Add in form-keys for proposal page for freelancer
    if (adminAjax) {
        adminAjax.form_keys.hz_contest_new_proposal_data_proc_save =
            '<?= FreeLinguistFormKey::create_form_key('hz_contest_new_proposal_data_proc_save') ?>';

    }
</script>

<div class="customer-participants view job_dtls">

    <div class="title-top">

        <div class="container">

            <i class="icon icon-box"></i>

            <p class="large-text">

                <?php echo get_post_meta($post_id, 'project_title', true); ?>
            </p>

        </div>

    </div>


    <section class="middle-content">

        <div class="container participant-sec">

            <div class="row submissions_btns">

                <?php


                $participants = $wpdb->get_results("SELECT * FROM wp_files WHERE `post_id` = $post_id AND 'user_id' = $author_id");

                ?>


                <a class="hirebttn2 fr"
                   href="<?php echo get_site_url(); ?>/job/<?php echo get_the_title($post_id); ?>/?lang=<?php echo $SiteLang; ?>"
                >
                    Job Details
                </a>

            </div>

        </div>

        <div class="container proposal-description-holder">
            <label for="proposal_description" class="enhanced-text bold-and-blocking">Proposal Description</label>
            <textarea name="proposal_description" id="proposal_description" rows="15" autocomplete="off" ></textarea>
            <span class="fl-proposal-save-error"></span>
        </div>

        <div class="container upload_file_row upload-file-row-padding">

            <?php


            if (true) {
                ?>

                <div class="upload-file regular-text">

                    <i class="fa fa-upload enhanced-text"></i>

                    Upload a Sample Picture or Pdf File

                    <input  name="files[]" id="hz_contest_new_proposal_data"
                           class="files-data hz_order_process"
                           data-id="<?php echo $post_id; ?>"
                           type="file">


                    <!--   code-notes the new proposal hidden input                 -->
                    <input type="hidden" id="proposal_id" name="proposal_id" value="">
                    <input type="hidden" id="file_id" name="file_id" value="">


                </div>
                <div class="upload-file regular-text" id="submit_proposal_span" style="display:none;">

                    <a href="#" style="color:#fff;" class="files-data"
                       type="button" id="save_proposal" data-project_id="<?php echo $post_id; ?>"
                    >
                        Save
                    </a>

                </div>

                <?php
                $sealExists = $wpdb->get_results(
                    "SELECT * FROM wp_fl_transaction WHERE user_id =".$currLingu." AND type = 'seal_Files'  AND project_id = $post_id");

                $b_seal_exists = false;
                if( (count($sealExists) >= 1)) {
                    $b_seal_exists = true;
                }

                if(!$job_freeze){
                    $seal_fee = (float)get_option('seal_fee') ? get_option('seal_fee') : 10;
                    ?>
                    <div class="fl-seal-content fr">
                        <?php if( $b_seal_exists  ){?>
                            <span class="fl-outer-dets">
                                <span class="fl-inner-dets">All your uploads are sealed. </span>
                                <small>( only the customer can see it. )</small>
                            </span>
                        <?php } else if ($b_seal_exists) { ?>
                            <span class="fl-outer-dets" ></span>
                        <?php } else { ?>
                        <button class="fl-proposals-seal"  data-jobid="<?= $post_id?>">
                            Seal Submissions
                        </button>
                        <br>
                        <span class="fl-outer-dets">
                            <span class="fl-inner-dets">Seal all my submissions: $<?= $seal_fee ?> </span>
                            <small>( only the customer can see it. )</small>
                            <span class="fl-error" style="display: none"></span>
                        </span>
                        <?php }  //end if else seal not purchased ?>
                    </div>
                    <?php
                }//end if not job freeze
                ?>

                <br>

                <!-- Progress Bar-->

                <div id="progress" class="progress" style="margin-top: 10px;">

                    <div class="progress-bar progress-bar-success"></div>

                </div>

                <div class="percent"></div>

                <!-- The container for the uploaded files -->

                <div id="files_name_container" class="files"></div>


                <?php

            }
            ?>

        </div>


        <div class="content-sec">

            <div class="container">

                <div class="col-md-9 slide-sec">

                </div>


                <div class="col-md-3 right-notify">

                    <div class="hz_discussion_row prize-lists">

                        <?php
                        echo hz_fl_discussion_list_both($post_id, $currLingu, $author_id); ?>

                    </div>

                    <div class="message-sec text-box">

                        <form id="contest_discussion">

                            <textarea title="comment" required name="comment" autocomplete="off" ></textarea>

                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

                            <input type="hidden" name="comment_to" value="<?php echo $author_id; ?>">

                            <input type="submit" class="red-btn enhanced-text" value="Contact Customer">

                        </form>

                    </div>

                </div>

            </div>

        </div>


    </section>

</div>


<script type="text/javascript">

    jQuery(function () {

        jQuery('.flexslider').flexslider({

            animation: "slide",

            animationLoop: false,

            itemWidth: 200,

            controlNav: false,

            itemMargin: 10,

            minItems: 1,

            maxItems: 4

        });

    });

    jQuery('.flexslider .slides li').css('width', jQuery('body').width());


</script>


