<?php
/*
* current-php-code 2020-Oct-14
* input-sanitized : content_id,lang,mode,page
* current-wp-template:  customer view of content detail
*/

$obsolete_content_id_from_unused_payment = (int)FLInput::get('content_id', 0);
$content_id_encoded = FLInput::get('content_id', 0);
$lang = FLInput::get('lang', 'en');
$mode = FLInput::get('mode');
$section = (int)FLInput::get('section', 0);

$content_id =  FreelinguistContentHelper::decode_id($content_id_encoded);
$favorite_content = get_user_meta(get_current_user_id(), '_favorite_content', true);
if (current_user_can('administrator')) {

    wp_redirect(admin_url());

}

get_header();

?>

<link href="<?php echo get_template_directory_uri().'/css/current-code/content-customer-read.css?version=0.1.1'; ?>" rel="stylesheet">

<?php

global $wpdb;

$wp_upload_dir = wp_upload_dir();
$basepath = $wp_upload_dir['baseurl'];
$check_login = (is_user_logged_in()) ? 1 : 0;
$current_user = wp_get_current_user();

try {
    $content = FreelinguistContentHelper::get_content_extended_information($content_id, true);
} catch (Exception $e) {
    //trigger 404 page and exit
    will_send_to_error_log('Trying to display missing or unowned content',will_get_exception_string($e));
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}

$tag_ids = implode(',',$content['tag_ids']);

if ($tag_ids) {

    $sql = "
             SELECT
            
              u.ID                                                         primary_id,
              u.user_nicename,
              ''                                                           user_id,
              u.display_name                                               title,
              meta_description.meta_value as  description,
              '0'                                                          price,
              meta_user_image.meta_value as  image,
            
              ''                                                           content_sale_type,
              'translator'                                                 job_type,
              '0'                                                          is_sold
            
            FROM wp_users u
              INNER JOIN (
                SELECT job_id FROM wp_tags_cache_job wtcj 
                WHERE wtcj.tag_id IN ($tag_ids) 
                  AND wtcj.type = " . FreelinguistTags::USER_TAG_TYPE . "
                ORDER BY RAND()
                LIMIT 0, 8
            
                         ) as similar_users ON similar_users.job_id = u.ID
            
            
              LEFT JOIN wp_usermeta meta_description
                ON meta_description.user_id = u.ID AND meta_description.meta_key = 'description'
            
              LEFT JOIN wp_usermeta meta_user_image
                ON meta_user_image.user_id = u.ID AND meta_user_image.meta_key = 'user_image'
            ";

    $similar_record_freelancer = $wpdb->get_results($sql, ARRAY_A);

    $sql = "
                SELECT
                  wlc.id                                                                            primary_id,
                  ''                                                                                user_nicename,
                  wlc.user_id,
                  wlc.content_title                                                                 title,
                  wlc.content_summary                                                               description,
                  wlc.content_amount                                                                price,
                  wlc.content_cover_image                                                           image,
                  wlc.content_sale_type,
                  'content'                                                                         job_type,
                  '0' as is_sold
                 FROM wp_linguist_content wlc
               INNER JOIN (
                            SELECT job_id FROM wp_tags_cache_job wtcj
                              INNER JOIN (
                                           SELECT id from wp_tags_cache_job c
                                           WHERE c.tag_id IN ($tag_ids) 
                                                AND c.type = " . FreelinguistTags::CONTENT_TAG_TYPE . "
                                           ORDER BY RAND()
                                           LIMIT 0, 8
                                         ) driver ON driver.id = wtcj.id
            
                              INNER JOIN wp_linguist_content content
                                ON content.id = wtcj.job_id AND content.publish_type = 'publish'AND content.user_id IS NOT NULL
            
                  ) as similar_content ON similar_content.job_id = wlc.id
                              
                             ";
    $similar_record_content = $wpdb->get_results($sql, ARRAY_A);
} else {
    $similar_record_freelancer = [];
    $similar_record_content = [];
}


$content_detail = $wpdb->get_results("SELECT * from wp_linguist_content_chapter WHERE user_id IS NOT NULL AND linguist_content_id = $content_id ORDER BY page_number ASC",
    ARRAY_A);
$rows = sizeof($content_detail);

$offset = 0;
$page = 1;
$limit = 1;
if ($section) {
    $page = $section;
    $offset = ($page - 1) * $limit;
}
$totalpages = ceil($rows / $limit);



$amount = (float)$content['content_amount'];

$content_detail = $wpdb->get_row("SELECT * from wp_linguist_content_chapter
                                          WHERE user_id IS NOT NULL AND linguist_content_id = $content_id 
                                           ORDER BY page_number ASC limit " . $offset . "," . $limit,
                        ARRAY_A);
$show_chapter = true;
if ($content_detail['content_visible'] == 'buy') {
    $current_user_id = get_current_user_id();
    $get_row = $wpdb->get_row("select * from wp_linguist_content where user_id IS NOT NULL AND id = $content_id and purchased_by=$current_user_id", ARRAY_A);
    if (is_array($get_row)) {
        $show_chapter = true;
    } else {
        $show_chapter = false;
    }
}

$customer_id = get_current_user_id();


$row = $wpdb->get_row("
              select * from wp_linguist_content 
              where id = $content_id and publish_type='Purchased' and purchased_by=$customer_id AND user_id IS NOT NULL
              ", ARRAY_A);
$isBuy = 0;
if (is_array($row)) {
    $isBuy = 1;
}

$content_author_detail = get_userdata($content['user_id']);

?>

<section>
    <div class="header-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-md-7 col-sm-12">
                    <div class="header-title">

                                <span class="bold-and-blocking large-text">

                                    <?php
                                    if ($show_chapter === true) {
                                        echo stripslashes_deep($content_detail['title']);
                                    } else {
                                        echo 'Page only for the buyer.';
                                    }
                                    ?>
                                </span>

                    </div>
                </div> <!-- /.col-->
            </div> <!-- /.row-->
        </div> <!-- /.container-->
    </div> <!-- /.header-area-->
</section>


<section class="freelinguist-customer-read">
    <div class="middle-content rr main_content">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 col-md-12 col-sm-12">

                    <div class="left_site_bar">
                        <div class="page-area"> <!-- code-notes moved this to be at top -->
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <a href="<?php echo site_url() . '/content/?lang=' . $lang .
                                        '&mode=view&content_id=' . FreelinguistContentHelper::encode_id($content_id);
                                        ?>"
                                       class="box-page"
                                    >
                                        Home
                                    </a>
                                </div>
                                <div class="col-lg-8 col-md-8 col-sm-12">
                                    <?php
                                    if ($totalpages > 1) { ?>
                                        <div class="box-number">
														<span class="enhanced-text"
                                                              style="float:left;">Page <?php echo $page; ?>
                                                            of <?php echo $totalpages; ?></span>


                                            <?php

                                            for ($i = 1; $i <= $totalpages; $i++) {
                                                $href = site_url() . '/content/?lang=en&mode=read&content_id=' .
                                                    FreelinguistContentHelper::encode_id($content_id) . '&section=' . $i;
                                                ?>
                                                <a class="enhanced-text"
                                                   href="<?php echo $href; ?>"><?php echo $i; ?></a>
                                                <?php
                                            }

                                            $href = site_url() . '/content/?lang=en&mode=read&content_id=' .
                                                FreelinguistContentHelper::encode_id($content_id) . '&section=' . $totalpages;
                                            ?>
                                            <a class="enhanced-text" href="<?php echo $href; ?>">Last</a>

                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <?php
                        if ($show_chapter === true) {
                            ?>
                            <div class="fl-content-chapter-html-container">
                                <?php echo($content_detail['content_html']); ?>
                            </div>
                        <?php } ?>


                        <div class="clearfix"></div>

                    </div>
                </div>

                <div class="col-lg-3 col-md-12 col-sm-12">
                    <div class="right_site_bar">
                        <div class="site_bar_box_1">
                        <!--
                        (logo) (name and chat)
                        (content name)
                        (price) (favorite)
                        (buy button)
                        (stars , if showing )
                        -->


                                <?php $avatar = hz_get_profile_thumb($content['user_id'],FreelinguistSizeImages::SMALL,false); ?>
                                <img class="freelinguist-small-profile-pic" style="" src="<?= $avatar ?>">
                                <!-- the content image -->

                                <div class="fl-name-and-chat-customercontent-read enhanced-text">
                                    <i class="fa fa-pencil-square-o large-text" aria-hidden="true"></i>
                                    <span class="fl-freelinguist-name">
                                        <?= get_da_name( $content_author_detail->ID) ?>
                                    </span>
                                    <br>
                                    <?php
                                    //code-notes chat part
                                    set_query_var('job_id',$content_id);
                                    set_query_var('to_user_id', $customer_id);
                                    set_query_var('job_type', 'content');
                                    set_query_var( 'b_show_name', 0 );
                                    get_template_part('includes/user/chat/chat', 'button-area');
                                    ?>

                                </div><!-- the name and chat button -->


                            <span class="bold-and-blocking  fl-content-title large-text"><?php echo stripslashes_deep($content['content_title']) ?></span>
                            <!-- the title -->

                            <div class="fl-favorite-and-price-holder">
                                <div class="floatleft fl-content-price-holder">
                                    <?php if ($content['content_sale_type'] !== 'Offer') {
                                        ?>
                                        <span class="fl-content-amount  large-text" style="text-align: left">
                                                $<?= amount_format($content['content_amount']) ?>
                                            </span>
                                    <?php } //the price  ?>
                                </div>


                                <div class="floatright fl-favorite-holder">
                                    <?php
                                    get_template_part('includes/user/contentdetail/contentdetail',
                                        'customer-button-favorite');
                                    ?><!-- the heart button -->
                                </div>
                            </div>


                            <!--  code-notes have inside in this order, span, purchase button, favorite button, chat button  -->
                            <div class="prement-box fl-content-read-buttons" style="clear: both">

                                <span class="fl-content-button-holder">
                                    <?php
                                    set_query_var( 'b_show_all_offers', 1 );
                                    get_template_part('includes/user/contentdetail/contentdetail',
                                        'customer-button-buy');
                                    ?> <!-- the buy button -->
                                </span>





                                <p class="enhanced-text">
                                    <?php if (!empty($row['rating_by_customer'])) {

                                        for ($j = 1; $j <= $row['rating_by_customer']; $j++) {
                                            ?>
                                            <span><i class="fa fa-star large-text" aria-hidden="true"></i></span>

                                        <?php }
                                    }
                                    ?>
                                </p> <!-- the stars -->



                            </div>
                            <!--  code-notes end refactored block   -->
                        </div>
                        <?php

                        foreach ($similar_record_freelancer as $value) {
                            $userdetail = get_userdata($value['primary_id']);
                            $country = get_user_meta($value['primary_id'], 'user_residence_country', true);
                            $country = ($country ? get_countries()[$country] : 'N/A');


                            ?>
                            <?php
                            //code-notes [image-sizing]  get sized image from url fragment
                            $bg_image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($value['image'],FreelinguistSizeImages::SMALL,true);

                            $priceValue = (get_user_meta($value['primary_id'], 'user_hourly_rate', true)) ? '$' .
                                get_user_meta($value['primary_id'], 'user_hourly_rate', true) . '/hours' : '';
                            $href = site_url() . "/user-account/?lang=$lang&profile_type=translator&user=" . $value['user_nicename'];


                            ?>
                            <div class="content-read-user-info-holder" style="padding:0;margin-bottom:20px">
                                <div class="user-info" style="width: 100%; display: inline-block;">
                                    <div class="slide-inn">
                                        <a href="<?php echo $href; ?>">
                                            <figure>
                                                <img src="<?php echo $bg_image; ?>" alt="freelinguist"
                                                     style="">
                                            </figure>
                                            <div class="col-md-12 description-user">
                                                                    <span class="eye">
                                                                        <img src="<?php echo get_template_directory_uri().'/images/eye-see.png'; ?>"
                                                                             alt="freelinguist"/>
                                                                    </span>

                                                <ul>
                                                    <li class="li-1 enhanced-text" style="max-height: 18px;overflow: hidden;">
                                                        <span>
                                                            <?= stripslashes_deep(substr($value['title'], 0, 25)); ?>
                                                        </span>
                                                    </li>
                                                    <li class="li-22">
                                                        <span  class="one-line-no-overflow">
                                                            <?php echo stripslashes_deep(substr($value['description'], 0, 55)); ?>
                                                        </span>
                                                    </li>

                                                    <li class="li-2 enhanced-text">
                                                        <span><?php echo $userdetail->display_name; ?></span>
                                                        <span class="pull-right"> </span>
                                                    </li>
                                                    <li class="li-2 enhanced-text">
                                                        <span>
                                                            <?php echo $country; ?>
                                                        </span>
                                                        <span class="pull-right colored">
                                                            <?php echo $priceValue; ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div> <!-- /.col -->
                                        </a>
                                        <div class="hire-freelancer-button-holder">
                                            <button class="red-btn-no-hover red-background-white-text hire-freelancer"
                                                    data-freelancer_nicename="<?= $userdetail->user_nicename ?>"
                                                    data-freelancer_id="<?= $userdetail->ID ?>"
                                            >
                                                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                                                Hire
                                            </button>
                                        </div>
                                    </div> <!-- /.slide-in -->
                                </div> <!-- /.user-info -->
                            </div> <!-- /(anon) -->

                            <?php
                        } //end for each similar record

                        ?>


                    </div> <!-- /.right_site_bar -->
                </div> <!-- /.col -->
            </div> <!-- /.row -->
        </div> <!-- /.container -->
    </div> <!-- /.middle-content -->

</section>
<?php

?>
<section>
    <div class="section-padding">
        <div class="container">
            <div class="row">
                <div class="comment-box comment-box_new-css">
                    <h3>Comments</h3>
                    <!---->
                    <?php
                    if ($check_login == 1) {
                        ?>
                        <i class="fa col-md-1 giant-text thumb-img">
                            <img src="<?php echo hz_get_profile_thumb(get_current_user_id()); ?>"
                                 class="wow fadeInUp">
                        </i>
                        <?php
                    } else if ($check_login == 0) {
                        echo '<i class="fa fa-user giant-text col-md-1" aria-hidden="true"></i>';
                    }
                    ?>
                    <form id="contest_discussion" class="col-md-11">
                        <input type="text" name="comment" placeholder="Join the discussion">
                        <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
                        <input type="submit" value="Comment" class="enhanced-text">
                    </form>
                </div>

                <!-- code-notes moved to below the chat button-->
                <div class="comment-box comments-list">
                    <?php

                    $content_id = $content['id'];

                    echo hz_fl_content_discussion_list_public($content_id);

                    ?>

                </div>

            </div> <!-- /.row -->
        </div> <!-- /.container -->
    </div> <!-- /.section-padding -->
</section>


<section class="fl-content-related">
    <div class="section-padding">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 wow fadeInUp">
                    <div class="owl-carousel carousel-area">
                        <?php
                        $upload_dir = wp_upload_dir();
                        foreach ($similar_record_content as $key => $value) {

                            $userdetail = get_userdata($value['user_id']);
                            $country = get_user_meta($value['user_id'], 'user_residence_country', true);
                            $country = ($country ? get_countries()[$country] : 'N/A');

                            if ($value['content_sale_type'] == 'Fixed') {
                                $priceValue = '$' . $value['price'];
                            } else if ($value['content_sale_type'] == 'Offer') {
                                $priceValue = 'Best Offer';
                            } else if ($value['content_sale_type'] == 'Free') {
                                $priceValue = '';
                            } else {
                                $priceValue = '$' . $value['price'] . '/' . $value['content_sale_type'];
                            }
                            //code-notes [image-sizing]  content get small sized image for content cover
                            $bg_image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($value['image'],FreelinguistSizeImages::SMALL,true);

                            $href = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($value['primary_id']);
                            $q =
                                "select content_view from wp_linguist_content where user_id IS NOT NULL AND id=" . $value['primary_id'];
                            $content_view = $wpdb->get_row($q, ARRAY_A);
                            ?>
                            <div class="" style="padding:0;margin-bottom:20px">
                                <div class="user-info" style="width: 100%; display: inline-block;">

                                    <div class="slide-inn">
                                        <span style="position:absolute;"
                                              class="fav add-favourited <?php
                                                echo(in_array($value['primary_id'], explode(',', $favorite_content)) ?
                                                    'favourited' :
                                                    ''); ?>"
                                              data-fav="<?php
                                                echo(in_array($value['primary_id'], explode(',', $favorite_content)) ? '1' : '0'); ?>"
                                              data-id="<?php echo $value['primary_id']; ?>"
                                              data-login="<?php echo(is_user_logged_in() ? '1' : '0'); ?>"
                                        >
                                        </span>

                                        <a href="<?php echo $href; ?>">
                                            <figure>
                                                <img src="<?php echo $bg_image; ?>" alt="freelinguist"
                                                     style="">
                                            </figure>

                                            <div class="description-user">
                                                <span class="eye">
                                                    <img src="<?php echo get_template_directory_uri().'/images/eye-see.png'; ?>"
                                                         alt="freelinguist"/>
                                                </span>
                                                <ul>
                                                    <li class="li-1 enhanced-text">
                                                        <span><?= stripcslashes(substr($value['title'], 0, 25)); ?></span>
                                                    </li>
                                                    <li class="li-22 ">
                                                        <span  class="one-line-no-overflow">
                                                            <?php echo stripslashes_deep(substr($value['description'], 0, 55)); ?>
                                                        </span>
                                                    </li>

                                                    <li class="li-2 enhanced-text">
                                                        <span><?php echo $userdetail->display_name; ?></span>
                                                        <span class="pull-right">
                                                            <?php if ($content_view['content_view'] != 0 && $content_view['content_view'] != '') {
                                                                echo $content_view['content_view'] . ' Views';
                                                            } ?>
                                                        </span>
                                                    </li>

                                                    <li class="li-2 enhanced-text">
                                                        <span><?php echo $country; ?></span>
                                                        <span class="pull-right colored">
                                                            <?php echo $priceValue; ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div> <!-- /.description-user -->
                                        </a>
                                        <span class="fl-content-button-holder ">
                                            <?php

                                            set_query_var( 'b_show_all_offers', 0 );
                                            set_query_var( 'content_id', $value["primary_id"] );
                                            get_template_part('includes/user/contentdetail/contentdetail',
                                            'customer-button-buy');
                                            ?>
                                        </span>
                                        <!-- code-notes above was inserted content buy button-->
                                    </div> <!-- /.slide-inn -->

                                </div> <!-- /.user-info -->
                            </div> <!-- /.(anon) -->
                        <?php } /* end each similar content*/?>
                    </div>  <!-- /.owl-carousal -->
                </div> <!-- /.col.wow -->
            </div> <!-- /.row -->
        </div> <!-- /.container -->
    </div> <!-- /.section-padding -->
    <?php

    ?>

</section>




