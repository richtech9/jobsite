<?php

/*
* current-php-code 2020-Nov-12
* input-sanitized : content_id, lang
* current-wp-template:  purchase button for content for customer view
*/

/**
 * @usage
 *  //(optional to show all offers button too)
 *  set_query_var( 'b_show_all_offers', 1 );
 *     // if missing, or 0,  will not show the extra offers button
 *
 *  //(optional to set content id outside of http query )
 *  set_query_var( 'content_id', 1 );
 *     // if missing or 0 will get the content_id from get or post, and expect it to maybe be encoded
 *     // set to un-encoded id if done by query_var
 *
 * //(optional to set customer id outside of login)
 *  set_query_var( 'customer_id', 1 );
 *     // if missing or 0 will get the customer_id from the login
 *      // if set to -1 will set 0 regardless of login
 *     // if outputting twig will make a twig condition based on the future customer id, and this is ignored
 *
 *  //(optional to print out twig instead of html)
 *   set_query_var( 'b_output_twig', 1 );
 *      // if missing or 0 will not output twig
 *      // if outputting twig, in addition to making conditions for the customer_id, will also make conditions if they bought this
 *
 * get_template_part('includes/user/contentdetail/contentdetail', 'customer-button-buy');
 *
 * @needs wp_linguist_content info :
 *  id,content_amount, user_id,content_sale_type, purchased_by, publish_type, number_copies_sold, max_to_be_sold
 */

$lang = FLInput::get('lang','en');


if (isset($content_id)) {
    $content_id = (int)$content_id;
} else {
    $content_id = 0;
}

if ($content_id === 0) {
    $content_id_encoded = FLInput::get('content_id', 0);
    $content_id =  FreelinguistContentHelper::decode_id($content_id_encoded);
}


if (isset($customer_id)) {
    $customer_id = (int)$customer_id;
} else {
    $customer_id = 0;
}

if ($customer_id === 0) {
    $customer_id = get_current_user_id();
}

if ($customer_id < 0) {
    $customer_id = 0;
}



if (isset($b_show_all_offers)) {
    $b_show_all_offers = (int)$b_show_all_offers;
} else {
    $b_show_all_offers = 0;
}



if (isset($b_output_twig)) {
    $b_output_twig = (int)$b_output_twig;
} else {
    $b_output_twig = 0;
}


/*
 * {% if ((user_logged_in is defined) and user_logged_in and (not (<?= $unit_item_info["primary_id"]?>  in purchase_content_array))) %}
 *
 * {% endif %}
 *
 * redirect_to= content cover page or linguist page
 */




if (!$content_id) {return;}

$content = FreelinguistContentHelper::get_content_extended_information($content_id);
$content_author_detail = get_userdata($content['user_id']);

//will_dump('cc',$content);

$is_bought_by_this_user = 0;
$offers_made_by_these_user_ids = [];
$price_array = [];

//get a list of all the offers
$all_offers_by_raw = $content['offersBy'];
$offers_by = maybe_unserialize($all_offers_by_raw);
if (empty($offers_by) ) {
    $offers_by = [];
}

foreach ($offers_by as $offer) {
    $offer_user_id = 0;
    if (isset($offer['cust_id'])) {
        $offer_user_id = (int)$offer['cust_id'];
    }
    if ($offer_user_id) {
        $offers_made_by_these_user_ids[] = $offer_user_id;
    }

    $offer_price = 0;
    if (isset($offer['amount'])) {
        $offer_price = (float)$offer['amount'];
    }
    $price_array[] = $offer_price;
}

$content_link = site_url()."/content/?lang=$lang&mode=view&content_id=".FreelinguistContentHelper::encode_id($content_id);


if (!$b_output_twig) {

    $row = $wpdb->get_row("
              select * from wp_linguist_content 
              where id = $content_id and publish_type='Purchased' and purchased_by=$customer_id
              ", ARRAY_A);

    if (is_array($row) && !empty($row)) {
        $is_bought_by_this_user = 1;
    }
}

?>


<?php

if ($content['content_sale_type'] == 'Offer') {

    if ($b_output_twig) {
        //code-notes do twig for offer type

        /*
        * Template logic for offers
        *      PHP: IF OWNED
        *      TWIG: IF user logged in and owns DO offer accepted
        *      TWIG: ELSE IF user logged in and in bids DO offer rejected
         *      TWIG: ELSE DO sold
        *      PHP ELSE
        *      TWIG: DO bid
        */
        ?>

        {% set offers_made_by_these_user_ids = [<?= implode(',',$offers_made_by_these_user_ids)?>] %}

        <?php if (intval($content['purchased_by'])) { ?>

            {% if (user_logged_in) and (<?= $content_id ?> in purchase_content_array) %}
                <!-- offer accepted -->

                <button class="fl-buy-button red-btn-no-hover red-background-white-text fl-bought-content"
                   data-cid="<?= $content_id ?>"
                >
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    <!-- owns this-->
                    Offer accepted
                </button>

            {% elseif (user_logged_in) and (logged_in_user_id in offers_made_by_these_user_ids) %}
                <!-- offer rejected -->

                <button class="fl-buy-button red-btn-no-hover red-background-white-text ">
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    Offer rejected
                </button>

            {% else %}
                <!-- sold -->

                <button data-cid="<?= $content_id ?>"
                   class="fl-buy-button red-btn-no-hover red-background-white-text
                        fl-content-purchased-by-other fl-content-won-by-other"
                >
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    <!-- does not own this-->
                    SOLD
                </button>

            {% endif %}
         <?php } else {  ?>
            <!-- bid -->

            <button
               class="fl-buy-button red-btn-no-hover red-background-white-text
                    fl-content-action-make-offer  regular-text"
               data-content_id="<?= $content_id ?>"
               data-min_bid="<?= floatval($content['content_amount']) ? floatval($content['content_amount']) : 100 ?>"
               data-href = "<?=$content_link?>"
            >

                <i class="fa fa-shopping-cart enhanced-text"></i>
                Make Offer
            </button>

            <?php if ($b_show_all_offers) {
                ?>

                <button class="fl-buy-button red-btn-no-hover red-background-white-text fl-content-action-view-offers"
                        data-content_id="<?= $content['id'] ?>"
                        data-href = "<?=$content_link?>"
                >
                    View All Offers
                </button>

                <?php
            } //end if show all offers

         } //end bid section for template

    } //end twig output for offer content
    else
    { //start html output for offer content

        /*
         * Logic :
         *  IF purchased by this user DO owned
         *  ELSE IF purchased, DO  sold  (no not look at logged in)
         *  ELSE DO bid button (and maybe view bids button)
         *
         */

        if ($is_bought_by_this_user) {
            //show the owned
            ?>
            <button class="fl-buy-button red-btn-no-hover red-background-white-text  fl-bought-content"
               data-cid="<?= $content_id ?>"
            >
                <i class="fa fa-shopping-cart enhanced-text"></i>
                <!-- owns this-->
                Offer accepted
            </button>

            <?php
        } // end if the user bought the content
        elseif (intval($content['purchased_by'])) { //start somebody bought this

            if ($customer_id && in_array($customer_id,$offers_made_by_these_user_ids)) {
                //start to show the offer rejected
                ?>

                <button class="fl-buy-button red-btn-no-hover red-background-white-text ">
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    Offer rejected
                </button>

                <?php

            } //end if offer rejected
            else { //start the sold button
                ?>
                <button data-cid="<?= $content_id ?>"
                   class="fl-buy-button red-btn-no-hover red-background-white-text
                        fl-content-purchased-by-other fl-content-won-by-other"
                >
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    <!-- does not own this-->
                    SOLD
                </button>

                <?php
            } //end showing the sold button

        } //end somebody bought this
        else { //start bidding
             ?>

                <button
                   class="fl-buy-button red-btn-no-hover red-background-white-text
                        fl-content-action-make-offer regular-text"
                    data-content_id="<?= $content_id ?>"
                    data-min_bid="<?= floatval($content['content_amount']) ? floatval($content['content_amount']) : 100 ?>"
                    data-href = "<?=$content_link?>"
                >

                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    Make Offer
                </button>

                <?php if ($b_show_all_offers) {
                    ?>

                    <button class="fl-buy-button red-btn-no-hover red-background-white-text fl-content-action-view-offers"
                        data-content_id="<?= $content['id'] ?>"
                        data-href = "<?=$content_link?>"
                    >
                        View All Offers
                    </button>

                    <?php
                } //end if show all offers
        } //end bidding

    } //end html output for offer content

} //end if offer

else { //start if fixed or free

    if ($b_output_twig) {
        //code-notes make twig to show buy button
            /*
            *  logged_in_user_id has the id
            *  purchase_content_array  array of integers for content id this user has purchased
            * user_logged_in and (<?= $content_id ?> in purchase_content_array) =>
            * user_logged_in and (not(<?= $content_id ?> in purchase_content_array)) ==>
            * (not user_logged_in) =>
             *
             * template logic
             *
             *      PHP: IF MAX OWNED COPIES
             *      TWIG: IF user logged in and owns DO owned
             *      TWIG: ELSE DO sold
             *      PHP ELSE
             *      TWIG: IF user logged in and owns DO owned
             *      TWIG: ELSE DO buy
             *
            */

        if ($content['number_copies_sold'] >= $content['max_to_be_sold']) {

            $button_words = 'Sold Out';
            $class_to_add = 'fl-content-sold-out';
            if (intval($content['max_to_be_sold']) === 1) {
                $button_words = 'Sold';
                $class_to_add = 'fl-content-purchased-by-other';
            }

            ?>
            {% if (user_logged_in) and (<?= $content_id ?> in purchase_content_array) %}
                <button class="fl-buy-button red-btn-no-hover red-background-white-text  fl-bought-content fl-disguise-button"
                   data-cid="<?= $content_id ?>"
                >
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    <!-- owns this-->
                    Owned
                </button>
            {% else %}
                 <!-- Do SOLD -->

                <button data-cid="<?= $content_id ?>"
                   class="fl-buy-button red-btn-no-hover red-background-white-text <?= $class_to_add ?>"
                >
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    <!-- does not own this-->
                    <?= $button_words ?>
                </button>

            {% endif %}
            <?php
        } //end if max already sold
        else { //start max not sold yet
            ?>
            {% if (user_logged_in) and (<?= $content_id ?> in purchase_content_array) %}
                <button class="fl-buy-button red-btn-no-hover red-background-white-text  fl-bought-content fl-disguise-button"
                   data-cid="<?= $content_id ?>"
                >
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    <!-- owns this-->
                    Owned
                </button>
            {% else %}
                <!-- Do Buy -->
                <!-- code-bookmark where the direct content buy button is-->
                <button
                        class="fl-buy-button red-btn-no-hover red-background-white-text fl-content-action-buy"
                        data-content_id="<?= $content_id ?>"
                        data-author="<?= $content_author_detail->display_name ?>"
                        data-fee_amount="<?= amount_format(getReferralProcessingCharges($content['content_amount'])) ?>"
                        data-total_amount="<?=  amountWithReferralProcessingFee($content['content_amount']) ?>"
                        data-content_amount="<?= amount_format($content['content_amount']) ?>"
                        data-content_title="<?= $content['content_title'] ?>"
                        data-href = "<?=$content_link?>"
                >
                    <i class="fa fa-shopping-cart enhanced-text"></i>
                    Buy
                </button>
            {% endif %}
            <?php
        }//end if max not sold yet

    } //end twig output
    else { //start html output

        /*
         * Refactor because probably has an error
         *
         *      IF user logged in and owns DO owned
         *      ELSE IF all the copies sold, DO the sold out (no not look at logged in)
         *      ELSE DO the buy button (no not look at logged in)
         */
        if ($is_bought_by_this_user) {
            //show the owned
            ?>
            <button class="fl-buy-button red-btn-no-hover red-background-white-text  fl-bought-content fl-disguise-button"
               data-cid="<?= $content_id ?>"
            >
                <i class="fa fa-shopping-cart enhanced-text"></i>
                <!-- owns this-->
                Owned
            </button>

            <?php
        } // end if the user bought the content
        elseif ($content['number_copies_sold'] >= $content['max_to_be_sold']) {
            //show the sold out
            $button_words = 'Sold Out';
            $class_to_add = 'fl-content-sold-out';
            if (intval($content['max_to_be_sold']) === 1) {
                $button_words = 'Sold';
                $class_to_add = 'fl-content-purchased-by-other';
            }
            ?>
            <button data-cid="<?= $content_id ?>"
               class="fl-buy-button red-btn-no-hover red-background-white-text <?= $class_to_add ?>"
            >
                <i class="fa fa-shopping-cart enhanced-text"></i>
                <!-- does not own this-->
                <?= $button_words ?>
            </button>

            <?php
        } // end if sold out
        else {
            //show the buy button for set price
            ?>
            <!-- code-bookmark where the direct content buy button is-->
            <button
               class="fl-buy-button red-btn-no-hover red-background-white-text fl-content-action-buy"
               data-content_id="<?= $content_id ?>"
               data-author="<?= $content_author_detail->display_name ?>"
               data-fee_amount="<?= amount_format(getReferralProcessingCharges($content['content_amount'])) ?>"
               data-total_amount="<?=  amountWithReferralProcessingFee($content['content_amount']) ?>"
               data-content_amount="<?= amount_format($content['content_amount']) ?>"
               data-content_title="<?= $content['content_title'] ?>"
               data-href = "<?=$content_link?>"
            >
                <i class="fa fa-shopping-cart enhanced-text"></i>
                Buy
            </button>

            <?php
        } // end if can buy
    } //end html output

}//end else not an offer

