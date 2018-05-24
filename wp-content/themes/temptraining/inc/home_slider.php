<?php if ($group = temptraining_opt('slider_main')) : ?>
  <?php $args = array(
    'numberposts'	=> -1,
    'post_status' => 'publish',
    'post_type'   => 'banner',
    'order'       => 'ASC',
    'tax_query' => array(
      array(
        'taxonomy' => 'group',
        'field' => 'id',
        'terms' => $group,
        'include_children' => false
      ),
    ),
  );
  $banners = get_posts($args);?>

  <?php if ($banners) : ?>
    <div id="home-slider" class="home-slider">
      <div class="owl-container">
        <div class="owl-carousel" data-owl-carousel="" data-navigation="nav2" data-owl-carousel-options='{
          "items": 1,
          "autoplay": true,
          "mouseDrag": false,
          "autoplayHoverPause": true,
          "autoplayTimeout": 3000,
          "autoplaySpeed": 3000,
          "navSpeed": 3000,
          <?php if (count($banners) > 1) : ?>"loop": true,<?php endif; ?>
          "dots": false
        }'
        >
          <?php foreach ($banners as $item) { ?>
            <?php $image = $link = false; ?>
            <?php $image = get_the_post_thumbnail_url($item, 'temptraining-banner-image'); ?>
            <?php $link = get_post_meta($item->ID, 'link', true); ?>
            <div class="home-slider__item" style="background-image: url(<?php echo $image; ?>)">
              <div class="container">
                <div class="row">
                  <div class="col-sm-7 col-md-5">
                    <h1 class="home-slider__item-title"><?php echo $item->post_title; ?></h1>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-8 col-md-4">
                    <h3 class="home-slider__item-text">
                      <?php echo strip_tags($item->post_excerpt); ?>
                    </h3>
                    <?php if ($link) : ?>
                      <div class="home-slider__item-link">
                        <a href="<?php echo $link; ?>">
                          <?php echo __('Хочу тренера', 'temptraining'); ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php }  ?>
        </div>
        <?php if (count($banners) > 1) : ?>
          <div class="home-slider__nav nav2">
            <div class="prev"><i class="icon-left-arrow"></i></div>
            <div class="next"><i class="icon-right-arrow"></i></div>
          </div>
        <?php endif; ?>
      </div>
    </div>


  <?php endif; ?>

<?php endif; ?>
