<?php
require_once('/var/www/u0249987/data/www/temptraining.ru/wp-config.php');
//require_once('../wp-config.php');

add_filter( 'wp_mail_from', 'vortal_wp_mail_from' );
function vortal_wp_mail_from( $email_address ){
  return 'info@temptraining.ru';
}

add_filter( 'wp_mail_from_name', 'vortal_wp_mail_from_name' );
function vortal_wp_mail_from_name( $email_from ){
  return 'TempTraining';
}

function dayCase($val) {
  if ($val == null)
    return "";
  if ($val > 4 && $val < 21)
    return " дней";
  $val = $val % 10;
  if ($val == 1)
    return " день";
  if (in_array($val, array(2,3,4)))
    return " дня";
  return " дней";
}

//Массив дней рассылки. -2 - за два дня, 0 - текущий день, 1 - первый день просрочки и т.д.
$payDays = array(-2, 2);

$today = new DateTime(date('Y-m-d', time()));

$dateLimit = date_add($today, date_interval_create_from_date_string('2 days'));

$clients = $wpdb->get_results(
  "SELECT u.client_id_name, u.client_name, u.client_tarif_name, u.tarif_cost, u.pay_date, u.can_pay, " . 
  "c.coach_id_name as coach_email, c.coach_name " . 
  "FROM " . $wpdb->prefix . "clients u " .
  "LEFT JOIN ". $wpdb->prefix . "coaches c ON u.coach_id = c.coach_id " .
  "WHERE u.notify = 1 AND DATE(u.pay_date) <= '" . date_format($dateLimit, 'Y-m-d') . "' ORDER BY u.pay_date ASC");

$messageAdmin = '';

$i = 0;
$dividerFlag = 1;
if ($wpdb->num_rows > 0) {
  foreach ($clients as $clientData) {
  
    $endDate =  htmlspecialchars($clientData ->pay_date . '' , ENT_QUOTES);

    $difference = intval(date_diff(date_create(date('Y-m-d 00:00:00')), date_create($clientData->pay_date))->format("%R%a"));

    $expirationFlag = $difference < 0 ? 0 : 1;

  if ((in_array($difference,$payDays))           //Если в массиве есть дни просрочки
    || ($difference < 0 && (($difference % 10) == 0))  //или каждые 10 дней просрочки
  ){
    $clientName = htmlspecialchars( $clientData ->client_name, ENT_QUOTES);
    $tarifName = htmlspecialchars( $clientData ->client_tarif_name, ENT_QUOTES);
    $paymentSum = htmlspecialchars( $clientData ->tarif_cost, ENT_QUOTES);
  
    $paymentMessage = $expirationFlag ? htmlspecialchars( 'истекает ', ENT_QUOTES) : htmlspecialchars( 'истек ', ENT_QUOTES);
    $endDate = $difference != 0 ? date_create($endDate)->format("d.m.Y") : 'сегодня!';
    
    if ($dividerFlag && $expirationFlag){
      $dividerFlag = 0;
      $messageAdmin = $messageAdmin.'';
    };

    $difMessage = "";

    if ($difference < 0) {
      $difference = abs($difference);
      $difMessage = "Просрочено уже " .(string)$difference . dayCase($difference) . ".";
    };

    $canPayMessage = "";

    if ($clientData->can_pay > 0){
      $canPayMessage = "
        Вы можете оплатить тренировки картой через личный кабинет https://temptraining.ru/client/ ";
    };  
    
    $message = '
      Уважаемый(ая) ' . $clientName . '!

      Мы напоминаем Вам о том, что необходимо совершить платеж на сумму ' . $paymentSum . ' рублей.
      Срок оплаты вашего тарифа (' . $tarifName . ') '. $paymentMessage . $endDate . '
      ' . $difMessage. $canPayMessage . '

      Если Вы оплатили будущий период, а письмо с напоминанием продолжает приходить, пожалуйста свяжитесь с нами. Возможно, мы не можем идентифицировать Ваш платёж.';  
    
    wp_mail( $clientData ->client_id_name, 'Напоминание о платеже', $message );
    //wp_mail( 'info@cms-admin.ru', 'Напоминание о платеже клиенту (' .$clientData ->client_id_name. ')', $message );

    $i += 1;
    $messageAdmin = $messageAdmin.strval($i) . '. ' . $clientName . ': тариф ' . $tarifName . ', сумма платежа ' . $paymentSum . ', срок '. $paymentMessage . $endDate . ', ' . $difMessage. '
    ';
  
  } elseif ($difference <= -10 && $difference % 10 == 0) { // Напоминание тренеру, если его клиент не платит больше 10 дней
    $messageCoach = '';
    $messageCoach .= 'Уважаемый(ая) ' . $clientData->coach_name . '!<br/>';
    $messageCoach .= 'Мы напоминаем Вам о том, что вашему клиенту ' . $clientData->client_name .
      ' необходимо совершить платеж на сумму ' . number_format( $clientData->tarif_cost, 0, '.', ' ' ) . 
      ' по тарифу "' . $clientData ->client_tarif_name . '" <br/>';
    $messageCoach .= 'На данный момент платеж просрочен на ' . (string)$difference . dayCase($difference);

    wp_mail( $clientData->coach_email, 'Напоминания о платеже клиента', $messageCoach, 'content-type: text/html' );
    //wp_mail( 'info@cms-admin.ru', 'Напоминания о платеже клиента (' . $clientData->coach_email . ')', $messageCoach, 'content-type: text/html' );
  }
}

wp_mail( 'info@temptraining.ru', 'Напоминания о платеже', $messageAdmin );
//wp_mail( 'info@cms-admin.ru', 'Напоминания о платеже админу (info@temptraining.ru)', $messageAdmin );
    
};

?>