<?php get_header(); ?>
<div class="container">
	<div class="row">
		<div id="sidebar" class="col-xx-12 col-md-3 pull-right">
			<div class="sidebar-container" data-fixed-sidebar=""><?php get_sidebar( 'category' ); ?></div>
		</div>
		<div class="col-md-9">
			<div id="content" role="main">

				<?php if ( have_posts() ) : ?>
					<?php $cat_id = get_cat_ID(single_cat_title('', false)); ?>
					<?php $subcats = get_categories(array('child_of' => $cat_id)); ?>
					<header class="archive-header">
						<h1 class="archive-title"><?php echo single_cat_title('', false); ?></h1>
						<?php
							// Show an optional term description.
							$term_description = term_description();
							if ( ! empty( $term_description ) ) :
								printf( '<div class="taxonomy-description">%s</div>', $term_description );
							endif;
						?>

					</header><!-- .archive-header -->

					<nav class="archive-subcats row">
						<?php
						wp_nav_menu(array(
							'theme_location'	=> 'category_menu_top',
							'container' => false,
							'menu_id' => 'category-menu',
							'link_before'	=> '<span>',
							'link_after'	=> '</span>'
						));
						?>
					</nav>

					<div class="archive-thumbs">
						<div class="row loadmore-container">
							<div class="masonry-container js-masonry" data-masonry-options='{"itemSelector": ".masonry-item" }'>
								<?php
								// Start the Loop.
								while ( have_posts() ) : the_post();
								/*
								 * Include the post format-specific template for the content. If you want to
								 * use this in a child theme, then include a file called called content-___.php
								 * (where ___ is the post format) and that will be used instead.
								 */
								get_template_part( 'content-thumbs-big', get_post_format() );
								endwhile;

								?>
							</div>
							<?php require get_template_directory() . '/inc/loop_ajax.php'; ?>
						</div>
						<?php
						/*

						// Previous/next page navigation.
						the_posts_pagination( array(
							'prev_text'          => __( 'Previous page', 'twentysixteen' ),
							'next_text'          => __( 'Next page', 'twentysixteen' ),
							'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'temptraining' ) . ' </span>',
						)); */ ?>
					</div>
				<?php
				else :
					// If no content, include the "No posts found" template.
					get_template_part( 'content', 'none' );
				endif;
				?>
			</div><!-- #content -->
		</div>
	</div>
</div><!-- #primary -->

<?php get_footer(); ?>
