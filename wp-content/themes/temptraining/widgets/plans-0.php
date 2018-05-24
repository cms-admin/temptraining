<?php
$prices = temptraining_get_widget_data_for('prices-table');
$prices_animation = array(
  3 => 'slideInLeft',
  2 => 'slideInDown',
  1 => 'slideInUp',
);
$prices_icons = array(
  3 => 'icon-triathlon',
  2 => 'icon-run',
  1 => 'icon-bike',
);
$prices_rel = array(
  3 => 'Триатлон',
  2 => 'Бег',
  1 => 'Велоспорт',
);
?>
<div class="plans-screen">
  <div class="container">
    <div class="row">
      <div class="col-sm-7 col-xm-6 col-md-7">
        <div class="plans-begin">
          <?php foreach ($prices as $item) : ?>
            <?php $title = explode('/', $item->title); ?>
            <button type="button" class="plans-begin__button price-<?php echo $title[0]; ?> wow <?php echo $prices_animation[$title[0]]; ?>"
              rel="<?php echo $title[1]; ?>"
              data-toggle="modal" data-target="<?php echo $item->link; ?>"
              data-price="<?php echo $item->sum; ?>" data-currency="<?php echo str_replace([' ', '.'], '', $item->currency); ?>">
              <span class="<?php echo $prices_icons[$title[0]]; ?>"></span>
            </button>
          <?php endforeach; ?>

          <figure class="wow zoomIn plans-begin__question" data-wow-delay="0.4s">
            <span id="plansBeginPrice" class="plans-begin__question-inner">
              <span class="default">?</span>
              <span class="price"></span>
            </span>
          </figure>
        </div>

      </div>
      <div class="col-sm-5 col-xm-6 col-md-5">
        <h3 class="plans-begin__title"><?php echo $widget->title; ?></h3>
        <div class="plans-begin__text">
          <?php echo $widget->text; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="plans-screen plans-begin2 fixed-bg">
  <div class="container">
    <div class="plans-begin2__content">
      <div class="row-flex">
        <div class="col-flex is-8 is-6-lap is-6-tab col-flex-center">
          <img class="wow slideInLeft img-responsive" src="<?php echo get_template_directory_uri(); ?>/images/begin2-left.png" alt="Наш сайт">
        </div>
        <div class="col-flex is-4 is-6-lap is-6-tab col-flex-center">
          <img class="wow slideInRight img-vertical" src="<?php echo get_template_directory_uri(); ?>/images/run-men.svg" alt="На старт">
        </div>
      </div>
    </div>
  </div>
  <figure class="triangle-down-right"></figure>
  <button type="button" class="plans-begin2__button" data-toggle="modal" data-target="#begin-modal">Начать тренироваться</button>
</div>
