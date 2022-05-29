<?php

/*
* current-php-code 2020-Nov-12
* input-sanitized : lang
* current-wp-template:  purchase button for content for customer view
*/

$lang = FLInput::get('lang','en');
/**
 * @usage get_template_part('includes/user/contentdetail/contentdetail', 'customer-button-buy-dialogs');
 */

?>

<div id="buyModal" class="modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body hirepopup">
                <h2 class="modal-title">Your Order</h2>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table width="100%" class="table table-bordered">
                                <tbody>
                                <tr class="freelinguist-order-step-1">
                                    <th>Title</th>
                                    <td><span class="fl-content-title"></span></td>
                                </tr>
                                <tr class="freelinguist-order-step-1">
                                    <th>Author</th>
                                    <td><span class="fl-content-author"></span></td>
                                </tr>
                                <tr class="freelinguist-order-step-1">
                                    <th>Price</th>
                                    <td>$<span class="fl-content-amount"></span>/td>
                                </tr>
                                <tr class="freelinguist-order-step-1">
                                    <th>Processing Fee</th>
                                    <td>
                                        $<span class="fl-content-processing-fee"></span>
                                    </td>
                                </tr>
                                <tr class="freelinguist-order-step-1">
                                    <th>Total</th>
                                    <td>
                                        $<span class="fl-content-amount-with-processing-fee"></span></td>
                                </tr>
                                </tbody>
                            </table>
                            <table width="100%" class="table table-bordered">
                                <tbody>
                                <tr class="freelinguist-order-step-1">
                                    <th>Wallet Balance</th>
                                    <td>$<span class="fl-wallet-amount"></span></td>
                                </tr>
                                <tr class="freelinguist-order-step-2">
                                    <td colspan="2">
                                        <iframe class="freelinguist-receipt" src="about:blank"></iframe>
                                    </td>
                                </tr>

                                </tbody>
                            </table>

                            <table width="100%" class="table table-bordered">
                                <tbody>
                                <tr class="freelinguist-order-step-1">
                                    <td>
                                        <input type="button" value="Pay &amp; Confirm Order"
                                               class="btn-md btn btn-danger  box-prement fl-content-id"
                                               data-content_id = ""
                                               onclick="hz_buy_content2( this );">
                                    </td>
                                </tr>
                                <tr class="freelinguist-order-step-2">
                                    <td>
                                        <input type="button" value="Go To New Content"
                                               class="btn-md btn btn-danger box-prement
                                               freelinguist-go-to-purchased-content"
                                        >
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                        </div> <!-- /.table-responsive-->
                    </div> <!-- /.col-->


                </div> <!-- /.row-->
            </div> <!-- /.modal-body -->
        </div> <!-- /.modal-content-->
    </div> <!-- /.modal-dialog-->
</div> <!-- /.modal-->

<div id="viewAllOfferModel" class="modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body hirepopup">
                <h2 class="modal-title">To be filled in by ajax</h2>


            </div> <!-- /.modal-body-->
        </div> <!-- /.modal-content-->
    </div> <!-- /.modal-dialog-->
</div> <!-- /.modal-->

<div id="makeOfferModel" class="modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>
                <h4>Make an offer to buy the content</h4>
                <p style="text-align:left">Important: Once the offer is accepted, the offer amount will be deducted
                    from your Wallet. <br/>Place the offer when you're ready to buy.</p>
            </div>
            <div class="modal-body hirepopup">

                <div class="form-group">
                    Offer Price (USD):<input type="text" id="offershoot" value="100" class="form-control"
                                             title="Offer Price">
                </div>

                <span id="offer_max_bid"></span>
                <span class="offer_notice"></span>

                <button
                        class="button_cc bttns btn blue-btn"
                        href="#"
                        id="offerSend"
                        data-min_bid=""
                        data-content_id=""
                >
                    Yes
                </button>


            </div> <!-- /.modal-body-->
        </div> <!-- /.modal-content-->
    </div> <!-- /.modal-dialog-->
</div> <!-- /.modal-->

<input type="hidden" id="maximum_bid_value" value="">

<!--suppress JSUnusedLocalSymbols -->
<script>
    var user_id_for_content_dialogs = <?= get_current_user_id() ?>;
    var login_link_for_content_dialogs = "<?= site_url()."/login/?lang=$lang" ?>";
</script>


<script>




</script>
