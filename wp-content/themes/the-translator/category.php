<?php get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

			<span class="bold-and-blocking larger-text"><?php _e( 'Categories for ', 'html5blank' ); single_cat_title(); ?></span>

			<?php get_template_part('loop'); ?>

			<?php get_template_part('pagination'); ?>

		</section>
		<!-- /section -->
	</main>

<?php get_sidebar(); ?>

<?php get_footer();
