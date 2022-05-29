<?php get_header(); ?>

<section class="middle-content">
	<div class="container">
		<div class="row">
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<span class="bold-and-blocking larger-text">
						<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
					</span>
					<?php the_content(); ?>
				</article>
			<?php endwhile; ?>
			<?php else: ?>
				<article>
					<span class="bold-and-blocking larger-text"><?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?></span>
				</article>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php ?>

<?php get_footer();
