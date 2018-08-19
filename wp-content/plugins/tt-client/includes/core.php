<?php

use TTClient\Client;
use TTClient\ClientYakassa;

if (!function_exists('plang')):
  /**
   * Упрощенный перевод для плагина
   * @param string $text
   * @param string $plugin
   * @return string
   */
  function plang($text, $plugin = 'tt-client')
  {
    return __($text, $plugin);
  }
endif;

/**
 * Автоматическое обновление плагина 
 */
function ttcli_check_update()
{
  if (!current_user_can('administrator') && !is_admin()) return false;

  require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

  $plugin = get_plugin_data( TT_CLIENT_DIR . 'tt-client.php' );

  $version = str_replace('.', '', $plugin['Version']);

  $last_update = TTClient\Client::getInstance()->getPluginOptions('last_update');

  if (function_exists("ttcli_update_$version") && $version != $last_update) {
    call_user_func( "ttcli_update_$version" );
  }
  
}

/**
 * Проверяет наличие колонки в админке
 */
function ttcli_check_column($table, $column)
{
  global $wpdb;

  $db_name = DB_NAME;

  $sql = "SELECT * "
        ."FROM information_schema.COLUMNS "
        ."WHERE "
        ."TABLE_SCHEMA = '{$db_name}' "
        ."AND TABLE_NAME = '{$wpdb->prefix}{$table}' "
        ."AND COLUMN_NAME = '{$column}'";

  return $wpdb->query($sql);
}

/**
 *  Проверяет наличие таблицы в базе данных сайта
 */
function ttcli_check_table($table = FALSE)
{
  if (!$table) return FALSE;

  global $wpdb;

  $table_name = $wpdb->prefix.$table;

  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    return FALSE;
  } else {
    return TRUE;
  }
}

/**
 * Версия 1.2.0
 */
function ttcli_update_120()
{
  // Обновление таблицы с клиентами
  global $wpdb;

  $pre = $wpdb->prefix;

  if(ttcli_check_column('clients', 'recurring_id') == 0){
    $wpdb->query("ALTER TABLE `{$pre}clients` ADD COLUMN `recurring_id` VARCHAR(50) NULL DEFAULT '0' AFTER `subscription`");
  }

  if(ttcli_check_column('clients', 'recurring_frequency') == 0){
    $wpdb->query("ALTER TABLE `{$pre}clients` ADD COLUMN `recurring_frequency` INT(11) NULL DEFAULT '0' AFTER `recurring_id`");
  }

  if(ttcli_check_column('clients', 'recurring_expiry') == 0){
    $wpdb->query("ALTER TABLE `{$pre}clients` ADD COLUMN `recurring_expiry` DATE NULL DEFAULT '0' AFTER `recurring_frequency`");
  }

  $options = Client::getInstance()->getPluginOptions();

  $options['last_update'] = 120;

  Client::getInstance()->saveOptions($options);
  
}

/**
 * Версия 1.2.2
 */
function ttcli_update_122()
{
  global $wpdb;
  
  $pre = $wpdb->prefix;

  if (ttcli_check_table('club') == FALSE){
    // создает таблицу `club`
    $wpdb->query("CREATE TABLE `{$pre}club` (`member_id` INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`member_id`))");
    // создает поле `member_id_name`
    $wpdb->query("ALTER TABLE `{$pre}club` ADD COLUMN `member_id_name` VARCHAR(50) NOT NULL DEFAULT '0' AFTER `member_id`");
    // создает поле `member_name`
    $wpdb->query("ALTER TABLE `{$pre}club` ADD COLUMN `member_name` VARCHAR(100) NOT NULL DEFAULT '0' AFTER `member_id_name`");
    // создает поле `ms_price`
    $wpdb->query("ALTER TABLE `{$pre}club` ADD COLUMN `ms_price` FLOAT(11,2) NULL DEFAULT '5000' AFTER `member_name`");
    // создает поле `ms_days`
    $wpdb->query("ALTER TABLE `{$pre}club` ADD COLUMN `ms_days` INT(4) NULL DEFAULT '365' AFTER `ms_price`");
    // создает поле `paid_until`
    $wpdb->query("ALTER TABLE `{$pre}club` ADD COLUMN `paid_until` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `ms_days`");
    // создает поле `client_id`
    $wpdb->query("ALTER TABLE `{$pre}club` ADD COLUMN `client_id` INT(11) NULL DEFAULT '0' AFTER `paid_until`");
  }

  if (ttcli_check_table('club_orders') == FALSE){
    // создает таблицу `club_orders`
    $wpdb->query("CREATE TABLE `{$pre}club_orders` (`id` INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))");
    // создает поле `merchant_id`
    $wpdb->query("ALTER TABLE `{$pre}club_orders` ADD COLUMN `merchant_id` VARCHAR(50) NOT NULL DEFAULT '0' AFTER `id`");
    // создает поле `merchant`
    $wpdb->query("ALTER TABLE `{$pre}club_orders` ADD COLUMN `merchant` VARCHAR(15) NOT NULL DEFAULT '0' AFTER `merchant_id`");
    // создает поле `type`
    $wpdb->query("ALTER TABLE `{$pre}club_orders` ADD COLUMN `type` VARCHAR(15) NOT NULL DEFAULT '0' AFTER `merchant`");
    // создает поле `member`
    $wpdb->query("ALTER TABLE `{$pre}club_orders` ADD COLUMN `member` INT(11) NOT NULL DEFAULT '0' AFTER `type`");
    // создает поле `date`
    $wpdb->query("ALTER TABLE `{$pre}club_orders` ADD COLUMN `date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `member`");
    // создает поле `amount`
    $wpdb->query("ALTER TABLE `{$pre}club_orders` ADD COLUMN `amount` FLOAT(11,2) NULL DEFAULT '0' AFTER `date`");
    // создает поле `period`
    $wpdb->query("ALTER TABLE `{$pre}club_orders` ADD COLUMN `period` VARCHAR(15) NOT NULL DEFAULT '0' AFTER `amount`");
  }

  $options = Client::getInstance()->getPluginOptions();

  if(ttcli_check_column('options', 'tt_client_templates') == 0){
    $templates = array(
      'last_update' => 122,

      'email_subject_order' => $options['email_subject_order'],
      'email_template_order' => $options['email_template_order'],

      'email_subject_order_auto' => $options['email_subject_order_auto'],
      'email_template_order_auto' => $options['email_template_order_auto'],

      'email_subject_preorder_auto' => $options['email_subject_preorder_auto'],
      'email_template_preorder_auto' => $options['email_template_preorder_auto'],
      
      'email_subject_recurring_end' => $options['email_subject_recurring_end'],
      'email_template_recurring_end' => $options['email_template_recurring_end'],
    );

    foreach ($templates as $key => $value) {
      if ($key != 'last_update') unset($options[$key]);
    }

    Client::getInstance()->saveOptions($templates, true);
  }

  $options['last_update'] = 122;
  
  Client::getInstance()->saveOptions($options);
  
}

function ttcli_num2word($num, $words) {
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

function ttcli_localdate($date, $short = FALSE){
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
 * Версия 1.3.6
 */
function ttcli_update_136()
{
  global $wpdb;

  $pre = $wpdb->prefix;

  $options = Client::getInstance()->getPluginOptions();

  $options['last_update'] = 136;

  $wpdb->query("ALTER TABLE `{$pre}clients` ADD COLUMN `can_recurring` TINYINT(1) NOT NULL DEFAULT '1' AFTER `can_pay`");
  
  Client::getInstance()->saveOptions($options);
}

/**
 * Обновление до версии 1.4.0
 *
 * @return void
 */
function ttcli_update_140()
{
  global $wpdb;
  $pre = $wpdb->prefix;
  
  $options = Client::getInstance()->getPluginOptions();
  $options['last_update'] = 140;

  $wpdb->query("ALTER TABLE `{$pre}orders` ADD COLUMN `status` VARCHAR(15) DEFAULT NULL AFTER `client_id`");

  Client::getInstance()->saveOptions($options);
}

/**
 * Добавляет новые Twig переменные и функции
 * @param $twig
 * @return mixed
 */
function ttcli_extend_twig($twig)
{
  // Функции для перевода темы на другие языки
  $twig->addFunction(new Twig_SimpleFunction ('plang', 'plang'));

  return $twig;
}
add_filter('timber/twig', 'ttcli_extend_twig');