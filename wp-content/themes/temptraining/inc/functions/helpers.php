<?php
/**
 * Custom Temptraining Sixteen template tags
 */

if ( ! function_exists( 'temptraining_entry_meta' ) ) :

function temptraining_entry_meta() {

	if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {
		temptraining_entry_date();
	}

	$format = get_post_format();
	if ( current_theme_supports( 'post-formats', $format ) ) {
		printf( '<span class="entry-format">%1$s<a href="%2$s">%3$s</a></span>',
			sprintf( '<span class="screen-reader-text">%s </span>', _x( 'Format', 'Used before post format.', 'temptraining' ) ),
			esc_url( get_post_format_link( $format ) ),
			get_post_format_string( $format )
		);
	}

	if ( 'post' === get_post_type() ) {
		temptraining_entry_taxonomies();
	}

	if ( ! is_singular() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( sprintf( __( 'Leave a comment<span class="screen-reader-text"> on %s</span>', 'temptraining' ), get_the_title() ) );
		echo '</span>';
	}
}
endif;

if (!function_exists('temptraining_entry_date')) :
/**
 * Prints HTML with date information for current post.
 */
function temptraining_entry_date() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		get_the_date(),
		esc_attr( get_the_modified_date( 'c' ) ),
		get_the_modified_date()
	);

	printf( '<span class="posted-on"><span class="screen-reader-text"><i class="icon-calendar-15"></i> %1$s: </span><a href="%2$s" rel="bookmark">%3$s</a></span>',
		_x( 'Опубликовано', 'Used before publish date.', 'temptraining' ),
		esc_url( get_permalink() ),
		$time_string
	);
}
endif;

if ( ! function_exists( 'temptraining_entry_taxonomies' ) ) :
/**
 * Prints HTML with category and tags for current post.
 */
function temptraining_entry_taxonomies() {
	$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'temptraining' ) );
	if ( $categories_list && temptraining_categorized_blog() ) {
		printf( '<span class="cat-links"><span class="screen-reader-text"><i class="icon-newspaper"></i> %1$s: </span>%2$s</span>',
			_x( 'Категория', 'Used before category names.', 'temptraining' ),
			$categories_list
		);
	}

	$tags_list = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'temptraining' ) );
	if ( $tags_list ) {
		printf( '<span class="tags-links"><span class="screen-reader-text"><i class="icon-referee-1"></i> %1$s: </span>%2$s</span>',
			_x( 'Тренер', 'Used before tag names.', 'temptraining' ),
			$tags_list
		);
	}
}
endif;

if ( ! function_exists( 'temptraining_post_thumbnail' ) ) :
/**
 * Displays an optional post thumbnail.
 */
function temptraining_post_thumbnail() {
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_singular() ) :
	?>

	<div class="post-thumbnail">
		<?php the_post_thumbnail(); ?>
	</div><!-- .post-thumbnail -->

	<?php else : ?>

	<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true">
		<?php the_post_thumbnail( 'post-thumbnail', array( 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
	</a>

	<?php endif; // End is_singular()
}
endif;

if ( ! function_exists( 'temptraining_excerpt' ) ) :
	/**
	 * Displays the optional excerpt.
	 */
	function temptraining_excerpt( $class = 'entry-summary' ) {
		$class = esc_attr( $class );

		if ( has_excerpt() || is_search() ) : ?>
			<div class="<?php echo $class; ?>">
				<?php the_excerpt(); ?>
			</div><!-- .<?php echo $class; ?> -->
		<?php endif;
	}
endif;

if ( ! function_exists( 'temptraining_excerpt_more' ) && ! is_admin() ) :
/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 */
function temptraining_excerpt_more() {
	$link = sprintf( '<div class="post-link"><a href="%1$s">%2$s</a></div>',
		esc_url( get_permalink( get_the_ID() ) ),
		/* translators: %s: Name of current post */
		__( 'Читать далее', 'temptraining' ) );
	return ' &hellip; ' . $link;
}
add_filter( 'excerpt_more', 'temptraining_excerpt_more' );
endif;

if ( ! function_exists( 'temptraining_categorized_blog' ) ) :
/**
 * Determines whether blog/site has more than one category.
 */
function temptraining_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'temptraining_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'temptraining_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so twentysixteen_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so twentysixteen_categorized_blog should return false.
		return false;
	}
}
endif;

/**
 * Flushes out the transients used in temptraining_categorized_blog().
 */
function temptraining_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'temptraining_categories' );
}
add_action( 'edit_category', 'temptraining_category_transient_flusher' );
add_action( 'save_post',     'temptraining_category_transient_flusher' );

if (!function_exists('temptraining_search_form')){
  function temptraining_search_form(){
    ob_start();
    require get_template_directory() . '/widgets/search.php';
    $form = ob_get_clean();

    return $form;
  }
  add_filter( 'get_search_form', 'temptraining_search_form', 100 );
}

if (!function_exists('temptraining_archive_title')):
	function temptraining_archive_title()
	{
		$tax = get_taxonomy( get_queried_object()->taxonomy );
		if ($tax->name == 'post_tag') :
			$title = sprintf( __( '%1$s: %2$s' ), __('Тренер', 'temptraining'), single_term_title( '', false ) );
		endif;

		return $title;
	}
endif;
add_filter( 'get_the_archive_title', 'temptraining_archive_title');

if(!function_exists('temptraining_date_format')){
	function temptraining_date_format($date, $short = false)
	{
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
}

if (!function_exists('temptraining_short_date')) {
  function temptraining_short_date($date){
    // формируем входную $date с учетом смещения
    $date = date('Y-m-d H:i:s', $date);

    // получаем значение даты и времени
    list($day, $time) = explode(' ', $date);

    // Разделяем отображение даты на составляющие
    list($y, $m, $d)  = explode('-', $day);

    // Замена числового обозначения месяца на словесное (склоненное в падеже)
    $m_arr = array('01'=>'янв','02'=>'фев','03'=>'мар','04'=>'апр','05'=>'мая','06'=>'июн','07'=>'июл','08'=>'авг','09'=>'сен','10'=>'окт','11'=>'ноя','12'=>'дек');
    $m = $m_arr[$m];

    // Замена чисел 01 02 на 1 2
    $d = sprintf("%2d", $d);
    // Формирование окончательного результата
    $result = array(
      'd' => $d,
      'm' => $m,
      'y' => $y,
    );

    return $result;
  }
}

if (!function_exists('temptraining_coaches_extend')) {
	function temptraining_coaches_extend($data){
		global $wpdb;
		foreach ($data as $key => $value) {
			$args = array(
				'numberposts'	=> 1,
				'post_status' => 'publish',
			  'post_type'   => 'coach',
				'meta_query'	=> array(
					array(
						'key'   => 'coach_id',
            'value' => $value->coach_id_name,
					)
				)
			);
			$extend = get_posts($args);

			if (!empty($extend)) {
				$data[$key]->extend = $extend[0];
			}

			$data[$key]->clients = $wpdb->get_results("SELECT client_id_name, client_id, client_name, client_tarif_id, client_tarif_name, tarif_cost, pay_date, can_pay, notify FROM " . $wpdb->prefix . "clients WHERE coach_id = '" . $value->coach_id . "' AND client_name != 'tt_admin' ORDER BY pay_date ASC");
		}

		return $data;
	}
}

/**
 *	Правильные окончания слов
 */
if (!function_exists('numToWord')) {
  function numToWord($num, $words) {
    $num = $num % 100;

    if ($num > 19) {
      $num = $num % 10;
    }
    switch ($num) {
      case 1: {
        return($words[0]);
      }
      case 2: case 3: case 4: {
        return($words[1]);
      }
      default: {
        return($words[2]);
      }
    }
   }
}
