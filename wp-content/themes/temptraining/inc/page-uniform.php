<?php
// Баннеры для страницы формы
$sliderArgs = array(
  'numberposts'	=> -1,
  'post_status' => 'publish',
  'post_type'   => 'banner',
  'order'       => 'ASC',
  'tax_query' => array(
    array(
      'taxonomy' => 'group',
      'field' => 'slug',
      'terms' => 'page-form',
      'include_children' => false
    ),
  ),
);
$sliderItems = get_posts($sliderArgs);
// Сама форма
$resultArgs = array(
  'numberposts'	=> -1,
  'post_status' => 'publish',
  'post_type'   => 'uniform',
  'orderby'     => 'date',
  'order'       => 'ASC',
);
$resultItems = get_posts($resultArgs);
?>
<div class="page-form">
  <div class="page-form__slider">
    <div class="container">
      <?php if ($sliderItems) : ?>
      <div class="row">
        <div class="col-sm-offset-2 col-xm-offset-3 col-sm-8 col-xm-6 nopad">
          <div class="owl-container">
            <div class="owl-carousel" data-owl-carousel="" data-navigation="nav2" data-owl-carousel-options='{
              "items": 1,
              "autoplay": true,
              "mouseDrag": false,
              "autoplayHoverPause": true,
              "autoplayTimeout": 3000,
              "autoplaySpeed": 3000,
              "navSpeed": 3000,
              <?php if (count($sliderItems) > 1) : ?>"loop": true,<?php endif; ?>
              "dots": true
              }'>
              <?php foreach ($sliderItems as $key=>$item) : ?>
                <?php $count = $key + 1; ?>
                <div class="page-form__slider-item">
                  <figure class="image" style="background-image: url(<?php echo get_the_post_thumbnail_url($item, 'original'); ?>)"></figure>
                  <div class="counter">
                    <?php if ($count < 10) : echo '0'.$count; else : echo $count; endif; ?>
                  </div>
                  <h3 class="title text-overflow"><?php echo $item->post_title; ?></h3>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if (count($sliderItems) > 1) : ?>
              <div class="page-form__slider-nav nav2">
                <div class="prev"><i class="icon-left-arrow"></i></div>
                <div class="next"><i class="icon-right-arrow"></i></div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>


  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="container">

      <header class="entry-header">
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
      </header><!-- .entry-header -->

      <?php if ($resultItems) : ?>
        <div class="entry-content">
          <div class="row">
            <?php $i = 1; ?>
            <?php foreach ($resultItems as $key=>$item) : ?>
              <?php
              preg_match( '/src="([^"]*)"/i', $item->post_content, $background ) ;
              $item->bg = (!empty($background[1])) ? $background[1] : false;
              $has_thumb = !empty(get_the_post_thumbnail_url($item, 'temptraining-big'));
              $col_xs = ($has_thumb) ? 8 : 12;
              $col_sm = ($has_thumb) ? 9 : 12;
              $col_md = ($has_thumb) ? 8 : 12;
              //var_dump($item);
              ?>
              <div class="col-sm-12 col-xm-6 col-md-6 <?php if($i == 1 || $i == 4) { echo 'light'; } else { echo 'dark'; } ?>">
                <article class="page-form__item" <?php if ($item->bg) echo 'style="background-image: url('.$item->bg.')"'?>>
                  <div class="row">
                    <?php if ($has_thumb) : ?>
                      <div class="col-xs-4 col-sm-3 col-md-4 pull-right">
                        <figure class="page-form__item-image">
                          <img src="<?php echo get_the_post_thumbnail_url($item, 'temptraining-big'); ?>" alt="<?php echo $item->post_title; ?>" />
                        </figure>
                      </div>
                    <?php endif; ?>
                    <div class="col-xs-<?php echo $col_xs; ?> col-sm-<?php echo $col_sm; ?> col-md-<?php echo $col_md; ?>">
                      <header class="page-form__item-head">
                        <h4 class="page-form__item-title"><?php echo $item->post_title; ?></h4>
                      </header>
                      <div class="page-form__item-text">
                        <?php echo trim(preg_replace('/\<[\/]?(table|tr|td|img)([^\>]*)\>/i', '', $item->post_content)); ?>
                      </div>
                      <?php if (!empty(get_post_meta($item->ID, 'price', true))) : ?>
                        <div class="page-form__item-price">
                          <?php echo __('Цена', 'temptraining') . ' ' . get_post_meta($item->ID, 'price', true); ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </article>
              </div>
              <?php if($i != 4) { $i++; } else {$i = 1;} ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </article>

  <div class="page-form__sizes">
    <div class="page-form__sizes-before"></div>
    <div class="container">
      <div class="row">
        <?php if (is_active_sidebar('uniform_sizes')) : ?>
          <?php dynamic_sidebar('uniform_sizes'); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
