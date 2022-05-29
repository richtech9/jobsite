<?php
/*
    * current-php-code 2020-Oct-15
    * input-sanitized : lang,type,trans
    * current-wp-template:  for customer wallet
*/


$lang = FLInput::get('lang', 'en');
$type = FLInput::get('type');
$trans = FLInput::get('trans', NULL);
$unused_text_search = null;
?>
<!-- START: transaction_history portion start from here -->

<?php if ($type === 'transaction_history') { ?>
    <?php get_header(); ?>


    <section class="middle-content wallet-content">
        <div class="container" id="container-body">
            <div class="row wallet-head">
                <div class="col-md-6 page-title">
                    <h3>
                        <i class="icon icon-wallet"></i>
                        <?php get_custom_string("Wallet - Transaction history"); ?>
                    </h3>
                </div>
                <div class="col-md-6 filter-sec">



                </div> <!-- /.filter-sec.col -->
            </div> <!-- /.wallet-head.row -->

            <div class="wallet-history">
                <div class="wallet-wraper">
                    <?php


                    $trans_history = $wpdb->get_results("SELECT * FROM wp_fl_transaction WHERE user_id =" . get_current_user_id() . " ORDER BY `time` DESC");

                    ?>
                    <table class="wallet-table transaction-table enhanced-text">
                        <thead>
                        <tr>
                            <td><?php get_custom_string("Date (UTC)"); ?></td>
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
                                echo '<tr>';
                                echo '<td>' . date_formatted($row->time) . '</td>';
                                echo '<td>' . $row->txn_id . '</td>';
                                echo '<td>' . $row->description . '</td>';
                                ?>
                                <td>
                                    <?php
                                    $file_href = add_query_arg(  ['action'=> 'contentreciept','lang'=>$lang,'receipt'=>$row->ID], get_site_url());
                                    ?>
                                    <a
                                       href="<?= $file_href ?>" download target="_blank" id="<?php echo $row->ID; ?>"
                                       class="download-icon receipts_info"></a></td>

                                <?php
                                echo '<td>' . $row->amount . '</td>';
                                echo '</tr>';
                            }
                        }

                        ?>
                        </tbody>
                    </table> <!-- /.wallet-table transaction-table -->
                </div> <!-- /.wallet-wrapper -->
                <?php get_template_part('pagination'); ?>
            </div> <!-- /.wallet-history -->
        </div> <!-- /.container -->
    </section> <!-- /.middle-content.wallet-content -->

    <?php get_footer('homepagenew'); ?>
    <?php exit; ?>

<?php } ?>
<!-- END: transaction_history portion  -->

<!-- START: Refill & withdraw portion start from here -->
<?php if ($type === 'refill_withdraw') { ?>
    <?php get_header(); ?>
    <section class="middle-content wallet-content">
        <div class="container" id="container-body">
            <div class="row wallet-head">
                <div class="col-md-6 page-title">
                    <h3>
                        <i class="icon icon-wallet"></i>
                        <?php get_custom_string("Wallet - Refills and Withdrwals"); ?>
                    </h3>
                </div>
                <div class="col-md-6 filter-sec">

                    <?php
                    $refill_withdraw_redirect_to = add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang], freeling_links('wallet_url'));

                    ?>

                    <div class="btn-group bootstrap-select">
                        <select title="Transaction Types" class="selectpicker" onchange="location = this.value;">

                            <option value="<?=$refill_withdraw_redirect_to ?>"><?php get_custom_string("All"); ?></option>
                            <option <?php echo $trans === 'withdraw' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang,'trans'=>'withdraw'], freeling_links('wallet_url'))?>"><?php get_custom_string("Withdrawals"); ?></option>
                            <option <?php echo $trans === 'refill' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang,'trans'=>'refill'], freeling_links('wallet_url'))?>"><?php get_custom_string("Refills"); ?></option>

                        </select>
                    </div> <!-- /.btn-group -->
                </div> <!-- /.filter-sec.col -->
            </div> <!-- /.wallet-head.row -->

            <div class="wallet-history">
                <div class="wallet-wraper">

                        <?php
                        set_query_var( 'wallet_history_post_status', [
                            FLTransactionLookup::POST_STATUS_PUBLISH,
                        ] );

                        if (!is_null($unused_text_search)) {
                            set_query_var( 'wallet_history_transaction_types', [
                                FLTransactionLookup::TRANSACTION_TYPE_REFILL,
                                FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
                                FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE,
                                FLTransactionLookup::TRANSACTION_TYPE_UNDO_PROCESSING_FEE
                            ] );

                        } elseif (!is_null($trans)) {
                            if ($trans === 'refill') {

                                set_query_var( 'wallet_history_transaction_types', [
                                    FLTransactionLookup::TRANSACTION_TYPE_REFILL,
                                    FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE,
                                ] );

                            } elseif ($trans === 'withdraw') {

                                set_query_var( 'wallet_history_transaction_types', [
                                    FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
                                ] );

                            }
                        } else {

                            set_query_var( 'wallet_history_transaction_types', [
                                FLTransactionLookup::TRANSACTION_TYPE_REFILL,
                                FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
                                FLTransactionLookup::TRANSACTION_TYPE_PROCESSING_FEE,
                                FLTransactionLookup::TRANSACTION_TYPE_UNDO_PROCESSING_FEE
                            ] );

                        }
                        set_query_var( 'wallet_history_user_id', get_current_user_id() );
                        set_query_var( 'wallet_history_sort', 'by_date_desc' );
                        set_query_var( 'wallet_history_page_number', 1 );
                        set_query_var( 'wallet_history_page_size', -1 );
                        get_template_part('includes/user/wallet/wallet', 'history-table');


                        ?>

                </div><!-- /.wallet-wrapper-->
            </div> <!-- /.wallet-history-->
        </div> <!-- /.container-->
    </section> <!-- middle-content.wallet-content-->

    <?php get_footer('homepagenew'); ?>
    <?php exit; ?>

<?php } /* end $type === 'refill_withdraw' */ ?>
<!-- END: Refill & withdraw portion  -->




<!-- START: $type === 'pending_failed_refills' start from here -->
<?php if ($type === 'pending_failed_refills') { ?>
    <?php get_header(); ?>
    <section class="middle-content wallet-content">
        <div class="container" id="container-body">
            <div class="row wallet-head">
                <div class="col-md-6 page-title">
                    <h3>
                        <i class="icon icon-wallet"></i>
                        <?php get_custom_string("Pending and Failed Refills or Withdrawals"); ?>
                    </h3>
                </div>
                <div class="col-md-6 filter-sec">

                    <?php
                    $refill_withdraw_redirect_to = add_query_arg(  ['type'=>'Pending and Failed Refills or Withdrawals','lang'=>$lang], freeling_links('wallet_url'));

                    ?>

                    <div class="btn-group bootstrap-select">
                        <select title="Transaction Types" class="selectpicker" onchange="location = this.value;">

                            <option value="<?=$refill_withdraw_redirect_to ?>"><?php get_custom_string("All"); ?></option>
                            <option <?php echo $trans === 'withdraw' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang,'trans'=>'withdraw'], freeling_links('wallet_url'))?>"><?php get_custom_string("Withdrawals"); ?></option>
                            <option <?php echo $trans === 'refill' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang,'trans'=>'refill'], freeling_links('wallet_url'))?>"><?php get_custom_string("Refills"); ?></option>

                        </select>
                    </div> <!-- /.btn-group -->
                </div> <!-- /.filter-sec.col -->
            </div> <!-- /.wallet-head.row -->

            <div class="wallet-history">
                <div class="wallet-wraper">

                    <?php
                    set_query_var( 'wallet_history_post_status', [
                        FLTransactionLookup::POST_STATUS_PENDING_TRANSACTION,
                        FLTransactionLookup::POST_STATUS_FAILED_TRANSACTION
                    ] );

                    if (!is_null($unused_text_search)) {
                        set_query_var( 'wallet_history_transaction_types', [
                            FLTransactionLookup::TRANSACTION_TYPE_REFILL,
                            FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
                        ] );
                    } elseif (!is_null($trans)) {
                        if ($trans === 'refill') {
                            set_query_var( 'wallet_history_transaction_types', [
                                FLTransactionLookup::TRANSACTION_TYPE_REFILL,
                            ] );

                        } elseif ($trans === 'withdraw') {

                            set_query_var( 'wallet_history_transaction_types', [
                                FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
                            ] );

                        }
                    } else {


                        set_query_var( 'wallet_history_transaction_types', [
                            FLTransactionLookup::TRANSACTION_TYPE_REFILL,
                            FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW,
                        ] );

                    }


                    set_query_var( 'wallet_history_user_id', get_current_user_id() );
                    set_query_var( 'wallet_history_sort', 'by_date_desc' );
                    set_query_var( 'wallet_history_page_number', 1 );
                    set_query_var( 'wallet_history_page_size', -1 );
                    get_template_part('includes/user/wallet/wallet', 'history-table');



                    ?>

                </div><!-- /.wallet-wrapper-->
            </div> <!-- /.wallet-history-->
        </div> <!-- /.container-->
    </section> <!-- middle-content.wallet-content-->

    <?php get_footer('homepagenew'); ?>
    <?php exit; ?>

<?php } /* end $type === 'pending_failed_refills' */ ?>
<!-- END: Pending and Failed Withdrawals  -->


<!-- Start: free_credit transaction history -->
<?php if ($type === 'free_credit') { ?>
    <?php get_header(); ?>
    <section class="middle-content wallet-content">
        <div class="container" id="container-body">
            <div class="row wallet-head">
                <div class="col-md-6 page-title">
                    <h3><i class="icon icon-wallet"></i><?php get_custom_string("Wallet - Your FREE credits"); ?></h3>
                </div>
                <div class="col-md-6 filter-sec">

                    <?php
                    $free_credit_redirect_to = add_query_arg(  ['type'=>'free_credit','lang'=>$lang], freeling_links('wallet_url'));
                    ?>

                    <div class="btn-group bootstrap-select">
                        <select title="Transaction Types" class="selectpicker" onchange="location = this.value;">
                            <option value="<?= $free_credit_redirect_to ?>">
                                <?php get_custom_string("Select"); ?>
                            </option>

                            <option <?php echo $trans === 'free_credits' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'free_credit','lang'=>$lang,'trans'=>'free_credits'], freeling_links('wallet_url')) ?>"
                            >
                                <?php get_custom_string("Free Credit"); ?>
                            </option>

                            <option <?php echo $trans === 'refund' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'free_credit','lang'=>$lang,'trans'=>'refund'], freeling_links('wallet_url')) ?>"
                            >
                                <?php get_custom_string("Refund-Free Credit"); ?>
                            </option>


                            <option <?php echo $trans === 'payment' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'free_credit','lang'=>$lang,'trans'=>'payment'], freeling_links('wallet_url')) ?>"
                            >
                                <?php get_custom_string("Payment-Free Credit"); ?>
                            </option>
                        </select>
                    </div> <!-- /.btn-group-->
                </div><!-- /.filter-sec-->
            </div> <!-- /.wallet-head-->

            <div class="wallet-history">
                <div class="wallet-wraper">

                        <?php
                        set_query_var( 'wallet_history_post_status', [
                            FLTransactionLookup::POST_STATUS_PUBLISH,
                        ] );

                        if (is_null($unused_text_search)) {
                            set_query_var( 'wallet_history_transaction_types', [
                                FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS,
                                FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_REFUND,
                                FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_USED,
                            ] );


                        } elseif (!is_null($trans)) {

                            if ($trans === 'refund') {
                                set_query_var( 'wallet_history_transaction_types', [
                                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_REFUND,
                                ] );
                            }  elseif ($trans === 'payment') {
                                set_query_var( 'wallet_history_transaction_types', [
                                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS,
                                ] );
                            } else {
                                set_query_var( 'wallet_history_transaction_types', [
                                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS,
                                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_REFUND,
                                    FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_USED,
                                ] );
                            }

                        } else {
                            set_query_var( 'wallet_history_transaction_types', [
                                FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS,
                                FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_REFUND,
                                FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_USED,
                            ] );
                        }
                        set_query_var( 'wallet_history_user_id', get_current_user_id() );
                        set_query_var( 'wallet_history_sort', 'by_date_desc' );
                        set_query_var( 'wallet_history_page_number', 1 );
                        set_query_var( 'wallet_history_page_size', -1 );
                        get_template_part('includes/user/wallet/wallet', 'history-table');
                        ?>
                </div> <!-- /.wallet-wrapper -->
            </div>  <!-- /.wallet-history -->
            <?php get_template_part('pagination'); ?>
        </div>  <!-- /.container -->
    </section>  <!-- /.middle-content.wallet-content xxx -->



    <?php get_footer('homepagenew'); ?>
    <?php exit; ?>

<?php } ?>


<!-- END: free_credit transaction history -->


