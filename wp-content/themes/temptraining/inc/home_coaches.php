<?php
$args = array(
  'numberposts'	=> -1,
  'post_status' => 'publish',
  'post_type'   => 'coach',
  'orderby'     => 'date',
  'order'       => 'DESC',
);
$post = get_post(13);
$link = get_permalink($post);
$items = get_posts($args);
if ($items) :
?>
  <div id="home-coaches" class="home-coaches">
    <div class="container">
      <h3 class="home-coaches__title">
        <a href="<?php echo $link; ?>"><?php echo __('Coaches', 'temptraining'); ?></a>
      </h3>
      <div class="row">
        <div class="owl-container">
          <div class="home-coaches__nav nav2">
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
              "1199": {
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
          }'
          >
            <?php foreach ($items as $item) {
              $image = $spec = false;
              $image = array (
                'thumb' => get_the_post_thumbnail_url($item, 'temptraining-normal'),
                'prev' => get_the_post_thumbnail_url($item, 'temptraining-big'),
              );
              $spec = get_post_meta($item->ID, 'spec', true);
              $label = intval(get_post_meta($item->ID, 'label', true));
              ?>
              <div class="home-coaches__container">
                <div class="home-coaches__item">
                  <figure class="home-coaches__item-photo" style="background-image: url(<?php echo $image['thumb']; ?>)"></figure>
                  <?php if ($label): ?>
                    <div class="home-coaches__item-label" data-self-size>
                      <?php echo wp_get_attachment_image($label, 'coach-label'); ?>
                    </div>
                  <?php endif; ?>
                  <div class="home-coaches__item-data">
                    <div class="home-coaches__item-spec"><?php echo $spec; ?></div>
                    <h4 class="home-coaches__item-title"><?php echo $item->post_title; ?></h4>
                    <div class="home-coaches__item-text"><?php echo $item->post_content; ?></div>
                    <div class="home-coaches__item-link">
                      <?php if (temptraining_count_by_tag(urldecode($item->post_name)) > 0) : ?>
                        <a href="<?php echo site_url('tag/' . $item->post_name). '/' ; ?>">
                          <?php echo __('Followers reports', 'temptraining'); ?>
                          (<?php echo temptraining_count_by_tag(urldecode($item->post_name)) ?>)
                        </a>
                      <?php else : ?>
                        <span><?php echo __('Yet reports', 'temptraining'); ?></span>
                      <?php endif; ?>
                    </div>
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
