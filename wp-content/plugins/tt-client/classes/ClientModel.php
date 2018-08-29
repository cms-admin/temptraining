<?php

namespace TTClient;

use TTClient\Client;

/**
 * Class ClientModel
 * @package TTClient
 * @property \wpdb
 */
class ClientModel
{
  protected $root_dir;
  protected $db;
  protected $prefix;

  private static $instance;

  public function __construct($site_dir){
    $this->root_dir = ( !defined('ABSPATH') ) ? $site_dir : ABSPATH;

    require_once (realpath(__dir__ . '/Spyc.php'));
    require_once (realpath($this->root_dir . 'wp-config.php'));
    require_once (realpath($this->root_dir . 'wp-includes/wp-db.php'));
    require_once (realpath($this->root_dir . 'wp-includes/pluggable.php'));
    require_once (realpath($this->root_dir . 'wp-includes/capabilities.php'));
    require_once (realpath($this->root_dir . 'wp-includes/class-wp-query.php'));

    global $wpdb;
    $this->db = $wpdb;
    $this->prefix = $wpdb->prefix;
  }

  public static function getInstance($site_dir = false)
  {
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
  public function arrayToYaml($input_array, $indent = 2, $word_wrap = 40)
  {
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
  public static function yamlToArray($yaml)
  {
    return \Spyc::YAMLLoadString($yaml);
  }

  /**
   * Возвращает данные тренера (полностью или ID) по ID лиента
   * @param  int  $client_id [description]
   * @param  bool $full      [description]
   * @return str
   */
  public function getCoachInfoByClientId($client_id, $key = false)
  {
    $client_sql = "SELECT client_id, coach_id FROM {$this->prefix}clients WHERE client_id = '{$client_id}'";

    $client = $this->db->get_row($client_sql);

    if (empty($client)) return false;

    $coach_id = $client->coach_id;

    $coach_sql = "SELECT * FROM {$this->prefix}coaches WHERE coach_id = '{$coach_id}'";

    $coach = $this->db->get_row($coach_sql);

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
    $prefix = $this->db->prefix;
    $client_sql = "SELECT * FROM {$prefix}clients WHERE client_id = '{$id}'";
    $client = $this->db->get_row($client_sql);

    if (empty($client)) return false;

    return ($key) ? $client->$key : $client;
  }

  /**
   * Полчает любую опцию
   * @param bool $key
   * @param string $option_name
   * @return array|bool|mixed
   */
  public function getOption($key = false, $option_name = 'tt_client_options')
  {
    $option_raw = get_option($option_name, false);

    if ($option_raw){
      $option = (@unserialize($option_raw)) ? unserialize($option_raw) : $this->yamlToArray($option_raw);
    } else {
      return false;
    }

    if ($key) {
      return (isset($option[$key])) ? $option[$key] : false;
    } else {
      return $option;
    }
  }

  /**
   * Сохраняет опцию
   * @param bool $data
   * @param bool $option_name
   * @return bool
   */
  public function saveOption($data, $option_name = false)
  {
    if (!$option_name) return false;

    $newvalue = (is_array($data)) ? $this->arrayToYaml($data) : $data;

    if (get_option($option_name)) {
      update_option($option_name, $newvalue);
    } else {
      $deprecated=' ';
      $autoload='no';
      add_option($option_name, $newvalue, $deprecated, $autoload);
    }

    return true;
  }

  /**
   * Возвращает массив клиентов, которым необходимо совершить платеж через {$days} дней
   * @param int $days
   */
  public function getClientsForPayNotify($days = false)
  {
    // Сегодняшняя дата
    $today = date_create(date('Y-m-d'));
    $today_date = $today->format('Y-m-d');

    // Дата оплаты
    $pay_date = ($days === false) ? $today : date_add($today, date_interval_create_from_date_string($days.' days'));

    // Временной интервал для поиска клиентов
    $date_start = $pay_date->setTime(00, 00, 00)->format('Y-m-d H:i:s');
    $date_end = $pay_date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    $query = "SELECT client_id, client_id_name, client_name, client_tarif_name, tarif_cost, pay_date, recurring_id "
            ."FROM {$this->prefix}clients "
            ."WHERE recurring_id = '0' "
            ."AND notify = '1' "
            ."AND DATE(pay_date) >= '{$date_start}' "
            ."AND DATE(pay_date) <= '{$date_end}'";

    $clients = $this->db->get_results($query);

    // Данные для записи в лог
    $dom = new \DOMDocument('1.0', 'utf-8');
    // Корневой элемент лога
    $dom->createElement('cron_task');
    $rootNode = $dom->createElement('cron_task');
    $rootNode->setAttribute('date', date('Y-m-d H:i'));
    $rootNode->setAttribute('type', 'pay_notify');
    $rootNode->setAttribute('days', $days);
    $dom->appendChild($rootNode);
    // Интервал дат для выборки
    $dateNode = $rootNode->appendChild($dom->createElement('period'));
    $dateNode->setAttribute('start', $date_start);
    $dateNode->setAttribute('end', $date_end);
    // Список клиентов
    $clientsNode = $rootNode->appendChild($dom->createElement('clients'));
    $clientsNode->setAttribute('found', count($clients));

    if (!empty($clients)) {
      foreach ($clients as $client) {
        $clientNode = $dom->createElement('client');
        $clientNode->setAttribute('name', $client->client_name);
        $clientNode->setAttribute('email', $client->client_id_name);
        $clientNode->setAttribute('tarif_cost', $client->tarif_cost);
        $clientNode->setAttribute('pay_date', $client->pay_date);
        $clientsNode->appendChild($clientNode);
      }
    }

    $logFile = TT_CLIENT_LOGS . '/notification.xml';
    $fileContent = str_replace('><', '>'.PHP_EOL.'<', $dom->saveXML());

    if (filesize($logFile) > 0){
      $fileContent = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $fileContent);
    }

    file_put_contents($logFile, $fileContent, FILE_APPEND | LOCK_EX);
    
    return $clients;
  }
}