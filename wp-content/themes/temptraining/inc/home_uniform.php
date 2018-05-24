<?php
$args = array(
  'numberposts'	=> -1,
  'post_status' => 'publish',
  'post_type'   => 'uniform',
  'orderby'     => 'date',
  'order'       => 'ASC',
);
$post = get_post(1895);
$link = get_permalink($post);
$items = get_posts($args);
if ($items) :
?>
<div id="home-uniform" class="home-uniform">
  <div class="container">
    <h3 class="home-uniform__title">
      <a href="<?php echo $link; ?>"><?php echo __('Uniform', 'temptraining'); ?></a>
    </h3>
    <div class="row">
      <div class="owl-container" data-magnific-popup="" data-magnific-popup-options='{
          "type": "image",
          "delegate": "a",
          "mainClass": "mfp-fade",
          "gallery": {
            "enabled": false
          }
        }'>
        <div class="home-uniform__nav nav2">
          <div class="prev"><i class="icon-left-arrow"></i></div>
          <div class="next"><i class="icon-right-arrow"></i></div>
        </div>
        <div class="owl-carousel" data-owl-carousel="" data-navigation="nav2" data-owl-carousel-options='{
          "responsive": {
            "0": {
              "items":1
            },
            "480": {
              "items":2
            },
            "768": {
              "items":3
            },
            "1024": {
              "items":4
            }
          },
          "autoplay": true,
          "mouseDrag": false,
          "autoplayHoverPause": true,
          "autoplayTimeout": 3000,
          "autoplaySpeed": 3000,
          "navSpeed": 3000,
          <?php if (count($items) > 1) : ?>"loop": true,<?php endif; ?>
          "dots": false
        }'>
          <?php foreach ($items as $item) { ?>
            <?php
              preg_match( '/src="([^"]*)"/i', $item->post_content, $background ) ;
              $item->bg = (!empty($background[1])) ? $background[1] : false;
              $has_thumb = !empty(get_the_post_thumbnail_url($item, 'temptraining-big'));
            ?>
            <div class="home-uniform__container">
              <div class="home-uniform__item">
                <figure class="home-uniform__item-image" <?php if ($item->bg) echo 'style="background-image: url('.$item->bg.')"'?>>
                  <a href="<?php echo get_the_post_thumbnail_url($item, 'temptraining-normal'); ?>">
                    <img src="<?php echo get_the_post_thumbnail_url($item, 'temptraining-big'); ?>" alt="<?php echo $item->post_title; ?>" />
                    <span class="home-uniform__item-zoom">
                      <span class="vertical-container">
                        <span class="vertical-content"><i class="icon-zoom-in"></i></span>
                      </span>
                    </span>
                  </a>
                </figure>
                <div class="home-uniform__item-title"><?php echo $item->post_title; ?></div>
                <?php /* if (!empty(get_post_meta($item->ID, 'price', true))) : ?>
                  <h4 class="home-uniform__item-price"><?php echo get_post_meta($item->ID, 'price', true); ?></h4>
                <?php endif; */ ?>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>
