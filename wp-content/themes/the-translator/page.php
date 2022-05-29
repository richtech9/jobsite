<?php get_header();
/*
* current-php-code 2021-Feb-11
* input-sanitized :
* current-wp-template:  basic container for default post templates
*/

?>


<section class="middle-content">
		<div class="container">

			<span class="bold-and-blocking larger-text"><?php the_title(); ?></span>

		<?php if (have_posts()): while (have_posts()) : the_post(); ?>

			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php the_content(); ?>

				<?php comments_template( '', true ); // Remove if you don't want comments ?>

				<br class="clear">

				<?php edit_post_link(); ?>

			</article>
			<!-- /article -->

		<?php endwhile; ?>

		<?php else: ?>

			<!-- article -->
			<article>

				<span class="bold-and-blocking large-text"><?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?></span>

			</article>
			<!-- /article -->

		<?php endif; ?>
		</div>
		</section>
		<!-- /section -->


<?php //get_sidebar(); ?>

<?php get_footer('homepagenew');
