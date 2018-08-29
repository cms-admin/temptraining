<?php

use TTClient\Client;
use TTClient\ClientModel;
use TTClient\ClientYakassa;
use TTClient\ClientFormatter;

if( wp_doing_ajax() ){
  // Сохранение настроек
  add_action('wp_ajax_ttcli_save_options', 'ttcli_ajax_save_options');
  add_action('wp_ajax_ttcli_save_templates', 'ttcli_ajax_save_templates');
  add_action('wp_ajax_ttcli_save_club_page', 'ttcli_ajax_save_club_page');
  add_action('wp_ajax_ttcli_save_feedback_options', 'ttcli_ajax_save_feedback_options');
  add_action('wp_ajax_ttcli_save_email_templates', 'ttcli_save_email_templates');

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

  // Отправка форм обратной связи
  add_action('wp_ajax_ttcli_form_training', 'ttcli_ajax_form_training');
  add_action('wp_ajax_nopriv_ttcli_form_training', 'ttcli_ajax_form_training');
  add_action('wp_ajax_ttcli_form_director', 'ttcli_ajax_form_director');
  add_action('wp_ajax_nopriv_ttcli_form_director', 'ttcli_ajax_form_director');
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
 * Сохраняет настройки обратной связи
 */
function ttcli_ajax_save_feedback_options()
{
  $ajax = $_POST['data'];
  $post = Client::getInstance()->postToArray($ajax);

  if (ClientModel::getInstance()->saveOption($post, 'tt_client_feedback')) {
    $message = [
      'title'   => plang('Выполнено'),
      'message' => plang('Настройки сохранены'),
      'type'    => 'success'
    ];
  } else {
    $message = [
      'title'   => plang('Ошибка'),
      'message' => plang('Не удалось сохранить настройки плагина'),
      'type'    => 'error'
    ];
  }

  wp_send_json($message);

  exit;
}

/**
 * Обработка сообщения из формы "Начать тренироваться"
 */
function ttcli_ajax_form_training()
{
  $data = [];
  parse_str($_POST['data'], $data);

  #проверка на ошибки
  $errors = [];

  if (!isset($data['training_offer'])) {
    $errors['training_offer'] = plang('Для отправки сообщения необходимо принять условия оферты');
  }

  if (!isset($data['training_username'])) {
    $errors['training_username'] = plang('Необходимо указать ваше имя');
  }

  if (!isset($data['training_contacts'])) {
    $errors['training_contacts'] = plang('Необходимо указать телефон или email');
  }

  if (!empty($errors)) {
    $result = [
      'success' => false,
      'errors'  => $errors
    ];

    wp_send_json($result);
  }

  $recaptchaSecret = ClientModel::getInstance()->getOption('recaptcha_secret', 'tt_client_feedback');

  if ($recaptchaSecret) {
    $recaptchaParams = [
      'secret'    => trim($recaptchaSecret),
      'response'  => $data['g-recaptcha-response']
    ];

    $recaptchaCurl = curl_init();
    curl_setopt_array($recaptchaCurl, [
      CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query($recaptchaParams)
    ]);

    $recaptchaResponse = json_decode(curl_exec($recaptchaCurl));
    curl_close($recaptchaCurl);

    if ($recaptchaResponse->success == false) {
      $result['success'] = false;
      $result['message'] = tlang('Проверка <b>Я не робот</b> не пройдена');

      wp_send_json($result);

      exit;
    }
  }

  // Отправка уведомления на email
  $notifyEmail = ClientModel::getInstance()->getOption('emails_training', 'tt_client_feedback');
  $notifyEmail = explode(',', $notifyEmail);

  $notifyFrom = 'From: ' . tlang('Уведомление от') . ' ' . get_option('siteurl');
  $notifySubject = 'From: ' . tlang('Новое сообщение из формы "Начать тренироваться"');

  foreach ($notifyEmail as $email) {
    $email = trim($email);
    if(is_email($email)){
      $headers[] = $email;
      $headers[] = 'content-type: text/html';

      wp_mail($email, $notifySubject, ttcli_feedback_notify($data, 'training'), $headers);
    }
  }

  $result['success'] = true;
  $result['message'] = plang('Ваше сообщение успешно отправлено!');

  wp_send_json($result);

  exit;
}

/**
 * Сообщения из формы "Написать директору"
 */
function ttcli_ajax_form_director ()
{
  $data = [];
  parse_str($_POST['data'], $data);

  #проверка на ошибки
  $errors = [];

  if (!isset($data['director_username'])) {
    $errors['director_username'] = plang('Необходимо указать ваше имя');
  }

  if (!isset($data['director_contacts'])) {
    $errors['director_contacts'] = plang('Необходимо указать телефон или email');
  }

  if (!empty($errors)) {
    $result = [
      'success' => false,
      'errors'  => $errors
    ];

    wp_send_json($result);
  }

  $recaptchaSecret = ClientModel::getInstance()->getOption('recaptcha_secret', 'tt_client_feedback');

  if ($recaptchaSecret) {
    $recaptchaParams = [
      'secret'    => trim($recaptchaSecret),
      'response'  => $data['g-recaptcha-response']
    ];

    $recaptchaCurl = curl_init();
    curl_setopt_array($recaptchaCurl, [
      CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query($recaptchaParams)
    ]);

    $recaptchaResponse = json_decode(curl_exec($recaptchaCurl));
    curl_close($recaptchaCurl);

    if ($recaptchaResponse->success == false) {
      $result['success'] = false;
      $result['message'] = tlang('Проверка <b>Я не робот</b> не пройдена');

      wp_send_json($result);

      exit;
    }
  }

  // Отправка уведомления на email
  $notifyEmail = ClientModel::getInstance()->getOption('emails_director', 'tt_client_feedback');
  $notifyEmail = explode(',', $notifyEmail);

  $notifyFrom = 'From: ' . tlang('Уведомление от') . ' ' . get_option('siteurl');
  $notifySubject = 'From: ' . tlang('Новое сообщение из формы "Написать директору"');

  foreach ($notifyEmail as $email) {
    $email = trim($email);
    if(is_email($email)){
      $headers[] = $email;
      $headers[] = 'content-type: text/html';

      wp_mail($email, $notifySubject, ttcli_feedback_notify($data, 'director'), $headers);
    }
  }

  $result['success'] = true;
  $result['message'] = plang('Ваше сообщение успешно отправлено!');

  wp_send_json($result);

  exit;
}

/**
 * Шаблон email письма о новом сообщении на сайте
 * @param  [type] $data    [description]
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function ttcli_feedback_notify($data, $form)
{
  $html = file_get_contents(TT_CLIENT_DIR . 'templates/emails/' . $form . '.html');

  switch ($form) {
    case 'training':
      $training_sport = [
        'price-1' => plang('Триатлон'),
        'price-2' => plang('Два спорта'),
        'price-3' => plang('Бег'),

      ];
      $message_replace = [
        '{{ title }}'         => plang('Новое сообщение из формы "Начать тренироваться"'),
        '{{ username }}'   => $data['training_username'],
        '{{ contacts }}'   => $data['training_contacts'],
        '{{ sport }}'  => $training_sport[$data['training_sport']],
        '{{ message }}'  => $data['training_message'],
      ];
      break;

    case 'director':
      $director_themes = [
        'theme-1' => plang('Оставить отзыв'),
        'theme-2' => plang('Задать вопрос'),
        'theme-3' => plang('Другое'),

      ];
      $message_replace = [
        '{{ title }}'         => plang('Новое сообщение из формы "Написать директору"'),
        '{{ username }}'   => $data['director_username'],
        '{{ contacts }}'   => $data['director_contacts'],
        '{{ theme }}'  => $director_themes[$data['director_theme']],
        '{{ message }}'  => $data['director_message'],
      ];
      break;

    default:
      $message_replace = [];
      break;
  }

  return strtr($html, $message_replace);
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