<form id="tt-client-yakassa" class="wpcf7-form" action="<?php echo $options['yakassa_url']; ?>" method="post">
  <div class="row">
    <div class="col-xm-4 col-md-3">
      <label>Выберите платежный период:</label>
      <br>
      <span class="wpcf7-form-control-wrap menu-subject">
        <select class="wpcf7-form-control wpcf7-select" id="select-payment-client">
          <option value="1" selected>Месяц</option>
          <option value="2">2 месяца</option>
          <option value="3">3 месяца</option>
          <option value="4">4 месяца</option>
          <option value="6">6 месяцев</option>
          <option value="12">Год</option>
        </select>
      </span>
    </div>
    <div class="col-xm-4 col-md-3">
      <label>Выберите способ оплаты:</label>
      <br>
      <span class="wpcf7-form-control-wrap">
        <input name="shopId" value="<?php echo $options['yakassa_shopid']; ?>" type="hidden"/>
        <input name="scid" value="<?php echo $options['yakassa_scid']; ?>" type="hidden"/>
        <input name="sum" value="<?php echo trim($current_client->tarif_cost); ?>" type="hidden" id="yandex_payment_sum_for_client">
        <input name="customerNumber" value="<?php echo trim($current_client->client_id_name); ?>" type="hidden"/>
        <input name="custName" value="<?php echo trim($current_client->client_name); ?>" type="hidden"/>
        <input name="orderDetails" value="Оплата тарифа <?php echo trim($current_client->client_tarif_name); ?> на 1 месяц" type="hidden" id="yandex_order_details_for_client"/>
        <input name="paymentType" value="AC" type="hidden"/>
        <input name="shopFailURL" value="https://temptraining.ru/client/" type="hidden"/>
        <input name="shopSuccessURL" value="https://temptraining.ru/client/" type="hidden"/>
        <input name="cps_email" value="<?php echo trim($user->user_email); ?>" type="hidden"/>
        <input name="month_count" value="1" type="hidden" id="month_count"/>

        <button type="submit" id="button-payment-client" class="submit"
          data-tarif-name="<?php echo $current_client->client_tarif_name; ?>"
          month-price="<?php echo $current_client->tarif_cost; ?>"
          data-month="1"
          data-price="<?php echo $current_client->tarif_cost; ?>"
          data-id="<?php echo $current_client->client_id; ?>" >Оплатить <?php echo $current_client->tarif_cost; ?> р.</button>
      </span>
    </div>
  </div>
</form>
