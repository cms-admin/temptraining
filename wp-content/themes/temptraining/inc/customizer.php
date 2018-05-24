<?php
function temptraining_customize_register( $wp_customize ) {
  // Блок настройки шапки.
  $wp_customize->add_section( 'temptraining_header_settings', array(
		'title'           => __( 'Header settings', 'temptraining' ),
		'description'     => __('Allows you to customize the title and contacts in the header', 'temptraining'),
		'priority'        => 30,
	) );

  // Опция: заголовок шапки
  $wp_customize->add_setting( 'theme_mods_temptraining[header_title]' , array(
    'capability'  => 'edit_theme_options',
    'type'        => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_header_title', array(
		'label'     => __( 'Header title', 'temptraining' ),
		'section'   => 'temptraining_header_settings',
		'settings'  => 'theme_mods_temptraining[header_title]',
    'type'      => 'textarea',
	  'priority'  => 10
	));

  // Опция: телефон в шапке
  $wp_customize->add_setting( 'theme_mods_temptraining[header_phone]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_header_phone', array(
		'label'     => __( 'Header phone', 'temptraining' ),
		'section'   => 'temptraining_header_settings',
		'settings'  => 'theme_mods_temptraining[header_phone]',
	  'priority'  => 11,
	));

  // Опция: email в шапке
  $wp_customize->add_setting( 'theme_mods_temptraining[header_email]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_header_email', array(
		'label'     => __( 'Header email', 'temptraining' ),
		'section'   => 'temptraining_header_settings',
		'settings'  => 'theme_mods_temptraining[header_email]',
	  'priority'  => 11,
    'type'      => 'textarea',
	));

  // Опция: адрес в шапке
  $wp_customize->add_setting( 'theme_mods_temptraining[header_address]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_header_address', array(
		'label'     => __( 'Header address', 'temptraining' ),
		'section'   => 'temptraining_header_settings',
		'settings'  => 'theme_mods_temptraining[header_address]',
	  'priority'  => 13,
    'type'      => 'textarea',
	));

  // Опция: режим работы в шапке
  $wp_customize->add_setting( 'theme_mods_temptraining[header_time]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_header_time', array(
		'label'     => __( 'Header work time', 'temptraining' ),
		'section'   => 'temptraining_header_settings',
		'settings'  => 'theme_mods_temptraining[header_time]',
	  'priority'  => 14,
    'type'      => 'textarea',
	));

  /**
   * Настройки слайдера
   */
  $wp_customize->add_section( 'temptraining_slider_settings', array(
 		'title'           => __( 'Slider settings', 'temptraining' ),
 		'description'     => __('Slider on homepage', 'temptraining'),
 		'priority'        => 31,
 	) );
  $wp_customize->add_setting( 'theme_mods_temptraining[slider_main]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );
  $wp_customize->add_setting( 'theme_mods_temptraining[slider_partners]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  // Получает массив с группами баннеров
  $slider_main_args = array(
    'taxonomy' => 'group',
    'hide_empty' => false
  );
  $slider_main_terms = get_terms($slider_main_args);
  foreach ($slider_main_terms as $key => $value) {
    $slider_main_choices[$value->term_id] = $value->name;
  }

  $wp_customize->add_control( 'temptraining_slider_main', array(
		'label'     => __( 'Main slider', 'temptraining' ),
		'section'   => 'temptraining_slider_settings',
		'settings'  => 'theme_mods_temptraining[slider_main]',
    'type'      => 'select',
    'choices'   => $slider_main_choices,
	  'priority'  => 10
	));

  $wp_customize->add_control( 'temptraining_slider_partners', array(
		'label'     => __( 'Partners slider', 'temptraining' ),
		'section'   => 'temptraining_slider_settings',
		'settings'  => 'theme_mods_temptraining[slider_partners]',
    'type'      => 'select',
    'choices'   => $slider_main_choices,
	  'priority'  => 11
	));

  // Блок настройки подвала.
  $wp_customize->add_section( 'temptraining_social_settings', array(
		'title'           => __( 'Footer settings', 'temptraining' ),
		'priority'        => 32,
	) );

  // Опция: social_facebook
  $wp_customize->add_setting( 'theme_mods_temptraining[social_facebook]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_social_facebook', array(
		'label'     => __( 'Facebook', 'temptraining' ),
		'section'   => 'temptraining_social_settings',
		'settings'  => 'theme_mods_temptraining[social_facebook]',
	  'priority'  => 10
	));

  // Опция: social_vk
  $wp_customize->add_setting( 'theme_mods_temptraining[social_vk]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_social_vk', array(
		'label'     => __( 'Vkontakte', 'temptraining' ),
		'section'   => 'temptraining_social_settings',
		'settings'  => 'theme_mods_temptraining[social_vk]',
	  'priority'  => 11
	));

  // Опция: social_twitter
  $wp_customize->add_setting( 'theme_mods_temptraining[social_twitter]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_social_twitter', array(
		'label'     => __( 'Twitter', 'temptraining' ),
		'section'   => 'temptraining_social_settings',
		'settings'  => 'theme_mods_temptraining[social_twitter]',
	  'priority'  => 11
	));

  // Опция: social_google
  $wp_customize->add_setting( 'theme_mods_temptraining[social_google]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_social_google', array(
		'label'     => __( 'Google+', 'temptraining' ),
		'section'   => 'temptraining_social_settings',
		'settings'  => 'theme_mods_temptraining[social_google]',
	  'priority'  => 11
	));

  // Опция: social_youtube
  $wp_customize->add_setting( 'theme_mods_temptraining[social_youtube]' , array(
    'capability'     => 'edit_theme_options',
    'type'           => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_social_youtube', array(
		'label'     => __( 'Youtube', 'temptraining' ),
		'section'   => 'temptraining_social_settings',
		'settings'  => 'theme_mods_temptraining[social_youtube]',
	  'priority'  => 11
	));

  // Блок настройки контактов.
  $wp_customize->add_section( 'temptraining_contact_settings', array(
		'title'           => __( 'Контакты', 'temptraining' ),
		'description'     => __('Месторасположение на карте', 'temptraining'),
		'priority'        => 60,
	) );

  // Опция: Координаты карты
  $wp_customize->add_setting( 'theme_mods_temptraining[contacts_map]' , array(
    'capability'  => 'edit_theme_options',
    'type'        => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_contacts_map', array(
		'label'     => __( 'Координаты карты', 'temptraining' ),
		'section'   => 'temptraining_contact_settings',
		'settings'  => 'theme_mods_temptraining[contacts_map]',
    'type'      => 'text',
	  'priority'  => 10
	));

  // Опция: Заголовок метки
  $wp_customize->add_setting( 'theme_mods_temptraining[contacts_marker_title]' , array(
    'capability'  => 'edit_theme_options',
    'type'        => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_contacts_marker_title', array(
		'label'     => __( 'Заголовок метки', 'temptraining' ),
		'section'   => 'temptraining_contact_settings',
		'settings'  => 'theme_mods_temptraining[contacts_marker_title]',
    'type'      => 'text',
	  'priority'  => 10
	));

  // Опция: Описание метки
  $wp_customize->add_setting( 'theme_mods_temptraining[contacts_marker_desc]' , array(
    'capability'  => 'edit_theme_options',
    'type'        => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_contacts_marker_desc', array(
		'label'     => __( 'Описание метки', 'temptraining' ),
		'section'   => 'temptraining_contact_settings',
		'settings'  => 'theme_mods_temptraining[contacts_marker_desc]',
    'type'      => 'textarea',
	  'priority'  => 10
	));

  // СТАТИЧЕСКАЯ ГЛАВНАЯ СТРАНИЦА
  $wp_customize->add_setting( 'theme_mods_temptraining[home_uniform]' , array(
    'capability'  => 'edit_theme_options',
    'type'        => 'option',
  ) );

  $wp_customize->add_control( 'temptraining_home_uniform', array(
		'label'     => __( 'Показывать форму на главной', 'temptraining' ),
		'section'   => 'static_front_page',
		'settings'  => 'theme_mods_temptraining[home_uniform]',
    'type'      => 'checkbox',
	  'priority'  => 10
	));

}
add_action( 'customize_register', 'temptraining_customize_register' );
