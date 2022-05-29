<?php get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

			<span class="bold-and-blocking larger-text"><?php _e( 'Tag Archive: ', 'html5blank' ); echo single_tag_title('', false); ?></span>

			<?php get_template_part('loop'); ?>

			<?php get_template_part('pagination'); ?>

		</section>
		<!-- /section -->
	</main>

<?php get_sidebar(); ?>

<?php get_footer();
