<?php
namespace TTClient;

use TTClient\ClientYakassa;

class Client
{
  protected $root_dir;

  protected $wpdb;

  private static $instance;

  public $months = [];

  public $order_statuses = [];

  public $error_codes = [];

  public function __construct($site_dir){
    $this->root_dir = ( !defined('ABSPATH') ) ? $site_dir : ABSPATH;

    require_once (__dir__ . '/Spyc.php');
    require_once ($this->root_dir . 'wp-config.php');
    require_once ($this->root_dir . 'wp-includes/wp-db.php');
    require_once ($this->root_dir . 'wp-includes/pluggable.php');
    require_once ($this->root_dir . 'wp-includes/capabilities.php');
    require_once ($this->root_dir . 'wp-includes/class-wp-query.php');
    require_once (TT_CLIENT_DIR . 'includes'.DIRECTORY_SEPARATOR.'errors.php');

    global $wpdb;
    $this->wpdb = $wpdb;

    // Статусы заказов

    $this->order_statuses = [
      '0' => __('Заказ зарегистрирован, но не оплачен', 'tt-client'),
      '1' => __('Предавторизованная сумма захолдирована (для двухстадийных платежей)', 'tt-client'),
      '2' => __('Проведена полная авторизация суммы заказа', 'tt-client'),
      '3' => __('Авторизация отменена', 'tt-client'),
      '4' => __('По транзакции была проведена операция возврата', 'tt-client'),
      '5' => __('Инициирована авторизация через ACS банка-эмитента', 'tt-client'),
      '6' => __('Авторизация отклонена', 'tt-client'),
    ];

    // Расшифровка кодов ошибок
    $this->error_codes = $lang_errors;

    // месяца
    $this->months = ['месяц', 'месяца', 'месяцев'];
  }

  public static function getInstance($site_dir = false) {
    if (null === self::$instance)
      return self::$instance = new self($site_dir);
    else
      return self::$instance;
  }

  /**
   * Преобразует массив в YAML
   * @param array $array
   * @return string
   */
  public function arrayToYaml($input_array, $indent = 2, $word_wrap = 40) {
    if(!empty($input_array)){

      foreach ($input_array as $key => $value) {
        $_k = str_replace(array('[',']'), '', $key); // был фатальный баг, если в ключах эти символы
        $array[$_k] = $value;
      }

    } else {
      $array = array();
    }

    return \Spyc::YAMLDump($array, $indent, $word_wrap);

  }

  /**
   * Преобразует YAML в массив
   * @param string $yaml
   * @return array
   */
  public static function yamlToArray($yaml) {
    return \Spyc::YAMLLoadString($yaml);
  }

  /**
   * Преобразует окончания слов в соответствии с числом
   * @param $num число
   * @param $words массив слов для (1, 2, много)
   */
  public function num2word($num, $words) {
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

  /**
   * Преобразует дату в понятный формат
   * @var timestamp
   * @return  string
   */
  public function localdate($date, $short = FALSE){
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
          $m_arr = [
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
          ];
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
   * Конвертирует опциональные сроки в человеко-понятные
   * @return  string
   */
  public function convertToPeriod($count, $type)
  {
    switch ($type) {
      case 'days':
        $days = ['день', 'дня', 'дней'];
        return $count . ' ' . $this->num2word($count, $days);
        break;

      case 'months':
        $months = ['месяц', 'месяца', 'месяцев'];
        return $count. ' ' . $this->num2word($count, $months);
        break;

      case 'years':
        $years = ['год', 'года', 'лет'];
        return $count . ' ' . $this->num2word($count, $years);
        break;

      default:
        return false;
        break;
    }
  }

  /**
   * Получает всех клиентов
   * @return array
   */
  public function getAllClients()
  {
    return $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}clients" );
  }

  /**
   * Получает всех тренеров
   * @return array
   */
  public function getAllCoaches()
  {
    return $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}coaches" );
  }

  /**
   * Возвращает общие накопленные суммы от платежей клиентов
   * @return array
   */
  public function getTotalAmounts()
  {
    $sql = "SELECT SUM(reward) as reward, SUM(tax) as tax FROM {$this->wpdb->prefix}coaches";
    return $this->wpdb->get_row($sql);
  }

  /**
   * Получает настройки плагина
   * @param  boolean $key - ключ настройки
   * @return array - массив настроей / значение настройки по её ключу
   */
  public function getPluginOptions($key=false, $templates = false)
  {
    $prefix = $this->wpdb->prefix;
    $option_name = ($templates) ? 'tt_client_templates' : 'tt_client_options';

    $sql = "SELECT option_value FROM {$prefix}options WHERE option_name='{$option_name}'";
    $row = $this->wpdb->get_row($sql);

    if ($row){
      $options = (@unserialize($row->option_value)) ? unserialize($row->option_value) : $this->yamlToArray($row->option_value);
    } else {
      return false;
    }

    return ($key) ? $options[$key] : $options;
  }

  /**
   * Преобразует POST-запрос, переданный по AJAX в массив значений
   * @param  array $data = $_POST['data']
   * @return array [name=>value]
   */
  public function postToArray($data = false){
    if (!$data) return false;

    foreach ($data as $post) {
      if (strpos($post['name'], ':') === false){
        $result[$post['name']] = $post['value'];
      } else {
        $result[preg_replace("/(.*):(.*)/i", "$1", $post['name'])][preg_replace("/(.*):(.*)/i", "$2", $post['name'])] = $post['value'];
      }
    }

    return $result;
  }

  /**
   * Сохраняет настройки плагина в админке
   * @param  array $data - массив с настройками
   * @return bool
   */
  public function saveOptions($data = false, $templates = false)
  {
    $option_name = ($templates) ? 'tt_client_templates' : 'tt_client_options';

    if($data) {
      if ($this->getPluginOptions(false, $templates)){ // обновляем опции

        $update = array('option_value' => $this->arrayToYaml($data));
        $this->wpdb->update("{$this->wpdb->prefix}options", $update, array('option_name' => $option_name));
        return true;

      } else { // создаем новую строку с опциями

        $save = array(
          'option_name' => $option_name,
          'option_value' => $this->arrayToYaml($data)
        );

        $this->wpdb->insert("{$this->wpdb->prefix}options", $save );
        return ($this->wpdb->insert_id) ? true : false;
      }

    } else {
      return false;
    }
  }

  /**
   * Получает настройки
   */
  public function getSomeOptions($key=false, $option_name = false)
  {
    if (!$option_name) return false;
    $prefix = $this->wpdb->prefix;

    $sql = "SELECT option_value FROM {$prefix}options WHERE option_name='{$option_name}'";
    $row = $this->wpdb->get_row($sql);

    if ($row){
      $options = (@unserialize($row->option_value)) ? unserialize($row->option_value) : $this->yamlToArray($row->option_value);
    } else {
      return false;
    }

    return ($key) ? $options[$key] : $options;
  }

  /**
   * Сохраняет настройки
   * @param  array $data - массив с настройками
   * @return bool
   */
  public function saveSomeOptions($data = false, $option_name = false)
  {
    if (!$option_name) return false;

    if($data) {
      if ($this->getSomeOptions(false, $option_name)){ // обновляем опции

        $update = array('option_value' => $this->arrayToYaml($data));
        $this->wpdb->update("{$this->wpdb->prefix}options", $update, array('option_name' => $option_name));
        return true;

      } else { // создаем новую строку с опциями

        $save = array(
          'option_name' => $option_name,
          'option_value' => $this->arrayToYaml($data)
        );

        $this->wpdb->insert("{$this->wpdb->prefix}options", $save );
        return ($this->wpdb->insert_id) ? true : false;
      }

    } else {
      return false;
    }
  }

  /**
   * Возвращает спецпредложения
   */
  public function getOffers()
  {
    $args = array(
      'numberposts' => -1,
      'post_status' => 'publish',
      'post_type'   => 'offers',
      'order'       => 'DESC',
    );

    $offers = get_posts($args);

    if (empty($offers)) return false;

    foreach ($offers as $key => $value) {
      $offers[$key]->post_class = implode(" ", get_post_class("post", $value->ID));
    }

    return $offers;
  }

  /**
   * Определяет роль пользователя
   * @return mixed
   *   0 - Гость
   *   1 - Админ
   *   2 - Тренер
   *   3 - Клиент
   *   4 - Член клуба
   */
  public function detectUser()
  {
    $user = wp_get_current_user();
    $prefix = $this->wpdb->prefix;

    // Если гость
    if ($user->ID == 0){
      return array("role" => 0);
    }

    // Если админ
    if (current_user_can('administrator')){
      return array(
        "role" => 1,
        "total"   => $this->getTotalAmounts(),
        "user"    => $user,
        "photo"   => get_avatar_url($user->ID, array('size' => 100)),
        "coaches" => $this->getAdminCoaches($user->user_login),
        "is_coach" => $this->isCoach($user->user_login),
      );
    }

    // Если тренер
    $is_coach = $this->isCoach($user->user_login);
    if ($is_coach){
      return array(
        "role" => 2,
        "data" => $is_coach
      );
    }

    // Если клиент
    $is_client = $this->isClient($user);
    if ($is_client){
      return [
        "role" => 3,
        "data" => $is_client,
      ];
    }

    // Если член клуба
    if ($is_member = $this->isMember($user)){
      return array(
        "role" => 4,
        "data" => $is_member,
      );
    }
  }

  /**
   * Получает расширенные данные по тренерам для кабинета администратора
   * @return array - расширенные данные тренера
   */
  public function getAdminCoaches($user_login)
  {
    $prefix = $this->wpdb->prefix;

    $sql = "SELECT coach_id_name FROM {$prefix}coaches WHERE coach_id_name != '{user_login}'";

    $coaches = $this->wpdb->get_results($sql);

    if (!empty($coaches)) {
      foreach ($coaches as $coach) {
        $crm_coach = $this->isCoach($coach->coach_id_name);
        if ($crm_coach) $crm_coaches[] = $crm_coach;
      }

      return $crm_coaches;
    } else {
      return false;
    }
  }

  /**
   * Проверяет является пользователь тренером
   * @param string $login - логин текущего пользователя
   * @return array
   */
  public function isCoach($login)
  {
    $args = array(
      'numberposts' => 1,
      'post_status' => 'publish',
      'post_type'   => 'coach',
      'order'       => 'ASC',
      'meta_query'  => array(
        array(
          'key'     => 'coach_id',
          'value'   => $login,
          'compare' => '=',
        )
      ),
    );

    $coach = get_posts($args);

    if (empty($coach)){
      return false;
    }

    $coach = $coach[0];

    // Фото тренера
    $coach->photo = get_the_post_thumbnail_url($coach, 'original');

    // Специализация тренера
    $coach->spec = get_post_meta($coach->ID, 'spec', true);

    // Данные кабинета тренера
    $coach = (object) array_merge((array) $this->getCoachCrmData($login), (array) $coach);

    // Количество учеников тренера
    $coach->clients_count = count($coach->clients);
    $coach->clients_title = $this->num2word($coach->clients_count, array('учеников', 'ученика', 'учеников'));

    return $coach;
  }

  /**
   * Проверяет является пользователь клиентом
   * @param object $user - текущий пользователь
   * @return array
   */
  public function isClient($user)
  {
    $prefix = $this->wpdb->prefix;

    $sql = "SELECT u.client_id_name, u.client_id, u.client_name as name, u.client_tarif_name, u.tarif_cost, u.pay_date, u.can_pay, u.can_recurring, u.coach_id, u.notify, u.recurring_id, u.recurring_frequency, u.recurring_expiry, c.coach_name "
          ."FROM {$prefix}clients u "
          ."LEFT JOIN {$prefix}coaches c ON u.coach_id = c.coach_id "
          ."WHERE u.client_id_name = '{$user->user_login}' "
          ."AND u.coach_id <> 0";

    $client = $this->wpdb->get_row($sql);

    if (empty($client)){
      return false;
    } else {
      if (intval($client->pay_date) == 0) $client->pay_date = false;

      // Фото клиента
      $client->photo_url = get_avatar_url($user->ID, array('size' => 100));

      // День автоплатежа клиента
      if ($client->pay_date && $client->recurring_frequency == 0){
        $pay_date = date_create($client->pay_date);
        $pay_date_frequency = intval($pay_date->format('d'));
        $client->pay_date_frequency = ($pay_date_frequency > 28) ? 1 : $pay_date_frequency;
      }

      // Незавершеные платежи клиента
      ClientYakassa::getInstance()->checkWaytingPayments($client->client_id);

      return $this->getClientHistory($client);
    }
  }

  /**
   * Проверяет является пользователь членом клуба
   * @param object $user - текущий пользователь
   * @return bool / array
   */
  public function isMember($user)
  {
    $prefix = $this->wpdb->prefix;

    $options = $this->getPluginOptions();

    $sql = "SELECT m.*, c.client_tarif_name as tariff, c.tarif_cost as premium_cost, c.pay_date as premium_pay_date, "
          ."c.recurring_id, c.recurring_frequency, c.recurring_expiry "
          ."FROM {$prefix}club m "
          ."LEFT JOIN {$prefix}clients c ON m.member_id_name = c.client_id_name "
          ."WHERE m.member_id_name = '{$user->user_login}'";

    $member = $this->wpdb->get_row($sql);

    if (empty($member)){
      return false;
    } else {
      // новый член или нет
      $is_new = $this->wpdb->get_row("SELECT * FROM {$prefix}club_orders WHERE member = '{$member->member_id}'");
      $member->is_new = (empty($is_new)) ? 1 : 0;

      // Фото клиента
      $member->photo_url = get_avatar_url($user->ID, array('size' => 100));

      // Последний заказ
      $last_order = $this->wpdb->get_row("SELECT max(id) as max_id FROM {$prefix}club_orders");
      $member->last_order = (int) $last_order->max_id + 1;

      // Описание срока членства
      $member->ms_period = $this->convertToPeriod($options['club_period_count'], $options['club_period_type']);

      // Подписка на TrainingPeaks Premium
      if ($member->premium_pay_date && $member->recurring_frequency == 0){
        $premium_pay_date = date_create($member->premium_pay_date);
        $premium_pay_date_frequency = intval($premium_pay_date->format('d'));
        $member->premium_pay_date_frequency = ($premium_pay_date_frequency > 28) ? 1 : $premium_pay_date_frequency;

        $today = date_create(date('Y-m-d'));
        $premium_pay_date = date_create($member->premium_pay_date);
        
        if ($today > $premium_pay_date) {
          // Просрочка оплаты премиум
          $member->premium_expired = true;
          $date_delta_premium = date_diff($today, $premium_pay_date);
          $member->premium_expired_text = 'истекла'  . ' ' . $date_delta_premium->days . ' ' . $this->num2word($date_delta_premium->days, ['день', 'дня', 'дней']) . ' назад';
        }
      }

      // Была ли раньше оплата подписки на TrainingPeaks Premium
      $is_new_premium = $this->wpdb->get_row("SELECT *
                                              FROM {$prefix}orders
                                              WHERE client_id = '{$member->client_id}'
                                              AND subscription > 0
                                              AND coach_id = 0");
      $member->is_new_premium = (empty($is_new_premium)) ? 1 : 0;

      // Просрочка оплаты
      $today = date_create(date('Y-m-d'));
      $pay_date = date_create($member->paid_until);
      $date_delta = date_diff($pay_date, $today);

      $member->expiry_status = ($pay_date < $today) ? 0 : 1;

      $member->expiry = $date_delta->days;
      $member->expiry_period = $date_delta->days . ' ' . $this->num2word($date_delta->days, array('день', 'дня', 'дней'));
      
      return $member;
    }
  }

  /**
   * Получает данные кабинета тренера
   */
  public function getCoachCrmData($login)
  {
    $crm = $this->wpdb->get_row("SELECT * FROM {$this->wpdb->prefix}coaches WHERE coach_id_name = '{$login}' ORDER BY coach_id DESC");

    // Выплаты тренера
    $crm->reward_history = $this->getCoachRewards($crm->coach_id);

    // Ученики тренера
    $crm->clients = $this->getCoachClients($crm->coach_id);

    // платежи клиентов по тренеру
    $crm->pays = $this->getCoachClientsPays($crm->coach_id);

    return $crm;
  }

  /**
   * Получает платежи клиентов, не вылаченные по тренеру
   * @param  int $coach_id [description]
   * @return array
   */
  public function getCoachClientsPays($coach_id)
  {
    $prefix = $this->wpdb->prefix;

    $sql = "SELECT o.coach_id, o.client_id, o.date, o.period, o.wage, o.reward_id, c.client_name "
          ."FROM {$prefix}orders o "
          ."LEFT JOIN {$prefix}clients c ON o.client_id = c.client_id "
          ."WHERE o.coach_id = '{$coach_id}' AND o.reward_id = '0' "
          ."ORDER BY o.date DESC";

    $pays = $this->wpdb->get_results($sql);

    return (!empty($pays)) ? $pays : false;
  }

  /**
   * Получает историю выплат тренера по ID
   */
  public function getCoachRewards($coach_id)
  {
    $prefix = $this->wpdb->prefix;

    $sql = "SELECT id, payment_date, amount, type FROM {$prefix}rewards WHERE coach_id = '{$coach_id}' ORDER BY payment_date DESC";

    $history = $this->wpdb->get_results($sql);

    if (!empty($history)){
      foreach ($history as $key => $value) {
        $history[$key]->pays = $this->getClientPays($value->id);
      }

      return $history;
    } else {
      return false;
    }
  }

  /**
   * Получает платежи клиентов, из которых состоит выплата тренера
   * @param  int $id - ID выплаты
   * @return array - массив платежей
   */
  public function getClientPays($id)
  {
    $prefix = $this->wpdb->prefix;

    $sql = "SELECT o.date, o.amount, o.wage, o.period, c.client_id_name, c.client_name, c.client_tarif_name "
          ."FROM {$prefix}orders o "
          ."LEFT JOIN {$prefix}clients c ON o.client_id = c.client_id "
          ."WHERE o.reward_id = '{$id}' "
          ."ORDER BY o.date DESC";

    $pays = $this->wpdb->get_results($sql);

    if ($pays) {
      foreach ($pays as $key => $value) {
        $pays[$key]->period_title = $this->num2word($value->period, $this->months);
      }
    }
    return ($pays) ? $pays : false;
  }

  /**
   * Получает клиентов тренера
   */
  public function getCoachClients($coach_id)
  {
    $prefix = $this->wpdb->prefix;

    $args = array(
      'role' => 'administrator'
    );

    $admins = get_users( $args );

    $sql = "SELECT client_id_name, client_id, client_name, client_tarif_name, tarif_cost, coach_id, pay_date, can_pay, notify "
          ."FROM {$prefix}clients "
          ."WHERE coach_id = '{$coach_id}' ";

    foreach ($admins as $admin) {
      $sql .= "AND client_name != '{$admin->user_login}' ";
    }

    $sql .= "ORDER BY pay_date ASC";

    $clients = $this->wpdb->get_results($sql);

    foreach ($clients as $client) {
      $result[] = $this->getClientHistory($client);
    }

    return $result;
  }

  /**
   * Получает историю платежей клиента
   * @param $client - объект строки таблицы `clients`
   */
  public function getClientHistory($client)
  {
    $prefix = $this->wpdb->prefix;

    $client->pay_class = (time() >= strtotime($client->pay_date)) ? 'danger' : 'success';

    $is_today = (date('Y-m-d', time()) == date('Y-m-d', strtotime($client->pay_date)));

    if ($client->pay_class == 'danger' && !$is_today && intval($client->pay_date) > 0){

      $today = new \DateTime(date('Y-m-d', time()));
      $pay_date = new \DateTime(date('Y-m-d', strtotime($client->pay_date)));
      $interval = $today->diff($pay_date);

      $client->pay_interval = $interval->days;
    }

    $sql = "SELECT coach_id, client_id, date, amount, period, wage, reward_id "
          ."FROM {$prefix}orders "
          ."WHERE coach_id = '{$client->coach_id}' "
          ."AND client_id = '{$client->client_id}' "
          ."ORDER BY date DESC";

    $history = $this->wpdb->get_results($sql);

    if (!empty($history)){
      foreach ($history as $key => $value) {
        $history[$key]->wait = ($value->reward_id) ? false : true;
        $history[$key]->period_title = $value->period . ' ' . ttcli_num2word($value->period, array('месяц', 'месяца', 'месяцев'));
      }

      $client->history = $history;
    }

    return $client;
  }

  /**
   * Возвращает данные тренера (полностью или ID) по ID лиента
   * @param  int  $client_id [description]
   * @param  bool $full      [description]
   * @return str
   */
  public function getCoachInfoByClientId($client_id, $key = false)
  {
    $prefix = $this->wpdb->prefix;

    $client_sql = "SELECT client_id, coach_id FROM {$prefix}clients WHERE client_id = '{$client_id}'";

    $client = $this->wpdb->get_row($client_sql);

    if (empty($client)) return false;

    $coach_id = $client->coach_id;

    $coach_sql = "SELECT * FROM {$prefix}coaches WHERE coach_id = '{$coach_id}'";

    $coach = $this->wpdb->get_row($coach_sql);

    if (empty($coach)) return false;

    return ($key) ? $coach->$key : $coach;
  }

  /**
   * Получает информацию о клиенте
   * @param  int  $id  [description]
   * @param  str $key [description]
   * @return str
   */
  public function getClientInfo($id, $key = false)
  {
    $prefix = $this->wpdb->prefix;
    $client_sql = "SELECT * FROM {$prefix}clients WHERE client_id = '{$id}'";
    $client = $this->wpdb->get_row($client_sql);

    if (empty($client)) return false;

    return ($key) ? $client->$key : $client;
  }

  /**
   * Получает информацию о члене клуба
   * @param  int  $id  [description]
   * @param  str $key [description]
   * @return str
   */
  public function getMemberInfo($id, $key = false)
  {
    $prefix = $this->wpdb->prefix;
    $member_sql = "SELECT m.*, c.client_tarif_name as tariff, c.tarif_cost as premium_cost, c.pay_date as premium_pay_date, "
          ."c.recurring_id, c.recurring_frequency, c.recurring_expiry "
          ."FROM {$prefix}club m "
          ."LEFT JOIN {$prefix}clients c ON m.client_id = c.client_id "
          ."WHERE m.member_id = '{$id}'";
    $member = $this->wpdb->get_row($member_sql);

    if (empty($member)) return false;

    return ($key) ? $member->$key : $member;
  }

  /**
   * Список последних оплативших в админке модуля
   */
  public function getAdminLastOrders($limit=20)
  {
    $prefix = $this->wpdb->prefix;
    $sql = "SELECT o.coach_id, o.client_id, o.date, o.amount, u.client_name, c.coach_name "
          ."FROM {$prefix}orders o "
          ."LEFT JOIN {$prefix}coaches c ON o.coach_id = c.coach_id "
          ."LEFT JOIN {$prefix}clients u ON o.client_id = u.client_id "
          ."WHERE c.coach_id <> 0 "
          ."ORDER BY o.date DESC "
          ."LIMIT {$limit}";

    $orders = $this->wpdb->get_results($sql);

    if (!empty($orders)){
      $n = 0;
      foreach ($orders as $order) {
        $clent_name = explode(' ', $order->client_name);
        if(count($clent_name) > 1) {
          $orders[$n]->initials = mb_substr($clent_name[0], 0, 1, 'utf-8').mb_substr($clent_name[1], 0, 1, 'utf-8');
        }

        $n++;
      }

      return $orders;
    } else {
      return false;
    }
  }

  /**
   *  Получает список всех должников
   */
  public function getAdminDebtors()
  {
    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));

    $pay_date_end = $today->setTime(0, 0, 0)->format('Y-m-d H:i:s');

    $sql = "SELECT u.client_name, u.pay_date, u.coach_id, u.notify, c.coach_name "
          ."FROM {$prefix}clients u "
          ."LEFT JOIN {$prefix}coaches c ON u.coach_id = c.coach_id "
          ."WHERE DATE(u.pay_date) < '{$pay_date_end}' "
          ."AND u.coach_id > 0 "
          ."ORDER BY u.notify ASC, u.pay_date ASC";

    $debtors = $this->wpdb->get_results($sql);

    if (!empty($debtors)){
      $n = 0;
      foreach ($debtors as $debtor) {
        $pay_date = date_create($debtor->pay_date);
        $interval = $today->diff($pay_date);

        if ($interval->m > 0) {
          $debtors[$n]->interval = $interval->m . ' ' . $this->num2word($interval->m, array('месяц', 'месяца', 'месяцев'))
            . ' и ' . $interval->d . ' ' . $this->num2word($interval->d, array('день', 'дня', 'дней'));
        } else {
          $debtors[$n]->interval = $interval->days . ' ' . $this->num2word($interval->days, array('день', 'дня', 'дней'));
        }

        if ($interval->days == 0 AND $interval->h > 0){
          $debtors[$n]->interval = 'вчера';
        }

        $debtors[$n]->days = $interval->days;

        $n++;
      }

      return $debtors;
    } else {
      return false;
    }
  }

  /**
   * Считает количество должников
   */
  public function getAdminDebtorsCount($active = true)
  {
    $notify = ($active === true) ? 1 : 0;
    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));

    $pay_date_end = $today->setTime(0, 0, 0)->format('Y-m-d H:i:s');

    $sql = "SELECT COUNT(client_id) "
          ."FROM {$prefix}clients "
          ."WHERE DATE(pay_date) < '{$pay_date_end}' "
          ."AND notify = {$notify} "
          ."AND coach_id > 0";

    return $this->wpdb->get_var($sql);
  }

  /**
   * Общий метод для обработки платежей со страницы кабинета
   */
  public function payment($post)
  {
    switch ($post['payment_merchant']) {
      case 'openbank':
        return $this->openbankClientOrder($post);
        break;

      default:
        return array(
          'success' => false,
          'error'   => tlang('Неопознанный мерчант')
        );
        break;
    }
  }

  public function paymentResult($type, $order_id)
  {
    switch ($type) {
      case 'membershipOpenbank':
        return $this->openbankMembershipOrder($order_id);
        break;

      case 'membershipPremiumOpenbank':
        return $this->openbankMembershipPremiumOrder($order_id);
        break;
    }
  }

  /**
   * Регистрация нового члена клуба
   */
  public function memberRegister($post)
  {
    if( email_exists($post['member_email']) ){
      return array(
        'status' => 'error',
        'message' => 'Пользователь с таким email уже зарегистрирован на сайте',
      );
    }

    // Создает нового пользователя Wordpress
    $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );

    $user_id = wp_insert_user(array(
      'user_pass'     => $random_password,
      'user_login'    => $post['member_email'],
      'user_nicename' => $post['member_name'] . ' ' . $post['member_soname'],
      'user_email'    => $post['member_email'],
      'display_name'  => $post['member_name'] . ' ' . $post['member_soname'],
      'nickname'      => $post['member_name'] . ' ' . $post['member_soname'],
      'first_name'    => $post['member_name'],
      'last_name'     => $post['member_soname'],
      'role'          => 'member',
    ));

    if (is_wp_error($user_id)){
      return array(
        'status' => 'error',
        'message' => $user_id->get_error_message(),
      );
    }

    // Создает нового пользователя в таблице `club`
    $prefix = $this->wpdb->prefix;
    $options = $this->getPluginOptions();
    $ms_date = date_interval_create_from_date_string($options['club_period_count'] . ' ' . $options['club_period_type']);
    $date = new \DateTime();
    $ms_seconds = $date->setTimeStamp(0)->add($ms_date)->getTimeStamp();
    $ms_days = $ms_seconds/86400;

    $member = array(
      'member_id_name'  => $post['member_email'],
      'member_name'     => $post['member_name'] . ' ' . $post['member_soname'],
      'ms_price'        => $options['club_price'],
      'ms_days'         => $ms_days,
    );

    $this->wpdb->insert("{$prefix}club", $member);
    $member_id = $this->wpdb->insert_id;

    if (!$member_id){
      return array(
        'status' => 'error',
        'message' => 'Ошибка записи данных в таблицу ' . $prefix . 'club',
      );
    } else {
      // отправляем письмо одмину
      $club_email = $this->getSomeOptions('admin_email', 'tt_club_options');
      $admin_email = ($club_email) ? $club_email : $options['email_address'];
      $admin_subject = 'Новая регистрация в клубе';
      $admin_message = 'Поступила новая заявка на регистрацию в клубе.<br />';
      $admin_message .= 'Данные пользователя:<br />';
      $admin_message .= 'Имя: ' . $post['member_name'] . ' ' . $post['member_soname'] . '<br />';
      $admin_message .= 'Email: ' . $post['member_email'] . '<br />';
      $admin_message .= 'Пароль: ' . $random_password . '<br />';

      $this->sendMessage($admin_subject, $admin_message, $admin_email );

      // Отправляем письмо пользователю
      $member_url = get_site_url(null, 'wp-login.php?action=lostpassword', 'https');
      $member_subject = 'Информация о регистрации в клубе Темп';
      $member_message = 'Уважаемый ' . $post['member_name'] . ' ' . $post['member_soname'] . '!<br />';
      $member_message .= 'Пожалуйста перейдите по ссылке <a href="'.$member_url.'" target="_blank">'.$member_url.'</a> и задайте свой пароль, используя в последствии е-майл '.$post['member_email'].' как логин.';

      $this->sendMessage($member_subject, $member_message, $post['member_email']);

      // Добавляет email в список рассылки Mailchimp
      $members = array(
        0 => array('email_address' => $post['member_email'], 'status' => 'subscribed')
      );
      $mc4wp_apikey = $this->getSomeOptions('api_key', 'mc4wp');
      $mc4wp_list = $this->getSomeOptions('mail_list', 'tt_club_options');

      if ($mc4wp_apikey && $mc4wp_list) {
        $mc4wp_dc = substr($mc4wp_apikey,strpos($mc4wp_apikey,'-')+1);
        $mc4wp_url = 'https://'.$mc4wp_dc.'.api.mailchimp.com/3.0/lists/'.$mc4wp_list.'/members';
        $this->mailchimpCurlConnect($mc4wp_url, 'POST', $mc4wp_apikey, array('members' => $members));
      }

      return array(
        'status' => 'success',
        'message' => 'Вы успешно зарегистрировались в клубе ТЕМП. Письмо с инструкциями отправлено на указанный email',
      );
    }

    return $post;
  }

  /**
   * Взаимодействует с MailChimp
   */
  public function mailchimpCurlConnect( $url, $request_type, $api_key, $data = array() )
  {
    if( $request_type == 'GET' )
      $url .= '?' . http_build_query($data);

    $mch = curl_init();
    $headers = array(
      'Content-Type: application/json',
      'Authorization: Basic '.base64_encode( 'user:'. $api_key )
    );
    curl_setopt($mch, CURLOPT_URL, $url );
    curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
    //curl_setopt($mch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
    curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
    curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type); // according to MailChimp API: POST/GET/PATCH/PUT/DELETE
    curl_setopt($mch, CURLOPT_TIMEOUT, 10);
    curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection

    if( $request_type != 'GET' ) {
      curl_setopt($mch, CURLOPT_POST, true);
      curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json
    }

    return curl_exec($mch);
  }

  /**
   * Отменяет премиум подписку у членов клуба
   */
  public function memberCancelPremium($member_id)
  {
    $prefix = $this->wpdb->prefix;

    $client_id = $this->getMemberInfo($member_id, 'client_id');

    $is_client = $this->wpdb->get_row("SELECT * FROM {$prefix}clients WHERE client_id = '{$client_id}'");

    if ($client_id && $is_client){
      $tariff_name = 'Отключение TrainingPeaks Premium';

      $this->wpdb->update($prefix.'clients', array('client_tarif_name' => $tariff_name), array('client_id' => $client_id));

      $message = 'Член клуба ' . $is_client->client_name . ' запросил отключение подписки "'.$is_client->client_tarif_name.'".';

      $this->sendAdminMessage('Запрос отключения премиум подписки', $message);

      return array(
        'title' => 'Успешно',
        'message' => 'Запрос на отключение подписки направлен администратору',
        'type'  => 'success'
      );
    } else {
      return array(
        'title' => 'Ошибка',
        'message' => 'Инормация о подписке не найдена в базе данных',
        'type'  => 'error'
      );
    }
  }

  /**
   * Подсчитывает количество пользователей
   * @param $type:
   *        `clients` - клиенты
   *        `coaches` - тренеры
   *        `members` - члены клуба
   *        `premium` - премиум подписки
   */
  public function getCountData($type)
  {
    $prefix = $this->wpdb->prefix;

    switch ($type) {
      case 'clients':
        # code...
        break;

      case 'coaches':
        # code...
        break;

      case 'members':
        $today = date_create(date('Y-m-d'));
        $today_date = $today->setTime(00, 00, 00)->format('Y-m-d H:i:s');
        $sql = "SELECT COUNT(member_id) as cnt FROM {$prefix}club WHERE DATE(paid_until) > '{$today_date}'";
        $result = $this->wpdb->get_row($sql);
        return $result->cnt;
        break;

      case 'premium':
        $sql = "SELECT COUNT(client_id) as cnt FROM {$prefix}clients WHERE coach_id = '0'";
        $result = $this->wpdb->get_row($sql);
        return $result->cnt;
        break;

      case 'membership_money':
        $sql = "SELECT COUNT(amount) as cnt FROM {$prefix}club_orders WHERE type = 'membership'";
        $result = $this->wpdb->get_row($sql);
        return $result->cnt;
        break;

      default:
        return false;
        break;
    }
  }

  /**
   * Увеличивает сумму в фонде клуба
   */
  public function increaseClubMoney($sum, $percent = 25)
  {
    $club_options = $this->getSomeOptions(false, 'tt_club_options');

    $money = (int) str_replace(' ', '', $club_options['counters']['money']);

    $new_money = floatval($money + intval($sum) * (intval($percent) / 100));

    $new_money = str_replace('&nbsp;', ' ', number_format_i18n($new_money, 0));

    $club_options['counters']['money'] = $new_money;

    $this->saveSomeOptions($club_options, 'tt_club_options');
  }

  /* ---------------------------------------------------------------------------------------------------------------- */
  /* РАБОТА С ОНЛАЙН ПЛАТЕЖАМИ ЧЕРЕЗ ПРОЦЕССИНГ БАНКА ОТКРЫТИЕ                                                        */
  /* ---------------------------------------------------------------------------------------------------------------- */

  /**
   * Формирует заказ клиента
   * @param  array $post - данные формы
   * @return array
   */
  public function openbankClientOrder($post)
  {
    $options = $this->getPluginOptions();

    $register_data = [
      'userName'    => $options['openbank_user'],
      'password'    => $options['openbank_pass'],
      'orderNumber' => urlencode($post['orderNumber']),
      'amount'      => intval($post['amount']),
      'returnUrl'   => $post['returnUrl'],
      'clientId'    => intval($post['clientId']),
      'description' => strip_tags($post['description']),
      'jsonParams'  => json_encode($post['jsonParams']),
    ];

    $response = $this->openbankGateway('register.do', $register_data);

    if (isset($response['errorCode'])){

      return array(
        'success' => false,
        'error'   => 'Причина: ' . $response['errorMessage'] . ' ' . $this->error_codes[$response['errorCode']],
      );
    } else {
      return array(
        'success'   => true,
        'redirect'  => $response['formUrl'],
      );
    }
  }

  /**
   * Отправляет запросы к платежному сервису
   * @param  array $data - массив данных заказа
   * @return array
   */
  private function openbankGateway($method, $data)
  {
    $options = $this->getPluginOptions();

    // Инициализируем запрос
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $options['openbank_url'].$method,  // Полный адрес метода
      CURLOPT_RETURNTRANSFER => true,                   // Возвращать ответ
      CURLOPT_POST => true,                             // Метод POST
      CURLOPT_POSTFIELDS => http_build_query($data)     // Данные в запросе
    ));

    // Выполненяем запрос
    $response = curl_exec($curl);
    // Декодируем из JSON в массив
    $response = json_decode($response, true);
    // Закрываем соединение
    curl_close($curl);
    // Возвращаем ответ
    return $response;
  }

  /**
   * Проверка оплаты заказа
   */
  public function openbankCheckOrder($order_id){
    $prefix = $this->wpdb->prefix;

    $is_order = $this->wpdb->get_row("SELECT * FROM {$prefix}orders WHERE order_id = '{$order_id}'");

    if($is_order){
      $tpl = [
        'status'  => 'info',
        'title'   => 'Информация о заказе',
        'message' => 'Данный заказ уже оплачен ' . date('d.m.Y', strtotime($is_order->date))
      ];
    } else {
      $options = $this->getPluginOptions();

      // Данные для запроса сведений о заказе
      $gateway_data = [
        'userName'  => $options['openbank_user'],
        'password'  => $options['openbank_pass'],
        'orderId'   => $order_id
      ];

      $response = $this->openbankGateway('getOrderStatusExtended.do', $gateway_data);

      if ((int) $response['orderStatus'] === 2 && (int) $response['errorCode'] === 0){ // Успешная оплата
        // Создает заказ
        $client_id = stristr($response['orderNumber'], '_', true);

        $client_row = $this->wpdb->get_row("SELECT * FROM {$prefix}clients WHERE client_id = '{$client_id}'");

        $is_recurring = (intval($client_row->recurring_frequency) > 0 && !empty($client_row->recurring_id)) ? true : false;

        $tpl = $this->openbankCreateOrder($response, $is_recurring, $client_id);
      } else {
        $tpl = array(
          'status'  => 'flase',
          'title'   => 'Ошибка №' . $response['actionCode'],
          'message' => $this->error_codes[$response['actionCode']]
        );
      }
    }

    return $tpl;
  }

  /**
   * Создает заказ при успешной оплате клиента
   * @param  array $response [description]
   * @param bool $recurring - true, если это автоплатеж
   * @param int $client_id - id клиента в таблице wp_clients
   * @return array
   */
  public function openbankCreateOrder($response, $recurring = false, $client_id = null)
  {
    $prefix = $this->wpdb->prefix;

    if (empty($client_id)) {
      $client_id = stristr($response['orderNumber'], '_', true);
    }

    foreach ($response['merchantOrderParams'] as $key => $param){
      $response['merchantOrderParams'][$param['name']] = $param['value'];
      unset($response['merchantOrderParams'][$key]);
    }

    foreach ($response['attributes'] as $key => $param){
      $response['attributes'][$param['name']] = $param['value'];
      unset($response['attributes'][$key]);
    }

    // Рассчитывает оплаченный период
    $order_amount = (int) $response['amount']/ 100;
    $order_period = $order_amount / (int) $this->getClientInfo($client_id, 'tarif_cost');

    // проверяем платит ли клиент за подписку
    $order_subscription = intval($this->getClientInfo($client_id, 'subscription')) * $order_period;

    // Вычисляем зарплату тренеру для таблицы заказов
    $coach_rate = $this->getCoachInfoByClientId($client_id, 'rate');
    $order_wage = ($order_amount - $order_subscription) * (intval($coach_rate) / 100);

    // Формирует данные для сохранения заказа в таблицу `orders`
    $order_data = [
      'order_id'      => $response['attributes']['mdOrder'],
      'coach_id'      => $this->getClientInfo($client_id, 'coach_id'),
      'client_id'     => $client_id,
      'date'          => date('Y-m-d H:i:s', ($response['date'] / 1000)),
      'amount'        => intval($response['amount']) / 100,
      'period'        => $order_period,
      'subscription'  => $order_subscription,
      'wage'          => $order_wage,
      'reward_id'     => 0
    ];

    // Добавляет новый заказ в БД
    $this->wpdb->insert("{$prefix}orders", $order_data);
    $order_id = $this->wpdb->insert_id;

    if($order_id) {
      $options = $this->getPluginOptions();

      // Отправляет инфу о заказе на email
      if ($recurring) {
        $this->sendRecurringOrderMessage($order_data);
      } else {
        $this->sendCreateOrderMessage($order_data);
      }

      // Увеличиваем зарплату тренера в таблице тренеров
      $coach_reward = intval($this->getCoachInfoByClientId($client_id, 'reward')) + $order_wage;

      // Увеличиваем налоги тренера в таблице тренеров
      $coach_tax = (int) $this->getCoachInfoByClientId($client_id, 'tax') + ((int) $options['coaches_tax'] / 100 * $order_wage);

      // Обновляет данные тренера
      $coach_data = [
        'reward'  => $coach_reward,
        'tax'     => $coach_tax
      ];
      $this->wpdb->update($prefix.'coaches', $coach_data, ['coach_id' => $order_data['coach_id']]);

      // вычисляем дату до которой продливается срок оплаты клиента
      $client_current_pay_date = $this->getClientInfo($client_id, 'pay_date');

      if (intval($client_current_pay_date) > 0){
        // Если клиент платит не в первый раз
        $date_before = date_create($client_current_pay_date);
      } else {
        // Если клиент раньше не платил
        $date_before = date_create(date('Y-m-d'));
      }

      // Дата следующей оплаты (к дате текущей оплаты прибаляется кол-во оплаченных месяцев
      $date_after = date_add($date_before, date_interval_create_from_date_string($order_period.' months'));
      /*
      $date_before_day = intval($date_before->format('d'));

      if ($date_before_day > 28 && isset($response['bindingInfo']['bindingId'])){
        $date_after_day = 1;
        $date_after_month = intval($date_before->format('m')) + 2;
        $date_after_year = intval($date_before->format('Y'));

        $date_after = date_create($date_after_year.'-'.$date_after_month.'-'.$date_after_day);
      } else {
        $date_after = date_add($date_before, date_interval_create_from_date_string($order_period.' months'));
      }
      */

      $client_data['pay_date'] = $date_after->setTime(21, 00, 00)->format('Y-m-d H:i:s');

      // параметры автоплатежа
      if (isset($response['bindingInfo']['bindingId']) && $recurring == false){
        $date_after_day = intval($date_after->format('d'));
        $date_after_day = ($date_after_day > 28) ? 28 : intval($date_after_day);
        $recurring_next_day = ($date_after_day < 10) ? '0'. $date_after_day : $date_after_day;
        $client_recurring_expiry = date_create($response['cardAuthInfo']['expiration'] . $recurring_next_day);

        $client_data['recurring_id'] = $response['bindingInfo']['bindingId'];
        $client_data['recurring_frequency'] = $recurring_next_day;
        $client_data['recurring_expiry'] = $client_recurring_expiry->format('Y-m-d');
      }
      //var_dump($client_data);
      $this->wpdb->update($prefix.'clients', $client_data, array('client_id' => $client_id) );

      if ($recurring){
        // информация для вывода в консоль
        $message = 'Автоплатеж по клиенту '.$this->getClientInfo($client_id, 'client_name'). 'прошел без ошибок';
      } else {
        // Информация для вывода клиенту
        $message = 'Вы оплатили '.$order_period.' '.$this->num2word($order_period, array('месяц', 'месяца', 'месяцев')).'<br />';

        if(isset($client_data['recurring_id'])){
          $message .= 'Подключен автоплатеж '.$client_data['recurring_frequency'].'-го числа каждого месяца.';
          $message .= ' Автоплатеж действителен до '.date('d.m.Y', strtotime($client_data['recurring_expiry'])).'.';
        } else {
          $message .= 'Дата следующего платежа: ' . date('d.m.Y', strtotime($client_data['pay_date']));
        }
      }


      $tpl = [
        'status'  => 'success',
        'title'   => $response['errorMessage'],
        'message' => $message
      ];
    } else {
      $tpl = array(
        'status'  => 'danger',
        'title'   => 'Ошибка!',
        'message' => 'Не удалось записать заказ в базу данных сайта.<br>Обратитесь к администратору'
      );
    }

    return $tpl;

  }

  /**
   * Отменяет автоплатеж для клиента
   */
  public function openbankRecurringCancel($client_id)
  {
    $options = $this->getPluginOptions();

    $request = array(
      'userName'  => $options['openbank_user'],
      'password'  => $options['openbank_pass'],
      'bindingId' => $this->getClientInfo($client_id, 'recurring_id'),
    );

    $response = $this->openbankGateway('unBindCard.do', $request);

    if (intval($response['errorCode']) === 0){

      $client_data = array(
        'recurring_id'        => 0,
        'recurring_frequency' => 0,
        'recurring_expiry'    => 0,
      );

      $this->wpdb->update($this->wpdb->prefix.'clients', $client_data, array('client_id' => $client_id) );

      return array(
        'title' => $response['errorMessage'],
        'message' => __('Автоплатеж отключен', 'tt-client'),
        'type'    => 'success'
      );

    } else {
      $errors = array(
        0 => 'Обработка запроса прошла без системных ошибок',
        1 => 'Неверное состояние связки (попытка деактивировать неактивную связку)',
        2 => 'Связка не найдена',
        3 => 'Доступ запрещён',
        4 => 'Пользователь должен сменить свой пароль',
        5 => 'Системная ошибка',
      );
      return array(
        'title' => $response['errorMessage'],
        'message' =>$errors[$response['errorCode']],
        'type'    => 'error'
      );
    }
  }

  /**
   * Проерка оплаты членства в клубе
   */
  public function openbankMembershipOrder($order_id)
  {
    $prefix = $this->wpdb->prefix;

    $is_order = $this->wpdb->get_row("SELECT * FROM {$prefix}club_orders WHERE merchant_id = '{$order_id}'");

    if($is_order){
      $tpl = array(
        'status'  => 'info',
        'title'   => 'Информация об оплате',
        'message' => 'Данный платеж уже обработан ' . date('d.m.Y', strtotime($is_order->date))
      );
      return $tpl;
    } else {
      $options = $this->getPluginOptions();
      $club_email = $this->getSomeOptions('admin_email', 'tt_club_options');
      $admin_email = ($club_email) ? $club_email : $options['email_address'];

      // Данные для запроса сведений о заказе
      $gateway_data = array(
        'userName'  => $options['openbank_user'],
        'password'  => $options['openbank_pass'],
        'orderId'   => $order_id
      );

      $response = $this->openbankGateway('getOrderStatusExtended.do', $gateway_data);

      if ((int) $response['orderStatus'] === 2 && (int) $response['errorCode'] === 0){ // Успешная оплата

        // Создает заказ
        foreach ($response['merchantOrderParams'] as $key => $param){
          $response['merchantOrderParams'][$param['name']] = $param['value'];
          unset($response['merchantOrderParams'][$key]);
        }
        foreach ($response['attributes'] as $key => $param){
          $response['attributes'][$param['name']] = $param['value'];
          unset($response['attributes'][$key]);
        }

        $payment_period = str_replace(';', ' ', $response['merchantOrderParams']['payment_period']);

        $member_id = $response['merchantOrderParams']['member_id'];

        $member = $this->getMemberInfo($member_id);

        // Сравнивает величину и период оплаты с настройками плагина
        if ((int) $options['club_price'] != $response['amount']/100 ||
            $options['club_period_count'].' '.$options['club_period_type'] != $payment_period){
          $tpl = array(
            'status'  => 'false',
            'title'   => 'Ошибка проверки платежа',
            'message' => 'Выявлено несовпадения в периоде или сумме оплаты'
          );

          return $tpl;
        }

        // Расчитывает дату до которой нужно продлить членство в клубе
        $ms_today = date_create(date('Y-m-d'));
        $ms_date_current = date_create($member->paid_until);

        // Если разница между "Сегодня" и paid_until отрицательная
        // то прибавляет год к текущей дате
        // если положительная - то к paid_until
        $ms_date_default = ($ms_date_current <= $ms_today) ? $ms_today : $ms_date_current;

        $ms_date_new = date_add($ms_date_default, date_interval_create_from_date_string($payment_period));
        $paid_until = $ms_date_new->setTime(21, 00, 00)->format('Y-m-d H:i:s');

        // Обновляет информацию о членстве
        $this->wpdb->update($prefix.'club', array('paid_until' => $paid_until), array('member_id' => $member_id) );

        // Создает новый заказ в таблице `club_orders`
        $order_data = array(
          'merchant_id' => $order_id,
          'merchant'    => $response['merchantOrderParams']['payment_merchant'],
          'type'        => $response['merchantOrderParams']['payment_type'],
          'member'      => $member_id,
          'date'        => date('Y-m-d H:i:s', ($response['date'] / 1000)),
          'amount'      => (int) $response['amount']/100,
          'period'      => $payment_period
        );

        $this->wpdb->insert("{$prefix}club_orders", $order_data);
        $id = $this->wpdb->insert_id;

        // Увеличивает сумму в фонде клуба
        $this->increaseClubMoney($order_data['amount']);

        // Формирует сообщение для отправки email клиенту
        if (isset($response['merchantOrderParams']['is_new'])){
          $template = $this->getPluginOptions('club_membership_start', true);

          $subject_member = $template['subject'];

          $message_replace = array(
            '{клиент}'    => $member->member_name,
            '{цена}'      => $order_data['amount'] . ' руб.',
            '{период}'    => $this->convertToPeriod($options['club_period_count'], $options['club_period_type']),
          );

          $message_member = stripslashes(strtr($template['message'], $message_replace));
        } else {
          $subject_member = 'Оплата членства в клубе';
          $message_member = $response['orderDescription'] . ' успешно проведена. Платеж сохранен в системе под номером №'.$id;
        }

        $this->sendMessage($subject_member, $message_member, $member->member_id_name);

        // Отправлет email админу
        $message_admin = $response['orderDescription'] . ' от клиента '.$member->member_name.' успешно проведена. Платеж сохранен в системе под номером №'.$id;
        $this->sendMessage('Оплата членства в клубе', $message_admin, $admin_email);

        // Выводит сообщению на страницу
        return  array(
          'status'  => 'success',
          'title'   => 'Успешно',
          'message' => $message_member
        );

      } else {
        // Возвращает информацию об ошибке
        $tpl = array(
          'status'  => 'false',
          'title'   => 'Ошибка №' . $response['actionCode'],
          'message' => $this->error_codes[$response['actionCode']]
        );

        return $tpl;
      }
    }
  }

  /**
   * Оплата премиум подписки членом клуба
   */
  public function openbankMembershipPremiumOrder($order_id)
  {
    $prefix = $this->wpdb->prefix;

    $is_order = $this->wpdb->get_row("SELECT * FROM {$prefix}orders WHERE order_id = '{$order_id}'");

    if($is_order){
    } else {
      $options = $this->getPluginOptions();

      // Данные для запроса сведений о заказе
      $gateway_data = array(
        'userName'  => $options['openbank_user'],
        'password'  => $options['openbank_pass'],
        'orderId'   => $order_id
      );

      $response = $this->openbankGateway('getOrderStatusExtended.do', $gateway_data);

      if ((int) $response['orderStatus'] === 2 && (int) $response['errorCode'] === 0){
        // Успешная оплата
        foreach ($response['merchantOrderParams'] as $key => $param){
          $response['merchantOrderParams'][$param['name']] = $param['value'];
          unset($response['merchantOrderParams'][$key]);
        }

        foreach ($response['attributes'] as $key => $param){
          $response['attributes'][$param['name']] = $param['value'];
          unset($response['attributes'][$key]);
        }

        $payment_period = $response['merchantOrderParams']['payment_months'] . ' months';

        $member_id = $response['merchantOrderParams']['member_id'];
        $client_id = $response['merchantOrderParams']['client_id'];

        $member = $this->getMemberInfo($member_id);

        // Расчитывает дату до которой нужно продлить членство в клубе
        $date_current = ($member->premium_pay_date) ? date_create($member->premium_pay_date) : date_create(date('Y-m-d'));
        $date_new = date_add($date_current, date_interval_create_from_date_string($payment_period));
        $premium_pay_date = $date_new->setTime(21, 00, 00)->format('Y-m-d H:i:s');

        // Обновляет/создает информацию о члене клуба в таблице клиентов
        $is_member_client = $this->wpdb->get_row("SELECT * FROM {$prefix}clients "
                                                ."WHERE client_id_name = '{$member->member_id_name}' "
                                                ."AND coach_id = 0");
        if ($is_member_client) {
          // Если клиент есть, но с членом клуба не связан
          if (!$member->client_id){
            $this->wpdb->update($prefix.'club', ['client_id' => $is_member_client->client_id], ['member_id' => $member->member_id]);
          }
          // обновляет данные
          $client_update = array(
            'pay_date'  => $premium_pay_date,
          );

          // параметры автоплатежа
          if (isset($response['bindingInfo']['bindingId']) && !$is_member_client->recurring_id){
            $client_update['recurring_id'] = $response['bindingInfo']['bindingId'];
            $client_update['recurring_frequency'] = $response['merchantOrderParams']['recFrequency'];

            $recurring_expiry = date_create($response['cardAuthInfo']['expiration'] . $client_update['recurring_frequency']);
            $client_update['recurring_expiry'] = $recurring_expiry->format('Y-m-d');
          }


          $client_where = array(
            'client_id_name' => $member->member_id_name,
            'coach_id'  => 0
          );
          $this->wpdb->update($prefix.'clients', $client_update, $client_where);
        } else {
          // создает новую запись
          $client_data = array(
            'client_id_name'    => $member->member_id_name,
            'client_name'       => $member->member_name,
            'client_tarif_name' => 'Подписка TrainingPeaks Premium',
            'tarif_cost'        => $response['amount']/100,
            'pay_date'          => $premium_pay_date,
            'can_pay'           => 1,
            'coach_id'          => 0,
          );

          // параметры автоплатежа
          if (isset($response['bindingInfo']['bindingId'])){
            $client_data['recurring_id'] = $response['bindingInfo']['bindingId'];
            $client_data['recurring_frequency'] = $response['merchantOrderParams']['recFrequency'];

            $recurring_expiry = date_create($response['cardAuthInfo']['expiration'] . $client_data['recurring_frequency']);
            $client_data['recurring_expiry'] = $recurring_expiry->format('Y-m-d');
          }

          //var_dump($client_data);
          $this->wpdb->insert("{$prefix}clients", $client_data);
          $this->wpdb->update($prefix.'club', ['client_id' => $this->wpdb->insert_id], ['member_id' => $member->member_id]);
        }

        $is_recurring = (isset($response['bindingInfo']['bindingId']) && isset($response['merchantOrderParams']['recFrequency'])) ? true : false;

        // Формирует данные для сохранения заказа в таблицу `orders`
        $order_data = array(
          'order_id'      => $response['attributes']['mdOrder'],
          'coach_id'      => 0,
          'client_id'     => ($member->client_id) ? $member->client_id : $is_member_client->client_id,
          'date'          => date('Y-m-d H:i:s', ($response['date'] / 1000)),
          'amount'        => 0,
          'period'        => $response['merchantOrderParams']['payment_months'],
          'subscription'  => intval($response['amount']) / 100,
          'wage'          => 0,
          'reward_id'     => 0
        );

        // Добавляет новый заказ в БД
        //var_dump($order_data);
        $this->wpdb->insert("{$prefix}orders", $order_data);
        $id = $this->wpdb->insert_id;

        // Формирует сообщение для отправки email клиенту
        $message_member = $response['orderDescription'] . ' успешно проведена. Платеж на сумму '.$order_data['subscription'].' руб. сохранен в системе под номером №'.$id;
        if ($is_recurring){
          $message_member .= '<br> Был подключен автоплатеж каждого '.$response['merchantOrderParams']['recFrequency']. ' числа месяца';
        }
        if (isset($response['merchantOrderParams']['is_activation'])){
          $message_member .= '<br> Для подключения к TrainingPeaks перейдите по <a href="https://temptraining.ru/tpregister/" target="_blank">ссылке</a>.';
        }

        $this->sendMessage('Оплата премиум подписки', $message_member, $member->member_id_name);

        // Отправлет email админу
        $message_admin = $response['orderDescription'] . ' от клиента '.$member->member_name.' успешно проведена. Платеж сохранен в системе под номером №'.$id;
        if (isset($response['merchantOrderParams']['is_activation'])){
          $message_admin .= '<br> Клиенту необходимо активировать Премиум.';
        }
        $this->sendAdminMessage('Оплата премиум подписки', $message_admin);

        // Выводит сообщению на страницу
        return array(
          'status'  => 'success',
          'title'   => 'Успешно!',
          'message' => $message_member
        );


      } else {
        // Возвращает информацию об ошибке
        return array(
          'status'  => 'false',
          'title'   => 'Ошибка №' . $response['actionCode'],
          'message' => $this->error_codes[$response['actionCode']]
        );
      }
    }
  }

  /* ---------------------------------------------------------------------------------------------------------------- */
  /* ФУНЦИИ КРОНА ДЛЯ АВТОМАТИЧЕСКИХ ПЛАТЕЖЕЙ                                                                         */
  /* ---------------------------------------------------------------------------------------------------------------- */
  public function cronRecurringPays()
  {
    $day = (int) date('d');

    $clients = $this->getRecurringTodayClients($day);

    if($clients){
      echo 'Found '.count($clients).' clients'.PHP_EOL;

      foreach ($clients as $client) {
        $this->makeRecurringPayment($client);
      }
    } else {
      echo 'Clients not found';
    }
  }

  /**
   * Автоматические платежи клиентов, которые просрочили оплату на 2 дня
   * @param  [type] $day [description]
   * @return [type]      [description]
   */
  public function cronRecurringPaysExpire($days)
  {
    // Дата: сегодня
    $today = date_create(date('Y-m-d'));
    $today_date = $today->format('Y-m-d');

    // Дата: оплата за вычетом просроченных дней
    $pay_date = date_sub($today, date_interval_create_from_date_string($days.' days'));
    $pay_date_start = $pay_date->setTime(00, 00, 00)->format('Y-m-d H:i:s');
    $pay_date_end = $pay_date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    // Частота автоплатежа
    $recurring_frequency = intval(date('d')) - intval($days);

    // Выбор клиентов для проведения оплаты
    $prefix = $this->wpdb->prefix;
    $query = "SELECT client_id, client_id_name, client_tarif_name, pay_date, tarif_cost, recurring_id, recurring_frequency, recurring_expiry "
            ."FROM {$prefix}clients "
            ."WHERE notify = '1' "
            ."AND recurring_id <> '' "
            ."AND recurring_frequency = '{$recurring_frequency}' "
            ."AND DATE(recurring_expiry) >= '{$today_date}' "
            ."AND DATE(pay_date) >= '{$pay_date_start}' "
            ."AND DATE(pay_date) <= '{$pay_date_end}'";

    // Клиенты, которые должны были оплатить $days дней назад
    $clients = $this->wpdb->get_results($query);

    echo 'Serch clients with `recurring_frequency` = '.$recurring_frequency.' and `pay_date` between '.$pay_date_start
        .' and ' . $pay_date_end . PHP_EOL;

    if (!empty($clients)){
      echo 'Found '.count($clients).' clients'.PHP_EOL;

      foreach ($clients as $client) {
        $this->makeRecurringPayment($client);
      }
    } else {
      echo 'Clients for `cronRecurringPaysExpire('.$days.')` not found...'.PHP_EOL;
    }
  }

  /**
   * Выбирает клиентов, у которых на сегодня назначен автоматический платеж
   * @param  int $day [description]
   * @return str
   */
  private function getRecurringTodayClients($day)
  {
    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));

    $today_date = $today->format('Y-m-d');
    $date_start = $today->setTime(00, 00, 00)->format('Y-m-d H:i:s');
    $date_end = $today->setTime(23, 59, 59)->format('Y-m-d H:i:s');


    $query = "SELECT client_id, client_id_name, client_tarif_name, tarif_cost, recurring_id, recurring_frequency, recurring_expiry "
            ."FROM {$prefix}clients "
            ."WHERE notify = '1' "
            ."AND recurring_id <> '' "
            ."AND recurring_frequency = '{$day}' "
            ."AND DATE(recurring_expiry) >= '{$today_date}' "
            ."AND DATE(pay_date) < '{$date_end}'";

    // Клиенты, которые должны оплатить сегодня
    $clients = $this->wpdb->get_results($query);

    echo 'Serch clients with `recurring_frequency` = '.$day.' and `recurring_expiry` > '.$today_date.PHP_EOL;

    return (empty($clients)) ? false : $clients;
  }

  /**
   * Проводит автоматический платеж
   * @param  [type] $client [description]
   * @return [type]         [description]
   */
  private function makeRecurringPayment($client)
  {
    $options = $this->getPluginOptions();

    $client_error_message = "Неудачная попытка проведения автоматического платежа. Следующая будет через 2 дня";

    $client_name = $this->getClientInfo($client->client_id, 'client_name');

    $order_statuses = $this->order_statuses;
    $error_codes = $this->error_codes;

    // сначала регистрируем заказ
    $order_request = array(
      'userName'    => $options['openbank_user_ssl'],
      'password'    => $options['openbank_pass_ssl'],
      'orderNumber' => $client->client_id . '_' . date('ymd_Hi'),
      'amount'      => (int) $client->tarif_cost * 100,
      'returnUrl'   => 'https://temptraining.ru',
      'clientId'    => $client->client_id,
      'description' => 'Автоматическая оплата тарифа ' . $client->client_tarif_name,
      'jsonParams'  => json_encode(array(
        'email'     => $client->client_id_name,
        'month_count' => 1
      )),
    );

    $order_result = $this->openbankGateway('register.do', $order_request);

    if(isset($order_result['orderId'])){

      echo 'Create order with number: '.$order_result['orderId'].PHP_EOL;

      $payment_request = array(
        'userName'    => $options['openbank_user_ssl'],
        'password'    => $options['openbank_pass_ssl'],
        'mdOrder'     => $order_result['orderId'],
        'bindingId'   => $client->recurring_id,
        'ip'          => gethostbyname(gethostname()),
      );

      $payment_result = $this->openbankGateway('paymentOrderBinding.do', $payment_request);

      if($payment_result['errorCode'] == 0){
        // проверяем оплачен ли заказ
        $final_request = array(
          'userName'  => $options['openbank_user_ssl'],
          'password'  => $options['openbank_pass_ssl'],
          'orderId'   => $order_result['orderId']
        );

        $final_result = $this->openbankGateway('getOrderStatusExtended.do', $final_request);

        if (intval($final_result['orderStatus']) === 2 && intval($final_result['errorCode']) === 0){ // Успешная оплата
          // Создает заказ
          $cron_result = implode('\n\l', $this->openbankCreateOrder($final_result, true));
        } else {
          $cron_result = 'Ошибка при попытке автоплатежа клиента '. $client_name . '.<br/>';

          if (intval($final_result['errorCode']) !== 0){
            $cron_result .= 'Причина: ' . $error_codes[$final_result['actionCode']] . '.<br/>';
          }

          if (intval($final_result['orderStatus']) !== 2){
            $cron_result .= 'Cтатус заказа: ' . $order_statuses[$final_result['orderStatus']] . '.<br/>';
          }

          $this->sendRecurringFailMessage($client->client_id, false, false);

          $adminError = array(
            'subject' => 'Ошибка автоплатежа по клиенту '.$client_name,
            'message' => $cron_result,
          );

          $this->sendAdminMessage($adminError['subject'], $adminError['message']);

        }

        echo str_replace('<br/>', PHP_EOL, $cron_result);

      } else {
        $errors = array(
          1 => 'Обработка запроса прошла без системных ошибок',
          2 => 'Необходимо указать CVC2/CVV2, поскольку у мерчатна нет разрешения на проведение оплаты без CVC',
          3 => 'Неверный формат CVC',
          4 => 'Неверный язык',
          5 => 'Связка не найдена',
          6 => 'Заказ с таким номером не найден',
          7 => 'Доступ запрещен',
          8 => 'Пользователь, осуществляющий вызов сервиса, должен изменить свой пароль',
          9 => 'Системная ошибка',
        );
        // отправляет email админу
        $this->sendRecurringFailMessage($client->client_id, $errors[$payment_result['errorCode']], false);
      }
    } else {
      $error_message =  'Ошибка №'. $order_result['errorCode'] . ': ' . $order_result['errorMessage'] . PHP_EOL;
      echo $error_message;
      $this->sendRecurringFailMessage($client->client_id);
      //$this->sendAdminMessage('Ошибка автоплатежа по клиенту '.$client->client_name, $error_message);
    }
  }

  /**
   * Напоминание о предстоящем платеже за $days дней до осуществления платежа
   */
  public function cronRecurringBeforeNotify($days = false)
  {
    if ($days === false) exit();

    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));

    $today_date = $today->format('Y-m-d');

    $date = date_add($today, date_interval_create_from_date_string($days.' days'));

    $day_frequency = (int) $date->format('d');

    $date_start = $date->setTime(00, 00, 00)->format('Y-m-d H:i:s');
    $date_end = $date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    echo 'Select clients with `pay_date` between ' . $date_start . ' and ' . $date_end . PHP_EOL;
    echo 'And with `recurring_frequency`=' . $day_frequency . PHP_EOL;

    $query = "SELECT client_id, client_id_name, client_name, client_tarif_name, tarif_cost, subscription, recurring_id, recurring_frequency, recurring_expiry "
            ."FROM {$prefix}clients "
            ."WHERE recurring_id <> '' "
            ."AND recurring_frequency = '{$day_frequency}' "
            ."AND DATE(recurring_expiry) >= '{$today_date}' "
            ."AND DATE(pay_date) >= '{$date_start}' "
            ."AND DATE(pay_date) <= '{$date_end}'";

    $clients = $this->wpdb->get_results($query);

    if(empty($clients)){
      echo 'Clients for `cronRecurringBeforeNotify` not found...' . PHP_EOL . 'Bye bye' . PHP_EOL;
    } else {
      $options = $this->getPluginOptions();

      foreach ($clients as $client) {
        // Список связок клиента
        $bindings_request = array(
          'userName'  => $options['openbank_user'],
          'password'  => $options['openbank_pass'],
          'clientId'  => $client->client_id,
        );

        $bindings_response = $this->openbankGateway('getBindings.do', $bindings_request);

        if($bindings_response['errorCode'] == 0){
          foreach ($bindings_response['bindings'] as $binding) {
            if($client->recurring_id == $binding['bindingId']){
              $card = $binding['maskedPan'];
            } else {
              echo 'Client card not found';
            }
          }

        } else {
          echo $bindings_response['errorMessage'];
        }

        if ($card){
          $message_replace = array(
            '{клиент}'    => $client->client_name,
            '{дни}'       => $days . ' ' . $this->num2word($days, array('день', 'дня', 'дней')),
            '{карта}'     => $card,
            '{сумма}'     => $client->tarif_cost,
            '{подписка}'  => $client->subscription,
            '{тариф}'     => $client->client_tarif_name,
          );

          $this->sendRecurringBeforeMessage($message_replace, $client->client_id_name);
          echo 'Send message to ' . $client->client_id_name . PHP_EOL;
        }

      }

    }

  }

  /**
   * Напоминание об окончании действия связки, и невозможности дальнейших автоплатежей
   */
  public function cronRecurringEndNotify($days = false)
  {
    if ($days === false) return false;

    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));

    $expiry = date_add($today, date_interval_create_from_date_string($days.' days'));

    $expiry_date = $expiry->format('Y-m-d');

    echo 'Select clients with `recurring_expiry` less ' . $expiry_date . PHP_EOL;

    $query = "SELECT client_id, client_id_name, client_name, client_tarif_name, recurring_id, recurring_expiry "
            ."FROM {$prefix}clients "
            ."WHERE recurring_id <> '' "
            ."AND DATE(recurring_expiry) > '0000-00-00' "
            ."AND DATE(recurring_expiry) <= '{$expiry_date}'";

    $clients = $this->wpdb->get_results($query);

    if(empty($clients)){
      echo 'Clients for `cronRecurringEndNotify` not found...' . PHP_EOL . 'Bye bye' . PHP_EOL;
    } else {
      $options = $this->getPluginOptions();

      foreach ($clients as $client) {
        // Список связок клиента
        $bindings_request = array(
          'userName'  => $options['openbank_user'],
          'password'  => $options['openbank_pass'],
          'clientId'  => $client->client_id,
        );

        $bindings_response = $this->openbankGateway('getBindings.do', $bindings_request);

        if($bindings_response['errorCode'] == 0){
          foreach ($bindings_response['bindings'] as $binding) {
            if($client->recurring_id == $binding['bindingId']){
              $card = $binding['maskedPan'];
            } else {
              echo 'Client card not found';
            }
          }

        } else {
          echo $bindings_response['errorMessage'];
        }

        if ($card){
          $client_data = array(
            'recurring_id'        => 0,
            'recurring_frequency' => 0,
            'recurring_expiry'    => 0,
          );

          $this->wpdb->update($this->wpdb->prefix.'clients', $client_data, array('client_id' => $client->client_id) );

          $message_replace = array(
            '{клиент}'    => $client->client_name,
            '{дни}'       => $days . ' ' . $this->num2word($days, array('день', 'дня', 'дней')),
            '{карта}'     => $card,
            '{тариф}'     => $client->client_tarif_name,
          );

          $this->sendRecurringEndMessage($message_replace, $client->client_id_name);
          echo 'Send message to ' . $client->client_id_name . PHP_EOL;
        }

      }
    }
  }

  /**
   * Напоминание клиентам о необходимости совершить платеж через $days дней
   * @param  integer $days [description]
   * @return [type]        [description]
   */
  public function cronClientsPayNotyfy($days = false)
  {
    if ($days === false) return false;

    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));

    $today_date = $today->format('Y-m-d');

    $date = ($days === false) ? $today : date_add($today, date_interval_create_from_date_string($days.' days'));

    $date_start = $date->setTime(00, 00, 00)->format('Y-m-d H:i:s');
    $date_end = $date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    echo 'Select clients with `pay_date` between ' . $date_start . ' and ' . $date_end . PHP_EOL;

    $query = "SELECT client_id, client_id_name, client_name, client_tarif_name, tarif_cost, pay_date, recurring_id "
            ."FROM {$prefix}clients "
            ."WHERE recurring_id = '0' "
            ."AND notify = '1' "
            ."AND DATE(pay_date) >= '{$date_start}' "
            ."AND DATE(pay_date) <= '{$date_end}'";

    $clients = $this->wpdb->get_results($query);

    if(empty($clients)){
      echo 'Clients for `cronClientsPayNotyfy` not found...' . PHP_EOL . 'Bye bye' . PHP_EOL;
    } else {
      foreach ($clients as $client) {

        $subject = 'Напоминание о платеже';

        $message = 'Уважаемый(ая) '. $client->client_name . '!<br>';
        $message .= 'Мы напоминаем Вам о том, что необходимо совершить платеж на сумму ' . $client->tarif_cost . ' рублей.<br>';
        $message .= 'Срок оплаты вашего тарифа (' . $client->client_tarif_name . ') ';
        $message .= 'истекает ' . $this->localdate(strtotime($client->pay_date)) . '<br>';
        $message .= 'Вы можете оплатить тренировки картой через личный кабинет https://temptraining.ru/client/<br>';
        $message .= 'Если Вы оплатили будущий период, а письмо с напоминанием продолжает приходить, пожалуйста свяжитесь с нами. Возможно, мы не можем идентифицировать Ваш платёж.';

        $this->sendMessage($subject, $message, $client->client_id_name);
      }
    }
  }

  /**
   * Напоминает клиенту о просроченой оплате
   * @param  boolean $days [description]
   * @return [type]        [description]
   */
  public function cronClientsPayExpired($days=false, $recurring = false)
  {
    if ($days === false) return false;

    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));

    $today_date = $today->format('Y-m-d');

    $date = date_sub($today, date_interval_create_from_date_string($days.' days'));

    $date_start = $date->setTime(00, 00, 00)->format('Y-m-d H:i:s');
    $date_end = $date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    echo 'Выбор клиентов с полем `pay_date` от ' . $date_start . ' и до ' . $date_end . PHP_EOL . '...' . PHP_EOL;

    $recurring_where = ($recurring) ? "WHERE recurring_id > '0' " : "WHERE recurring_id = '0' ";

    $query = "SELECT client_id, client_id_name, client_name, client_tarif_name, tarif_cost, pay_date, recurring_id "
            ."FROM {$prefix}clients "
            .$recurring_where
            ."AND notify = '1' "
            ."AND DATE(pay_date) >= '{$date_start}' "
            ."AND DATE(pay_date) <= '{$date_end}'";

    $clients = $this->wpdb->get_results($query);

    if(empty($clients)){
      echo 'Клиенты для выполнения функции `cronClientsPayExpired('.$days.')` не найдены...' . PHP_EOL . 'Выход...' . PHP_EOL . PHP_EOL;
    } else {
      $admin_message = '';
      foreach ($clients as $client) {
        // напоминалка клиенту
        $client_subject = 'Напоминание о просроченном платеже';

        $client_message = 'Уважаемый(ая) '. $client->client_name . '!<br>';
        $client_message .= 'Мы напоминаем Вам о том, что необходимо совершить платеж на сумму ' . $client->tarif_cost . ' рублей.<br>';
        $client_message .= 'Срок оплаты вашего тарифа (' . $client->client_tarif_name . ') ';
        $client_message .= 'истек ' . $this->localdate(strtotime($client->pay_date)) . '<br>';
        $client_message .= 'Просрочено уже ' . $days . ' ' . $this->num2word($days, array('день', 'дня', 'дней')). '<br>';
        $client_message .= 'Вы можете оплатить тренировки картой через личный кабинет https://temptraining.ru/client/<br>';
        $client_message .= 'Если Вы оплатили будущий период, а письмо с напоминанием продолжает приходить, пожалуйста свяжитесь с нами. Возможно, мы не можем идентифицировать Ваш платёж.';

        $this->sendMessage($client_subject, $client_message, $client->client_id_name);

        // напоминалка тренеру
        //if ($this->getCoachInfoByClientId($client->client_id, 'notify')) {}
        if ($days > 9){
          $coach_subject = 'Напоминаие о платеже клиента';

          $coach_message = 'Уважаемый(ая) '.$this->getCoachInfoByClientId($client->client_id, 'coach_name').'!<br>';
          $coach_message .= 'Мы напоминаем Вам о том, что вашему клиенту '.$client->client_name.' ';
          $coach_message .= 'необходимо совершить платеж на сумму '.$client->tarif_cost.' рублей. ';
          $coach_message .= 'по тарифу '.$client->client_tarif_name.'.<br>';
          $coach_message .= 'На данный момент платеж просрочен на '.$days.' '.$this->num2word($days, array('день','дня','дней'));

          $coach_email = $this->getCoachInfoByClientId($client->client_id, 'coach_id_name');

          $this->sendMessage($coach_subject, $coach_message, $coach_email);
        }



        // напоминание админу
        $admin_email = $this->getPluginOptions('admin_noty');
        if($admin_email){
          $admin_message .= 'Клиент: '.$client->client_name.'.<br>';
          $admin_message .= 'Сумма: '.$client->tarif_cost . ' рублей.<br>';
          $admin_message .= 'Срок оплаты: '.$this->localdate(strtotime($client->pay_date)).' ';
          $admin_message .= 'Просрочено уже: '. $days . ' ' . $this->num2word($days, array('день', 'дня', 'дней')).'<br>';
          $admin_message .= 'Тренер: '.$this->getCoachInfoByClientId($client->client_id, 'coach_name').'.<br>';
          $admin_message .= 'Тариф: '.$client->client_tarif_name.'.<br><hr>';
        }
      }
      if (trim($admin_message)) return $admin_message;
    }
  }

  /* ---------------------------------------------------------------------------------------------------------------- */
  /* СПОРТИВНЫЙ КЛУБ                                                                                                  */
  /* ---------------------------------------------------------------------------------------------------------------- */

  public function cronClubMembershipNotify()
  {
    $options = $this->getPluginOptions();
    $templates = $this->getPluginOptions('club_membership_end', true);
    $prefix = $this->wpdb->prefix;

    $today = date_create(date('Y-m-d'));
    $date_interval = $options['club_notify']['membership_count'] . ' ' . $options['club_notify']['membership_type'];
    $date = date_add($today, date_interval_create_from_date_string($date_interval));

    $date_start = $date->setTime(00, 00, 00)->format('Y-m-d H:i:s');
    $date_end = $date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    echo 'Select members with `paid_until` between ' . $date_start . ' and ' . $date_end . PHP_EOL;

    $query = "SELECT * "
            ."FROM {$prefix}club "
            ."WHERE DATE(paid_until) >= '{$date_start}' "
            ."AND DATE(paid_until) <= '{$date_end}'";

    $members = $this->wpdb->get_results($query);

    if(empty($members)){
      echo 'Members for `cronClubMembershipNotify` not found...' . PHP_EOL . 'Bye bye' . PHP_EOL;
    } else {
      echo 'For `cronClubMembershipNotify` found ' . count($members) . ' members' . PHP_EOL;

      $subject = $templates['subject'];

      foreach ($members as $member) {
        $message_replace = array(
          '{клиент}'    => $member->member_name,
          '{срок}'      => $this->convertToPeriod($options['club_notify']['membership_count'], $options['club_notify']['membership_type']),
          '{цена}'      => $options['club_price'] . ' руб.',
          '{период}'    => $this->convertToPeriod($options['club_period_count'], $options['club_period_type']),
        );

        $message = strtr($templates['message'], $message_replace);

        $this->sendMessage($subject, $message, $member->member_id_name);
      }
    }
  }

  /* ---------------------------------------------------------------------------------------------------------------- */
  /* ОТПРАВКА УВЕДОМЛЕНИЙ                                                                                             */
  /* ---------------------------------------------------------------------------------------------------------------- */
  /**
   * Отправляет письмо об оплате заказа
   */
  public function sendCreateOrderMessage($order)
  {
    $options = $this->getPluginOptions();
    $templates = $this->getPluginOptions(false, true);

    $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
    $headers[] = 'content-type: text/html';

    $subject = $templates['email_subject_order'];

    $message_replace = array(
      '{клиент}'    => $this->getClientInfo($order['client_id'], 'client_name'),
      '{тренер}'    => $this->getCoachInfoByClientId($order['client_id'], 'coach_name'),
      '{сумма}'     => $order['amount'],
      '{подписка}'  => $order['subscription'],
      '{тариф}'     => $this->getClientInfo($order['client_id'], 'client_tarif_name'),
    );

    $message = strtr($templates['email_template_order'], $message_replace);

    // Отправляет email администратору при необходимости
    if (is_email($options['admin_noty'])){
      wp_mail($options['admin_noty'], $subject, $message, $headers );
    }

    // Отправляет email тренеру при необходимости
    $coach_notify = $this->getCoachInfoByClientId($order['client_id'], 'notify');
    $coach_email = $this->getCoachInfoByClientId($order['client_id'], 'coach_id_name');

    if ($coach_notify && is_email($coach_email)){
      wp_mail($coach_email, $subject, $message, $headers );
    }

  }

  /**
   * Отправляет письмо об автоматической оплате заказа
   */
  public function sendRecurringOrderMessage($order)
  {
    $options = $this->getPluginOptions();
    $templates = $this->getPluginOptions(false, true);

    $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
    $headers[] = 'content-type: text/html';

    $subject = $templates['email_subject_order_auto'];

    $message_replace = array(
      '{клиент}'    => $this->getClientInfo($order['client_id'], 'client_name'),
      '{тренер}'    => $this->getCoachInfoByClientId($order['client_id'], 'coach_name'),
      '{сумма}'     => $order['amount'],
      '{подписка}'  => $order['subscription'],
      '{тариф}'     => $this->getClientInfo($order['client_id'], 'client_tarif_name'),
    );

    $message = strtr($templates['email_template_order_auto'], $message_replace);

    // Отправояет email клиенту
    $client_email = $this->getClientInfo($order['client_id'], 'client_id_name');
    $client_notify = $this->getClientInfo($order['client_id'], 'notify');
    if ($client_notify && is_email($client_email)){
      wp_mail($client_email, $subject, $message, $headers );
    }

    // Отправляет email администратору при необходимости
    if (is_email($options['admin_noty'])){
      wp_mail($options['admin_noty'], $subject, $message, $headers );
    }

    // Отправляет email тренеру при необходимости
    $coach_notify = $this->getCoachInfoByClientId($order['client_id'], 'notify');
    $coach_email = $this->getCoachInfoByClientId($order['client_id'], 'coach_id_name');

    if ($coach_notify && is_email($coach_email)){
      wp_mail($coach_email, $subject, $message, $headers );
    }
  }

  /**
   * Отправляет клиенту уведомление о предстоящем автоплатеже
   */
  public function sendRecurringBeforeMessage($message_replace, $client_email)
  {
    $options = $this->getPluginOptions();
    $templates = $this->getPluginOptions(false, true);

    $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
    $headers[] = 'content-type: text/html';

    $subject = $templates['email_subject_preorder_auto'];

    $message = strtr($templates['email_template_preorder_auto'], $message_replace);

    if (is_email($client_email)){
      wp_mail($client_email, $subject, $message, $headers );
    }
  }

  /**
   * Отправляет сообщение об окончании срока действия карты и автоплатежа
   */
  public function sendRecurringEndMessage($message_replace, $client_email)
  {
    $options = $this->getPluginOptions();
    $templates = $this->getPluginOptions(false, true);

    $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
    $headers[] = 'content-type: text/html';

    $subject = $templates['email_subject_recurring_end'];

    $message = strtr($templates['email_template_recurring_end'], $message_replace);

    if (is_email($client_email)){
      wp_mail($client_email, $subject, $message, $headers );
    }
  }

  /**
   * Отправляет письмо админу при ошибке автоматического платежа
   */
  public function sendRecurringFailMessage($client_id, $error=false, $send_admin = true)
  {
    $options = $this->getPluginOptions();

    $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
    $headers[] = 'content-type: text/html';

    $subject = 'Ошибка при проведении автоматического платежа';

    $message = 'При попытке оплаты в автоматическом режиме по клиенту'.': ';
    $message .= $this->getClientInfo($client_id, 'client_name'). ' ';
    $message .= 'произошла ошибка';

    if($error) {
      $message .= '<br>. Текст ошибки: ' . $error;
    } else {
        $message .= '<br>. Следующая попытка будет через 2 дня';
    }

    $client_email = $this->getClientInfo($client_id, 'client_id_name');
    if (is_email($client_email)){
      wp_mail($client_email, $subject, $message, $headers );
    }


    if (is_email($options['admin_noty']) && $send_admin){
      wp_mail($options['admin_noty'], $subject, $message, $headers );
    }
  }

  /**
   * Отправляет клиенту уведомление о предстоящем платеже
   * @param  [type] $subject      [description]
   * @param  [type] $message      [description]
   * @param  [type] $client_email [description]
   * @return [type]               [description]
   */
  public function sendMessage($subject, $message, $email)
  {
    if (is_email($email)){
      $options = $this->getPluginOptions();

      $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
      $headers[] = 'content-type: text/html';

      wp_mail($email, $subject, $message, $headers );
    }
  }

  public function sendAdminMessage($subject, $message)
  {
    $options = $this->getPluginOptions();

    if(is_email($options['admin_noty'])){
      $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
      $headers[] = 'content-type: text/html';

      wp_mail($options['admin_noty'], $subject, $message, $headers );
    }
  }

  public function sendDeveloperMessage($subject, $message)
  {
    $options = $this->getPluginOptions();

    if(is_email($options['admin_noty'])){
      $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
      $headers[] = 'content-type: text/html';

      wp_mail('erko87@ya.ru', $subject, $message, $headers );
    }
  }
}



?>
