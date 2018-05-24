<?php

add_action( 'admin_enqueue_scripts', 'ercrm_admin_styles' );

function ercrm_admin_styles($hook){

  wp_enqueue_style( 'ttcli_menu', TT_CLIENT_CSS_URL . 'menu.css' );

  if (strpos($hook, 'ttcli') === FALSE) {
    return;
  }

  wp_enqueue_style( 'ttcli_grid', TT_CLIENT_CSS_URL . 'reflex.css' );
  wp_enqueue_style( 'ttcli_fonts', TT_CLIENT_CSS_URL . 'fonts.css' );
  wp_enqueue_style( 'ttcli_icons', TT_CLIENT_CSS_URL . 'flaticon.css' );
  wp_enqueue_style( 'ttcli_alerts', TT_CLIENT_CSS_URL . 'sweetalert.css' );
  wp_enqueue_style( 'ttcli_switchery', TT_CLIENT_CSS_URL . 'switchery.css' );
  wp_enqueue_style( 'ttcli_select', TT_CLIENT_CSS_URL . 'nice-select.css' );
  wp_enqueue_style( 'ttcli_datetime', TT_CLIENT_CSS_URL . 'jquery.datetimepicker.css' );
  wp_enqueue_style( 'ttcli_scroll', TT_CLIENT_CSS_URL . 'jquery.scrollbar.css' );
  wp_enqueue_style( 'ttcli_tooltips', TT_CLIENT_CSS_URL . 'tooltip.css' );
  wp_enqueue_style( 'ttcli_backend', TT_CLIENT_CSS_URL . 'backend.css', array(), '1.0.1' );

  wp_enqueue_script( 'ttcli_bootstrap', TT_CLIENT_JS_URL . 'bootstrap.js', ['jquery']);
  wp_enqueue_script( 'ttcli_alerts', TT_CLIENT_JS_URL . 'sweetalert.min.js', ['jquery']);
  wp_enqueue_script( 'ttcli_switchery', TT_CLIENT_JS_URL . 'switchery.js', ['jquery']);
  wp_enqueue_script( 'ttcli_select', TT_CLIENT_JS_URL . 'jquery.nice-select.js', ['jquery']);
  wp_enqueue_script( 'ttcli_datetime', TT_CLIENT_JS_URL . 'jquery.datetimepicker.full.js', ['jquery']);
  wp_enqueue_script( 'ttcli_validator', TT_CLIENT_JS_URL . 'jquery.form-validator.js', ['jquery']);
  wp_enqueue_script( 'ttcli_scroll', TT_CLIENT_JS_URL . 'jquery.scrollbar.js', ['jquery']);
  wp_enqueue_script( 'ttcli_tooltips', TT_CLIENT_JS_URL . 'Tooltip.js', ['jquery']);
  wp_enqueue_script( 'ttcli_list', TT_CLIENT_JS_URL . 'list.js', ['jquery']);
  wp_enqueue_script( 'ttcli_list_pagination', TT_CLIENT_JS_URL . 'list.pagination.min.js', ['jquery']);
  wp_enqueue_script( 'ttcli_admin', TT_CLIENT_JS_URL . 'admin.js', ['jquery']);

  wp_localize_script('ttcli_admin', 'ttcli_ajax',
    array(
      'url' => admin_url('admin-ajax.php')
    )
  );

  wp_enqueue_media();
}
