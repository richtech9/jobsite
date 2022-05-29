<?php

/*
    * current-php-code 2020-Oct-15
    * input-sanitized : lang,redirect_to
    * current-wp-template:  for translator wallet
*/

$lang = FLInput::get('lang', 'en');

?>
<style>

    .modal-backdrop.in {
        opacity: 0.7 !important;
    }
</style>

<?php
if (get_user_meta(get_current_user_id(), 'total_user_balance', true)) {

} else {
    update_user_meta(get_current_user_id(), 'total_user_balance', 0);
}
get_template_part('includes/user/wallet/wallet', 'payment-success-modal');
 ?>


<section class="middle-content wallet-content">
    <div class="container" id="container-body">
        <div class="row wallet-head">
            <div class="col-md-6">
                <i class="icon icon-wallet"></i>
                <span class="bold-and-blocking large-text">
                    <?php get_custom_string("Wallet"); ?>
                </span>
            </div>
        </div>
        <!-- START: Refill credit form -->
        <?php
        if (get_user_meta(get_current_user_id(), 'total_user_balance', true) < 0) {
            get_template_part('includes/user/wallet/wallet', 'refill-modal');
        } /* end if wallet balance is negative*/
        else {
            if (get_user_meta(get_current_user_id(), 'total_user_balance', true) >= 0) {
                get_template_part('includes/user/wallet/wallet', 'refill-static');
            } /* end if wallet balance is negative*/
        }
        ?>
        <!-- END: Refill credit form -->

        <div class="wallet-history">

            <?php get_template_part('includes/user/wallet/wallet', 'last-history'); ?>

            <!-- START: Request for withdrawal -->
            <?php get_template_part('includes/user/wallet/wallet', 'request-widthdraw'); ?>
            <!-- END: Request for withdrawal -->

            <!-- START: TAX Form -->
            <div class="wallet-wraper">
                <h5><?php get_custom_string("TAX form(only for information purpose)"); ?></h5>
                <div class="request-withdraw tax-info">
                    <div class="clear">
                        <?php
                        $upload_dir = wp_upload_dir();
                        $user_dirname = $upload_dir['basedir'];
                        $file_path = get_user_meta(get_current_user_id(), FreelinguistUserHelper::META_KEY_NAME_TAX_FORM, true);
                        $file = null;
                        if (!empty($file_path)) {
                            $file = $user_dirname . '/' . $file_path;
                        }

                        ?>
                        <script type="text/javascript">
                            function download_tax_form_not_exist(datais) {
                                bootbox.alert(datais);
                            }
                        </script>
                    </div>
                    <h6><?php get_custom_string("Update tax form if you have major status changes"); ?></h6>
                    <div id="alert_message_model will-test-a"></div>
                    <div class="clear">
                        <label>
                            <?php get_custom_string("if you are a U.S. Person"); ?>:
                        </label>
                        <a href="#" id="1"
                           class="btn blue-btn update-btn email_the_form enhanced-text"
                        >
                            <?php get_custom_string("Email me my link to the W-9 form"); ?>
                        </a>
                    </div>
                    <div class="clear">
                        <label><?php get_custom_string("if you are not a U.S. Person"); ?>:</label> <a
                                href="#" id="2"
                                class="btn blue-btn update-btn email_the_form enhanced-text"><?php get_custom_string("Email me my link to the W-8BEN form"); ?></a>
                    </div>
                    <div class="">
                        <form name="uploadsignedform" id="uploadsignedform" action="<?php echo get_permalink(); ?>"
                              enctype="multipart/form-data"
                        >
                            <div class="upload-file enhanced-text upload-file-button" style="width: 30%;">
                                <label>
                                    <?php get_custom_string("Upload Signed Tax Form"); ?>
                                    <b><i class="file-icon large-text"></i> </b>
                                </label>
                                <input type="file" name="files[]" id="uploadSignedTaxForm"
                                       class="signedfilesdata files-data btn blue-btn update-btn   enhanced-text">
                            </div>
                        </form>
                        <br>
                        <?php
                        if (file_exists($file)) {
                            $tax_url = add_query_arg(  ['action'=> 'download_tax_form','lang'=>$lang], freeling_links('wallet_url'));
                            ?>
                            <a href="<?= $tax_url ?>"
                               class="download-taxform download-taxform"
                            >
                                <?php get_custom_string("Download tax form"); ?>
                            </a>

                        <?php } else { ?>
                            <a href="javascript:;" class="download-taxform download-taxform"
                               onclick="download_tax_form_not_exist('<?php get_custom_string("No signed tax form has been uploaded"); ?>');"
                            >
                                <?php get_custom_string("Download tax form"); ?>
                            </a>

                        <?php }
                        ?>
                    </div>
                </div>
            </div>
            <!-- END: TAX Form -->
        </div>
    </div>
</section>
