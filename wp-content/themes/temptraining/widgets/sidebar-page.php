<?php
$post->image = get_the_post_thumbnail_url($post, 'temptraining-normal');
$post->class = ($post->image) ? ' has-image' : '';
?>
<div class="widget sidebar-page<?php echo $post->class; ?>">
  <h3 class="widget-title">
    <a class="sidebar-page__content-link" href="<?php echo get_permalink($post); ?>"><?php echo $post->post_title; ?></a>
  </h3>
  <?php /*
  <?php if ($post->image) : ?>
    <figure class="sidebar-page__image hidden-xm">
      <a href="<?php echo get_permalink($post); ?>">
        <img src="<?php echo $post->image; ?>" alt="<?php echo $post->post_title; ?>" class="img-responsive" />
      </a>
    </figure>
  <?php endif; ?>
  <div class="sidebar-page__content">
    <figure class="sidebar-page__content-icon">
      <i class="icon-trophy"></i>
    </figure>
    <h4 class="sidebar-page__content-title">
      <a class="sidebar-page__content-link" href="<?php echo get_permalink($post); ?>"><?php echo $post->post_title; ?></a>
    </h4>
  </div>
  */ ?>
</div>
