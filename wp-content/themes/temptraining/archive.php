<?php get_header(); ?>
<div class="container">
	<div id="content" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="archive-header">
				<?php
					the_archive_title( '<h1 class="archive-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' );
				?>
			</header><!-- .page-header -->

			<div class="archive-thumbs full-width">
				<div class="row loadmore-container">
					<div class="masonry-container js-masonry" data-masonry-options='{"itemSelector": ".masonry-item" }'>
						<?php
						// Start the Loop.
						while ( have_posts() ) : the_post();

							/*
							 * Include the Post-Format-specific template for the content.
							 * If you want to override this in a child theme, then include a file
							 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
							 */
							$grid_size = array();
							get_template_part('content-thumbs-big');
	 					endwhile; ?>
					</div>

					<?php require get_template_directory() . '/inc/loop_ajax.php'; ?>
				</div>
			</div>
		<?php else :
			get_template_part( 'content', 'none' );

		endif; ?>
	</div>
</div>

<?php get_footer(); ?>
