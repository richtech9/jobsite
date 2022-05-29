<?php
//used in the translator and customer wallet pages


$lang = FLInput::get('lang', 'en');
$usd_formatter = numfmt_create( 'en_US', NumberFormatter::CURRENCY );
?>

<!--START: Transaction history-->
<div class="wallet-wraper">
    <h5><?php get_custom_string("Transaction history"); ?></h5>
    <?php
    $trans_history = $wpdb->get_results(
        "SELECT *, UNIX_TIMESTAMP(time) as da_ts FROM wp_fl_transaction WHERE user_id =" . get_current_user_id() . " ORDER BY `time` DESC limit 5"
    );
    ?>
    <a href="<?=add_query_arg(  ['type'=>'transaction_history','lang'=>$lang],freeling_links('wallet_url'));?>" class="view-all">
        <?php get_custom_string("See All"); ?>
    </a>


    <table class="wallet-table transaction-table enhanced-text">
        <thead>
        <tr>
            <td><?php get_custom_string("Date"); ?></td>
            <td><?php get_custom_string("Transaction ID"); ?></td>
            <td><?php get_custom_string("Description"); ?></td>
            <td><?php get_custom_string("Printable receipt"); ?></td>
            <td><?php get_custom_string("Amount"); ?></td>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($trans_history) {
            foreach ($trans_history as $row) {
                $file_href = add_query_arg(  ['action'=> 'contentreciept','lang'=>$lang,'receipt'=>$row->ID], get_site_url());
                ?>
        <tr>
            <td>
                <span
                        class="fl-wallet  a-timestamp-full-date-time"
                        data-ts="<?= $row->da_ts ?>"
                ></span>
            </td>

            <td>
               <span><?= $row->txn_id ?></span>
            </td>

            <td>
                <span>
                    <?= $row->description ?>
                </span>
            </td>

            <td>
                <a
                        href="<?= $file_href ?>"  download  target="_blank" id="<?php echo $row->ID; ?>"
                        class="download-icon receipts_info"
                >
                </a>
            </td>

            <td>
                <span>
                    <?= $usd_formatter->formatCurrency($row->amount,'USD') ?>
                </span>
            </td>
        </tr>
                <?php


            }
        }

        ?>
        </tbody>
    </table> <!-- /.wallet-table -->
</div>
<!-- END: Transaction history -->

<!-- START: Refills and withdrawals -->
<div class="wallet-wraper">
    <h5><?php get_custom_string("Refills and withdrawals"); ?></h5>
        <a href="<?= add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang], freeling_links('wallet_url')) ?>"
           class="view-all"
        >
            <?php get_custom_string("See All"); ?>
        </a>
    <?php
    $transaction = $wpdb->get_results(
        "SELECT * FROM wp_fl_transaction WHERE user_id =" . get_current_user_id() . " AND type = 'refill'"
    );
    ?>


        <?php

        set_query_var( 'wallet_history_transaction_types', [
            FLTransactionLookup::TRANSACTION_TYPE_REFILL,
            FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
            FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE,
            FLTransactionLookup::TRANSACTION_TYPE_UNDO_PROCESSING_FEE
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
</div> <!-- ./wallet-wrapper-->

<!-- END: Refills and withdrawals -->



<!-- START: Pending and failed -->
<div class="wallet-wraper">
    <h5><?php get_custom_string("Pending and Failed Refills or Withdrawals"); ?></h5>
    <a href="<?= add_query_arg(  ['type'=>'pending_failed_refills','lang'=>$lang], freeling_links('wallet_url')) ?>"
       class="view-all"
    >
        <?php get_custom_string("See All"); ?>
    </a>

    <?php

    set_query_var( 'wallet_history_transaction_types', [
        FLTransactionLookup::TRANSACTION_TYPE_REFILL,
        FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
    ] );

    set_query_var( 'wallet_history_post_status', [
        FLTransactionLookup::POST_STATUS_PENDING_TRANSACTION,
        FLTransactionLookup::POST_STATUS_FAILED_TRANSACTION,
    ] );

    set_query_var( 'wallet_history_user_id', get_current_user_id() );
    set_query_var( 'wallet_history_sort', 'by_date_desc' );
    set_query_var( 'wallet_history_page_number', 1 );
    set_query_var( 'wallet_history_page_size', 5 );
    get_template_part('includes/user/wallet/wallet', 'history-table');
    ?>
</div> <!-- ./wallet-wrapper-->

<!-- END: Refills and withdrawals -->