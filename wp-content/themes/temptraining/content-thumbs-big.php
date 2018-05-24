<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 */
	$post_id = get_the_ID();
	$post_cats = wp_get_post_categories($post_id);
	foreach ($post_cats as $key => $value) {
		$post_cats[$key] = get_category($value);
	}

	$post_meta = wp_get_post_tags($post_id);
?>
<div class="masonry-item">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<a class="post-thumbnail" href="<?php the_permalink(); ?>"
			style="background-image: url(<?php echo the_post_thumbnail_url(); ?>)">
			<?php foreach ($post_cats as $item) : ?>
				<span class="cat-<?php echo $item->cat_ID; ?>"><?php echo $item->name; ?></span>
			<?php endforeach; ?>
		</a>

		<div class="post-container">
			<h3 class="post-title"><?php the_title(); ?></h3>
			<div class="post-text">
				<?php the_excerpt(); ?>
			</div>

			<div class="post-meta clearfix">
				<div class="post-meta__group">
					<?php if (count($post_meta) == 1 && !empty($post_meta[0]->name)) : ?>
						<a class="post-meta__group-item" href="<?php echo get_tag_link($post_meta[0]->term_id); ?>">
							<i class="icon-referee-1"></i>
							<span><?php echo __( 'Тренер', 'temptraining' ) . ': ' . $post_meta[0]->name; ?></span>
						</a>
					<?php endif; ?>
					<span class="post-meta__group-item">
						<i class="icon-calendar-15"></i>
						<span><?php echo get_the_date( 'd.m.Y', $post_id ); ?></span>
					</span>
				</div>
			</div>
		</div>
	</article><!-- #post-## -->
</div>
