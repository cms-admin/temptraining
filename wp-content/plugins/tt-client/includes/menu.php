<?php

/**
 * Меню для админки плагина
 */
add_action('admin_menu', 'tt_client_admin_menu_setup');

function tt_client_admin_menu_setup() {
  // Главная страница плагина
  add_menu_page(
    __('Кабинет клиента', 'tt-client'), // Page title
    __('Кабинет', 'tt-client'),         // Menu title
    'administrator',                    // Capability
    'ttcli',                        // Menu slug
    'tt_client_admin_index',            // Function
    'none',                             // Icon url
    9                                   // Position 
  );

  add_submenu_page(
    'ttcli',                            // Parent slug
    __('Спортивный клуб', 'tt-client'),            // Page title
    __('Клуб', 'tt-client'), // Menu title
    'administrator',                    // Capability
    'ttcli-club',                       // Menu slug
    'tt_client_admin_club'              // Function
  );

  add_submenu_page(
    'ttcli',                                // Parent slug
    __('Шаблоны', 'tt-client'),           // Page title
    __('Шаблоны писем', 'tt-client'),   // Menu title
    'administrator',                // Capability
    'ttcli-templates',                       // Menu slug
    'tt_client_admin_templates'            // Function
  );

  add_submenu_page(
    'ttcli',                        // Parent slug
    __('Настройки', 'tt-client'),          // Page title
    __('Настройки плагина', 'tt-client'),  // Menu title
    'administrator',                // Capability
    'ttcli-settings',                       // Menu slug
    'tt_client_admin_settings'            // Function
  );

  // регистрируем зависимые плагины
  if(!is_plugin_active('timber-library/timber.php')){
    add_action( 'admin_notices', 'tt_client_require_plugin_notice' );
  }
}

/**
 * Опопвещение о необходимости установить зависимые плагины
 * @return [type] [description]
 */
function tt_client_require_plugin_notice(){
  $context  = Timber::get_context();
  Timber::render('admin/notice.twig', $context );
}