<?php

use TTClient\Client;
use TTClient\ClientYakassa;

if (is_dir('/var/www/u0249987/data/www/temptraining.ru/')){
  $SITE_DIR = '/var/www/u0249987/data/www/temptraining.ru/'; // for production
} else {
  $SITE_DIR = 'd:\\OSPanel\\domains\\temptraining.loc\\'; // for development
}
require_once($SITE_DIR . 'wp-content/plugins/tt-client/classes/Client.php');

// загрузка класса
$ttcli = Client::getInstance($SITE_DIR);

// предупрежение об автоматическом платеже
// $ttcli->cronRecurringBeforeNotify(2);

// предупреждение об окончании срока действия карты (автоплатежа по данной карте)
$ttcli->cronRecurringEndNotify(30);

// автоматические платежи
$ttcli->cronRecurringPays();

// автоматический платеж с просрочкой на 2 дня
$ttcli->cronRecurringPaysExpire(2);

if(!isset($admin_message)) $admin_message = '';

// напоминение клиентам о просрочке на 10 дней
$admin_message .= $ttcli->cronClientsPayExpired(10, true);

if (trim($admin_message)) $ttcli->sendAdminMessage('Напоминание о просроченных платежах клиентов', $admin_message);
