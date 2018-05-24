<?php
$widget = apply_filters('widget', $instance);
foreach ($GLOBALS['_wp_sidebars_widgets'] as $sidebar=>$widgets_list){
  if(is_array($widgets_list) && isset($widget['id'])){
    if(in_array($widget['id'], $widgets_list)) $is_sidebar = $sidebar;
  }
}
$widget['sidebar'] = (isset($is_sidebar)) ? $is_sidebar : false;

$args = [
  'cat'         => $widget['cat'],
  'post_type'   => 'post',
  'post_status' => 'publish',
  'order'       => 'ASC'
];
if($widget['sidebar'] == 'news-left'){
  $args['meta_key'] = 'event_date';
  $args['orderby'] = 'meta_value';

  $today = date('Ymd', time());

  $args['meta_query'] = [
    'key' => 'event_date',
    'value' => $today,
    'compare' => '>'
  ];
}
$args['numberposts'] = (!empty($widget['limit'])) ? intval($widget['limit']) : -1 ;
$posts = get_posts($args);
?>

<h3 class="widget-title">
  <?php
  if ($widget['sidebar'] == 'news-left') :
/*  $post = get_post(1768);
    $link = get_permalink($post); */
    $link = '/camps/';
  ?>
    <a href="<?php echo $link; ?>"><?php echo $widget['title']; ?></a>
  <?php else : ?>
    <?php echo $widget['title']; ?>
  <?php endif; ?>
</h3>
<?php if(!empty($widget['text'])) : ?>
<div class="widget-description">
  <?php echo $widget['text']; ?>
</div>
<?php endif; ?>

<?php if(is_array($posts)) : ?>
<div class="widget-content__list">
  <?php if($widget['sidebar'] == 'footer') : ?>
    <?php foreach ($posts as $item) : ?>
      <?php $item->image = get_the_post_thumbnail_url($item, 'thumbnail'); ?>
      <article class="post-item<?php if ($item->image) : ?> has-image<?php endif; ?>">
          <?php if ($item->image) : ?>
            <figure class="post-item__image">
              <img src="<?php echo $item->image; ?>" alt="<?php echo $item->post_title; ?>" class="img-responsive" />
            </figure>
          <?php endif; ?>
          <h4 class="post-item__title"><?php echo $item->post_title; ?></h4>
          <div class="post-item__link">
            <a href="<?php echo get_permalink($item); ?>"><?php echo __('Read more', 'temptraining'); ?></a>
          </div>
      </article>
    <?php endforeach; ?>

  <?php else : ?>

    <?php $del = 0.3; ?>
    <?php foreach ($posts as $item) { ?>

      <?php $item->image = get_the_post_thumbnail_url($item, 'thumbnail'); ?>

      <article class="post-item<?php if ($item->image) : ?> has-image<?php endif; ?>"
        <?php if ($widget['sidebar'] == 'news-left') : ?> data-animate='{"type": "fadeInLeft", "wait": "<?php echo $del; ?>"}'<?php endif; ?>>
        <figure class="post-item__image">
          <?php if ($widget['sidebar'] == 'news-left') : ?>
            <?php if (strpos(get_permalink($item), 'no-page') == false) : ?><a href="<?php echo get_permalink($item); ?>"><?php endif; ?>
              <img src="<?php echo $item->image; ?>" alt="<?php echo $item->post_title; ?>" class="img-responsive hidden-xx hidden-xm" />
              <img src="<?php echo get_the_post_thumbnail_url($item, 'temptraining-flag'); ?>" alt="<?php echo $item->post_title; ?>" class="img-responsive hidden-xx visible-xm" />
            <?php if (strpos(get_permalink($item), 'no-page') == false) : ?></a><?php endif; ?>
          <?php else : ?>
            <img src="<?php echo $item->image; ?>" alt="<?php echo $item->post_title; ?>" class="img-responsive" />
          <?php endif; ?>
        </figure>

        <h4 class="post-item__link">
          <?php if (strpos(get_permalink($item), 'no-page') == false) : ?><a href="<?php echo get_permalink($item); ?>"><?php endif; ?>
            <?php echo $item->post_title; ?>
          <?php if (strpos(get_permalink($item), 'no-page') == false) : ?></a><?php endif; ?>
        </h4>

        <?php if (trim($item->post_excerpt)) : ?>
        <div class="post-item__date">
          <?php if ($widget['sidebar'] == 'news-left') : ?>
            <img src="<?php echo $item->image; ?>" alt="<?php echo $item->post_title; ?>" class="img-responsive visible-xx" />
          <?php endif; ?>
          <?php $event = explode(PHP_EOL, $item->post_excerpt); ?>
          <?php echo $event[0]; ?>
          <?php if (isset($event[2])) : ?> <span><?php echo $event[2]; ?></span><?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="post-item__text">
          <?php
          if($widget['sidebar'] == 'news-left') :
            if (stripos( $item->post_content, '<!--more-->' ) > 0) {
              $echo = preg_grep("/(<!--more-->)/iU", explode("\n", $item->post_content));
              echo str_replace('<!--more-->', '', $echo[0]);
            } else {
              echo mb_substr(strip_tags($item->post_content), 0, 250, 'utf-8') . '...';
            }

          else :
            echo $item->post_content;
          endif;
          ?>
        </div>
      </article>
      <?php $del = $del + 0.1; ?>
    <?php } ?>
  <?php endif; ?>
</div>
<?php endif; ?>
