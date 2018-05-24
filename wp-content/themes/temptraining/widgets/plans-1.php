
<?php
$args = array(
  'numberposts'	=> 12,
  'post_status' => 'publish',
  'post_type'   => 'coach',
  'order'       => 'DESC',
);
$items = get_posts($args);
?>
<div class="plans-screen plans-coaches fixed-bg">
  <figure class="triangle-up-left"></figure>
  <div class="container">
    <div class="row">
      <div class="col-xx-12 visible-xx plans-coaches__text" data-clone-xx="#coachesIntro"></div>
      <div class="col-sm-7 col-xm-8 col-md-6">
        <div class="round-list" data-view="#plansCoachesDetail">
          <figure class="plans-coaches__question wow rotateIn" data-wow-delay="0.2s"></figure>
          <?php if ($items) : ?>
          <ul data-radius="180">
            <?php foreach ($items as $item) :
              $image = array (
                'thumb' => get_the_post_thumbnail_url($item, 'temptraining-normal'),
                'prev' => get_the_post_thumbnail_url($item, 'temptraining-big'),
              );
            ?>
            <li class="round-list__item">
              <a href="#<?php echo $item->post_name; ?>">
                <figure class="round-list__item-image">
                  <figure style="background-image: url(<?php echo $image['thumb']; ?>)"></figure>
                </figure>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-xx-12 col-sm-5 col-xm-4 col-md-3">
        <div id="plansCoachesDetail" class="plans-coaches__detail round-view">
          <?php foreach ($items as $key=>$item) :
            $image = array (
              'thumb' => get_the_post_thumbnail_url($item, 'temptraining-normal'),
              'prev' => get_the_post_thumbnail_url($item, 'temptraining-big'),
            );
            $spec = get_post_meta($item->ID, 'spec', true);
          ?>
            <div id="<?php echo $item->post_name; ?>" class="round-view__item<?php if($key > 0) : ?> hidden<?php endif; ?>">
              <figure class="plans-coaches__detail-image" style="background-image: url(<?php echo $image['thumb']; ?>)"></figure>
              <div class="plans-coaches__detail-post">
                <?php echo $spec; ?>
              </div>
              <h3 class="plans-coaches__detail-name"><?php echo $item->post_title; ?></h3>
              <div class="plans-coaches__detail-text">
                <?php echo $item->post_content; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-sm-12 col-xm-12 col-md-3">
        <div id="coachesIntro" class="plans-coaches__text wow zoomInRight" data-wow-delay="0.2s">
          <?php echo $widget->text; ?>
        </div>
      </div>
    </div>
  </div>
  <figure class="triangle-down-right"></figure>
</div>
