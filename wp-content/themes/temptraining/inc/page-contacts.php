<?php
$content = preg_split("/(\[special_page.*\])/iU", get_the_content());
?>
<div class="page-contacts">
  <div class="container">
    <div class="row">
      <div class="col-xm-6 col-md-6">
        <div class="map-container">
          <figure class="map-frame"></figure>
          <div id="map-contacts" class="map-content"></div>
          <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;coordorder=longlat" type="text/javascript"></script>
        	<script type="text/javascript">
            <?php echo 'ymaps.ready(function (){'.
                  'var myMap = new ymaps.Map("map-contacts",{'.
                    'center: ['. temptraining_opt('contacts_map') .'],'.
                    'zoom: 16,});'.
                  'var point = new ymaps.Placemark('.
                    '['. temptraining_opt('contacts_map') .'],'.
                    '{'.
                      'iconCaption: "'. temptraining_opt('contacts_marker_title') .'",'.
                      'balloonContentHeader: "'. temptraining_opt('contacts_marker_title') .'",'.
                      'balloonContentBody: "'. temptraining_opt('contacts_marker_desc') .'"'.
                    '},{'.
                      'preset: "islands#redDotIconWithCaption"'.
                    '}'.
                  ');'.
                  'myMap.geoObjects.add(point);'.
                  'myMap.behaviors.disable("scrollZoom");'.
                '}'.
              ');'; ?>
        	</script>
        </div>
      </div>
      <div class="col-xm-6 col-md-6">
        <header class="entry-header">
          <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </header>
        <div class="page-contacts__text">
          <?php echo $content[1]; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="page-contacts__widgets">
  <div class="container">
    <div class="row">
      <?php dynamic_sidebar('contacts'); ?>
    </div>
  </div>
</div>
