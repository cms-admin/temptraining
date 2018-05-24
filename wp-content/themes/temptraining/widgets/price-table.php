<?php
$widget = apply_filters('widget', $instance);
if (stripos($widget['title'], '/' )){
  $widget['title'] = explode('/', $widget['title']);
  $del = 0.8 - intval($widget['title'][0]) * 0.2;
} else {
  $del = 0.2;
}
?>
<div class="col-sm-4 home-prices__container" data-sr="enter bottom, wait <?php echo $del; ?>s">
  <div class="home-prices__item">
    <h4 class="home-prices__item-tile">
      <?php if (is_array($widget['title'])) : ?>
        <span class="number"><?php echo $widget['title'][0]; ?></span>
        <span class="separate">/</span>
        <span class="text"><?php echo $widget['title'][1]; ?></span>
      <?php else : ?>
        <?php echo $widget['title']; ?>
      <?php endif; ?>
    </h4>
    <div class="home-prices__item-price">
      <strong><?php echo $widget['sum']; ?></strong>
      <span><?php echo $widget['currency']; ?></span>
    </div>
    <div class="home-prices__item-text">
      <?php echo $widget['text']; ?>
    </div>
    <?php if (!empty($widget['link'])) : ?>
      <div class="home-prices__item-link">
        <a data-toggle="modal" data-target="<?php echo $widget['link']; ?>" href="#" data-rel="<?php echo $widget['title'][1]; ?>"><?php echo __('Begin', 'temptraining'); ?></a>
      </div>
    <?php endif; ?>
  </div>
</div>
