<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      update_coupon_info

 * Description: update_coupon_info

 *

 */

add_action('wp_ajax_update_coupon_info', 'update_coupon_info');


function update_coupon_info(){
    /*
   * current-php-code 2020-Oct-14
   * ajax-endpoint  update_coupon_info
   * input-sanitized :coupon
   */
    //task-future-work  FREE_credits are not used, also nothing stopping from creating an endless number of new buyer accounts
    $coupon = FLInput::get('coupon');

    if($coupon){

        $Email_CouponAmount = 50;

        $requested_code = strtolower($coupon);

        // the customer can enter the email of another customer (check the existence of the other customer).  Then both customers will receive $50 FREE credits. The transaction message could be: “Referral bonus.”

        $user = get_userdata(get_current_user_id());

        $user_by_email = get_user_by( 'email', $requested_code );

        if(!empty($user_by_email) && get_current_user_id() != $user_by_email->ID){

            $user_id = $user_by_email->ID;

            $user_meta = get_userdata($user_id);

            $user_roles = $user_meta->roles;

            if(in_array("customer", $user_roles)){

                $referral_user_code = get_user_meta($user_id,'used_coupon');

                $curr_requested_code = $user->user_email;

                if (!in_array($curr_requested_code, $referral_user_code)){

                    add_user_meta( $user_id, 'used_coupon', $user->user_email);

                    $user_free_credit_amount = get_user_meta( $user_id, 'FREE_credits', true);
                    if (empty($user_free_credit_amount)) {$user_free_credit_amount = 0;}

                    $user_free_credit_amount =  $user_free_credit_amount +  $Email_CouponAmount;

                    update_user_meta( $user_id, 'FREE_credits',amount_format($user_free_credit_amount));

                    transaction_updated('Referral Bonus : '.$curr_requested_code,$user_id,$Email_CouponAmount,
                        'Referral Bonus : '.$curr_requested_code,
                        FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS]
                    );

                    // Current user

                    $referral_user_code = get_user_meta(get_current_user_id(),'used_coupon');

                    if (!in_array($requested_code, $referral_user_code)){

                        add_user_meta( get_current_user_id(), 'used_coupon', $requested_code);

                        $user_free_credit_amount = get_user_meta( get_current_user_id(), 'FREE_credits', true);

                        $user_free_credit_amount =  $user_free_credit_amount +  $Email_CouponAmount;

                        update_user_meta( get_current_user_id(), 'FREE_credits',amount_format($user_free_credit_amount));

                        transaction_updated('Referral Bonus : '.$requested_code,get_current_user_id(),
                            $Email_CouponAmount,'Referral Bonus : '.$requested_code,
                            FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS]
                        );

                        echo "success";

                        exit;

                    }

                    echo "success";

                    exit;

                }

            }

        }



        global $wpdb;

        $current_user_usedCoupon = get_user_meta( get_current_user_id(), 'used_coupon');


        if (!in_array($requested_code, $current_user_usedCoupon)){

            $results            = $wpdb->get_row( "SELECT * FROM wp_coupons where coupon_code = '$requested_code'");

            if(!empty($results)){

                add_user_meta( get_current_user_id(), 'used_coupon', $requested_code);

                $CouponAmount = $results->coupon_value;

                $user_free_credit_amount = get_user_meta( get_current_user_id(), 'FREE_credits', true);

                $user_free_credit_amount =  $user_free_credit_amount +  $CouponAmount;

                update_user_meta( get_current_user_id(), 'FREE_credits',amount_format($user_free_credit_amount));

                transaction_updated('Coupon Bonus : '.$coupon,get_current_user_id(),$CouponAmount,
                    'Coupon Bonus : '.$requested_code,
                    FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS]
                );

                echo "success";

                exit;

            }

            echo "not_available";

            exit;

        }else{

            echo "already_used";

            exit;

        }

    }else{

        echo 'failed';

        exit;

    }

}