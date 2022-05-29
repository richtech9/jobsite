<?php
/**
 * Called by @see FreelinguistUnitGenerator::generate_template()
 *   the information is passed by the global variable $unit_item_info that it sets before including this
 *   this information is created by one of the sister functions of
 *
 *     @see FreelinguistUnitGenerator::get_top_and_per_user_info()
 *     @see FreelinguistUnitGenerator::get_top_and_per_content_info()
 *
 * The data expected , and how its used, is in the comments below
 *
 * Builds a twig template,
 *
 *   Please note that we are creating the twig template, which itself is creating the html
 *    so we are nesting three languages here: php->twig->html
 *     phpstorm, this version, is confused sometimes by this and will not syntax or error check correctly
 *
 */
    global $unit_item_info;
    //will_send_to_error_log("unit template vars",$unit_item_info,false,true);
    /*
     * Information needed


    job_type 'translator' OR 'content'

    primary_id wp_fl_user_data_lookup.user_id OR wp_linguist_content.id

    href  site_url().'/user-account/?lang=en&user='.wp_user.user_nicename
          OR
          site_url().'/content/?lang=en&mode=view&content_id='.base64_encode(wp_linguist_content.id);

    image wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key = 'user_image' OR  wp_linguist_content.content_cover_image

    eye_image get_template_directory_uri().'/images/eye-see.png

    title comma separated list of tag names OR wp_linguist_content.content_title

    description wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key = 'description' OR  wp_linguist_content.content_summary

    name  wp_user.display_name OR wp_linguist_content.user_id->display_name

    view_or_rating call translater_rating( with wp_fl_user_data_lookup.user_id, wp_fl_user_data_lookup.rating_as_freelancer) OR string  wp_linguist_content.content_view 'View'

    country   get_countries() [] wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key = 'user_residence_country'
              OR
               get_countries() [] wp_linguist_content.user_id->wp_usermeta.meta_key = 'user_residence_country'

    rate_or_price_or_offer
                        wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key = 'user_hourly_rate' and '/hours' if not empty
                        OR
                        if(wp_linguist_content.content_sale_type =='Fixed'){ '%' . wp_linguist_content.content_amount }
                        else if(wp_linguist_content.content_sale_type =='Offer'){ 'Best Offer' }
                        else if(wp_linguist_content.content_sale_type =='Free'){ '$0' }

    mag_image get_template_directory_uri().'/images/mag.png

    purchase_action 'buy'

     */
?>
<li class="">
    <div class="user-info" style="width: 100%; display: inline-block;border:1px solid #ddd;padding:0px;">
        <div class="slide-inn">
            {% if ((user_logged_in is defined) and (user_logged_in))  %}

                <!-- template condition loggged in and doing favorite-->
                <span style="position:absolute;"
                      class="fav add-favourited

                    <?php if(  ($unit_item_info["job_type"] === "content")) {?>
                    {% if <?= $unit_item_info["primary_id"]?> in favorite_content_array %}
                        favourited
                    {% endif %}
                    <?php }else if(  ($unit_item_info["job_type"] === "translator")) {  ?>
                    {% if <?= $unit_item_info["primary_id"]?> in favorite_users_array %}
                        favourited
                    {% endif %}
                    <?php } //end if block in favorites?>
                    "
                    <?php if(  ($unit_item_info["job_type"] === "content")) {?>
                        {% if <?= $unit_item_info["primary_id"]?> in favorite_content_array %}
                        data-fav="1"
                        {% endif %}
                    <?php }else if(  ($unit_item_info["job_type"] === "translator")) {  ?>
                        {% if <?= $unit_item_info["primary_id"]?> in favorite_users_array %}
                        data-fav="0"
                        {% endif %}
                    <?php  } // end of if job_type is content or user for favorites ?>

                      data-c_type="<?= $unit_item_info["job_type"]?>"
                      data-id="<?= $unit_item_info["primary_id"]?>">

                </span>
                <!-- end template condition loggged in and is_favorite-->
            {% endif %}


            <a href="<?= $unit_item_info["href"]?>">

                <figure    data-debug_id="<?= $unit_item_info["primary_id"]?>"
                           data-debug_type="<?= $unit_item_info["job_type"]?>"   >
                    <img src="<?= $unit_item_info["image"]?>" alt="freelinguist" >
                </figure>

                <div class="col-md-12 description-user">

                    <span class="eye">
                        <img src="<?= $unit_item_info["eye_image"]?>" alt="freelinguist" />
                    </span>

                    <ul>

                        <li class="li-1  enhanced-text " >
                            <span class="one-line-no-overflow"><?= substr($unit_item_info["title"], 0, 25)?></span>
                        </li>

                        <li class="li-22 one-line-no-overflow">
                            <span class="one-line-no-overflow"><?= substr($unit_item_info["description"], 0, 55)?></span>
                        </li>

                        <li class="li-2 enhanced-text">
                            <span><?= substr($unit_item_info["name"], 0, 16)?></span>
                            <span class="pull-right"><?= $unit_item_info["view_or_rating"]?></span>
                        </li>

                        <li class="li-2 enhanced-text">
                            <span><?= $unit_item_info["country"]?></span>
                            <span class="pull-right colored"><?= $unit_item_info["rate_or_price_or_offer"]?></span>
                        </li>

                    </ul>
                </div>
            </a>

        </div>
    </div>
    <?php if ($unit_item_info["job_type"] === "translator") { ?>
        <div class="hire-freelancer-button-holder enhanced-text">
            <button class="red-btn-no-hover red-background-white-text hire-freelancer"
                    data-freelancer_nicename="<?= $unit_item_info["nicename"]  ?>"
                    data-freelancer_id="<?= $unit_item_info["primary_id"] ?>"
            >
                Hire
            </button>
        </div>
    <?php } //end if search item is translator ?>
    <?php if(  ($unit_item_info["job_type"] === "content")) { ?>
        <span class="fl-content-button-holder enhanced-text">
        <?php
            //code-notes making the button
            set_query_var( 'b_show_all_offers', 0 );
            set_query_var( 'content_id', $unit_item_info["primary_id"] );
            set_query_var( 'customer_id', -1 ); //customer id is ignored in twig anyway
            set_query_var( 'b_output_twig', 1 );
            get_template_part('includes/user/contentdetail/contentdetail',
                'customer-button-buy');
        } // end of if job_type is content
        ?>
        </span>
    {% if ((display_admin_info is defined) and display_admin_info ) %}
        <div class="fl-admin-unit">

            <span class="fl-admin-unit-part fl-admin-unit-tag" >
                <span class="fl-admin-unit-value ">{{ unit_tag }}</span>
            </span>

            <span class="fl-admin-unit-part fl-admin-unit-source" >
                <span class="fl-admin-unit-value ">{{ unit_source }}</span>
            </span>

            <span class="fl-admin-unit-part fl-admin-unit-type" >
                <span class="fl-admin-unit-value ">{{ unit_type }}</span>
            </span>

            <span class="fl-admin-unit-part fl-admin-unit-id" >
                <span class="fl-admin-unit-value ">ID {{ unit_id }}</span>
            </span>

            <span class="fl-admin-unit-part fl-admin-unit-pk" >
                <span class="fl-admin-unit-value ">PK {{ unit_pk }}</span>
            </span>

            <span class="fl-admin-unit-part fl-admin-unit-created" >
                <span class="fl-admin-unit-value a-timestamp-full-date-time " data-ts="{{ unit_ts }}"></span>
            </span>

            {% if ((user_logged_in is defined) and user_logged_in ) %}
                <span class="fl-admin-unit-part fl-admin-logged-in-email" >
                    <span class="fl-admin-unit-value ">Logged in as {{ logged_in_user_email }}</span>
                </span>

                <?php if(  ($unit_item_info["job_type"] === "content")) {?>
                    {% if <?= $unit_item_info["primary_id"]?> in favorite_content_array %}
                     <span class="fl-admin-unit-part fl-admin-is-favorite" >
                        <span class="fl-admin-unit-value ">Favorite Content!</span>
                     </span>
                    {% endif %} {# end if content in favorites #}
                <?php }else if(  ($unit_item_info["job_type"] === "translator")) {  ?>
                    {% if <?= $unit_item_info["primary_id"]?> in favorite_users_array %}
                     <span class="fl-admin-unit-part fl-admin-is-favorite" >
                        <span class="fl-admin-unit-value ">Favorite User!</span>
                     </span>
                    {% endif %} {# end if user in favorites #}
                <?php  } // end of if job_type is content or user for favorites ?>


                <?php if(  ($unit_item_info["job_type"] === "content")) {?>
                    {% if ( (<?= $unit_item_info["primary_id"]?>  in purchase_content_array)) %}
                         <span class="fl-admin-unit-part fl-admin-user-purchased " >
                            <span class="fl-admin-unit-value ">Purchased</span>
                         </span>
                    {% endif %}  {# end if for purchased or not #}
                <?php  } // end of purchase check ?>
            {% endif %} {# end if logged in #}


        </div>
    {% endif %}{# end if admin content #}
</li>