<?php

namespace TTClient;

use TTClient\Client;

class ClientModel
{
  protected $root_dir;
  protected $wpdb;
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
    $this->wpdb = $wpdb;
    $this->prefix = $wpdb->prefix;
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
   * Возвращает данные тренера (полностью или ID) по ID лиента
   * @param  int  $client_id [description]
   * @param  bool $full      [description]
   * @return str
   */
  public function getCoachInfoByClientId($client_id, $key = false)
  {
    $client_sql = "SELECT client_id, coach_id FROM {$this->prefix}clients WHERE client_id = '{$client_id}'";

    $client = $this->wpdb->get_row($client_sql);

    if (empty($client)) return false;

    $coach_id = $client->coach_id;

    $coach_sql = "SELECT * FROM {$this->prefix}coaches WHERE coach_id = '{$coach_id}'";

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
}