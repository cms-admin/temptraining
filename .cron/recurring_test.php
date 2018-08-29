<?php
if (is_dir('/var/www/u0249987/data/www/temptraining.ru/')){
  define(SITE_DIR, '/var/www/u0249987/data/www/temptraining.ru/'); // for production
} else {
  define(SITE_DIR, 'd:\\OpenServer\\domains\\temptraining.loc\\'); // for development
}
require_once(SITE_DIR . 'wp-content/plugins/tt-client/classes/Client.php');

// загрузка класса
$ttcli = Client::getInstatce(SITE_DIR);

// автоматические платежи
$ttcli->cronRecurringPays();
