<?php

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

/**
 * Установка ширины контента
 */
function temptraining_content_width() {
  $GLOBALS['content_width'] = apply_filters( 'temptraining_content_width', 768 );
}
add_action( 'after_setup_theme', 'temptraining_content_width', 0 );