<?php if ($group = temptraining_opt('slider_partners')) : ?>
<?php
  $args = array(
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
  $banners = get_posts($args);
?>
  <?php if ($banners) :?>
    <div id="home-partners" class="home-partners">
      <div class="container">
        <h3 class="widget-title"><?php echo __('Our partners', 'temptraining'); ?></h3>
        <div class="row">
          <div class="owl-container">
            <div class="owl-carousel" data-owl-carousel="" data-owl-carousel-options='{
              "responsive": {
                "0": {
                  "items":1
                },
                "480": {
                  "items":2
                },
                "768": {
                  "items":3
                }
              },
              "autoplay": true,
              "mouseDrag": false,
              "autoplayHoverPause": true,
              "autoplayTimeout": 3000,
              "autoplaySpeed": 3000,
              "navSpeed": 3000,
              <?php if (count($banners) > 3) : ?>"loop": true,<?php endif; ?>
              "dots": false
              }'>
              <?php foreach ($banners as $item) { ?>
                <?php $link = get_post_meta($item->ID, 'link', true); ?>
                <div class="home-partners__item">
                  <div class="vertical-container">
                    <div class="vertical-content">
                      <?php if ($link) : ?><a href="<?php echo $link; ?>" target="_blank" rel="noopener"><?php endif; ?>
                        <img src="<?php echo get_the_post_thumbnail_url($item, 'temptraining-partner'); ?>"
                             alt="<?php echo $item->post_title; ?>" />
                      <?php if ($link) : ?></a><?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>
