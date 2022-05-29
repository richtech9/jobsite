<?php

/*
   * current-php-code 2020-Sep-30
   * input-sanitized : lang,spage, text
   * current-wp-template:  footer
   */
FreelinguistDebugFramework::note('old footer');
?>
<footer class="footer freelinguist-footer-nudge">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<?php if(is_user_logged_in() && (xt_user_role() == "translator")) : ?>
						<?php wp_nav_menu (array('menu'=>'translator-inner-page-footer-menu')); ?>
				<?php else: ?>
					<?php wp_nav_menu (array('menu'=>'customer-inner-page-login-menu') ); ?>
				<?php endif; ?>				
			</div>
			<div class="col-md-6 text-right">
					<div class="addthis_inline_share_toolbox"></div>
					<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-582d312a8d799188"></script>
				<p class="copyright"><?php get_custom_string_by_id(get_option('footer_copyright_text'),672); ?>.</p>
			</div>
		</div>
	</div>
</footer>

<script>
jQuery(function(){
	jQuery("#close").click(function(){
		jQuery('#notification').fadeOut('slow');
	});
});
</script>


