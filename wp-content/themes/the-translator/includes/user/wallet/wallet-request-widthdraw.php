<?php
/*
    * current-php-code 2020-Feb-25
    * input-sanitized :
    * current-wp-template:  for translator wallet
*/
//show on the wallet customer and freelancer pages, the form to ask for a withdraw. Will be modified later for syncing with paypal and stripe and others
$default_method = get_user_meta(get_current_user_id(), 'default_payment_method', true);
?>
<div class="wallet-wraper">
    <div id="success_error_requestWithdraw"></div>
    <h5>
        <?php get_custom_string("Request for withdrawal"); ?>
    </h5>

    <div class="request-withdraw">
        <div class="row">
            <form name="requestWithdraw" id="requestWithdraw" method="post"
                  action="<?php echo freeling_links('wallet_url'); ?>"
            >
                <div class="col-md-12 refill-credit">
                    <div class="refill-box">
                        <strong>USD </strong>
                        <div class="form-group">
                            <input title="amount" type="text"
                                   onkeypress="return (event.charCode === 8 || event.charCode === 0) ? null : event.charCode >= 48 && event.charCode <= 57"
                                   name="amount" class="form-control"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-md-12 refill-credit">
                    <div class="form-group">
                        <label>Withdrawal message </label>
                        <textarea title="Widthdrawl Message" type="text" name="withdrawal_message"
                                  class="form-control"  autocomplete="off" ></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="box-inner">
                        <h6><?php get_custom_string("payment Method"); ?></h6>
                        <div class="radio-box-sec enhanced-text">
                            <input value="<?= FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_PAYPAL] ?>"
                                   title = "PayPal"
                                   type="radio" <?php echo ($default_method == FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_PAYPAL]) ? 'checked' : ''; ?>
                                   class="request_payment_notify" id="request_payment_notify_paypal"
                                   name="request_payment_notify"
                            >
                            <label class="enhanced-text">
                                <?php get_custom_string("Paypal"); ?>
                            </label>
                        </div>
                        <div class="radio-box-sec enhanced-text">
                            <input value="<?= FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_ALIPAY] ?>"
                                   title="AliPay"
                                   type="radio" <?php echo ($default_method == FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_ALIPAY]) ? 'checked' : ''; ?>
                                   class="request_payment_notify" id="request_payment_notify_alipay"
                                   name="request_payment_notify"
                            >
                            <label class="enhanced-text">
                                <?php get_custom_string("AliPay"); ?>
                            </label>
                        </div>
                        <div class="payment_method_info">
                            <?php
                            if ($default_method == FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_PAYPAL]) {
                                if (!empty(get_user_meta(get_current_user_id(), 'paypal_account', true))) {
                                    echo '<div class="paymentmethodinfo">' .
                                        get_user_meta(get_current_user_id(), 'paypal_account', true) . "</div>";
                                }
                            } elseif ($default_method == FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_ALIPAY]) {
                                if (!empty(get_user_meta(get_current_user_id(), 'alipay_account', true))) {
                                    echo '<div class="paymentmethodinfo">' .
                                        get_user_meta(get_current_user_id(), 'alipay_account', true) . "</div>";
                                }
                            }
                            ?>
                        </div>

                    </div>
                    <label id="request_payment_notify-error" class="error"
                           for="request_payment_notify"></label>
                    <button name="submit_requestWithdraw" class="btn blue-btn pay-refill">
                        <?php get_custom_string("Request withdrawal"); ?>
                    </button>
                    <br/>
                    <?php
                    $withdrawal_fee_percentage = get_option('withdrawal_fee_percentage');
                    $withdrawal_fee_base = get_option('withdrawal_fee_base');
                    ?>
                    <small>
                        <?php echo get_custom_string_return("Fees") . ': 1.' .
                            get_custom_string_return("Paypal or Alipay fee") . "; 2." .
                            get_custom_string_return("PeerOK Manual processing fee");
                        ?>
                        <?php echo ': ' . $withdrawal_fee_base . ' + ' . $withdrawal_fee_percentage . '% * '; ?>
                        <?php echo get_custom_string_return("Withdrawal Amount"); ?>
                    </small>
                </div> <!-- /.col -->
            </form> <!-- /.requestWidthdrawl -->
        </div> <!-- /.row -->
    </div> <!-- /.request-widthdraw -->
</div>


