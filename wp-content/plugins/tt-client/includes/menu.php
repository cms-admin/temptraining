<?php

/**
 * Меню для админки плагина
 */
add_action('admin_menu', 'tt_client_admin_menu_setup');

function tt_client_admin_menu_setup() {
  // Главная страница плагина
  add_menu_page(
    plang('Кабинет клиента'),
    plang('Кабинет'),
    'administrator',
    'ttcli',
    'tt_client_admin_index',
    'none',
    9
  );

  add_submenu_page(
    'ttcli',
    plang('Спортивный клуб'),
    plang('Клуб'),
    'administrator',
    'ttcli-club',
    'tt_client_admin_club'
  );

  add_submenu_page(
    'ttcli',
    plang('Шаблоны'),
    plang('Шаблоны писем'),
    'administrator',
    'ttcli-templates',
    'tt_client_admin_templates'
  );

  add_submenu_page(
    'ttcli',
    plang('Сообщения'),
    plang('Обратная связь'),
    'administrator',
    'ttcli-feedback',
    'tt_client_admin_feedback'
  );

  add_submenu_page(
    'ttcli',
    plang('Настройки'),
    plang('Настройки плагина'),
    'administrator',
    'ttcli-settings',
    'tt_client_admin_settings'
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