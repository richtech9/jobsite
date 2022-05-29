<?php


/**
 * prints out html for a page of content
 * @param int $content_page
 * @param int $content_limit , if empty will use default size
 * @param bool $b_throw_exceptions, throw sql exceptions if true
 * @return int (the number of units created in the html)
 */
function freelinguist_print_content_units($content_page, $content_limit = null,$b_throw_exceptions = false)
{
    /*
     * current-php-code 2020-Oct-13
     * internal-call
     * input-sanitized :
     */
    $count_units_generated = 0;
    ?>
    <div class="row">
        <?php
        global $wpdb;
        if (empty($content_limit)) {
            $content_limit = (int)get_option('fl_page_limit_content_mall', 80);
        }

        $content_offset = ($content_page - 1) * $content_limit;
        $sql_statement =
            "SELECT wlc.id,wlc.user_id,wlc.content_view,wlc.show_content show_content,
                          wlc.content_cover_image,wlc.content_summary,wlc.description_image,
                          wlc.content_title,wlc.content_amount,wlc.content_sale_type,
                          u.user_nicename,u.display_name
                  FROM wp_linguist_content wlc
                  LEFT JOIN wp_users u ON wlc.user_id=u.ID
                  WHERE (wlc.publish_type='Publish' OR wlc.publish_type = 'Purchased') AND
                    wlc.show_content=1 AND wlc.parent_content_id IS NULL AND wlc.user_id IS NOT NULL
                  ORDER BY wlc.id DESC
                  LIMIT $content_offset,$content_limit
                  ";//code-notes added limit

        $linguist_contents_list = $wpdb->get_results($sql_statement, ARRAY_A);
//        will_send_to_error_log('dat sql for content', $wpdb->last_query);
        if ($b_throw_exceptions) { will_throw_on_wpdb_error($wpdb);}
        $current_user_id = get_current_user_id();
        $favContentIds = get_user_meta($current_user_id, '_favorite_content', true);
        foreach ($linguist_contents_list as $value) {

            //code-notes [image-sizing]  content size small
            $image =  FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
                $value['content_cover_image'],FreelinguistSizeImages::SMALL,true);

            $country = get_user_meta($value['user_id'], 'user_residence_country', true);
            $country = ($country ? get_countries()[$country] : 'N/A');
            if ($value['content_sale_type'] == 'Fixed') {
                $priceValue = '$' . $value['content_amount'];
            } else if ($value['content_sale_type'] == 'Offer') {
                $priceValue = 'Best Offer';
            } else if ($value['content_sale_type'] == 'Free') {
                $priceValue = '';
            } else {
                $priceValue = '$' . $value['content_amount'] . '/' . $value['content_sale_type'];
            }
            $href = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($value['id']);
            $count_units_generated ++;
            ?>
            <div class="col-md-3 free-linguist-content-unit" style="padding:0;margin-bottom:20px">
                <div class="user-info freelinguist-double-title-unit" style="width: 100%; display: inline-block;">

                    <div class="slide-inn">
                        <span style="position:absolute;"
                              class="fav add-favourited
                              <?= in_array($value['id'], explode(',', $favContentIds)) ? 'favourited' : ''; ?>"

                              data-fav="<?= in_array($value['id'], explode(',', $favContentIds)) ? '1' : '0'; ?>"
                              data-id="<?= $value['id']; ?>"
                              data-login="<?= (is_user_logged_in() ? '1' : '0'); ?>"></span>

                        <a href="<?= $href; ?>">
                            <figure>
                                <img src="<?= $image; ?>" alt="freelinguist" style="width:100%;">
                            </figure>

                            <div class="description-user">

                                <span class="eye">
                                    <!--suppress HtmlUnknownTarget -->
                                    <img src="<?= get_template_directory_uri(); ?>/images/eye-see.png"
                                         alt="freelinguist"/>
                                </span>

                                <ul>
                                    <li class="li-1 enhanced-text">
                                        <span><?= stripslashes_deep(substr($value['content_title'], 0, 25)); ?></span>
                                    </li>

                                    <li class="li-22 freelinguist-title">
                                        <?= stripslashes_deep(substr($value['content_summary'], 0, 55)); ?>
                                    </li>

                                    <li class="li-2 enhanced-text">
                                        <span><?= substr($value['display_name'], 0, 10); ?></span> <span
                                                class="pull-right"><?php if ($value['content_view'] != 0 && $value['content_view'] != '') {
                                                echo $value['content_view'] . ' Views';
                                            } ?></span>
                                    </li>
                                    <li class="li-2 enhanced-text"><span><?= $country; ?></span> <span
                                                class="pull-right colored"><?= $priceValue; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </a>
                    </div> <!-- /.slide-in -->

                </div> <!-- /.user-info -->
            </div> <!-- /.free-linguist-content-unit -->
        <?php } ?>
    </div> <!-- /.row -->
    <?php //end of function
    return $count_units_generated;
} // end freelinguist_print_content_units function