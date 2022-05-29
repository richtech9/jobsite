<!-- footer for translator(Linguist) -->
<?php
FreelinguistDebugFramework::note('footer-homepage-new');
/*
   * current-php-code 2020-Sep-30
   * input-sanitized : lang
   * current-wp-template:  footer
   */
$lang_url_is = FLInput::get('lang', 'en');
$languageis = $lang_url_is ? '?lang='.$lang_url_is : '';
?>


<footer id="footer-new">
	<div class="container">
		<div class="footer-left">

			<ul class="language-link">

				<?php if( $_SERVER['SERVER_NAME'] != 'www.wenren8.com'){ ?>				
					<?php dynamic_sidebar('language_st'); ?>
					
				<?php } ?>
			</ul>
			<ul class="footer-link">
				<li><a href="<?php echo get_site_url(); ?>/terms-of-service/<?php echo $languageis; ?>">TOS</a></li>
				<li><a href="<?php echo get_site_url(); ?>/privacy-peerok/<?php echo $languageis; ?>">Privacy</a></li>
				<!--li><a href="<?php echo get_site_url(); ?>/community/<?php echo $languageis; ?>">Community</a></li-->
				<li><a href="<?php echo get_site_url(); ?>/peerok-faq/<?php echo $languageis; ?>">FAQ</a></li>
			</ul>
		</div>
		<div class="payment">
			<!--suppress HtmlUnknownTarget -->
            <img src="<?php bloginfo('template_url'); ?>/images/payment-methods2.png">
		</div>
	</div>
</footer>
<style type="text/css">
.pricing_table_cont table{width: 100%;text-align: center;  margin-top: 25px; margin-bottom: 25px}
.pricing_table_cont table th{background: #FFF; color: #000; width: 33%; text-align: center; border: 1px solid #d8dada!important; font-weight: bold; padding: 16px 0px!important;}
.pricing_table_cont th{padding: 16px 0px!important;}
.pricing_table_cont table td{border: 1px solid #d8dada!important;  padding: 16px 0px!important; background: #2a8ac8; color: #FFF!important; font-weight: bold}
.pricing_table_cont table td:nth-child(3){background: #eaa332;}
.pricing_table_cont table th:nth-child(2){background: #2a8ac8; color: #FFF;}
.pricing_table_cont table th:nth-child(3){background: #eaa332; color: #FFF;}
.pricing_table_cont table p {margin: 0;}
.pricing_table_cont table td font{}
</style>
<?php wp_footer(); ?>
<script>
jQuery(function(){
	jQuery("#close").click(function(){
		jQuery('#notification').fadeOut('slow');
	});
});
</script>

<div status="display:none" class="copy-fee-modal-template">
<div  class="modal fee-modal" role="dialog" >
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body hirepopup">
                <h2 class="modal-title">
                    <span class="fee-modal-title"></span>
                </h2>
                <div class="row">
                    <div class="col-md-12">
                        <div class="fee-description">

                        </div>
                        <div class="table-responsive">
                            <table width="100%" class="table table-bordered">
                                <tbody class="insert-lines-here">

                                    <!-- this row is copied as many times as needed and filled in -->
                                    <tr class="freelinguist-fee-step-1 fee-line fee-liner-template">
                                        <td>
                                            <span class="fee-modal-line-title"></span>
                                            <span class="fee-modal-line-description"></span>
                                        </td>
                                        <td>
                                            <span class="fee-modal-line-content"></span>
                                        </td>
                                    </tr>

                                    <tr class="freelinguist-fee-step-1 fee-total">
                                        <th>
                                            <span class="fee-modal-total-title large-text">
                                                Total
                                            </span>
                                        </th>
                                        <td>
                                            <span class="fee-modal-total-content large-text"></span>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>

                            <table width="100%" class="table table-bordered">
                                <tbody>

                                    <tr class="freelinguist-fee-step-1">
                                        <th>
                                            <span class="fee-modal-pre-wallet-title">
                                               Current Wallet Balance
                                            </span>
                                        </th>
                                        <td>
                                            <span class="fee-modal-pre-wallet-content"></span>
                                        </td>
                                    </tr>

                                    <tr class="freelinguist-fee-step-1">
                                        <th>
                                            <span class="fee-modal-post-wallet-title">
                                               New Wallet Balance
                                            </span>
                                        </th>
                                        <td>
                                            <span class="fee-modal-post-wallet-content"></span>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                            <?php

                            ?>
                            <table width="100%" class="table table-bordered">
                                <tbody>
                                    <tr class="freelinguist-fee-step-1">
                                        <td colspan="2" style="text-align: center">
                                            <button class="btn-md btn btn-primary fee-modal-confirm"
                                                    data-dismiss="modal" aria-label="Close"
                                            >
                                                Pay &amp; Confirm
                                            </button>
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
</div> <!-- /.copy-fee-modal-template-->


