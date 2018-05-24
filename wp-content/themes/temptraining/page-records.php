<?php
get_header();
$bg_image = get_the_post_thumbnail_url($post, 'original');
$custom = get_post_custom($post->ID);
?>

<div id="content" role="main" class="achievements">
	<?php if($bg_image) : ?>
	<div class="achievements-image" style="background-image: url(<?php echo $bg_image; ?>)">
		<div class="container">
			<h2><?php echo $post->post_title; ?></h2>
			<p><?php echo $custom['subtitle'][0]; ?></p>
		</div>
	</div>
	<?php endif; ?>

	<div class="achievements-content" >
		<div class="container">
			<?php echo $post->post_content; ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>
