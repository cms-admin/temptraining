<?php
// СПЕЦИАЛЬНЫЕ ТИПЫ ЗАПИСЕЙ: БАНЕРЫ, ТРЕНЕРЫ, ФОРМА
require get_template_directory() . '/inc/custom_posts/banner.php';
require get_template_directory() . '/inc/custom_posts/coach.php';
require get_template_directory() . '/inc/functions/post-types.php';
require get_template_directory() . '/inc/BFI_Thumb.php';

/**
 * Дополнительные стили для панели управления
 */
function temptraining_backend_styles()
{
  wp_enqueue_style( 'temptraining-backend-menu', get_template_directory_uri() . '/css/backend.css' );
}

add_action( 'admin_enqueue_scripts', 'temptraining_backend_styles' );

if ( ! function_exists( 'temptraining_setup' ) ) :
  function temptraining_setup() {
    load_theme_textdomain( 'temptraining', get_template_directory() . '/languages' );

    add_theme_support( 'automatic-feed-links' );

    add_theme_support( 'title-tag' );

    require get_template_directory() . '/inc/functions/menu.php';

    add_theme_support('html5', array(
  		'search-form',
  		'comment-form',
  		'comment-list',
  		'gallery',
  		'caption',
  	));

    add_theme_support('post-formats', array(
  		'сoach',
      'banner',
  		'uniform',
  		'starts',
      'video'
  	));

    add_theme_support('post-thumbnails');
    set_post_thumbnail_size( 720, 405, true );

    add_image_size( 'temptraining-flag', 120, 9999, false );
    add_image_size( 'temptraining-partner', 9999, 100, false );
    add_image_size( 'temptraining-micro', 120, 120, true );
    add_image_size( 'temptraining-small', 250, 250, true );
    add_image_size( 'temptraining-normal', 720, 405, false );
    add_image_size( 'temptraining-big', 1280, 720, false );
    add_image_size( 'temptraining-full', 1920, 1080, false );

    add_image_size('coach-label', 120, 9999, false);
  }
endif; // temptraining_setup
add_action( 'after_setup_theme', 'temptraining_setup' );

// Add Customizer functionality.
require get_template_directory() . '/inc/customizer.php';

/**
 * Установка ширины контента
 */
function temptraining_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'temptraining_content_width', 768 );
}
add_action( 'after_setup_theme', 'temptraining_content_width', 0 );

/**
 * Позиции для виджетов
 */
require get_template_directory() . '/inc/functions/widgets.php';
// Add Custom widgets.
require get_template_directory() . '/inc/temptraining_widgets.php';

/**
 * Стили и скрипты темы
 */
function temptraining_scripts()
{
  wp_enqueue_style( 'tt-bootstrap', get_template_directory_uri() . '/css/bootstrap.css', array(), null );
  wp_enqueue_style( 'tt-flex', get_template_directory_uri() . '/css/flex.css', array(), null );
  wp_enqueue_style( 'tt-fonts', get_template_directory_uri() . '/css/fonts.css', array('tt-bootstrap'), null );
  wp_enqueue_style( 'owl-carousel', get_template_directory_uri() . '/css/owl.carousel.css', array('tt-fonts'), null );
  wp_enqueue_style( 'owl-theme', get_template_directory_uri() . '/css/owl.theme.default.css', array('owl-carousel'), null );
  wp_enqueue_style( 'owl-transitions', get_template_directory_uri() . '/css/owl.transitions.css', array('owl-theme'), null );
  wp_enqueue_style( 'magnific-popup', get_template_directory_uri() . '/css/magnific-popup.css', array(), null );
  wp_enqueue_style( 'nice-select', get_template_directory_uri() . '/css/nice-select.css', array(), null );
  wp_enqueue_style( 'animate', get_template_directory_uri() . '/css/animate.css', array('tt-fonts'), null );
  wp_enqueue_style( 'switchery', get_template_directory_uri() . '/css/switchery.css');
  wp_enqueue_style( 'tt-style', get_stylesheet_uri(), [], '1.1.4' );
  wp_enqueue_style( 'tt-responsive', get_template_directory_uri() . '/css/responsive.css', array('tt-style'), null );

  wp_deregister_script( 'jquery' );
  wp_register_script( 'jquery', get_template_directory_uri() . '/js/jquery-1.12.3.min.js', false, NULL, true );
  wp_enqueue_script( 'jquery' );

  wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap.js', array('jquery'), '3.3.4', true );
  wp_enqueue_script( 'jquery-ui', get_template_directory_uri() . '/js/jquery-ui.min.js', array('bootstrap'), '1.12.0', true );
  wp_enqueue_script( 'bootstrap-trans', get_template_directory_uri() . '/js/bootstrap-transition.js', array('bootstrap'), '2.3.2', true );
  wp_enqueue_script( 'scroll-reveal', get_template_directory_uri() . '/js/scrollReveal.js', array('bootstrap-trans'), '2015', true );
  wp_enqueue_script( 'waypoints', get_template_directory_uri() . '/js/jquery.waypoints.min.js', array('bootstrap-trans'), '4.0.1', true );
  wp_enqueue_script( 'owl-carousel', get_template_directory_uri() . '/js/owl.carousel.js', array('scroll-reveal'), '2.0.0', true );
  wp_enqueue_script( 'magnific-popup', get_template_directory_uri() . '/js/jquery.magnific-popup.js', array('owl-carousel'), '2.0.0', true );
  wp_enqueue_script( 'nice-select', get_template_directory_uri() . '/js/jquery.nice-select.js', array('jquery'), null, true );
  wp_enqueue_script( 'switchery', get_template_directory_uri() . '/js/switchery.js', array('jquery'), null, true );
  wp_enqueue_script( 'masonry', get_template_directory_uri() . '/js/masonry.pkgd.min.js', array(), '4.1.1', true );
  wp_enqueue_script('WOW', get_template_directory_uri() . '/js/wow.js', array('jquery'), '1.1.0', true);
  wp_enqueue_script('isMobile', get_template_directory_uri() . '/js/isMobile.js', array('jquery'), '0.4.0', true);
  wp_enqueue_script(
    'tt-scripts',
    get_template_directory_uri() . '/js/scripts.js',
    array('scroll-reveal', 'owl-carousel', 'magnific-popup'),
    '',
    true
  );

  global $post;
  $cats = wp_get_post_categories($post->ID);

  if ((has_shortcode($post->post_content, 'relap') || in_array(3, $cats) || in_array(30, $cats)) && false == is_home()){
    wp_enqueue_script('relap_io', 'https://relap.io/api/v6/head.js?token=Fm4WCIwIIUK_5BcS', [], false, false);
    add_filter('script_loader_tag', 'add_async_attribute', 10, 2);
  }

  if (in_array(3, $cats) || in_array(30, $cats)){
    $post->post_content = $post->post_content . '<script id="kG-QzAzMtf4ELRMT">if (window.relap) window.relap.ar(\'kG-QzAzMtf4ELRMT\');</script>';
  }
}
add_action( 'wp_enqueue_scripts', 'temptraining_scripts' );

/**
 * Функия для ассинхронной загрузки скриптов
 */
function add_async_attribute($tag, $handle)
{
  if ( 'relap_io' !== $handle )
    return $tag;
  return str_replace( ' src', ' async="async" src', $tag );
}

/**
 * Получает опции темы
 */
function temptraining_opt($key, $default = FALSE)
{
  $default = (!$default) ? __('Not specified', 'temptraining') : $default ;

  return get_theme_mod($key, $default);
}

function temptraining_sp2br($string)
{
  $string_arr = explode(' ', $string);

  $output = '';

  foreach ($string_arr as $word) {
    $output .= '<span>' . $word . '</span>';
  }

  return $output;
}

function temptraining_br2span($string)
{
  $string_arr = explode('<br />', nl2br($string));

  $output = '';

  foreach ($string_arr as $word) {
    $output .= '<span>' . trim($word) . '</span>';
  }

  return $output;
}

function temptraining_count_by_tag($tag = ""){
  if(stripos( $tag, '-' ) > 0){
    $tag_family = explode('-', $tag);
    $tag = $tag_family[1];
  }
	$posts = get_posts( array('numberposts' => -1, 'post_type'   => 'post', 'tag' => $tag,	'post_status' => 'publish') );

	$countReviews = count($posts);

	return $countReviews;

}

// Add Shortcode
require get_template_directory() . '/inc/functions/shortcodes.php';

/**
 * Форматирует дату в человеческий формат
 * @param type $date
 * @param type $short
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
 * AJAX Load More
 * @link http://www.billerickson.net/infinite-scroll-in-wordpress
 */
function temptraining_ajax_load_more() {
  $args = unserialize(stripslashes($_POST['query']));
	$args['paged'] = $_POST['page'] + 1; // следующая страница
	$args['post_status'] = 'publish';
	$q = new WP_Query($args);

  ob_start();
	if( $q->have_posts() ):
    while($q->have_posts()): $q->the_post();
    get_template_part( 'content-thumbs-big', $q->get_post_format() );
    endwhile;
	  $data = ob_get_clean();
    wp_send_json_success( $data );
  endif;

  wp_die();
}
add_action( 'wp_ajax_loadmore', 'temptraining_ajax_load_more' );
add_action( 'wp_ajax_nopriv_loadmore', 'temptraining_ajax_load_more' );

require get_template_directory() . '/inc/functions/helpers.php';

if (!function_exists('_wp_render_title_tag')){
  add_action( 'wp_head', 'tt_render_title' );

  function tt_render_title() {

    echo '<title>' . wp_title( '|', true, 'right' ) . '</title>';

  }

  add_filter( 'wp_title', 'tt_filter_wp_title' );

  if (!function_exists('_wp_render_title_tag')){

    function tt_filter_wp_title( $title ) {

      global $page, $paged;

      // Get the Site Name
      $site_name = get_bloginfo( 'name' );

      // Get the Site Description
      $site_description = get_bloginfo( 'description' );

      $filtered_title = '';

      // For Homepage or Frontpage
      if(  is_home() || is_front_page() ) {

        $filtered_title .= $site_name;

        if ( !empty( $site_description ) )  {
          $filtered_title .= ' &#124; '. $site_description;
        }
      } elseif( is_feed() ) {
        $filtered_title = '';
      } else{

        $filtered_title = $title . $site_name;

      }

      // Add a page number if necessary:
      if( $paged >= 2 || $page >= 2 ) {
        $filtered_title .= ' &#124; ' . sprintf( __( 'Page %s', 'spacious' ), max( $paged, $page ) );
      }

      // Return the modified title
      return $filtered_title;

    }
  }
}

/**
 * Упрощенный перевод
 * @param  string $text строка для перевода
 * @return string - строка с переводом
 */
function tlang($text){
  return __($text, 'temptraining');
}

remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'feed_links_extra', 3 );
