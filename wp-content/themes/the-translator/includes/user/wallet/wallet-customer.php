<?php
/*
    * current-php-code 2020-Oct-15
    * input-sanitized : lang,redirect_to,type
    * current-wp-template:  for customer wallet
*/

$lang = FLInput::get('lang', 'en');




global $wpdb;
?>
<style>

    /*noinspection CssUnusedSymbol*/
    .modal-backdrop.in {
        opacity: 0.7 !important;
    }
</style>
<?php
if (get_user_meta(get_current_user_id(), 'total_user_balance', true)) {

} else {
    update_user_meta(get_current_user_id(), 'total_user_balance', 0);
}



get_template_part('includes/user/wallet/wallet', 'wallet-payment-success-modal');


?>




<section class="middle-content wallet-content">
    <div class="container" id="container-body">
        <div class="row wallet-head">
            <div class="col-md-6">
                <i class="icon icon-wallet"></i>
                <span class="bold-and-blocking large-text"><?php get_custom_string("Wallet"); ?></span>
            </div>
        </div>
        <!-- START: Refill credit form -->
        <?php
        if (get_user_meta(get_current_user_id(), 'total_user_balance', true) < 0) {
            get_template_part('includes/user/wallet/wallet', 'refill-modal');
        } /* end if wallet balance is negative*/
        else {
            if (get_user_meta(get_current_user_id(), 'total_user_balance', true) >= 0) {
                get_template_part('includes/user/wallet/wallet', 'refill-static');
            } /* end if wallet balance is negative*/
        }
        ?>

        <!-- END: Refill credit form -->

        <div class="wallet-history">

            <?php get_template_part('includes/user/wallet/wallet', 'last-history'); ?>

            <!-- START: Request for withdrawal -->
            <?php get_template_part('includes/user/wallet/wallet', 'request-widthdraw'); ?>
            <!-- END: Request for withdrawal -->

            <!-- START: FREE credits -->
            <div class="wallet-wraper">
                <div class="clear">
                    <h3>
                        <?php get_custom_string("Your FREE credits"); ?>
                        : <?php echo amount_format(get_user_meta(get_current_user_id(), 'FREE_credits', true)); ?>
                        USD
                    </h3>
                </div>
                <h5><?php get_custom_string("Activities related to FREE Credits"); ?></h5>
                <?php
                $free_credit_url = add_query_arg(  ['type'=>'free_credit','lang'=>$lang],
                    freeling_links('wallet_url'));
                ?>
                <a href="<?= $free_credit_url ?>" class="view-all">
                    <?php get_custom_string("See All"); ?>
                </a>

                <?php

                set_query_var( 'wallet_history_transaction_types', [
                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_REFUND,
                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS,
                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_USED,
                ] );

                set_query_var( 'wallet_history_post_status', [
                    FLTransactionLookup::POST_STATUS_PUBLISH
                ] );

                set_query_var( 'wallet_history_user_id', get_current_user_id() );
                set_query_var( 'wallet_history_sort', 'by_date_desc' );
                set_query_var( 'wallet_history_page_number', 1 );
                set_query_var( 'wallet_history_page_size', 5 );

                get_template_part('includes/user/wallet/wallet', 'history-table');


                ?>

            </div> <!-- /.wallet-wrapper-->
            <!-- END: FREE credits -->

            <!-- START: coupon Form -->
            <div class="request-coupon wallet-wraper">
                <div class="row">
                    <div id="couponErrors_message" class="couponErrors_message alert"></div>
                    <form class="couponForm" id="couponForm" method="post"
                          action='<?php echo freeling_links('wallet_url'); ?>' novalidate="novalidate">
                        <div class="col-md-12 refill-credit">
                            <div class="coupon-box" style="width:25%">
                                <div class="form-group">
                                    <input type="text" Placeholder="<?php get_custom_string('Enter Promo Code'); ?>"
                                           name="coupon" class="form-control" maxlength="1000">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <input type="submit" class="btn blue-btn pay-coupon" name="submit"
                                   value="<?php get_custom_string('Submit'); ?>">
                        </div>
                    </form>
                </div> <!-- /.row -->
            </div> <!-- /.request-coupon-->
        </div> <!-- /.wallet-history-->
    </div> <!-- /.container-->
</section> <!-- /.wallet-conent-->









