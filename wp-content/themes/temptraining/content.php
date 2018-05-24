<?php
/**
 * The default template for displaying content
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			endif;
		?>
	</header><!-- .entry-header -->

	<div class="entry-content" data-magnific-popup="" data-magnific-popup-options='{
    "type": "image",
    "delegate": "a.has-image",
    "mainClass": "mfp-fade",
    "gallery": {
      "enabled": true
    }
  }'>
		<?php
		/* translators: %s: Name of current post */
		the_content( sprintf(
			__( 'Продолжить чтение %s', 'temptraining' ),
			the_title( '<span class="screen-reader-text">', '</span>', false )
		) );

		wp_link_pages( array(
			'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Страница:', 'temptraining' ) . '</span>',
			'after'       => '</div>',
			'link_before' => '<span>',
			'link_after'  => '</span>',
			'pagelink'    => '<span class="screen-reader-text">' . __( 'Страница', 'temptraining' ) . ' </span>%',
			'separator'   => '<span class="screen-reader-text">, </span>',
		) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
    <?php temptraining_entry_meta(); ?>
		<?php edit_post_link( __( 'Править', 'temptraining' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
