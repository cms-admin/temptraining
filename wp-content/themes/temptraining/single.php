<?php get_header(); ?>

<div class="container">
	<section class="single-page">
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();

				/*
				 * Include the post format-specific template for the content. If you want to
				 * use this in a child theme, then include a file called called content-___.php
				 * (where ___ is the post format) and that will be used instead.
				 */
				get_template_part( 'content', get_post_format() );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

				$cats = get_the_category();
				$is_camps = false;
				foreach ($cats as $key => $value) {
					if($value->slug == 'camps'){
						$is_camps = true;
					}
				}

				if($is_camps == false){
					// Previous/next post navigation.
					the_post_navigation( array(
						'next_text' => '<span class="nav-item">' .
							'<span class="post-title">%title</span> <span class="nav-icon"><i class="icon-right-arrow"></i></span></span>',
						'prev_text' => '<span class="nav-item"><span class="nav-icon"><i class="icon-left-arrow"></i></span> ' .
							'<span class="post-title">%title</span></span>',
					) );
				}


		// End the loop.
		endwhile;
		?>
	</section>
</div>

<?php get_footer(); ?>
