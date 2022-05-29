<?php
/*
* current-php-code 2020-Feb-08
* input-sanitized :
* current-wp-template:  withdraw settings for  freelancer
*/
$default_withdraw_method = get_user_meta( get_current_user_id(), FLPaymentGateways::OPTION_NAME_DEFAULT_WITHDRAW_METHOD, true );
if (empty($default_withdraw_method)) {$default_withdraw_method = '';}
$enabled_withdraw_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_WITHDRAW_GATEWAYS,FLPaymentGateways::DEFAULT_WITHDRAW_GATEWAY_NAMES_ARRAY);
if (!in_array($default_withdraw_method,$enabled_withdraw_gateways_array_of_names)) {$default_withdraw_method = '';}


?>

<section class="payment_prefrence setting-sec">
    <h4><?php get_custom_string('Withdrawal Preference',current_language()); ?></h4>
    <div class="contnet-box">
        <div id="form_success_message_user_withdraw_pref"></div>
        <form class="setting-form" method="post" name="withdraw_preference_form" id="withdraw_preference_form"
               action="<?php echo freeling_links('setting_page_url'); ?>"
        >
            <div class="box-inner">
                <p><?php get_custom_string('Select a default payment Method',current_language()); ?>:</p>

                <?php if (in_array(FLPaymentGateways::GATEWAY_PAYPAL,$enabled_withdraw_gateways_array_of_names)) { ?>
                <div class="radio-box-sec">
                    <label class="large-text">

                        <input <?php echo ($default_withdraw_method == FLPaymentGateways::GATEWAY_PAYPAL) ? 'checked' : ''; ?> type="radio"
                                name="withdraw_pref" value="<?= FLPaymentGateways::GATEWAY_PAYPAL ?>">

                        <?php get_custom_string('Paypal',current_language()); ?>
                    </label>
                </div>
                <?php } ?>

                <?php if (in_array(FLPaymentGateways::GATEWAY_ALIPAY,$enabled_withdraw_gateways_array_of_names)) { ?>
                <div class="radio-box-sec">
                    <label class="large-text">
                        <input <?php echo ($default_withdraw_method == FLPaymentGateways::GATEWAY_ALIPAY) ? 'checked' : ''; ?>
                                type="radio"  name="withdraw_pref" value="<?= FLPaymentGateways::GATEWAY_ALIPAY ?>">
                        <?php get_custom_string('AliPay',current_language()); ?>
                    </label>
                </div>
                <?php } ?>



                <label  id="withdraw_pref-error" class="error large-text" for="withdraw_pref"></label>
            </div> <!-- -/.box-inner -->
            <?php if (count($enabled_withdraw_gateways_array_of_names)) { ?>
            <input type="submit" value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
            <?php } else { ?>
                <h3>No Withdraw Gateways Set</h3>
            <?php }?>
        </form>
    </div> <!-- /.contnet-box-->
</section> <!-- /.payment_prefrence-->