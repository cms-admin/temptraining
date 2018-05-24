<?php $event = explode(PHP_EOL, $post->post_excerpt); ?>
<?php
$icon = array(
  0 => 'ion ion-android-calendar',
  1 => 'ion ion-android-navigate',
  2 => 'ion ion-android-bicycle',
);
$event_more = (get_post_meta($post->ID, 'event_more', true)) ? get_post_meta($post->ID, 'event_more', true) : __("Узнать больше", "temptraining");
?>
<div class="col-flex is-full">
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="post-header col-flex-center">
      <figure class="post-thumbnail"><?php echo the_post_thumbnail('temptraining-micro'); ?></figure>
      <h3 class="post-title"><?php the_title(); ?></h3>
    </header>

    <div class="post-body">
      <?php if(!empty($event)) : ?>
        <ul class="post-meta">
          <?php foreach ($event as $key => $value) : ?>
            <li class="post-meta__item row-flex-center">
              <i class="<?php echo $icon[$key]; ?>"></i> <span><?php echo $event[$key]; ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <div class="post-excerpt">
        <?php echo mb_substr(strip_tags($post->post_content), 0, 250, 'utf-8') . '...'; ?>
      </div>

      <div class="post-link col-flex-center">
        <figure class="post-link__icon">
          <i class="ion ion-more"></i>
        </figure>
        <a href="<?php the_permalink(); ?>"><?php echo $event_more; ?></a>
      </div>
    </div>



  </article>
</div>
