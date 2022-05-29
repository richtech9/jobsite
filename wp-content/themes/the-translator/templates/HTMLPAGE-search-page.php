<?php

/*

Template Name: Search Template

*/



/*
* current-php-code 2020-Oct-16
* input-sanitized : lang,mode,spage,text
* current-wp-template:  Searches
* current-wp-top-template
*/

//code-notes add favorite listeners to the hearts, to add and remove favorite users and content

$lang = FLInput::get('lang','en');
$mode = FLInput::get('mode');
$spage = (int)FLInput::get('spage',0);
$text = FLInput::get('text');
define('FREELINGUIST_SEARCH_PAGE_SIZE', 20);
//will_dump('get',$_GET);


get_header();

global $wpdb;
$current_user = wp_get_current_user();

$current_user_id = $current_user->ID;

$current_translater_bid = get_all_bids_of_particular_job($current_user_id);


$allTags = $wpdb->get_results(/** @lang text */
    "SELECT tag_name FROM wp_interest_tags wit WHERE 1=1  LIMIT 0,10", ARRAY_A);
$allTags = array_column($allTags, 'tag_name');

$resultArray = array();

if ($mode || $text) {

    if ($mode) {
        $slicedTags = explode(' ', $mode);
        $query = $mode;
    } else {
        $slicedTags = (explode(' ', $text));
        $query = $text;
    }

    $lastTag = end($slicedTags);
    $orTags = '';
    foreach ($slicedTags as $st) {
        $orTags .= " OR wit.tag_name like '%{$st}%'";
    }
    $orTags = ltrim($orTags, ' OR ');
    $cachedTags = $wpdb->get_row(
        "SELECT GROUP_CONCAT(ID) as tag_id FROM wp_interest_tags wit WHERE ($orTags)", ARRAY_A);

    $allowRole = array('translator', 'customer');

    //ELASTIC CONNECTION

    //un-logged role, search content and freelancers.
    //customer role: search content and freelancers.
    //freelancer role: search projects/contests.


    $param1 = [
        'index' => 'translator'
    ];

    $param2 = [
        'index' => 'content'
    ];
    $total_hits = 0;
    $pages_total = 0 ;
    $response = null;

    if ($spage < 1) {
        $spage = 1;
    }

    $max_pages = 10000/FREELINGUIST_SEARCH_PAGE_SIZE - FREELINGUIST_SEARCH_PAGE_SIZE - 1; //offset + page < 10000

    $actual_pages = $spage;
    if ($spage > $max_pages) {
        $spage = $max_pages;
    }

    $from_place = ($spage - 1) * FREELINGUIST_SEARCH_PAGE_SIZE;

    try {
        $es = new FreelinguistElasticSearchHelper();
        $client = $es->get_client();

        if ($client->indices()->exists($param1) && $client->indices()->exists($param2)) {
            $index = 'translator,content';
        } else if ($client->indices()->exists($param1) && !$client->indices()->exists($param2)) {
            $index = 'translator';
        } else if (!$client->indices()->exists($param1) && $client->indices()->exists($param2)) {
            $index = 'content';
        } else {
            $index = '';
        }
        if (is_user_logged_in() && xt_user_role() == 'translator') {
            //$index = 'project,contest';
            $param1 = [
                'index' => 'project'
            ];

            $param2 = [
                'index' => 'contest'
            ];
            if ($client->indices()->exists($param1) && $client->indices()->exists($param2)) {
                $index = 'project,contest';
            } else if ($client->indices()->exists($param1) && !$client->indices()->exists($param2)) {
                $index = 'project';
            } else if (!$client->indices()->exists($param1) && $client->indices()->exists($param2)) {
                $index = 'contest';
            } else {
                $index = '';
            }
        }




        //echo $index;exit;
        $params = [
            'index' => $index,
            'type' => 'freelinguist',
            'body' => [
                "from" => $from_place,
                "size" => FREELINGUIST_SEARCH_PAGE_SIZE,
                "sort" => [
                    [ "recent_ts" => ["order" => "desc"]],
                    "_score"
                ],
                'query' => [
                    'query_string' => [
                        'fields' => ['title', 'tags', 'description', 'instruction'],
                        'query' => $query,
                    ]
                ]
            ]
        ];

              //will_dump('es params',$params);

        $response = $client->search($params);



    } catch (Exception $e) {
        will_send_to_error_log("Error with es search", will_get_exception_string($e));
        //if PeerOK try to redirect to the job page
        $job_search_link = freeling_links('job_listing_url');

        if (is_user_logged_in() && xt_user_role() == 'translator') {
            //code-notes if the ES is off, and this is a translator then simply redirect to the job search
            will_send_to_error_log('redirecting to job search page',$job_search_link);
            wp_safe_redirect($job_search_link);
            exit;
        }


        try {
            $response = FreelinguistSearchFromDB::search($query,$spage-1,FREELINGUIST_SEARCH_PAGE_SIZE);
        } catch (Exception $me) {
            will_send_to_error_log("Error with db search", will_get_exception_string($me));
        }

    }

    $foundData = [];
    if ($response && is_array($response)) {
        if (  isset($response['hits']) && isset($response['hits']['total'])) {
            $total_hits = (int)$response['hits']['total'];
        }
        if ($total_hits) {
            $pages_total = (int)ceil($total_hits / floatval(FREELINGUIST_SEARCH_PAGE_SIZE));
        }
        if ($pages_total > $max_pages) {
            $pages_total = $max_pages;
        }
        //   will_dump('es response',$response); die();
        $foundData = $response['hits']['hits'];
    } else {
        will_dump("Error with Search");

    }

} else {
    //no search params given so redirect to home
    wp_safe_redirect(site_url());
    exit;
}

$searchValue = $query;
$favContentIds = '';
$favTranslatorIds = '';
if (is_user_logged_in()) {
    $current_user_id = get_current_user_id();
    //FAV
    $favContentIds = get_user_meta($current_user_id, '_favorite_content', true);
    $favTranslatorIds = get_user_meta($current_user_id, '_favorite_translator', true);
}
?>

    <!-- code-notes removed /js/jquerymin.js (version 1.11.3) -->

    <div class="title-sec">

        <div class="container">

            <span class="bold-and-blocking large-text">Search Result for “<?php echo $searchValue; ?>”</span>

        </div>

    </div>

    <div class="project-post-cont search-res">

        <div class="container">

            <div class="search-cont">

                <div class="search-cont-left- col-md-12">


                    <?php
                    #code-notes added calculated pagination links to the page

                    $url_template = get_site_url() . "/searches/?spage=%page%lang=$lang&text=".urlencode($query) ;
                    //freelinguist_print_pagination_bar($spage,$pages_total,$url_template,'top'); //code-notes at bottom only
                    ?>

                    <?php
                    if (is_user_logged_in() && xt_user_role() == 'translator') {
                        echo '<div class="job-table full-width">';
                        echo '<div class="job-table-head">
							<ul>
								<li class="delivery-date">Delivery Date</li>
								<li class="project">Title</li>
								<li class="project" style="width:22%">Description</li>
								<li class="project" style="text-align:center;">Budget(Award Assured or Not)</li>
								<li class="project" style="text-align:center;">Post Date</li>
								<li class="project" style="text-align:center;">Bid/Participate</li>
							</ul>
						</div>';
                    }
                    ?>
                    <div class="search-row<?php if (is_user_logged_in() && xt_user_role() == 'translator') { ?> job-table-data <?php } ?>">
                        <ul>
                            <?php

                            if ($foundData) {
                                global $wpdb;
                                $wp_interest_tags = $wpdb->prefix . "interest_tags";
                                $upload_dir = wp_upload_dir();
                                ?>
                                <?php
                                foreach ($foundData as $data) {
                                    $priceValue = '';
                                    $jobs = $data['_source'];
                                    $image = get_template_directory_uri() . '/images/default-img-400x240.gif';

                                    if ($jobs['job_type'] == 'content') {
                                        $addtData = $wpdb->get_row("SELECT wlc.user_id,wlc.content_sale_type,
                                          wlc.content_amount price,wlc.content_cover_image image,
                                          content_view
                                           FROM wp_linguist_content wlc 
                                           WHERE wlc.user_id IS NOT NULL AND wlc.id='" . $jobs['job_id'] . "'", ARRAY_A);

                                        if (empty($addtData)) {
                                            continue; //deleted and out of sync with catch
                                        } else {
                                            $jobs = array_merge($jobs, $addtData);
                                        }
                                        //will_send_to_error_log('jobs',$jobs);

                                        $priceLable = 'Price or best price';
                                        $viewLable = 'Num of view';
                                        //code-notes [image-sizing]  content getting small size for unit
                                        $image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
                                            $jobs['image'],FreelinguistSizeImages::SMALL,true);


                                        $href = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($jobs['job_id']);

                                        if (isset($jobs['price'])) {
                                            $priceValue = '$' . $jobs['price'] . '/' . $jobs['content_sale_type'];
                                        }

                                        $noOfDone = '';
                                        if (isset($jobs['content_view']) && intval($jobs['content_view'])) {
                                            $number_views = intval($jobs['content_view']);
                                            $view_word = 'View';
                                            if ($number_views > 1) {
                                                $view_word = 'Views';
                                            }
                                            $noOfDone = "$number_views $view_word";
                                        }

                                        $favIds = $favContentIds;
                                        $job_type = 'content';
                                        $b_is_fav = in_array($jobs['job_id'], explode(',', $favIds));
                                        $country = get_user_meta($jobs['user_id'], 'user_residence_country', true);
                                        $country = ($country ? get_countries()[$country] : '');

                                    } elseif ($jobs['job_type'] == 'translator') {
                                        $addtData = $wpdb->get_row(
                                                "SELECT 
                                                            u.user_nicename,
                                                            u.display_name title,
                                                            (SELECT meta_value FROM `wp_usermeta` WHERE `meta_key` = 'user_image' and user_id=u.ID) image 
                                                          FROM  wp_users u 
                                                          WHERE u.ID='" . $jobs['job_id'] . "'
                                                  ", ARRAY_A);

                                        if (empty($addtData)) {
                                            continue; //deleted translator , out of sync with cache
                                        }
                                        else {
                                            $jobs = array_merge($jobs, $addtData);
                                        }
                                        $priceLable = 'Hourly Rate';
                                        $viewLable = '# of project done';
                                        $image = $avatar = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($jobs['image'],FreelinguistSizeImages::SMALL,true);

                                        $username = $jobs['user_nicename'];

                                        $href = site_url() . "/user-account/?lang=$lang&profile_type=translator&user=" . $username;

                                        if (isset($jobs['price'])) {
                                            $priceValue = '$' . $jobs['price'] . '/hourly';
                                        }

                                        //$noOfDone = '15 Project Done';
                                        $noOfDone = ''; //code-notes hiding hard coded data
                                        $favIds = $favTranslatorIds;
                                        $country = get_user_meta($jobs['job_id'], 'user_residence_country', true);
                                        $country = ($country ? get_countries()[$country] : '');
                                        $job_type = 'translator';
                                        $b_is_fav = in_array($jobs['job_id'], explode(',', $favIds));
                                    }

                                    ?>
                                    <li class="freelinguist-search-result-item"
                                        data-jid="<?= $jobs['job_id'] ?>"
                                        data-recent_ts="<?= $jobs['recent_ts'] ?>"
                                        data-job_type="<?= $jobs['job_type'] ?>"
                                    >
                                        <?php
                                        if ($jobs['job_type'] == 'contest' || $jobs['job_type'] == 'project') {

                                            $job_id = $jobs['job_id'];

                                            $tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
                                            if ($jobs['job_type'] == 'contest') {
                                                $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
                                            } else if ($jobs['job_type'] == 'project') {
                                                $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
                                            }

                                            $job_tbl = hz_is_linguist_asg($job_id, get_current_user_id());


                                            $ptype = get_post_meta($job_id, 'fl_job_type', true);
                                            //code-notes change wording of prize assured if insurance or not
                                            $is_guarented_phrase = '';
                                            if ($jobs['job_type'] === "contest") {
                                                $is_guarented_phrase = get_post_meta($job_id, 'is_guaranted', true) ? ' (Client Insurance)' : ' (Prize Assured)';
                                            }

                                            $job_title = get_post_meta($job_id, 'project_title', true);
                                            $tags = $wpdb->get_results(/** @lang text */
                                                "SELECT GROUP_CONCAT(tag_id) as tag_ids FROM wp_tags_cache_job WHERE `job_id` = $job_id AND type =$tagType");
                                            $tags_name_array = array();
                                            foreach ($tags as $k => $v) {
                                                $post_tags_array = explode(",", $v->tag_ids);
                                                foreach ($post_tags_array as $v1) {
                                                    $interest_tags = $wpdb->get_results(/** @lang text */
                                                        "SELECT * FROM $wp_interest_tags WHERE `id` = $v1");
                                                    foreach ($interest_tags as $k2 => $v2) {
                                                        $tags_name_array[] = $v2->tag_name;
                                                    }
                                                }
                                            }
                                            $link = $ptype == 'contest' ? get_the_permalink($job_id) . '&action=proposals' : get_site_url() . '/job/' . get_the_title($job_id);

                                            $type = $jobs['job_type'] == "contest" ? "Competition" : ucfirst($jobs['job_type']);
                                            $job_des = get_post_meta($job_id, 'project_description', true);
                                            echo '<div class="delivery-date-sta project-sta enhanced-text">' . get_post_meta($job_id, 'job_standard_delivery_date', true) . '<br><em>' . $type . '</em></div>';

                                            echo /** @lang text */
                                                '<div class="project-sta enhanced-text">'.
                                                '<a style="color:black;" href="' . $link . '">' .
                                                wp_trim_words($job_title, 3, '') .
                                                '<br><em>' .'' . '</em></a></div>';

                                            echo /** @lang text */
                                                '<div class="job-table-detail enhanced-text" style="width:50%">
									<a style="color:black;" href="' . $link . '">

									<div class="job-table-detail-top">' . wp_trim_words($job_des, 4, '...') . '</div>

									
									
									<div class="job-table-detail-col" style="overflow:hidden">' . implode(',', $tags_name_array) . '</div><br>
									<div class="job-table-detail-col">$' . str_replace("_", "-", get_post_meta($job_id, 'estimated_budgets', true)) . '' . $is_guarented_phrase . '</div>

									<div class="job-table-detail-col will-date">' . get_the_date('',$job_id) . '</div>

									</a>

								</div>';

                                            if ($ptype == 'contest') {


                                                $all_contest_paricipants = get_post_meta($job_id, 'all_contest_paricipants');

                                                if (in_array(get_current_user_id(), $all_contest_paricipants)) {


                                                    echo /** @lang text */
                                                        '<a class="hirebttn2" href="' . $link . '">View</a>';

                                                } else {

                                                    echo '<a  linguid="' . get_current_user_id() . '" contestid="' . $job_id . '" lang="' . $lang . '" class="hirebttn2 prt_accept" href="#">PARTICIPATE</a>';

                                                }


                                            } else {

                                                $bid_exist = check_bid_exist($job_id);

                                                if ($bid_exist) {
                                                    if ($job_tbl !== false) {

                                                    } else {

                                                        echo /** @lang text */
                                                            '<a class="hirebttn2" href="' . $link . '">Bidded</a>';

                                                    }
                                                } else {
                                                    echo '<a id="placebidbutton" data-target="#placeBidModel_' . $job_id . '" data-toggle="modal" class="hirebttn2" href="#">Bid</a>';
                                                }

                                            } ?>


                                            <div role="dialog" id="placeBidModel_<?php echo $job_id; ?>"
                                                 class="modal fade">

                                                <div class="modal-dialog">

                                                    <!-- Modal content-->

                                                    <div class="modal-content">

                                                        <div class="modal-header">

                                                            <button data-dismiss="modal" class="close huge-text"
                                                                    type="button">×
                                                            </button>

                                                            <h4 class="modal-title"><?php get_custom_string('Apply to this job'); ?></h4>

                                                        </div>

                                                        <div class="modal-body">

                                                            <div id="alert_message_model"></div>
                                                            <?php
                                                            if (isset($current_translater_bid[0]->comment_ID)) {
                                                                $comment_id = $current_translater_bid[0]->comment_ID;
                                                                $key = 'bid_price';
                                                                $bid_price = get_comment_meta($comment_id, $key);
                                                                if ($bid_price[0]) {
                                                                    $bid_price = $bid_price[0];
                                                                }
                                                            }
                                                            ?>
                                                            <form class="comment-form" id="commentform"
                                                                  onsubmit="return place_the_bid(this)" method="post"
                                                                  action="<?php echo get_permalink(); ?>"
                                                                  novalidate="novalidate"><p
                                                                        class="comment-form-comment">
                                                                    <label for="bidPrice"><?php get_custom_string('Bid Price'); ?></label>

                                                                    <input type="number" class="form-control"
                                                                           name="bidPrice" min="1"
                                                                           title = "Bid Price"
                                                                           value="<?php if (isset($bid_price)) {
                                                                               echo $bid_price;
                                                                           } ?>">
                                                                    <input type="hidden"
                                                                           value="<?php if (isset($current_translater_bid[0]->comment_ID)) {
                                                                               echo $current_translater_bid[0]->comment_ID;
                                                                           } ?>" name="comment_ID">

                                                                </p>

                                                                <p class="comment-form-comment">

                                                                    <label for="comment"><?php get_custom_string('Notes'); ?></label><br>

                                                                    <textarea maxlength="10000" class="form-control"
                                                                              style="height:200px" aria-required="true"
                                                                              name="comment"  autocomplete="off"

                                                                              id="comment"><?php echo isset($current_translater_bid[0]->comment_content) ? stripslashes(stringTrim($current_translater_bid[0]->comment_content)) : ''; ?></textarea>

                                                                </p>

                                                                <p class="form-submit">

                                                                    <input type="submit"
                                                                           value="<?php get_custom_string('Apply to this job'); ?>"
                                                                           class="btn blue-btn" id="submit"
                                                                           name="submit">

                                                                    <input type="hidden" id="comment_post_ID"
                                                                           value="<?php echo $job_id; ?>"
                                                                           name="comment_post_ID">

                                                                    <input type="hidden" id=""
                                                                           value="<?= $lang ?>"
                                                                           name="lang">

                                                                    <input type="hidden" value="0" id="comment_parent"
                                                                           name="comment_parent">

                                                                </p>

                                                            </form>

                                                        </div>

                                                    </div>


                                                </div>

                                            </div> <!-- #placeBidModel_-->


                                            <?php


                                        } else if (xt_user_role() != 'translator') {
                                            ?>
                                            <a href="<?php echo $href; ?>">
                                                <div class="search-row-left">
                                                    <img src="<?php echo $image; ?>" alt="">
                                                </div>
                                                <div class="search-row-right">
                                                    <span class="bold-and-blocking large-text">
                                                        <?php echo $jobs['title']; ?>
                                                        <br>
                                                        <label><?php echo $country; ?></label>
                                                    </span>
                                                    <div class="ratingsec">
                                                        <div class="price"><?php echo $priceValue; ?></div>
                                                        <div class="price">
                                                            <span class="hide">Not yet rated</span>
                                                        </div>
                                                        <div class="price"><?php echo $noOfDone; ?></div>
                                                    </div>
                                                    <p><?php echo substr($jobs['description'], 0, 550); ?></p>
                                                    <ul class="option">
                                                        <li class="view">
                                                            <a href="#">
                                                                <i class="fa fa-eye larger-text" aria-hidden="true"></i>
                                                            </a>
                                                        </li>
                                                        <li class="cart active hide">
                                                            <a href="#">
                                                                <i class="fa fa-cart-arrow-down larger-text" aria-hidden="true"></i>
                                                            </a>
                                                        </li>
                                                        <li class="heart <?=($b_is_fav ? 'active' : ''); ?>">
                                                            <a href="#">
                                                                <i      class="fa fa-heart<?php
                                                                                echo(!$b_is_fav ? '-o' : '');
                                                                                ?> fa-favorite larger-text"
                                                                        data-fav="<?= ($b_is_fav? '0': '1') ?>"
                                                                        data-job_id="<?= $jobs['job_id'] ?>"
                                                                        data-id="<?= $jobs['job_id'] ?>"
                                                                        data-c_type="<?= $job_type ?>"
                                                                        data-login="<?= get_current_user_id() ?>"
                                                                ></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </a>
                                            <?php if ($job_type === 'translator') { ?>
                                            <div class="hire-freelancer-button-holder">
                                                <button class="red-btn-no-hover hire-freelancer"
                                                        data-freelancer_nicename="<?= $jobs['user_nicename'] ?>"
                                                        data-freelancer_id="<?= $jobs['job_id']?>"
                                                >
                                                    Hire
                                                </button>
                                            </div>
                                            <?php } //end if search item is translator ?>

                                            <?php if ($job_type === 'content') { ?>
                                                <span class="fl-content-button-holder ">
                                                    <?php

                                                    set_query_var( 'b_show_all_offers', 0 );
                                                    set_query_var( 'content_id', $jobs['job_id'] );
                                                    get_template_part('includes/user/contentdetail/contentdetail',
                                                        'customer-button-buy');
                                                    ?>
                                                </span>
                                                <!-- code-notes above was inserted content buy button-->
                                            <?php } //end if search item is translator ?>

                                    <?php }//end if current user is translator ?>
                                    </li>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="search-row">
                                    No results found!
                                </div>
                            <?php } ?>
                        </ul>

                    </div>
                    <?php if (is_user_logged_in() && xt_user_role() == 'translator') {
                        echo '</div>';
                    } ?>
                    <!-- code-notes added footer page bar and search stats  -->
                    <?php freelinguist_print_pagination_bar($spage,$pages_total,$url_template,'bottom'); ?>

                    <div class="freelinguist-search-meta">

                        <?php if ($total_hits > 0 && $pages_total > 1) { ?>
                            <span class="freelinguist-total-search-hits">There are <?= number_format($total_hits) ?> Results for this Search</span>
                         <?php } ?>

                        <?php
                        if ($actual_pages < $pages_total) {
                            $max_results = number_format($pages_total * FREELINGUIST_SEARCH_PAGE_SIZE);
                            ?>
                            <br>
                            <span class="freelinguist-total-search-overflow">Only displaying the first <?= $max_results ?> matches</span>
                        <?php } ?>

                    </div> <!-- ./freelinguist-search-meta-->
                </div> <!-- ./search-cont-left -->


            </div> <!-- /search-cont -->

        </div>

    </div>



    <?php
    get_template_part('includes/user/author-user-info/translator', 'hire-dialog');
    get_template_part('includes/user/contentdetail/contentdetail', 'customer-button-buy-dialogs');
    ?>

<?php get_footer('homepagenew');