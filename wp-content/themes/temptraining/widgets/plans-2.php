<?php
  preg_match_all('|<li>(.*)<\/li>|i', $widget->text, $list);
  if (is_array($list[1])) :
?>
<div class="plans-screen plans-howto">
  <h3 class="plans-howto__title"><?php echo $widget->title; ?></h3>
  <div class="container">
    <div class="plans-howto__list">
      <?php foreach ($list[1] as $key => $value) : ?>
        <?php $order = $key + 1; ?>
        <?php $delay = ($key + 1) * 0.2; ?>
        <div class="plans-howto__list-item<?php if($key & 1) : ?> align-right<?php endif; ?>">
          <figure class="dot wow zoomIn bg-<?php echo $order; ?>" data-wow-delay="<?php echo $delay; ?>s"></figure>
          <figure class="image wow <?php if($key & 1) : ?> slideInLeft<?php else: ?>slideInRight<?php endif; ?>" data-wow-delay="<?php echo $delay; ?>s">
            <img src="<?php echo get_template_directory_uri() . '/images/howto-' . $order . '.png'; ?>" alt="howto-<?php echo $order; ?>" />
          </figure>
          <div class="text">
            <?php echo $value; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <figure class="triangle-down-right"></figure>
</div>
<?php endif; ?>
