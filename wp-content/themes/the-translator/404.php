<?php global $freelinguist_not_found_security_reason; ?>
<?php
/*
* current-php-code 2020-Nov-23
* input-sanitized :
* current-wp-template:  404 not found page
 * current-wp-top-template
*/
$data = explode('/', $_SERVER['REQUEST_URI']);
FreelinguistDebugFramework::note('404 stuff',$_SERVER['REQUEST_URI']);
get_header();
?>
<?php

if(!empty($data)){
	$count_data = count($data); 
	if($count_data > 2){
		$result_jobs = $data[$count_data-2];
	//	$result_parameter = $data[$count_data-1];
		if($result_jobs == 'jobs'){
			//$result_parameter = ltrim($result_parameter, ':');
			wp_redirect( freeling_links('job_listing_url').'&is=myJob' );
			exit;
		}
	}
}
?> 
	<section class="middle-content">
		<div class="container" style="alignment: center">

			<!-- article -->

<!--                code-notes part of the form-key logic. We dispay an additional message if called through that library-->
                <?php if (isset($freelinguist_not_found_security_reason) && !empty($freelinguist_not_found_security_reason)) { ?>
                    <span class="bold-and-blocking larger-text">Form could not be processed</span>
                    <span class="bold-and-blocking large-text"><?= $freelinguist_not_found_security_reason ?></span>
                <?php } else { ?>
                    <span class="bold-and-blocking larger-text"><?php _e( 'Page not found', 'html5blank' ); ?></span>
                <?php } ?>
                <span class="bold-and-blocking large-text">
					<a href="<?php echo home_url(); ?>"><?php _e( 'Return home?', 'html5blank' ); ?></a>
				</span>
				<img src="<?php echo get_template_directory_uri().'/images/404-Page-Not-Found.png'; ?>">
			<!-- /article -->

		</div>
		<!-- /section -->
	</section>

<?php //get_sidebar(); ?>

<?php get_footer();
