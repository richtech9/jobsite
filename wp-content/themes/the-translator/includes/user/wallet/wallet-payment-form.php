<?php
//used in the translator and customer wallet pages for refills
global $none_shall_pass_again_on_payment;

if (empty($none_shall_pass_again_on_payment)) {
    $none_shall_pass_again_on_payment = 'ok';
} else {
    return; //do not allow mistakes to load this twice in the same page
}
$lang = FLInput::get('lang', 'en');
$enabled_payment_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_PAYMENT_GATEWAYS,FLPaymentGateways::DEFAULT_PAYMENT_GATEWAY_NAMES_ARRAY);

$default_payment_method = get_user_meta( get_current_user_id(), FLPaymentGateways::OPTION_NAME_DEFAULT_PAYMENT_METHOD, true );
if (empty($default_payment_method)) {$default_payment_method = '';}
if (!in_array($default_payment_method,$enabled_payment_gateways_array_of_names)) {$default_payment_method = '';}

?>
<script src="https://js.stripe.com/v3/"></script>
<!-- code-notes must be sourced directly from stripe and must be in the page and not enqueued -->
<div class="wallet-inner">
    <div class="total-credit large-text">
        <?php
        $userdetail = get_userdata(get_current_user_id());
        $user_amount = get_user_meta(get_current_user_id(), 'total_user_balance', true);
        $total_amount = !empty($user_amount) ? $user_amount : '0.00';
        ?>
        <?php get_custom_string("Your Credits"); ?>: 
        <strong>
            <?php echo amount_format($total_amount); ?>
            USD
        </strong>
    </div>


    <h5><?php get_custom_string("Refill Credits"); ?>:</h5>

    <div class="row">
        <?php
        $f_url = add_query_arg(['action' => FLPaypalHandler::ACTION_NAME, 'lang' => $lang], get_permalink());
        ?>
        <form name="form_paypal_and_amount" method="post" action="<?php echo $f_url; ?>">
            <div class="col-md-6 refill-credit">
                <div class="refill-box">

                    <input type="hidden" name="cpp_header_image"
                           value="<?php echo get_template_directory_uri(); ?>/images/logo-1000-by-200.png">

                    <strong>USD </strong>
                    <select title="amount" name="amount" onchange="getprocessingFeeByAmount()" id="amount"
                            class="selectpicker" data-fullamount="<?= 5 + get_refill_processing_charges(5)?>">
                        <option value="5">5.00</option>
                        <option value="10">10.00</option>
                        <option value="25">25.00</option>
                        <option value="50">50.00</option>
                        <option value="100">100.00</option>
                        <option value="250">250.00</option>
                        <option value="500">500.00</option>
                        <option value="1000">1000.00</option>
                        <option value="2000">2000.00</option>
                        <option value="4000">4000.00</option>
                        <option value="8000">8000.00</option>
                        <option value="10000">10000.00</option>
                    </select>
                </div> <!-- /.refill-box -->
                <div id="processing_charges_select_amount"
                     style="font-weight:bold;padding-top:5px;"
                >
                    <?php echo get_custom_string_return('Processing Fee'); ?>
                    : $<?php echo amount_format(get_refill_processing_charges(5)); ?>
                </div>
            </div> <!-- /.refill-credit -->

            <div class="col-md-6">
                <div class="box-inner">
                    <h6><?php get_custom_string("payment Method"); ?></h6>
                    <?php
                    $check_me = '';
                    if ($default_payment_method) {$check_me = $default_payment_method;}
                    else { $check_me = (count($enabled_payment_gateways_array_of_names)? $enabled_payment_gateways_array_of_names[0]: '');}
                    ?>

                    <?php if (empty($enabled_payment_gateways_array_of_names)) { ?>
                        <div class="radio-box-sec">
                            <label class="enhanced-text">
                               <h4>No Payment Methods Enabled</h4>
                            </label>
                        </div>
                    <?php } ?>

                    <?php if (in_array(FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD,$enabled_payment_gateways_array_of_names)) { ?>
                    <div class="radio-box-sec">
                        <label class="enhanced-text">
                            <input type="radio" <?php
                            echo ($check_me === FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD) ?
                                'checked=checked' :
                                ''; ?>
                                   name="payment_notify" value="<?= FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD ?>">
                            <?php get_custom_string("Credit Card"); ?>
                        </label>
                    </div>
                    <?php } ?>



                    <?php if (in_array(FLPaymentGateways::GATEWAY_PAYPAL,$enabled_payment_gateways_array_of_names)) { ?>
                    <div class="radio-box-sec">
                        <label class="enhanced-text">
                            <input type="radio"
                                <?php echo ($check_me === FLPaymentGateways::GATEWAY_PAYPAL) ?
                                    'checked=checked' :
                                    ''; ?>
                                   name="payment_notify" value="<?= FLPaymentGateways::GATEWAY_PAYPAL ?>">
                            <?php get_custom_string("Paypal"); ?>
                        </label>
                    </div>
                    <?php } ?>



                    <?php if (in_array(FLPaymentGateways::GATEWAY_ALIPAY,$enabled_payment_gateways_array_of_names)) { ?>
                    <div class="radio-box-sec">
                        <label class="enhanced-text">
                            <input type="radio"
                                <?php echo ($check_me === FLPaymentGateways::GATEWAY_ALIPAY) ?
                                    'checked=checked' :
                                    ''; ?>
                                   name="payment_notify" value="<?= FLPaymentGateways::GATEWAY_ALIPAY ?>">
                            <?php get_custom_string("AliPay"); ?>
                        </label>
                    </div>
                    <?php } ?>



                </div>


                <button id="button-begin-paypal-refill" class="btn blue-btn pay-refill" type="submit">
                    <?php get_custom_string("pay & confirm"); ?>
                </button>

                <button class="" id="button-begin-stripe-refill" type="button">
                    <?php get_custom_string("pay & confirm"); ?>
                </button>

                <button class="" id="button-begin-alipay-refill" type="button">
                    <?php get_custom_string("pay & confirm"); ?>
                </button>


            </div> <!-- /.col-->
        </form> <!-- /.formPayPal-->
    </div> <!-- /.row-->


    <div class="row">
        <div class="col-md-6 refill-credit"></div>
        <div class="col-md-6">


            
            
            <script>
                function getprocessingFeeByAmount() {
                    var amount_x = document.getElementById("amount").value;
                    var processing_fee_text = "<?php echo get_custom_string_return('Processing Fee'); ?>";
                    let write_to_here = document.getElementById("processing_charges_select_amount");
                    fl_show_amount_with_fee(amount_x, write_to_here, processing_fee_text);
                }
            </script>
        </div> <!-- /.col -->
    </div> <!-- /.row -->
</div> <!-- /.wallet-inner  -->
<?php
get_template_part('includes/user/wallet/wallet', 'payment-stripe');//include stripe dialog
?>

