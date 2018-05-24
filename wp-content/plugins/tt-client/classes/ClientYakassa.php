<?php

namespace TTClient;

use TTClient\Client;
use TTClient\ClientModel;
use YandexCheckout\Client as KassaClient;

class ClientYakassa
{
  protected $root_dir;
  protected $wpdb;
  protected $payment_type = 'yandex_money';
  protected $kassa;
  protected $options;

  private static $instance;

  public function __construct($site_dir){
    $this->root_dir = ( !defined('ABSPATH') ) ? $site_dir : ABSPATH;

    require_once (__dir__ . '/Spyc.php');
    require_once ($this->root_dir . 'wp-config.php');
    require_once ($this->root_dir . 'wp-includes/wp-db.php');
    require_once ($this->root_dir . 'wp-includes/pluggable.php');
    require_once ($this->root_dir . 'wp-includes/capabilities.php');
    require_once ($this->root_dir . 'wp-includes/class-wp-query.php');
    require_once (TT_CLIENT_DIR . 'classes/lib/autoload.php');

    global $wpdb;
    $this->wpdb = $wpdb;
  }

  /**
   * @return ClientYakassa
   */
  public static function getInstance($site_dir = false) {
    if (null === self::$instance)
      return self::$instance = new self($site_dir);
    else
      return self::$instance;
  }

  /**
   * Авторизация в API Кассы
   *
   * @return object $kassa
   */
  private function connect()
  {
    $this->options = Client::getInstance()->getPluginOptions();
    $kassa = new KassaClient();
    if (!empty($this->options['yakassa_shopid']) && !empty($this->options['yakassa_secret_key'])) {
      $kassa->setAuth($this->options['yakassa_shopid'], $this->options['yakassa_secret_key']);
    }

    return $kassa;
  }

  /**
   * Проверяет соответствие суммы оплаты, периода и стоимости тарифа
   *
   * @param array $post
   * @return void
   */
  public function checkFormTariff($post)
  {
    $prefix = $this->wpdb->prefix;
    $client_id_name = $post['client_id_name'];

    $query = "SELECT * "
    ."FROM {$prefix}clients "
    ."WHERE client_id_name = '{$client_id_name}' "
    ."AND can_pay = '1'";

    $client = $this->wpdb->get_row($query);

    return ($client) ? $client : false;
  }

  /**
   * Формирует платеж
   *
   * @param integer $sum  Сумма оплаты.
   * @param object $client
   * @return void
   */
  public function createPayment($sum, $client, $type = 'tariff', $payment_type = false)
  {
    $options = Client::getInstance()->getPluginOptions();
    $kassa = $this->connect();

    switch ($type){
      case 'tariff':
        $paymentParams = [
          'amount' => [
            'value' => number_format($sum, 2, '.', ''),
            'currency' => 'RUB',
          ],
          'receipt' => array( // Данные для формирования чека в онлайн-кассе (для соблюдения 54-ФЗ)
            'items' => array(
              array(
                'description' => $client->client_tarif_name,                // *Название товара
                'quantity'  => intval($sum) / intval($client->tarif_cost),  // *Количество
                'amount'  => array(                                         // *Сумма с указанием валюты
                  'value' => number_format($sum, 2, '.', ''),               // *Сумма
                  'currency'  => 'RUB',                                     // *Код валюты
                ),
                'vat_code'  => 1,                                           // *Ставка НДС, число от 1 до 6
              ),
            ),
            'tax_system_code' => 3,
            'email' => $client->client_id_name
          ),
          'payment_method_data' => array(
            'type' => ($payment_type) ? $payment_type : $this->payment_type,
          ),
          'confirmation' => array(
            'type' => 'redirect',
            'return_url' => home_url('client')
          ),
          'capture' => false
        ];
        break;
    }

    try {
      $response = $kassa->createPayment($paymentParams, uniqid('', true));
    } catch (Exception $exc) {
      return array(
        'success' => false,
        'error'   => $exc->getMessage(),
      );
    }

    if ($response['status'] == 'pending'){

      # Добавляет запись о заказе в базу данных заказов
      $newOrder = $this->kassaCreateOrder($response, $client);

      # Перенаправляет клиента на страницу оплаты
      if ($newOrder){
        $result = [
          'success' => true,
          'redirect'  => $response['confirmation']['confirmation_url'],
        ];
      } else {
        $result = [
          'success' => false,
          'error'   => 'Ошибка записи заказа № ' . $response['id'] . ' в базу данных сайта',
        ];
      }
      return $result;
    }
  }

  /**
   * Создает запись об оплате клиента
   *
   */
  private function kassaCreateOrder($response, $client)
  {
    // Дата формирования заказа
    date_default_timezone_set('Europe/Moscow');
    $orderDate = date_create(strftime($response['created_at']))->format('Y-m-d H:i:s');

    // Рассчитывает оплаченный период
    $orderAmount = intval($response['amount']['value']);
    $orderPeriod = $orderAmount / intval($client->tarif_cost);

    // проверяем платит ли клиент за подписку
    $orderSubscription = intval($client->subscription) * $orderPeriod;

    // Вычисляем зарплату тренеру для таблицы заказов
    $coachRate = intval(ClientModel::getInstance()->getCoachInfoByClientId($client->client_id, 'rate'));
    $orderWage = ($orderAmount - $orderSubscription) * ($coachRate / 100);

    // Формирует данные для сохранения заказа в таблицу `orders`
    $orderData = [
      'order_id'      => $response['id'],
      'coach_id'      => $client->coach_id,
      'client_id'     => $client->client_id,
      'status'        => $response['status'],
      'date'          => $orderDate,
      'amount'        => $orderAmount,
      'period'        => $orderPeriod,
      'subscription'  => $orderSubscription,
      'wage'          => $orderWage,
      'reward_id'     => 0
    ];

    // Добавляет новый заказ в БД
    $this->wpdb->insert("{$this->wpdb->prefix}orders", $orderData);

    return $this->wpdb->insert_id;
  }

  /**
   * Проверяет незавершенные заказы
   *
   * @param [type] $client_id
   * @param [type] $order_id
   * @return void
   */
  public function checkWaytingPayments($client_id)
  {
    $sql = "SELECT * FROM {$this->wpdb->prefix}orders WHERE `client_id` = '{$client_id}' AND `status` = 'pending' "
            ."AND coach_id <> 0";

    $waiting_orders = $this->wpdb->get_results($sql, 'ARRAY_A');

    if (!empty($waiting_orders)) {
      $kassa = $this->connect();

      foreach ($waiting_orders as $payment){
        $paymentCheck = $kassa->getPaymentInfo($payment['order_id']);

        if($paymentCheck['status'] == 'canceled'){
          $this->wpdb->delete($this->wpdb->prefix . 'orders', ['id' => $payment['id']]);
        }
      }
    }
  }

  /**
   * Обрабатывает уведомления кассы
   *
   * @param [type] $event
   * @param [type] $object
   * @return void
   */
  public function checkNotification($event, $object)
  {
    switch($event){
      case 'payment.waiting_for_capture':
        // Ищем платеж в базе
        $pendingOrder = $this->getPendingOrder($object['id']);
        if ($pendingOrder && $object['paid'] === true) {
          $this->confirmPendingOrder($pendingOrder, $object);
        }
        break;
    }
  }

  /**
   * Находит заказ в статусе ожидания в бд
   *
   * @param [type] $paymentId
   * @return void
   */
  private function getPendingOrder($paymentId)
  {
    $result = $this->wpdb->get_row("SELECT * FROM {$this->wpdb->prefix}orders WHERE `order_id` = '{$paymentId}' AND `status` = 'pending'", 'ARRAY_A');

    return (!empty($result)) ? $result : false;
  }

  /**
   * Подтверждает оплату и обновляет заказ
   *
   * @param [type] $order
   * @param [type] $payment
   * @return void
   */
  private function confirmPendingOrder($order, $payment)
  {
    $kassa = $this->connect();
    // Еще раз убедимся, что заказ оплачен
    $chekPayment = $kassa->getPaymentInfo($payment['id']);

    // Проводим проверку оплаты и суммы
    if(true !== $chekPayment->paid) return false;
    if(intval($order['amount']) !== intval($chekPayment['amount']['value'])) return false;

    // Подтверждаем оплату
    $idempotenceKey = uniqid('', true);

    $response = $kassa->capturePayment(
      [
        'amount' => $chekPayment['amount'],
      ],
      $payment['id'],
      $idempotenceKey
    );

    if ($response['status'] == 'succeeded'){
      // Обновляем значение заказа в БД
      $this->wpdb->update(
        $this->wpdb->prefix . 'orders',
        ['status' => 'paid'],
        ['order_id' => $order['order_id'], 'status' => 'pending']
      );

      // Отправляет инфу о заказе на email
      Client::getInstance()->sendCreateOrderMessage($order);

      // Обрабатывает параметры заказа
      $this->processNewOrder($response, $order);
    }
  }

  /**
   * Undocumented function
   *
   * @param [type] $order
   * @param [type] $payment
   * @return void
   */
  private function processNewOrder($payment, $order)
  {
    $options = Client::getInstance()->getPluginOptions();

    // Увеличиваем зарплату тренера в таблице тренеров
    $coach_reward = intval(ClientModel::getInstance()->getCoachInfoByClientId($order['client_id'], 'reward'))
      + intval($order['wage']);

    // Увеличиваем налоги тренера в таблице тренеров
    $coach_tax = intval(ClientModel::getInstance()->getCoachInfoByClientId($order['client_id'], 'tax'))
      + (intval($options['coaches_tax']) / 100 * intval($order['wage']));

    // Обновляет данные тренера
    $coach_data = [
      'reward'  => $coach_reward,
      'tax'     => $coach_tax
    ];
    $this->wpdb->update($this->wpdb->prefix.'coaches', $coach_data, ['coach_id' => $order['coach_id']]);

    // вычисляем дату до которой продливается срок оплаты клиента
    $client_current_pay_date = ClientModel::getInstance()->getClientInfo($order['client_id'], 'pay_date');

    if (intval($client_current_pay_date) > 0){
      $date_before = date_create($client_current_pay_date);
    } else {
      $date_before = date_create(date('Y-m-d'));
    }

    $date_before_day = intval($date_before->format('d'));

    $date_after = date_add($date_before, date_interval_create_from_date_string($order['period'] . ' months'));

    $client_data['pay_date'] = $date_after->setTime(21, 00, 00)->format('Y-m-d H:i:s');

    $this->wpdb->update($this->wpdb->prefix.'clients', $client_data, ['client_id' => $order['client_id']]);

  }
}
