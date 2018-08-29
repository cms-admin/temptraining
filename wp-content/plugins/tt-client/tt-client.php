<?php
use TTClient\Client;
use TTClient\ClientYakassa;
use TTClient\ClientModel;
use TTClient\ClientEmail;

/*
Plugin Name: Темп Клиент
Description: Личный кабинет для клиентов и тренеров
Version: 1.5.3
Author: Александр Ерко <info@cms-admin.ru>
Author URI: http://cms-admin.ru/
Plugin URI: http://cms-admin.ru/
*/
define('TT_CLIENT_DIR', plugin_dir_path(__FILE__));

define('TT_CLIENT_URL', plugin_dir_url(__FILE__));

define('TT_CLIENT_V', '1.5.1');

define('TT_CLIENT_CSS_URL', TT_CLIENT_URL . 'assets/css/');
define('TT_CLIENT_JS_URL', TT_CLIENT_URL . 'assets/js/');
define('TT_CLIENT_ICONS_URL', TT_CLIENT_URL . 'assets/icons/');
define('TT_CLIENT_VIEWS', TT_CLIENT_DIR . 'templates/');
define('TT_CLIENT_THEME_VIEWS', get_template_directory() . '/tt-client/');
define('TT_CLIENT_LOGS', TT_CLIENT_DIR . '/.logs/');

if (is_dir('/var/www/u0249987/data/www/temptraining.ru/')){
  define('TT_CLIENT_HOME', 'https://temptraining.ru/'); // for production
} else {
  define('TT_CLIENT_HOME', 'http://temptraining.loc/'); // for development
}

/**
 * Загрузка плагина
 */
function tt_client_load(){

  /**
   * Классы плагина
   */
  require_once(TT_CLIENT_DIR.'classes/Client.php');
  require_once(TT_CLIENT_DIR.'classes/ClientYakassa.php');
  require_once(TT_CLIENT_DIR.'classes/ClientModel.php');
  require_once(TT_CLIENT_DIR.'classes/ClientEmail.php');

  /**
   * Ajax запросы
   */
  require_once(TT_CLIENT_DIR.'includes/ajax.php');

  if(is_admin()) {
    // подключаем файлы администратора, только если он авторизован
    require_once(TT_CLIENT_DIR.'includes/admin.php');
  }

  require_once(TT_CLIENT_DIR.'includes/core.php');

}
tt_client_load();

register_activation_hook(__FILE__, 'tt_client_activation');
register_deactivation_hook(__FILE__, 'tt_client_deactivation');

/**
 * Активация плагина
 */
function tt_client_activation() {
  # регистрируем действие при удалении
  register_uninstall_hook(__FILE__, 'tt_client_uninstall');

}

/**
 * Деактивация плагина
 */
function tt_client_deactivation() {
    // при деактивации
}

/**
 * Удаление плагина
 */
function tt_client_uninstall()
{
  //действие при удалении
}

/**
 * регистрация фильтров и событий
 */
function tt_client_init()
{
  // Расположение TWIG-Шаблонов
  Timber::$locations = array(TT_CLIENT_VIEWS, TT_CLIENT_THEME_VIEWS);

  // Обновление плагина
  ttcli_check_update();
}
add_action('plugins_loaded', 'tt_client_init');

/**
 * Стили и скрипты для страницы кабинета
 * @return [type] [description]
 */
function tt_client_layouts()
{
  global $post;

  $recaptchaSecret = ClientModel::getInstance()->getOption('recaptcha_secret', 'tt_client_feedback');

  if ($recaptchaSecret) {
    wp_enqueue_script('recaptha', 'https://www.google.com/recaptcha/api.js', false, null, true);
  }

  switch ($post->post_name) {
    # Кабинет клиента
    case 'client':
      wp_enqueue_style( 'sweetalert', TT_CLIENT_CSS_URL . 'sweetalert.css', array(), TT_CLIENT_V);
      wp_enqueue_style( 'frontend', TT_CLIENT_CSS_URL . 'frontend.css', array(), TT_CLIENT_V);

      wp_enqueue_script( 'jstree', TT_CLIENT_JS_URL . 'jstree.js', array('jquery'), TT_CLIENT_V, true );
      wp_enqueue_script( 'list', TT_CLIENT_JS_URL . 'list.js', array('jquery'), TT_CLIENT_V, true );
      wp_enqueue_script( 'list-fuzzysearch', TT_CLIENT_JS_URL . 'list.fuzzysearch.min.js', array('jquery'), TT_CLIENT_V, true );
      wp_enqueue_script( 'list-pagination', TT_CLIENT_JS_URL . 'list.pagination.min.js', array('jquery'), TT_CLIENT_V, true );
      wp_enqueue_script( 'sweetalert', TT_CLIENT_JS_URL . 'sweetalert.min.js', array('jquery'), TT_CLIENT_V, true );
      wp_enqueue_script( 'tt_client_frontend', TT_CLIENT_JS_URL . 'frontend.js', array('jquery', 'jstree', 'switchery', 'sweetalert'), TT_CLIENT_V, true );

      wp_localize_script('tt_client_frontend', 'ttajax',
        array(
          'url' => admin_url('admin-ajax.php')
        )
      );
      break;
    # Лэндинг
    case 'club':
      wp_enqueue_style( 'sweetalert', TT_CLIENT_CSS_URL . 'sweetalert.css', array(), TT_CLIENT_V);
      wp_enqueue_style( 'switchery', TT_CLIENT_CSS_URL . 'switchery.css', array(), TT_CLIENT_V );
      wp_enqueue_style( 'club', TT_CLIENT_CSS_URL . 'club.css', array(), TT_CLIENT_V);

      wp_enqueue_script( 'countup', TT_CLIENT_JS_URL . 'countUp.js', array('jquery'), TT_CLIENT_V, true );
      wp_enqueue_script( 'switchery', TT_CLIENT_JS_URL . 'switchery.js', array('jquery'), TT_CLIENT_V, true);
      wp_enqueue_script( 'sweetalert', TT_CLIENT_JS_URL . 'sweetalert.min.js', array('jquery'), TT_CLIENT_V, true );
      wp_enqueue_script( 'validate', TT_CLIENT_JS_URL . 'validate.js', array('jquery'));
      wp_enqueue_script( 'club', TT_CLIENT_JS_URL . 'club.js', array('jquery'), TT_CLIENT_V, true );

      wp_localize_script('club', 'ttajax',
        array(
          'url' => admin_url('admin-ajax.php')
        )
      );
      break;
  }

  # Формы обратной связи
  wp_enqueue_style( 'feedback', TT_CLIENT_CSS_URL . 'feedback.css', ['nice-select', 'switchery'], TT_CLIENT_V);
  wp_enqueue_script( 'feedback', TT_CLIENT_JS_URL . 'feedback.js', ['jquery', 'nice-select', 'switchery'], TT_CLIENT_V, true );
  wp_localize_script('feedback', 'feedback_ajax',
    [
      'url' => admin_url('admin-ajax.php')
    ]
  );
}
add_action( 'wp_enqueue_scripts', 'tt_client_layouts' );

function tt_client_login_redirect( $redirect_to, $request, $user )
{
  if ( isset( $user->roles ) && is_array( $user->roles ) ) {
    $tt_user = Client::getInstance()->detectUser();

    if (intval($tt_user['role']) != 1) {
      return site_url('/client/');
    } else {
      return $redirect_to;
    }
  } else {
    return $redirect_to;
  }
}
add_filter( 'login_redirect', 'tt_client_login_redirect', 10, 3 );

/**
 * Регистрация шорткода для вывода формы оплаты
 * @return html
 */
function tt_client_page() {
  $options = get_option('tt_client_options');

  // Обработка ответа от Яндекс.Кассы
  if (file_get_contents("php://input")) {
    $post = json_decode(file_get_contents("php://input"), true);
    if ($post['type'] == 'notification') {
      ClientYakassa::getInstance()->checkNotification($post['event'], $post['object']);
    }
  }

  // Обработка ошибок
  if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['errorId'])){

    $tpl = array(
      'status'  => 'flase',
      'title'   => 'Ошибка №' . $_GET['errorId'],
      'message' => Client::getInstance()->error_codes[$_GET['errorId']]
    );
  }

  if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    if(isset($_GET['type']) && isset($_GET['orderId'])){

      $payment_type = $_GET['type'];
      $order_id = $_GET['orderId'];

      $tpl = Client::getInstance()->paymentResult($payment_type, $order_id);

    } else if (isset($_GET['orderId'])){
      $orderId = $_GET['orderId'];
      $tpl = Client::getInstance()->openbankCheckOrder($orderId);
    }
  }

  $user = Client::getInstance()->detectUser();

  $context  = Timber::get_context();

  $context['icons_url'] = TT_CLIENT_ICONS_URL;
  $context['offers'] = Client::getInstance()->getOffers();

  switch ($user['role']) {
    case 0:
      // Гость
      $context['login'] = wp_login_form(array('echo'  => 0));
      Timber::render('cabinet_guest.twig', $context );
      break;

    case 1:
      // Админ
      $context['total'] = $user['total'];
      $context['user'] = $user['user'];
      $context['photo'] = $user['photo'];
      $context['coaches'] = $user['coaches'];
      $context['is_coach'] = ($user['is_coach']) ? $user['is_coach'] : false;

      Timber::render('cabinet_admin.twig', $context );
      break;

    case 2:
      // Тренер
      $context['coach'] = $user['data'];
      Timber::render('cabinet_coach.twig', $context );
      break;

    case 3:
      // Клиент
      $context['client'] = $user['data'];
      $context['tpl'] = (isset($tpl)) ? $tpl : false;
      $context['options'] = Client::getInstance()->getPluginOptions();

      Timber::render('cabinet_client.twig', $context );
      break;

    case 4:
      // Член клуба
      $context['member'] = $user['data'];
      $context['tpl'] = (isset($tpl)) ? $tpl : false;
      $context['options'] = Client::getInstance()->getPluginOptions();
      Timber::render('cabinet_member.twig', $context );
      break;

    default:
      $context['error'] = get_404_template();
      Timber::render('cabinet_404.twig', $context );
      break;
  }

}
add_shortcode( 'tt-client', 'tt_client_page' );

/**
 * Landing page Клуб
 */
function tt_club_page()
{
  $options = Client::getInstance()->getSomeOptions(false, 'tt_club_options');

  $count_members  = ($options['counters']['members'])
                  ? $options['counters']['members']
                  : Client::getInstance()->getCountData('members');

  $count_money  = ($options['counters']['money'])
                ? intval(str_replace(' ', '', $options['counters']['money']))
                : Client::getInstance()->getCountData('membership_money');

  preg_match_all("/<p.*?>(.*?)<\/p>/", $options['intro']['text'], $intro_text);

  $context  = Timber::get_context();
  $context['club'] = $options;

  $context['count_members'] = $count_members;
  $context['count_members_str'] = Client::getInstance()->num2word($count_members, array('человек', 'человека', 'человек'));

  $context['count_money'] = (int) $count_money;
  $context['count_money_str'] = Client::getInstance()->num2word($context['count_money'], array('рубль', 'рубля', 'рублей'));

  $context['intro_text'] = $intro_text[0];

  Timber::render('club.twig', $context );
}
add_shortcode( 'tt-club', 'tt_club_page' );

// хук для выплат тренерам
if ( is_admin() ) {
  add_action('wp_ajax_ttclient_do_reward', 'do_reward');
  add_action('wp_ajax_ttclient_do_tax', 'do_tax');
}
if( wp_doing_ajax() ){
  add_action('wp_ajax_ttclient_toggle_coach_noty', 'toggle_coach_noty');
}

/**
 * Выплата вознаграждения
 *
 * @return void
 */
function do_reward(){
  $coach_id = $_POST['coach_id'];
  $error = FALSE;
  $message = FALSE;

  global $wpdb;

  $coach = $wpdb->get_row("SELECT coach_id, reward FROM " . $wpdb->prefix . "coaches WHERE coach_id = '" . $coach_id ."'");

  if (intval($coach->reward) > 0){
    // строка в таблицу выплат
    $reward_data = array(
      'coach_id'      => $coach_id,
      'payment_date'  => date('Y-m-d H:i:s'),
      'amount'        => floatval($coach->reward),
      'type'          => 1
    );

    // создает запись в таблице с выплатами
    $wpdb->insert($wpdb->prefix . 'rewards', $reward_data);
    $new_reward = $wpdb->insert_id;

    // если запись создана
    if ($new_reward) {
      // обнуляем вознаграждение тренера
      $wpdb->update($wpdb->prefix . 'coaches', array('reward' => 0), array('coach_id' => $coach_id));

      // обновляем таблицу оплат
      $update = $wpdb->update(
        $wpdb->prefix . 'orders',
        array('reward_id' => $new_reward),
        array('coach_id' => $coach_id, 'reward_id' => 0)
      );

      if ($update > 0){
        $message = 'Выплата вознаграждения прошла успешно';
      } else {
        $error = 'Не удалось обновить данные в таблице ' . $wpdb->prefix . 'orders';
      }

    } else {
      $error = 'Не удалось записать данные в таблицу ' . $wpdb->prefix . 'rewards';
    }
  } else {
    $error = 'Отсутствуют средства для вылаты вознаграждения';
  }

  $response = array(
    'error'   => $error,
    'message' => $message
  );

  wp_send_json($response);

  exit;
}

/**
 * Выплата налога
 * @global type $wpdb
 */
function do_tax(){
  $coach_id = $_POST['coach_id'];
  $error = FALSE;
  $message = FALSE;
  $options = get_option('tt_client_options');

  global $wpdb;

  $coach = $wpdb->get_row("SELECT coach_id, tax FROM " . $wpdb->prefix . "coaches WHERE coach_id = '" . $coach_id ."'");

  if (intval($coach->tax) > 0){
    // строка в таблицу выплат
    $reward_data = array(
      'coach_id'      => $coach_id,
      'payment_date'  => date('Y-m-d H:i:s'),
      'amount'        => floatval($coach->tax),
      'type'          => 2
    );

    // создает запись в таблице с выплатами
    $wpdb->insert($wpdb->prefix . 'rewards', $reward_data);
    $new_reward = $wpdb->insert_id;

    // если запись создана
    if ($new_reward) {
      // обнуляем налог тренера до налога на выплату налога ))
      $tax_future = floatval($coach->tax) * floatval($options['coaches_tax']) / 100;
      $wpdb->update($wpdb->prefix . 'coaches', array('tax' => $tax_future), array('coach_id' => $coach_id));

      if ($update !== FALSE){
        $message = 'Выплата налога прошла успешно';
      } else {
        $error = 'Не удалось обновить данные в таблице ' . $wpdb->prefix . 'coaches';
      }

    } else {
      $error = 'Не удалось записать данные в таблицу ' . $wpdb->prefix . 'rewards';
    }
  } else {
    $error = 'Отсутствуют средства для вылаты налога';
  }

  $response = array(
    'error'   => $error,
    'message' => $message
  );

  wp_send_json($response);

  exit;
}

function toggle_coach_noty(){
  $role = ttcli_check_role();

  if ($role['name'] == 'coach'){
    global $wpdb;

    $coach_id = intval($_POST['coach_id']);

    $state = intval($_POST['state']);

    $update = $wpdb->update( $wpdb->prefix . 'coaches', array('notify' => $state), array('coach_id' => $coach_id));

    if ($update){
      $response['title'] = 'Выполнено!';
      $response['type'] = 'success';
      $response['message'] = ($state === 1) ?
        'Вы включили уведомления о платежах клиентов' :
        'Вы отключили уведомления о платежах клиентов';
    } else {
      $response['title'] = 'Ошибка!';
      $response['type'] = 'error';
      $response['message'] = 'Ошибка записи в базу данных';
    }
  } else {
    $response['title'] = 'Ошибка!';
    $response['type'] = 'error';
    $response['message'] = 'У вас недостаточно прав для данного действия';
  }

  wp_send_json($response);

  exit;
}

/**
 * Отображает форму обратной связи
 * @param string $form
 * @return bool
 */
function ttcli_get_form($form = 'training')
{
  if ($form == 'training') {
    $form_enable = ClientModel::getInstance()->getOption('form_training', 'tt_client_feedback');
  } elseif ($form == 'director') {
    $form_enable = ClientModel::getInstance()->getOption('form_director', 'tt_client_feedback');
  }

  $context  = Timber::get_context();

  $context['recaptcha_sitekey'] = ClientModel::getInstance()->getOption('recaptcha_sitekey', 'tt_client_feedback');

  if ($form_enable) {
    echo Timber::compile('forms/feedback/' . $form . '.twig', $context);
  }
}