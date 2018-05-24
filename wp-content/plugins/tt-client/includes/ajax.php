<?php

use TTClient\Client;
use TTClient\ClientYakassa;

if( wp_doing_ajax() ){
  // Сохранение настроек
  add_action('wp_ajax_ttcli_save_options', 'ttcli_ajax_save_options');
  add_action('wp_ajax_ttcli_save_templates', 'ttcli_ajax_save_templates');
  add_action('wp_ajax_ttcli_save_club_page', 'ttcli_ajax_save_club_page');

  // Оплата клиента через банк открытие
  add_action('wp_ajax_ttcli_openbank_payment', 'ttcli_ajax_openbank_payment');

  // Оплата клиента через яндекс.кассу
  add_action('wp_ajax_ttcli_yakassa_payment', 'ttcli_ajax_yakassa_payment');

  // Оплата в кабинете
  add_action('wp_ajax_ttcli_payment', 'ttcli_ajax_payment');

  // Отмена автоплатежа
  add_action('wp_ajax_ttcli_openbank_cancel_recurring', 'ttcli_ajax_openbank_cancel_recurring');

  // Отказ от премиум подписки
  add_action('wp_ajax_ttcli_member_cancel_premium', 'ttcli_ajax_member_cancel_premium');

  // Регистрация члена клуба
  add_action('wp_ajax_ttcli_member_register', 'ttcli_ajax_member_register');
  add_action('wp_ajax_nopriv_ttcli_member_register', 'ttcli_ajax_member_register');
}

/**
 * Сохранение наятроек
 */
function ttcli_ajax_save_options(){
  $ajax = $_POST['data'];
  $post = Client::getInstance()->postToArray($ajax);

  $result = Client::getInstance()->saveOptions($post);

  $alert_succes = array(
    'title'   => __('Выполнено', 'tt-client'),
    'message' => __('Настройки сохранены', 'tt-client'),
    'type'    => 'success'
  );

  $alert_fail = array(
    'title'   => __('Ошибка', 'tt-client'),
    'message' => __('Не удалось сохранить настройки плагина', 'tt-client'),
    'type'    => 'error'
  );

  $alert = ($result) ? $alert_succes : $alert_fail;

  wp_send_json($alert);
  
  exit;
}

/**
 * Сохранение шаблонов
 */
function ttcli_ajax_save_templates(){
  $ajax = $_POST['data'];
  $post = Client::getInstance()->postToArray($ajax);

  $result = Client::getInstance()->saveOptions($post, true);

  $alert_succes = array(
    'title'   => __('Выполнено', 'tt-client'),
    'message' => __('Настройки сохранены', 'tt-client'),
    'type'    => 'success'
  );

  $alert_fail = array(
    'title'   => __('Ошибка', 'tt-client'),
    'message' => __('Не удалось сохранить настройки плагина', 'tt-client'),
    'type'    => 'error'
  );

  $alert = ($result) ? $alert_succes : $alert_fail;

  wp_send_json($alert);
  
  exit;
}

/**
 * Сохраняет настройки страницы клуба
 */
function ttcli_ajax_save_club_page()
{
  $ajax = $_POST['data'];
  $post = Client::getInstance()->postToArray($ajax);

  $result = Client::getInstance()->saveSomeOptions($post, 'tt_club_options');

  $alert_succes = array(
    'title'   => __('Выполнено', 'tt-client'),
    'message' => __('Настройки сохранены', 'tt-client'),
    'type'    => 'success'
  );

  $alert_fail = array(
    'title'   => __('Ошибка', 'tt-client'),
    'message' => __('Не удалось сохранить настройки плагина', 'tt-client'),
    'type'    => 'error'
  );

  $alert = ($result) ? $alert_succes : $alert_fail;

  wp_send_json($alert);
  
  exit;
}

/**
 * Проведение платежа
 */
function ttcli_ajax_openbank_payment(){
  $ajax = $_POST['data'];

  $post = Client::getInstance()->postToArray($ajax);

  if (isset($post['orderBundle'])){
    $post['orderBundle'] = array(
      'orderCreationDate' => date('Y-m-d H:i', time()),
      'customerDetails' => array(
        'email' => $post['jsonParams']['email'],
      ),
    );
  } 
  
  $result = Client::getInstance()->openbankClientOrder($post);

  wp_send_json($result);
  
  exit;
}

/**
 * Отмена автоплатежа
 */
function ttcli_ajax_openbank_cancel_recurring()
{
  $client_id = $_POST['client_id'];

  $result = Client::getInstance()->openbankRecurringCancel($client_id);

  wp_send_json($result);
  
  exit;
}

/**
 * Общая функция для всех платежей в кабинете
 */
function ttcli_ajax_payment()
{
  $ajax = (isset($_POST['data'])) ? $_POST['data'] : $_POST;

  $post = (isset($ajax['post_no_convert'])) ? $ajax : Client::getInstance()->postToArray($ajax);

  $result = Client::getInstance()->payment($post);

  wp_send_json($result);
  
  exit;
}

/**
 * Отменяет премиум подписку у членов клуба
 */
function ttcli_ajax_member_cancel_premium()
{
  $member_id = $_POST['member_id'];

  $result = Client::getInstance()->memberCancelPremium($member_id);

  wp_send_json($result);
  
  exit;
}

/**
 * Регистрация нового члена клуба
 */
function ttcli_ajax_member_register()
{
  $ajax = (isset($_POST['data'])) ? $_POST['data'] : $_POST;

  $post = (isset($ajax['post_no_convert'])) ? $ajax : Client::getInstance()->postToArray($ajax);

  $result = Client::getInstance()->memberRegister($post);

  wp_send_json($result);
  
  exit;
}

/**
 * Процесс оплаты через Яндекс.Кассу
 *
 * @return void
 */
function ttcli_ajax_yakassa_payment()
{
  $ajax = (isset($_POST['data'])) ? $_POST['data'] : $_POST;

  $post = (isset($ajax['post_no_convert'])) ? $ajax : Client::getInstance()->postToArray($ajax);

  $post_check = ClientYakassa::getInstance()->checkFormTariff($post);
 
  if ($post_check){
    $result = ClientYakassa::getInstance()->createPayment(intval($post['sum']), $post_check, $post['type'], $post['payment_type']);
  } else {
    $result = array(
      'success' => false,
      'error'   => 'Несоответствие платежных данных стоимости тарифа!'
    );
  }

  wp_send_json($result);
  
  exit;
}