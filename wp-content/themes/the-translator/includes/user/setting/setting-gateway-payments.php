<?php
/*
* current-php-code 2020-Feb-08
* input-sanitized :
* current-wp-template:  payment settings for both customer and freelancer
*/
$enabled_payment_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_PAYMENT_GATEWAYS,FLPaymentGateways::DEFAULT_PAYMENT_GATEWAY_NAMES_ARRAY);
$enabled_withdraw_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_WITHDRAW_GATEWAYS,FLPaymentGateways::DEFAULT_WITHDRAW_GATEWAY_NAMES_ARRAY);

$default_payment_method = get_user_meta( get_current_user_id(), FLPaymentGateways::OPTION_NAME_DEFAULT_PAYMENT_METHOD, true );
if (empty($default_payment_method)) {$default_payment_method = '';}
if (!in_array($default_payment_method,$enabled_payment_gateways_array_of_names)) {$default_payment_method = '';}
?>

<section class="payment_prefrence setting-sec">
    <h4><?php get_custom_string('Payment Preference',current_language()); ?></h4>
    <div class="contnet-box">
        <div id="form_success_message_user_payment_pref"></div>
        <form class="setting-form" method="post" name="payment_preference_form" id="payment_preference_form"
              action="<?php echo freeling_links('setting_page_url'); ?>"
        >
            <div class="box-inner">
                <p><?php get_custom_string('Select a default payment Method',current_language()); ?>:</p>

                <?php if (in_array(FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD,$enabled_payment_gateways_array_of_names)) { ?>
                <div class="radio-box-sec">
                    <label class="large-text">
                        <input type="radio" <?php echo ($default_payment_method == FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD) ? 'checked' : ''; ?>
                                id="payment_notify" name="payment_notify" value="<?= FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD ?>">
                        <?php get_custom_string('Credit Card',current_language()); ?>
                    </label>
                </div>
                <?php } ?>

                <?php if (in_array(FLPaymentGateways::GATEWAY_PAYPAL,$enabled_payment_gateways_array_of_names)) { ?>
                <div class="radio-box-sec">
                    <label class="large-text">
                        <input type="radio" <?php echo ($default_payment_method == 'paypal') ? 'checked' : ''; ?>
                                id="payment_notify" name="payment_notify" value="<?= FLPaymentGateways::GATEWAY_PAYPAL ?>">
                        <?php get_custom_string('Paypal',current_language()); ?>
                    </label>
                </div>
                <?php } ?>

                <?php if (in_array(FLPaymentGateways::GATEWAY_ALIPAY,$enabled_payment_gateways_array_of_names)) { ?>
                <div class="radio-box-sec">
                    <label class="large-text">
                        <input type="radio" <?php echo ($default_payment_method == 'alipay') ? 'checked' : ''; ?>
                                id="payment_notify" name="payment_notify" value="<?= FLPaymentGateways::GATEWAY_ALIPAY ?>">
                        <?php get_custom_string('AliPay',current_language()); ?>
                    </label>
                </div>
                <?php } ?>

                <label style=" float: left; width: 100%;" id="payment_notify-error" class="error large-text" for="payment_notify"></label>
            </div> <!-- /.box-inner -->

            <?php if (count($enabled_payment_gateways_array_of_names)) { ?>
            <input type="submit" name="payment_preference_submit" id="payment_preference_submit"
                    value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
            <?php } else { ?>
                <h3>No Payment Gateways Set</h3>
            <?php }?>
        </form>


        <div class="box-inner update-card">
            <div id="form_success_message_user_update_account"></div>
            <form class="setting-form" method="post" name="accountForm" id="accountForm"
                   action="<?php echo freeling_links('setting_page_url'); ?>" novalidate="novalidate"
            >
                <?php if (in_array(FLPaymentGateways::GATEWAY_PAYPAL,$enabled_withdraw_gateways_array_of_names)) { ?>
                <div class="form-group">
                    <label class="large-text"><?php get_custom_string('Paypal Account',current_language()); ?></label>
                    <input title="Paypal Account" type="text"
                            value="<?php echo get_user_meta(get_current_user_id(),'paypal_account',true); ?>"
                            name="paypal_account" id="paypal_account" class="form-control valid" maxlength="20"
                            aria-required="true" aria-invalid="false">
                </div>
                <?php } ?>

                <?php if (in_array(FLPaymentGateways::GATEWAY_ALIPAY,$enabled_withdraw_gateways_array_of_names)) { ?>
                <div class="form-group">
                    <label class="large-text"><?php get_custom_string('Alipay Account',current_language()); ?></label>
                    <input title="AliPay Account"
                            value="<?php echo get_user_meta(get_current_user_id(),'alipay_account',true); ?>"
                            name="alipay_account" id="alipay_account" type="text" class="form-control" maxlength="20">
                </div>
                <?php } ?>

                <?php if (count($enabled_withdraw_gateways_array_of_names)) { ?>
                <input name="submit_account" id="submit_account" type="submit"
                        value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
                <?php } else { ?>
                    <h3>No Withdraw Gateways Set</h3>
                <?php }?>
            </form>
        </div> <!-- /.box-inner -->
    </div> <!-- /.contet-box -->
</section> <!-- /.payment_prefrence -->