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
}