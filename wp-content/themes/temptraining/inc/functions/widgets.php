<?php
function temptraining_get_widget_data_for($sidebar_name) {
	global $wp_registered_sidebars, $wp_registered_widgets;

	// Holds the final data to return
	$output = array();

	// Loop over all of the registered sidebars looking for the one with the same name as $sidebar_name
	$sidebar_id = false;
	foreach( $wp_registered_sidebars as $sidebar ) {
		if( $sidebar['id'] == $sidebar_name ) {
			// We now have the Sidebar ID, we can stop our loop and continue.
			$sidebar_id = $sidebar['id'];
			break;
		}
	}

	if( !$sidebar_id ) {
		// There is no sidebar registered with the name provided.
		return $output;
	}

	// A nested array in the format $sidebar_id => array( 'widget_id-1', 'widget_id-2' ... );
	$sidebars_widgets = wp_get_sidebars_widgets();
	$widget_ids = $sidebars_widgets[$sidebar_id];

	if( !$widget_ids ) {
		// Without proper widget_ids we can't continue.
		return array();
	}

	// Loop over each widget_id so we can fetch the data out of the wp_options table.
	foreach( $widget_ids as $id ) {
		// The name of the option in the database is the name of the widget class.
		$option_name = $wp_registered_widgets[$id]['callback'][0]->option_name;

		// Widget data is stored as an associative array. To get the right data we need to get the right key which is stored in $wp_registered_widgets
		$key = $wp_registered_widgets[$id]['params'][0]['number'];

		$widget_data = get_option($option_name);

		// Add the widget data on to the end of the output array.
		$output[] = (object) $widget_data[$key];
	}

	return $output;
}

function temptraining_widgets_init() {
  register_sidebar(array(
    'name'  => __( 'Prices table', 'temptraining' ),
    'id'    => 'prices-table',
    'description' => __( 'Display the site prices', 'temptraining' ),
    'class'       => 'widget-prices',
  ));

  register_sidebar(array(
    'name'  => __( 'News left', 'temptraining' ),
    'id'    => 'news-left',
    'description' => __( 'Widgets for home page left side', 'temptraining' ),
    'class'       => 'home-leftside',
    'before_widget' => '<aside id="%1$s" class="%2$s">',
    'after_widget'  => '</aside>',
  ));

  register_sidebar(array(
    'name'  => __( 'News right', 'temptraining' ),
    'id'    => 'news-right',
    'description' => __( 'Widgets for home page right side', 'temptraining' ),
    'class'       => 'home-rightside',
    'before_widget' => '<aside id="%1$s" class="%2$s">',
    'after_widget'  => '</aside>',
  ));

  register_sidebar(array(
    'name'  => __( 'Main bottom left', 'temptraining' ),
    'id'    => 'mb-left',
    'before_widget' => '<aside id="%1$s" class="widget-mb__left %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
  ));

  register_sidebar(array(
    'name'  => __( 'Main bottom right', 'temptraining' ),
    'id'    => 'mb-right',
    'before_widget' => '<aside id="%1$s" class="widget-mb__right %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
  ));

  register_sidebar(array(
    'name'  => __( 'Footer', 'temptraining' ),
    'id'    => 'footer',
    'before_widget' => '<aside id="%1$s" class="col-xm-6 %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
  ));

  register_sidebar( array(
		'name' 				   => __( 'Правая колонка', 'temptraining' ),
		'id' 					    => 'right_sidebar',
		'description'   	=> __( 'Показывает виджеты в правой колонке сайта.', 'temptraining' ),
		'before_widget' 	=> '<aside id="%1$s" class="col-xm-6 col-md-12 widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

  register_sidebar( array(
		'name' 				   => __( 'Размеры формы', 'temptraining' ),
		'id' 					    => 'uniform_sizes',
		'description'   	=> __( 'Мужские и женские размеры формы.', 'temptraining' ),
		'before_widget' 	=> '<aside id="%1$s" class="col-xm-6 widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

  register_sidebar( array(
    'name'           => __( 'Сборы - шапка', 'temptraining' ),
    'id'              => 'camps_top',
    'before_widget'   => '<aside id="%1$s" class="widget-camps__top %2$s"><div class="container">',
    'after_widget'    => '</div></aside>',
    'before_title'    => '<h3 class="widget-title"><span>',
    'after_title'     => '</span></h3>'
  ) );

  register_sidebar( array(
    'name'           => __( 'Сборы - подвал', 'temptraining' ),
    'id'              => 'camps_bottom',
    'before_widget'   => '<aside id="%1$s" class="widget-camps__bottom %2$s"><div class="container">',
    'after_widget'    => '</div></aside>',
    'before_title'    => '<h3 class="widget-title"><span>',
    'after_title'     => '</span></h3>'
  ) );

  register_sidebar( array(
    'name'           => __( 'Статьи', 'temptraining' ),
    'id'              => 'articles',
    'before_widget'   => '<div class="masonry-item"><article id="%1$s" class="hentry %2$s">',
    'after_widget'    => '</aside></div>',
    'before_title'    => '',
    'after_title'     => ''
  ) );

  register_sidebar( array(
    'name'           => __( 'Контакты', 'temptraining' ),
    'id'              => 'contacts',
    'before_widget'   => '<aside id="%1$s" class="widget-contacts %2$s">',
    'after_widget'    => '</aside>',
    'before_title'    => '<h3 class="widget-title"><span>',
    'after_title'     => '</span></h3>'
  ) );

  register_sidebar( array(
    'name'           => __( 'Лэндинг Планы', 'temptraining' ),
    'id'              => 'plans',
    'before_widget'   => '<div id="%1$s" class="plans-section %2$s">',
    'after_widget'    => '</div>',
    'before_title'    => '<h3 class="plans-section__title">',
    'after_title'     => '</h3>'
  ) );
}
add_action( 'widgets_init', 'temptraining_widgets_init' );
?>
