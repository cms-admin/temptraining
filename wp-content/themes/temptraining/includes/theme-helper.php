<?php

/**
 * Упрощенный перевод
 * @param  string $text строка для перевода
 * @return string - строка с переводом
 */
function tlang($text){
  return __($text, THEME_NAME);
}

/**
 * Получает опции темы
 * @param $key
 * @param bool $default
 * @return string
 */
function temptraining_opt($key, $default = FALSE)
{
  $default = (!$default) ? __('Not specified', 'temptraining') : $default ;

  return get_theme_mod($key, $default);
}

/**
 * Форматирует дату в человеческий формат
 * @param string $date
 * @param bool $short
 * @return string
 */
function temptraining_date_format($date, $short = FALSE){
  // формируем входную $date с учетом смещения
  $date = date('Y-m-d H:i:s', $date);

  // сегодняшняя дата
  $today     = date('Y-m-d', strtotime(date('Y-m-d H:i:s')));
  // вчерашняя дата
  $yesterday = date('Y-m-d', strtotime(date('Y-m-d H:i:s'))-(86400));

  // получаем значение даты и времени
  list($day, $time) = explode(' ', $date);
  switch( $day ) {
    // Если дата совпадает с сегодняшней
    case $today:
      $result = 'Сегодня';
      list($h, $m, $s)  = explode(':', $time);
      $result .= ' в '.$h.':'.$m;
      break;
    //Если дата совпадает со вчерашней
    case $yesterday:
      $result = 'Вчера';
      list($h, $m, $s)  = explode(':', $time);
      $result .= ' в '.$h.':'.$m;
      break;
    default: {
      // Разделяем отображение даты на составляющие
      list($y, $m, $d)  = explode('-', $day);
      // Замена числового обозначения месяца на словесное (склоненное в падеже)
      if ($short === FALSE){
        $m_arr = array(
          '01'=>'января',
          '02'=>'февраля',
          '03'=>'марта',
          '04'=>'апреля',
          '05'=>'мая',
          '06'=>'июня',
          '07'=>'июля',
          '08'=>'августа',
          '09'=>'сентября',
          '10'=>'октября',
          '11'=>'ноября',
          '12'=>'декабря',
        );
        $m = $m_arr[$m];
      }
      // Замена чисел 01 02 на 1 2
      $d = sprintf("%2d", $d);
      // Формирование окончательного результата
      if ($short === FALSE){
        $result = $d.' '.$m.' '.$y;
      } else {
        $result = $d.'.'.$m.'.'.$y;
      }
    }
  }
  return $result;
}

/**
 * Считает количество постов по тегу
 * @param string $tag
 * @return int
 */
function temptraining_count_by_tag($tag = "")
{
  if(stripos( $tag, '-' ) > 0){
    $tag_family = explode('-', $tag);
    $tag = $tag_family[1];
  }
  $posts = get_posts( array('numberposts' => -1, 'post_type'   => 'post', 'tag' => $tag,	'post_status' => 'publish') );

  $countReviews = count($posts);

  return $countReviews;

}