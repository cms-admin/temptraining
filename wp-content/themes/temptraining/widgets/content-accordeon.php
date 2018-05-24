<?php
$widget = apply_filters('widget', $instance);
foreach ($GLOBALS['_wp_sidebars_widgets'] as $sidebar=>$widgets_list){
  if(is_array($widgets_list) && isset($widget['id'])){
    if(in_array($widget['id'], $widgets_list)) $is_sidebar = $sidebar;
  }
}
$widget['sidebar'] = (isset($is_sidebar)) ? $is_sidebar : false;

$args = array(
  'cat'         => $widget['cat'],
  'post_type'   => 'post',
  'post_status' => 'publish',
  'order'       => 'DESC',
);
$args['numberposts'] = (!empty($widget['limit'])) ? intval($widget['limit']) : -1 ;
$posts = get_posts($args);
?>
<h3 class="widget-title">
  <?php
  if ($widget['sidebar'] == 'news-right') :
    $link = get_category_link('30');
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
  <div class="widget-content__accordeon" data-toggle="accordion" data-accordion-options='{"heightStyle": "content"}'>
    <?php foreach ($posts as $item) { ?>
      <?php $item->image = get_the_post_thumbnail_url($item, 'temptraining-flag'); ?>
      <?php $item->image_orig = get_the_post_thumbnail_url($item, 'original'); ?>
      <h3 class="post-item__title clearfix">
        <span class="post-item__title-text pull-left"><?php echo $item->post_title; ?></span>
        <span class="post-item__title-text meta-date pull-right"><?php echo date('d.m.Y', strtotime($item->post_date)); ?></span>
      </h3>
      <div class="post-item__body clearfix">
        <div class="post-item__text<?php if ($item->image) : ?> has-image<?php endif; ?>">
          <?php if ($item->image) : ?>
            <figure class="post-item__image">
              <a href="<?php echo get_permalink($item); ?>">
                <span class="hidden-xx">
                  <?php echo get_the_post_thumbnail($item, [120, 120, 'bfi_thumb' => true]); ?>
                </span>
                <span class="visible-xx">
                  <?php echo get_the_post_thumbnail($item, [360, 360, 'bfi_thumb' => true]); ?>
                </span>
              </a>
            </figure>
          <?php endif; ?>
          <?php echo mb_substr(strip_tags($item->post_content), 0, 250, 'utf-8') . '...'; ?>
          <div class="post-item__link">
            <a href="<?php echo get_permalink($item); ?>"><?php echo __('Read more', 'temptraining'); ?></a>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
<?php endif; ?>
