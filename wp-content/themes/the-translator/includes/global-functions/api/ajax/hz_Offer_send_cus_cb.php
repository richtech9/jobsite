<?php

add_action( 'wp_ajax_hz_Offer_send_cus', 'hz_Offer_send_cus_cb'  );

function hz_Offer_send_cus_cb(){
    //code-bookmark this sends the offer the customer made to the freelancer
    //code-notes the offers and their statuses are stored in the wp_linguist_content table in the column offersBy as php serialized
    global $wpdb;

    /*
     * current-php-code 2020-Oct-14
     * ajax-endpoint  hz_Offer_send_cus
     * input-sanitized : contestId,min_bid,offerShoot
     */

    $contestId = (int)FLInput::get('contestId');
    $offerShoot = FLInput::get('offerShoot');
    $min_bid =  FLInput::get('min_bid');
    $offersArr = [];


    $cusid = get_current_user_id();



    $table = $wpdb->prefix.'linguist_content';

    $sql_statment = /** @lang text */
        "SELECT * FROM $table WHERE id =".$contestId."";
    $offerList = $wpdb->get_results( $sql_statment );



    $allOffers = $offerList[0]->offersBy;
    $unserOffer = maybe_unserialize($allOffers);
    if (empty($unserOffer) ) {
        $unserOffer = [];
    }


    $customer_id_array = array_column($unserOffer, 'cust_id');
    //code-notes code is setting array key of boolean false to update instead of array, when no previous offers
    $key = array_search($cusid, $customer_id_array);

    if($offerList[0]->user_id != $cusid)
    {
        if($offerShoot >=$min_bid)
        {

            if($allOffers == ''){

                $offersArr[] = array( 'cust_id' => $cusid, 'amount' => $offerShoot ,'status'=>'processing',
                    'created_at'=>time());

                $serialized_array = serialize($offersArr);

                /*$update_Offer =*/ $wpdb->update( $table, array( 'offersBy' => $serialized_array ), array( 'id' => $contestId ) );

                echo '<div style="color:green;">Your offer has been sent successfully. The freelancer will either accept or reject the offer soon. You will own the content once the freelancer accepts your offer.</div>';

            }elseif(($allOffers != '') && ($key !== false) && ($key >= 0) && ($unserOffer[$key]['cust_id'] == $cusid)){
                //code-notes check for false above because otherwise false will be cast to 0, then we will be setting $unserOffer[false]
                $unserOffer[$key]['amount'] = $offerShoot;

                $unserOffer[$key]['status'] = 'processing';
                $unserOffer[$key]['created_at'] = time();

                $neserialized_array = serialize($unserOffer);

                /*$update_Offer =*/ $wpdb->update( $table, array( 'offersBy' => $neserialized_array ), array( 'id' => $contestId ) );

                echo '<div style="color:green;">Your offer has been sent successfully. The freelancer will either accept or reject the offer soon. You will own the content once the freelancer accepts your offer.</div>';
            }else{

                $unserOffer[] = array( 'cust_id' => $cusid, 'amount' => $offerShoot ,
                    'status'=>'processing','created_at'=>time());

                $neserialized_array = serialize($unserOffer);

                /*$update_Offer =*/ $wpdb->update( $table, array( 'offersBy' => $neserialized_array ), array( 'id' => $contestId ) );

                echo '<div style="color:green;">Thanks for the offer, we will notify you when freelancer accepts offer. Keep wallet balance more or equal to your offer amount, else offer will not get accepted.</div>';
            }
        }else{
            echo '<div style="color:red;">Minimum bid amount is '.$min_bid.'$.</div>';
        }
    }else{
        echo '<div style="color:red;">You can not place on your own content</div>';
    }

    wp_die();

}