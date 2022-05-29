<?php
/*
  Usage involves setting  pagination variables using set_query_var, in the calling parent, and then these are defined as php vars by the wp system
  set_query_var( 'wallet_history_page_size', N ); # defaults to 5, if -1 then there is no pagination
  set_query_var( 'wallet_history_page_number', N ); #defaults to 1
 set_query_var( 'wallet_history_sort', *one of by_date_asc|by_date_desc* ); #defaults to by_date_desc

 The user MAY be defined, if not it defaults to the current user
 set_query_var( 'wallet_history_user_id', N );

 The transaction types MAY be limited to an array of the integer values of the types
  set_query_var( 'wallet_history_transaction_types', A );

 The post status MAY be limited to an array of the integer values of the status
   set_query_var( 'wallet_history_post_status', A );
  */
?>
<table class="wallet-table transaction-table enhanced-text " data-userid="<?= $wallet_history_user_id ?>">
    <thead>
    <tr>
        <td><?php get_custom_string("Date"); ?></td>
        <td style="width:15em"><?php get_custom_string("Transaction ID"); ?></td>
        <td><?php get_custom_string("Description"); ?></td>
        <td><?php get_custom_string("Printable receipt"); ?></td>
        <td><?php get_custom_string("Amount"); ?></td>
        <td><?php get_custom_string("Status") ?></td>
    </tr>
    </thead>
    <tbody>

<?php
global $wpdb;
$lang = FLInput::get('lang', 'en');
$usd_formatter = numfmt_create( 'en_US', NumberFormatter::CURRENCY );

if (!isset($wallet_history_user_id)) {$wallet_history_user_id = get_current_user_id();}
$wallet_history_user_id = (int)$wallet_history_user_id;
if (empty($wallet_history_user_id)) {return;} //no user , then no display

if (!isset($wallet_history_page_size)) {$wallet_history_page_size = 5;}

if (!isset($wallet_history_page_number)) {$wallet_history_page_number = 1;}
if (!isset($wallet_history_sort)) {$wallet_history_sort = 'by_date_desc';}
if (!isset($wallet_history_transaction_types) ) {$wallet_history_transaction_types = [];}
if (!isset($wallet_history_post_status)) {$wallet_history_post_status = [];}


$lookup_res = FLTransactionLookup::get_rows($wallet_history_user_id,$wallet_history_page_size,$wallet_history_page_number,
    $wallet_history_sort,$wallet_history_transaction_types,$wallet_history_post_status);

$post_ids_of_interest = [];
foreach ($lookup_res as $transaction_row) {
    $lookup_post_id = (int)$transaction_row->post_id;
    $post_ids_of_interest[] = $lookup_post_id;
}
if (empty($post_ids_of_interest)) {
    ?>
        <tr>
            <td colspan="5" style="text-align: center">
                <h5> No Record </h5>
            </td>
        </tr>

    <?php
    echo
    "    </tbody>\n</table><!-- ./wallet-table-->";//this version of php editor has a bug with html closing tags so put these in a string
    return;
}

$payments = FLPaymentSummary::make_summaries($post_ids_of_interest);
//        foreach ($payments as $pay_node) {
//            will_dump('payments',$pay_node);
//        }

 foreach ($lookup_res as $transaction_row) {
    $transaction_amount_raw = floatval($transaction_row->transaction_amount);
    $transactionType = FLTransactionLookup::transaction_type_int_to_enum($transaction_row->transaction_type);
    $transactionReason = $transaction_row->transaction_reason;
    $transactionTxnID = $transaction_row->txn;
    $post_status = FLTransactionLookup::post_status_int_to_enum($transaction_row->post_status);
    $transactionStatus = fl_get_payment_post_status_string($transaction_row->post_id,true,$post_status,null);
    $admin_withdraw_cancel_message = '';
    if ($transactionType == 'withdraw'  ) {
        $transactionAmount = '<small class="minus-icon large-text">&#45;</small> $' . amount_format($transaction_amount_raw);
        $admin_withdraw_cancel_message = $transaction_row->withdraw_cancel_message;
    } elseif ($transactionType == 'refill') {
        $transactionAmount = '<small class="add-icon large-text">&#43;</small> $' . amount_format($transaction_amount_raw);
    } elseif (
            $transactionType == FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE] ||
            $transactionType == 'undo_processing_fee'
    ) {
        if ($transaction_amount_raw > 0) {
            $transactionAmount = '<small class="add-icon large-text">&#43;</small> $' . amount_format($transaction_amount_raw);
        } elseif ($transaction_amount_raw < 0) {
            $transactionAmount = '<small class="minus-icon large-text">&#45;</small> $' . amount_format(abs($transaction_amount_raw));
        } else {
            $transactionAmount = '<small class="large-text">&#45;</small> $' . amount_format($transaction_amount_raw);
        }
    }elseif ($transactionType == 'FREE_credits') {
        $transactionAmount = '<small class="add-icon large-text">&#43; (cr)</small> $' . amount_format($transaction_amount_raw);
    }
    elseif ($transactionType == 'FREE_credits_refund') {
        $transactionAmount = '<small class="add-icon large-text">&#43; (cr)</small> $' . amount_format($transaction_amount_raw);
    }
    elseif ($transactionType == 'FREE_credits_used') {
        $transactionAmount = '<small class="minus-icon large-text">&#43; (cr)</small> $' . amount_format($transaction_amount_raw);
    }
    else {
        $transactionAmount = 0.00;
    }

    ?>
    <tr data-pid="<?= $transaction_row->post_id?>">
        <td>
            <?php
            ?>
            <span
                class="fl-wallet  a-timestamp-full-date-time"
                data-ts="<?= $transaction_row->post_created_at_ts ?>"
            ></span>
        </td>
        <td>
            <div class="">
               <?= $transactionTxnID ?>
            </div>
        </td>
        <td>
            <strong>
                <?php echo $transactionReason; ?>
            </strong>
            <?php if ($admin_withdraw_cancel_message) { ?>
                <span class="fl-wallet-admin-cancel-message"><?= $admin_withdraw_cancel_message ?></span>
            <?php } ?>
        </td>
        <td>
            <?php
            $file_href = add_query_arg(  ['action'=> 'recieptInfo','type'=>2,'lang'=>$lang,'receipt'=>$transaction_row->post_id], get_site_url());
            ?>
            <a
                href="<?= $file_href?>"  download  target="_blank" id="<?php echo $transaction_row->post_id; ?>"
                class="download-icon receipts_info"
            >
            </a>
        </td>
        <td class="amount large-text">
            <?php echo $transactionAmount; ?>
        </td>
        <td><?= $transactionStatus ?></td>
    </tr>
<?php } ?>

</tbody>
</table> <!-- ./wallet-table -->