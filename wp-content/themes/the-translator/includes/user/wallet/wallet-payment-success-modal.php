<?php
//task-future-work this is obsolete, but can be modified to work fine (payment success)
$redirect_to = FLInput::get('redirect_to');

if ($redirect_to === "payment") { ?>
    <script>
        jQuery(function () {

            jQuery('#PaymentSuccessModel').modal('show');
            jQuery('#PaymentSuccessModel').on('hidden.bs.modal', function () {
                window.location = '<?php echo freeling_links("wallet_url"); ?>';
            });
        });
    </script>

    <div role="dialog" id="PaymentSuccessModel" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button data-dismiss="modal" class="close close_page_reload" type="button">×</button>
                    <h4 class="modal-title">
                        <?php get_custom_string('Refill Information'); ?>
                    </h4>
                </div>
                <div class="modal-body">
                    <span class="span_payment_success">
                        <?php get_custom_string(
                            'Your wallet will be updated once the background transaction is completed if there is any');
                        ?>
                    </span>

                    <div class="right-align">
                        <button data-dismiss="modal" class="btn blue-btn btn-ok close_page_reload" type="button">
                            <?php get_custom_string('Ok'); ?>
                        </button>
                    </div>
                </div> <!-- /.model-body-->
            </div> <!-- /.model-content-->
        </div> <!-- /.model-dialog-->
    </div> <!-- /.model-->

<?php } ?>