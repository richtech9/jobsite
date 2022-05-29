<?php
/*
    * current-php-code 2020-Feb-25
    * input-sanitized :
    * current-wp-template:  for translator and customer wallet
*/
//shown in the wallet pages for all when a balance is negative
global $wpdb;
?>

<div class="modal fade in" id="walletReminder" role="dialog">

    <div class="modal-dialog modal-lg">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">
                    <?php get_custom_string("You balance is negative, please refill credits to cover your spendings."); ?>
                </h4>

            </div>
            <div class="modal-body">
                <?php
                get_template_part('includes/user/wallet/wallet', 'payment-form');
                ?>
            </div> <!-- /.model-body-->
        </div> <!-- /.model-content-->
    </div> <!-- /.model-dialog-->
</div> <!-- /.model-->

<?php
$current_user_id = get_current_user_id();
$pending_status_flag = FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING;
$sql = "
SELECT (sum(payment_amount) - sum(processing_fee_included)) as payment_waiting
FROM wp_payment_history
WHERE user_id = $current_user_id AND payment_status = '$pending_status_flag' group by user_id;";
$pending_value = floatval($wpdb->get_var($sql));
will_throw_on_wpdb_error($wpdb);
$current_balance = floatval(get_user_meta(get_current_user_id(), 'total_user_balance', true));
//
$b_show_dialog = true;
if ($pending_value + $current_balance > 0) { $b_show_dialog = false;}
if ($b_show_dialog) {
?>

<script>
    jQuery(function () {
        jQuery('#walletReminder').modal({
            backdrop: 'static',
            keyboard: true,
            show: true
        });

    });
</script>
<?php } else {?>
    <div class="wallet-inner">
        <div class="total-credit large-text">
            <?php
            $total_amount = !empty($current_balance) ? $current_balance : '0.00';
            ?>
            <div style="margin-top: 2em;display: block" >
                <?php get_custom_string("Credits"); ?>: 
                <strong >
                    <?php echo amount_format($current_balance); ?>
                    USD
                </strong>
            </div>
            (Pending transactions: <?= amount_format($pending_value)?> USD)
        </div>

        <button type="button"
                class="btn blue-btn "
                data-toggle="modal"
                data-target="#walletReminder"
        >
            Make Another Payment

        </button>
    </div>
<?php
}
