<?php
date_default_timezone_set('Europe/Moscow');
?>
<div class="row-flex">
  <div class="col-flex is-1-2 is-2-3-tab is-2-3-lap">
    <form name="PaymentForm" action="<?php echo get_admin_url(); ?>admin-post.php" method="post" id="tt-client-openbank" class="wpcf7-form">
      <input type="hidden" name="action" value="submit-openbank" />

      <input type="hidden" name="orderNumber" value="<?php echo trim($current_client->client_id).'_'.date('Ymd_Hi', time()); ?>" />
      <input type="hidden" name="amount" value="<?php echo intval(trim($current_client->tarif_cost) * 100); ?>" />
      <input type="hidden" name="returnUrl" value="<?php echo get_permalink(); ?>" />
      <input type="hidden" name="clientId" value="<?php echo $current_client->client_id; ?>" />

      <input type="hidden" name="description" value="Оплата тарифа <?php echo trim($current_client->client_tarif_name); ?>" />
      <input type="hidden" name="jsonParams" value='{"month_count": 1}' />

      <div class="row">
        <div class="col-sm-6 col-xm-6 col-md-6">
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
        <div class="col-sm-6 col-xm-6 col-md-6">
          <label class="hidden-xx">&nbsp;</label>
          <br>
          <span class="wpcf7-form-control-wrap">
            <button type="submit" id="button-payment-client" class="submit"
              data-tarif-name="<?php echo $current_client->client_tarif_name; ?>"
              month-price="<?php echo $current_client->tarif_cost; ?>"
              data-month="1"
              data-price="<?php echo $current_client->tarif_cost; ?>"
              data-id="<?php echo $current_client->client_id; ?>" >Оплатить <?php echo $current_client->tarif_cost; ?> рублей</button>
          </span>
        </div>
      </div>
    </form>
  </div>

  <?php if (!empty($current_client->history)) : ?>
    <div class="col-flex is-1-4 is-1-3-tab is-1-3-lap">
      <button type="button" class="btn-billing__client slide-toggle" rel="#payHistory">
        <span class="btn-billing__client-icon"></span> <span>История платежей</span>
      </button>
    </div>
  <?php endif; ?>
</div>

<?php if (!empty($current_client->history)) : ?>
  <div id="payHistory" class="dash-coaches" style="display: none;" data-list="" data-list-pages="5"
    data-list-options='{
      "valueNames": ["date", "sum"],
      "listClass": "history-list"
  }'>
    <ul class="history-list">
      <?php foreach($current_client->history as $payRow) : ?>
        <li class="client-row history-row">
          <i class="jstree-icon jstree-themeicon" role="presentation"></i>
          <span class="fix-2">
            <i class="ion ion-calendar"></i>&nbsp;
            <span class="date"><?php echo date("d.m.Y", strtotime($payRow->date)); ?></span>
          </span>
          <span class="fix-2">
            <i class="ion ion-card"></i>&nbsp;
            <span class="sum"><?php echo number_format($payRow->amount, 2, '.', ' '); ?></span>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>

    <ul class="listjs-pagination"></ul>

  </div>
<?php endif; ?>
