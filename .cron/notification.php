<?php

use TTClient\Client;
use TTClient\ClientYakassa;

define('SEP', DIRECTORY_SEPARATOR);

if (is_dir('/var/www/u0249987/data/www/temptraining.ru/')){
  $SITE_DIR = '/var/www/u0249987/data/www/temptraining.ru/'; // for production
} else {
  $SITE_DIR = realpath(__dir__ . SEP . '..' . SEP) . SEP; // for development
}

require_once($SITE_DIR . 'wp-content'.SEP.'plugins'.SEP.'tt-client'.SEP.'classes'.SEP.'Client.php');

// загрузка класса
$ttcli = Client::getInstance($SITE_DIR);

// напоминанием клиентам за 2 дня до оплаты
$ttcli->cronClientsPayNotyfy(2);

// напоминение клиентам о просрочке на 2 дня
if(!isset($admin_message)) $admin_message = '';
$admin_message .= $ttcli->cronClientsPayExpired(2);

// напоминение клиентам о просрочке на 10 дней
$admin_message .= $ttcli->cronClientsPayExpired(10);

// напоминение клиентам о просрочке на 20 дней
$admin_message .= $ttcli->cronClientsPayExpired(20);

// напоминение клиентам о просрочке на 30 дней
$admin_message .= $ttcli->cronClientsPayExpired(30);

if (trim($admin_message)) $ttcli->sendAdminMessage('Напоминание о просроченных платежах клиентов', $admin_message);

// напоминание об окончании членства в клубе
$ttcli->cronClubMembershipNotify();
