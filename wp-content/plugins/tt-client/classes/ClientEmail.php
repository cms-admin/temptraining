<?php

namespace TTClient;

use TTClient\Client;
use TTClient\ClientModel;

/**
 * Class ClientModel
 * @package TTClient
 * @property \wpdb
 */
class ClientEmail
{
  protected $root_dir;

  private static $instance;

  public function __construct($site_dir){
    $this->root_dir = ( !defined('ABSPATH') ) ? $site_dir : ABSPATH;

    require_once (realpath($this->root_dir . 'wp-config.php'));
    require_once (realpath($this->root_dir . 'wp-includes/pluggable.php'));
    require_once (realpath($this->root_dir . 'wp-includes/capabilities.php'));
  }

  public static function create($site_dir = false)
  {
    if (null === self::$instance)
      return self::$instance = new self($site_dir);
    else
      return self::$instance;
  }

  /**
   * Отправляет клиенту напоминания о предстоящих платежах
   */
  public function sendClientPayNotyfy($client, $options)
  {
    $notifyEmail = $client->client_id_name;
    $notifySubject = 'From: ' . tlang('Напоминание о платеже');

    $html = file_get_contents(TT_CLIENT_DIR . 'templates/emails/clients_pay_notyfy.html');

    $message_replace = [
      '{client_name}' => $client->client_name,
      '{tarif_cost}'  => $client->tarif_cost . ' ' . tlang('руб.'),
      '{tarif_name}'  => $client->client_tarif_name,
      '{pay_date}'    => date_create($client->pay_date)->format('d.m.Y'),
      '{pay_link}'    => ($client->pay_link) ? $client->pay_link : home_url('client'),
    ];

    $notifyText = strtr($html, $message_replace);

    if(is_email($notifyEmail)){
      $headers[] = 'From: ' . $options['email_name'] . ' <' . $options['email_address'] . '>';
      $headers[] = 'content-type: text/html';
      
      wp_mail($notifyEmail, $notifySubject, $notifyText, $headers);
    }
  }
}