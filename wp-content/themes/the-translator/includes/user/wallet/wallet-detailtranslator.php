<?php
/*
    * current-php-code 2020-Oct-15
    * input-sanitized : lang,q,type,trans
    * current-wp-template:  for translator wallet
*/


$lang = FLInput::get('lang', 'en');
$type = FLInput::get('type');
$unused_text_search = NULL;
$trans = FLInput::get('trans', NULL);
?>
<!-- START: transaction_history portion start from here -->
<?php if (($type === 'transaction_history') || !is_null($unused_text_search)) { ?>
    <?php get_header(); ?>

    <section class="middle-content wallet-content">
        <div class="container" id="container-body">
            <div class="row wallet-head">
                <div class="col-md-6 page-title">
                    <h3>
                        <i class="icon icon-wallet"></i><?php get_custom_string("Wallet - Transaction history"); ?>
                    </h3>
                </div>
                <div class="col-md-6 filter-sec">



                </div> <!-- /.col -->
            </div> <!-- /.wallet-head -->

            <div class="wallet-history">
                <div class="wallet-wraper">
                    <?php
                    $trans_history = $wpdb->get_results(
                            "SELECT * FROM wp_fl_transaction WHERE user_id =" . get_current_user_id() . " ORDER BY `time` DESC"
                    );

                    ?>
                    <table class="wallet-table transaction-table enhanced-text">
                        <thead>
                        <tr>
                            <td><?php get_custom_string("Date (UTC)"); ?></td>
                            <td><?php get_custom_string("Transaction ID"); ?></td>
                            <td><?php get_custom_string("Description"); ?></td>
                            <td><?php get_custom_string("Printable receipt"); ?></td>
                            <td><?php get_custom_string("Total"); ?>
                                <small><?php get_custom_string("Amount"); ?></small>
                            </td>
                            <td><?php get_custom_string("FREE Credits"); ?></td>
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
                                    <a target="_blank"
                                       download href="<?= $file_href ?>" id="<?php echo $row->ID; ?>"
                                       class="download-icon receipts_info"
                                    ></a>
                                </td>

                                <?php
                                echo '<td></td>';
                                echo '<td></td>';


                                echo '<td>' . $row->amount . '</td>';
                                echo '</tr>';
                            }
                        }

                        ?>
                        </tbody>
                    </table> <!-- /.wallet-table -->
                </div> <!-- /.wallet-wrapper -->

                <?php get_template_part('pagination'); ?>

            </div> <!-- /.wallet-history -->
        </div> <!-- /.container -->
    </section> <!-- /.wallet-content -->

    <?php get_footer('homepagenew'); ?>
    <?php exit; ?>

<?php } ?>
<!-- START: transaction_history portion  -->

<!-- START: Refill and withdraw -->
<?php if ($type === 'refill_withdraw') { ?>
    <?php get_header(); ?>
    <section class="middle-content wallet-content">
        <div class="container" id="container-body">
            <div class="row wallet-head">
                <div class="col-md-6 page-title">
                    <h3>
                        <i class="icon icon-wallet"></i>
                        <?php get_custom_string("Wallet - Refills And Withdrawals"); ?>
                    </h3>
                </div>
                <div class="col-md-6 filter-sec">
                    <?php
                    $refill_withdraw_redirect_to = add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang], freeling_links('wallet_url'));
                    ?>

                    <div class="btn-group bootstrap-select">
                        <select title="Transaction Type" class="selectpicker" onchange="location = this.value;">


                            <option value="<?=$refill_withdraw_redirect_to ?>"><?php get_custom_string("All"); ?></option>
                            <option <?php echo $trans === 'withdraw' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang,'trans'=>'withdraw'], freeling_links('wallet_url'))?>"><?php get_custom_string("Withdrawals"); ?></option>
                            <option <?php echo $trans === 'refill' ? 'selected' : ''; ?>
                                    value="<?= add_query_arg(  ['type'=>'refill_withdraw','lang'=>$lang,'trans'=>'refill'], freeling_links('wallet_url'))?>"><?php get_custom_string("Refills"); ?></option>


                        </select>
                    </div> <!-- /.button-group -->
                </div> <!-- /.col -->
            </div> <!-- /.row -->


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

                </div> <!-- /.wallet-wrapper-->

                <?php get_template_part('pagination'); ?>

            </div> <!-- /.wallet-history-->
        </div> <!-- /.container-->
    </section> <!-- /.wallet-content-->

    <?php get_footer('homepagenew'); ?>
    <?php exit; ?>
<?php } ?>
<!-- END: Refill and withdraw -->


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
