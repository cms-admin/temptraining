<?php
  $post_class = implode(" ", get_post_class("ticket", $post->ID));
  $post_date = temptraining_short_date(strtotime(get_post_meta($post->ID, 'event_date', true)));
  $post_meta = explode(PHP_EOL, $post->post_excerpt);
  $post_more = (get_post_meta($post->ID, 'event_more', true)) ? get_post_meta($post->ID, 'event_more', true) : __("Узнать больше", "temptraining");
?>
<article id="post-<?php the_ID(); ?>" class="<?php echo $post_class; ?>">
  <div class="row-flex">
    <div class="col-flex is-9 is-9-lap is-9-tab">
      <div class="post-main">
        <figure class="post-thumbnail">
          <?php echo the_post_thumbnail('temptraining-micro'); ?>
        </figure>
        <div class="post-date">
          <span><?php echo $post_date['m']; ?></span>
          <span class="day"><?php echo $post_date['d']; ?></span>
          <span><?php echo $post_date['y']; ?></span>
        </div>
        <div class="post-data">
          <h3 class="post-title"><?php the_title(); ?></h3>
          <div class="post-excerpt">
            <?php echo mb_substr(strip_tags($post->post_content), 0, 250, 'utf-8') . '...'; ?>
          </div>
        </div>
      </div>
      <div class="post-addons">
        <div class="post-addons__person">
          <i class="ion ion-person-stalker"></i> <span><?php echo $post_meta[2]; ?></span>
        </div>
        <div class="post-addons__period">
          <i class="ion ion-ios-timer-outline"></i> <span><?php echo $post_meta[0]; ?></span>
        </div>
      </div>
    </div>
    <div class="col-flex is-3 is-3-lap is-3-tab post-separate">
      <div class="post-place">
        <i class="ion ion-location"></i>
        <span><?php echo $post_meta[1]; ?></span>
      </div>
      <?php if (strpos(get_permalink($post), 'no-page') == false) : ?>
        <a class="post-button" href="<?php the_permalink(); ?>"><?php echo $post_more; ?></a>
      <?php else : ?>
        <span class="disable">
          <span class="post-button"><?php echo $post_more; ?></span>
        </span>
      <?php endif; ?>
    </div>
  </div>
</article>
