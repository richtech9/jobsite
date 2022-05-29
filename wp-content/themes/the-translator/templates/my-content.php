<?php
/* Template Name: My Content */

/*
* current-php-code 2020-Oct-15
* input-sanitized :
* current-wp-template:  customer views bought content
* current-wp-top-template
*/


if (!defined('WPINC')) {
    die;
}
$lang = FLInput::get('lang', 'en');
check_login_redirection_home($lang);
if (current_user_can('administrator')) {
    wp_redirect(admin_url());
}
get_header();
add_action('wp_head', 'add_meta_tags', 2); // Cache clear

global $wpdb;

$prefix = $wpdb->prefix;


$current_user_id = get_current_user_id();
$page = (get_query_var('page')) ? get_query_var('page') : 1;
$table_content = $wpdb->prefix . 'linguist_content';
$Per_Page = 8;


//code-bookmark This is where the purchased content is read from , to display to the customer as a list
$purchased_content = $wpdb->get_results("

        SELECT tc.*,
          tc.id as content_id,
          tt.tag_id,
           (SELECT GROUP_CONCAT(tt.tag_id)) as tag_ids,
           UNIX_TIMESTAMP(updated_at) as updated_at_ts,
           UNIX_TIMESTAMP(requested_completion_at) as  requested_completion_ts
        FROM wp_linguist_content tc
          LEFT JOIN wp_tags_cache_job tt on tc.id = tt.job_id AND tt.type = " . FreelinguistTags::CONTENT_TAG_TYPE . "
        WHERE tc.purchased_by = $current_user_id AND tc.publish_type='Purchased'
        GROUP BY tc.id
        ORDER BY tc.purchased_at DESC;
                                     
    ");


$purchased_contents = $wpdb->get_results("
                  select * from wp_linguist_content 
                  where purchased_by = $current_user_id AND publish_type='Purchased' 
                  order by purchased_at desc");

if (count($purchased_contents) <= $Per_Page) {
    $Num_Pages = 1;
} elseif ((count($purchased_contents) % $Per_Page) == 0) {
    $Num_Pages = (count($purchased_contents) / $Per_Page);
} else {
    $Num_Pages = (count($purchased_contents) / $Per_Page) + 1;
    $Num_Pages = (int)$Num_Pages;
}
$total_pages = $Num_Pages;
$big = 999999999; // need an unlikely integer

?>
<style>


    @media (max-width: 767px) {
        #datatable thead {
            display: none;
        }



        .rr table tbody tr td.thrd-td h5 strong {
            width: auto !important;
        }

        #datatable tbody td:first-child:before {
            content: "Purchase Date";
        }

        #datatable tbody td:nth-child(2):before {
            content: "Title";
        }

        #datatable tbody td:nth-child(3):before {
            content: "Description";
        }

        #datatable tbody td:nth-child(5):before {
            content: "Status";
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

<style type="text/css">

    section.noprojects_sec {

        min-height: 600px !important;

    }

</style>


<?php if ($purchased_content) { ?>

    <section class="middle-content rr freelinguist-customer-content-dashboard">

        <div class="container">

            <div class="own-job-dashboard">

                <div class="job-table">

                    <div class="tabblee table-responsive enhanced-text">

                        <table class="" id="datatable">

                            <thead>

                            <tr>

                                <th class="first-th enhanced-text">Purchase Date</th>
                                <th class="snd-th enhanced-text">Title</th>
                                <th class="trd-th enhanced-text">Description</th>
                                <th class="trd-th enhanced-text">&nbsp;</th>
                                <th class="forth-th enhanced-text" >Status</th>

                            </tr>

                            </thead>

                            <tbody>
                            <?php
                            foreach ($purchased_content as $k => $content) {

                                $new_date = '';
                                $contentTagsAr = [];
                                if ($content->tag_ids) {
                                    $contentTags = $wpdb->get_results("select tag_name from wp_interest_tags where id IN (" . $content->tag_ids . ")", ARRAY_A);


                                    if ($contentTags) {
                                        $contentTagsAr = array_column($contentTags, 'tag_name');
                                        $contentTagsAr = stripslashes_deep($contentTagsAr);
                                    }

                                }


                                ?>
                                <tr data-job-id="content-<?php echo $content->content_id; ?>">

                                    <td class="scnd-td enhanced-text">
                                        <a style="color: #666;"
                                            href="<?php
                                                echo freeling_links('content_detail_url'); ?>&mode=view&content_id=<?php
                                                echo FreelinguistContentHelper::encode_id($content->content_id); ?>"
                                        >
                                            <?php echo date('Y-m-d', strtotime($content->purchased_at));?>
                                            <br>
                                            <em>Content</em>
                                        </a>
                                    </td>

                                    <td class="scnd-td enhanced-text">

                                        <p>
                                            <a style="color: #666;"
                                              href="<?php
                                                    echo freeling_links('content_detail_url'); ?>&mode=view&content_id=<?php
                                                    echo FreelinguistContentHelper::encode_id($content->content_id); ?>"
                                            >
                                                <?php
                                                echo stripslashes(mb_strimwidth($content->content_title, 0, 100, ' ...'));
                                                ?>
                                            </a>
                                        </p>
                                        <em>
                                            <?php echo substr(get_userdata($content->user_id)->display_name, 0, 10); ?>
                                        </em>
                                    </td>

                                    <td class="thrd-td enhanced-text">

                                        <a style="color: #666;"
                                           href="<?php
                                                echo freeling_links('content_detail_url'); ?>&mode=view&content_id=<?php
                                                echo FreelinguistContentHelper::encode_id($content->content_id); ?>"
                                        >
                                            <p>
                                                <?php echo stripslashes(mb_strimwidth($content->content_summary,
                                                    0, 150, ' ...')); ?>
                                            </p>

                                            <em>
                                                <?php echo implode(",", $contentTagsAr) ?>
                                            </em>

                                        </a>
                                    </td>

                                    <td class="frth-td enhanced-text">
                                        <strong style="float:right;">
                                            <a style="color: #666;"
                                                href="<?php
                                                    echo freeling_links('content_detail_url'); ?>&mode=view&content_id=<?php
                                                    echo FreelinguistContentHelper::encode_id($content->content_id); ?>"
                                            >
                                                $<?php echo str_replace("_", "-", $content->content_amount); ?>
                                            </a>
                                        </strong>
                                    </td>


                                    <td class="frth-td enhanced-text fl-customer-content-status">

                                        <a style="color: #666;"
                                           href="<?php
                                                echo freeling_links('content_detail_url'); ?>&mode=view&content_id=<?php
                                                echo FreelinguistContentHelper::encode_id($content->content_id); ?>"
                                        >
                                            <?php
                                            $con_status = 'Delivery';
                                            $requested_completion_ts = intval($content->requested_completion_ts);
                                            $new_date = ($requested_completion_ts +
                                                    (60 * 60* floatval(get_option("auto_job_approvel_customer_hours"))))*1000;

                                            if (
                                                //code-notes added !== to request_revision as a condition to show timer in the customer content dashboard
                                                ($content->status !== 'request_revision') &&
                                                $requested_completion_ts &&
                                                (
                                                    ($content->status == 'request_completion') ||
                                                    ($content->publish_type == 'Purchased' && $content->purchased_by == get_current_user_id())
                                                )
                                            ) {
                                                $con_status = 'Review (<span class="demo_time" data-content_id="' .
                                                                $content->content_id . '" id="content_time_' . $content->content_id . '" data-new_date="' .
                                                                $new_date . '"></span>)';
                                            }

                                            if ($content->status == 'request_rejection') {
                                                $con_status = 'Dispute';
                                            }
                                            if ($content->status == 'completed') {
                                                $con_status = 'Completed';
                                            }
                                            if ($content->status == 'rejected') {
                                                $con_status = 'Rejected';
                                            }
                                            if ($content->status == 'hire_mediator') {
                                                $con_status = 'Mediation';
                                            }
                                            ?>
                                            <?php echo $con_status; ?>
                                            <?= FLRedDot::generate_dot_html_for_user(
                                                [ FLRedDot::TYPE_CONTENT],$content->content_id
                                            );
                                            ?>

                                        </a>
                                    </td>
                                </tr>

                                <?php
                            } //end foreach purchased content
                            ?>
                            </tbody>
                        </table>

                        <div class="paginationdiv pgnt_dashboard-page">
                        </div>

                    </div> <!-- /.tabblee.table-responsive -->

                </div> <!-- /.job-table -->

            </div> <!-- /.own-job-dashboard -->

        </div> <!-- /.container -->

    </section> <!-- /.middle-content -->

<?php } else { ?>

    <section class="noprojects_sec">

        <div class="container">

                <br><br><br><br>

                <h3 style="text-align: center">
                    No content.
                </h3>

                <br><br><br><br>

        </div>

    </section> <!--/.noprojects_sec  -->

<?php } ?>
<?php get_footer('homepagenew'); ?>



<script>

    jQuery(function ($) {
        if ($(window).width() > 767) {
            jQuery('#datatable').DataTable({
                "pagingType": "full_numbers",
                "searching": false,
                "bInfo": false,
                "order": [[0, "desc"]],
                "language": {
                    "lengthMenu": "_MENU_ <table style='width:50%;float:left;'><tr><td class='records-per-page  regular-text'>records per page</td></tr></table>",

                },
                "fnInitComplete": function () {


                    jQuery('.own-job-dashboard select').css({"float": "left"});
                    jQuery('#datatable_length').css({"width": "100%"});
                    jQuery('#datatable_length label').css({"padding": "10px 10px", "width": "40%", "float": "left"});


                },
                "drawCallback": function (/*settings*/) {
                    time_show();
                }
            });
        }
    });


    function time_show() {
        jQuery('.demo_time').each(function () {

            var content_id = jQuery(this).data('content_id');


            var countDownDate = new Date(parseInt(jQuery(this).data('new_date'))).getTime();

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
                document.getElementById("content_time_" + content_id + "").innerHTML = days + "d " + hours + "h "
                    + minutes + "m " + seconds + "s  left";

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("content_time_" + content_id + "").innerHTML = "EXPIRED";
                }
            }, 1000);
        });
    }

</script>

