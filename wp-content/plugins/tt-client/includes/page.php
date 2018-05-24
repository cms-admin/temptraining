<?php
global $wpdb;
$options = get_option('tt_client_options');

# Данные текущего пользователя
$user = wp_get_current_user();

$coaches_args = array(
  'numberposts'	=> -1,
  'post_status' => 'publish',
  'post_type'   => 'coach',
  'order'       => 'ASC',
);

$coaches = get_posts($coaches_args);

foreach ($coaches as $key => $value) {
  $coach_id = get_post_meta($value->ID, 'coach_id', true);
  if ( $user->user_login == 'tt_admin' ){
    if('kalinin@temptraining.ru' == $coach_id){
      $current_coach = $coaches[$key];
    }
  } else {
    if($user->user_email == $coach_id){
      $current_coach = $coaches[$key];
    }
  }
}

$crm_coaches = temptraining_coaches_extend(
  $wpdb->get_results(
    "SELECT coach_id_name, coach_id, coach_name, reward, tax FROM " . $wpdb->prefix . "coaches WHERE coach_id_name != 'tt_admin' ORDER BY coach_id DESC"
  )
);

if($user->user_login != 'tt_admin' && !empty($current_coach)){
  $crm_coach = $wpdb->get_row(
    "SELECT * FROM " . $wpdb->prefix . "coaches WHERE coach_id_name = '" . $user->user_email . "' ORDER BY coach_id DESC"
  );
  $current_coach_clients = $wpdb->get_results("SELECT client_id_name, client_id, client_name, client_tarif_id, client_tarif_name, tarif_cost, pay_date, can_pay, notify FROM " . $wpdb->prefix . "clients WHERE coach_id = '" . $crm_coach->coach_id . "' AND client_name != 'tt_admin' ORDER BY pay_date ASC");
}

if($user->user_login != 'tt_admin' && empty($current_coach)){
  $current_client = $wpdb->get_results(
    "SELECT client_id_name, client_id, client_name, client_tarif_id, client_tarif_name, tarif_cost, pay_date, can_pay, coach_id, notify ".
    "FROM " . $wpdb->prefix . "clients WHERE client_id_name = '" . $user->user_login . "' AND client_name != 'tt_admin' ORDER BY pay_date ASC");

  if (!empty($current_client)) {
    $current_client = $current_client[0];
    $current_client->coach = $wpdb->get_results(
      "SELECT coach_id_name, coach_id, coach_name FROM " . $wpdb->prefix . "coaches WHERE coach_id = '".  $current_client->coach_id ."' ORDER BY coach_id DESC"
    );
    if (!empty($current_client->coach)){
      $current_client->coach = $current_client->coach[0];
    }
    $current_client->pay_class = (time() >= strtotime($current_client->pay_date)) ? 'danger' : 'success';

    $current_client->history = $wpdb->get_results(
      "SELECT id, client_id, date, amount ".
      "FROM " . $wpdb->prefix . "orders WHERE client_id = '" . $current_client->client_id . "' ORDER BY date ASC");
  }
}
?>
<div id="content" role="main" class="dash">
	<div class="container">
		<?php if ( is_user_logged_in() ) : ?>
			<header class="entry-header">
				<h1 class="entry-title">Личный кабинет</h1>
			</header>

			<?php
			if ( isset($current_coach) ) :
				$cc_photo = get_template_directory_uri() . '/timthumb.php?src=' .
					get_the_post_thumbnail_url($current_coach, 'original') . '&w=100&h=100&a=t';
			?>
        <section class="dash-header">
          <figure class="dash-header__image">
            <img src="<?php echo $cc_photo; ?>" alt="<?php echo $current_coach->post_title; ?>">
          </figure>
          <div class="dash-header__data">
            <h3 class="title"><?php echo $current_coach->post_title; ?></h3>
            <div class="text<?php if ( $user->user_login != 'tt_admin' ) : ?> coach-text<?php endif; ?>">
              <?php if ( $user->user_login == 'tt_admin' ) : ?>
                <button type="button" class="btn-show jstree-showall" rel="#dashCoaches">
                  <i class="ion ion-eye"></i> <span>Развернуть</span>
                </button>
                <button type="button" class="btn-hide jstree-hideall" rel="#dashCoaches">
                  <i class="ion ion-eye-disabled"></i> <span>Свернуть</span>
                </button>
              <?php else : ?>
                <?php echo get_post_meta($current_coach->ID, 'spec', true); ?>
              <?php endif; ?>
            </div>
          </div>
          <?php if ( $user->user_login == 'tt_admin' ) : ?>
            <?php
              $sql_admin_pays = "SELECT SUM(reward) as total_reward, SUM(tax) as total_tax "
                                . "FROM "  . $wpdb->prefix . "coaches";
              $admin_pays = $wpdb->get_row($sql_admin_pays);

              $total_rewards = $wpdb->get_results("SELECT r.coach_id, r.payment_date, r.amount, r.type, c.coach_name "
                . "FROM " . $wpdb->prefix . "rewards r "
                . "LEFT JOIN ". $wpdb->prefix . "coaches c ON r.coach_id = c.coach_id "
                . "ORDER BY r.payment_date DESC");
            ?>

            <?php if (!empty($total_rewards)) : ?>
              <button class="dash-header__tile slide-toggle is-blue" rel="#totalRewardHistory">
                <figure class="dash-header__tile-icon">
                  <img src="<?php echo TT_CLIENT_ICONS_URL; ?>time-is-money.svg" alt="История выплат" />
                </figure>
                <span>Выплаты</span>
              </button>
            <?php endif; ?>

            <div class="dash-header__tile">
              <figure class="dash-header__tile-icon">
                <img src="<?php echo TT_CLIENT_ICONS_URL; ?>wallet.svg" alt="Вознаграждение" />
              </figure>
              <span><?php echo number_format_i18n($admin_pays->total_reward, 0); ?></span>
            </div>

            <div class="dash-header__tile is-orange">
              <figure class="dash-header__tile-icon">
                <img src="<?php echo TT_CLIENT_ICONS_URL; ?>budget.svg" alt="Налог" />
              </figure>
              <span><?php echo number_format_i18n($admin_pays->total_tax, 0); ?></span>
            </div>
          <?php elseif (!empty($crm_coach)) : ?>
            <button class="dash-header__tile slide-toggle" rel="#totalPayHistory">
              <figure class="dash-header__tile-icon">
                <img src="<?php echo TT_CLIENT_ICONS_URL; ?>wallet.svg" alt="Вознаграждение" />
              </figure>
              <span><?php echo number_format_i18n($crm_coach->reward, 0); ?></span>
            </button>
            <div class="dash-header__tile is-orange">
              <figure class="dash-header__tile-icon">
                <img src="<?php echo TT_CLIENT_ICONS_URL; ?>budget.svg" alt="Налог" />
              </figure>
              <span><?php echo number_format_i18n($crm_coach->tax, 0); ?></span>
            </div>
          <?php endif; ?>
        </section>

      <!-- ИСТОРИЯ ВЫПЛАТ -->
      <?php if (!empty($total_rewards)) : ?>
      <div id="totalRewardHistory" class="dash-coaches" style="display: none;" data-list="" data-list-pages="5"
        data-list-options='{
          "valueNames": ["date", "sum"],
          "listClass": "history-list"
        }'>
        <ul class="history-list">
          <?php foreach($total_rewards as $reward) : ?>
          <li class="client-row history-row">
            <i class="jstree-icon jstree-themeicon" role="presentation"></i>
            <span class="fix-2">
              <i class="ion ion-calendar"></i>&nbsp;
              <span class="date"><?php echo date("d.m.Y", strtotime($reward->payment_date)); ?></span>
            </span>

            <?php if ($reward->type == 1) : ?>
              <span class="fix-2">
                <i class="ion ion-card"></i>&nbsp;
                <span>Вознаграждение</span>
              </span>
            <?php else : ?>
              <span class="fix-2">
                <i class="ion ion-calculator"></i>&nbsp;
                <span>Налог</span>
              </span>
            <?php endif; ?>

            <span class="fix-2">
              <i class="ion ion-cash"></i>&nbsp;
              <span class="sum"><?php echo number_format($reward->amount, 2, '.', ' '); ?></span>
            </span>

            <span class="fix-flex">
              <i class="ion ion-person"></i>&nbsp;
              <span class="coach"><?php echo $reward->coach_name; ?></span>
            </span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php elseif (!empty($current_client)) : ?>
        <div class="dash-header is-client">
  				<figure class="dash-header__image">
  					<i class="ion ion-ios-contact-outline"></i>
  				</figure>
  				<div class="dash-header__data">
  					<h3 class="title"><?php echo $current_client->client_name; ?></h3>
  					<div class="text">
              <span><?php echo $current_client->client_tarif_name; ?></span>
              <span><strong>Тренер</strong>: <?php echo $current_client->coach->coach_name; ?></span>
              <span>
                <strong>Оплата</strong>:
                <span class="<?php echo $current_client->pay_class; ?>">
                  <?php echo date("d.m.Y", strtotime($current_client->pay_date)); ?>
                </span>
              </span>
            </div>
  				</div>
  			</div>

        <?php if (!empty($tpl)) : ?>
          <section class="dash-info <?php echo $tpl['status']; ?>">
            <h4 class="dash-info__title"><?php echo $tpl['title']; ?></h4>
            <div class="dash-info__text"><?php echo $tpl['message']; ?></div>
            <a class="dash-info__close" href="<?php echo site_url( "client/" ); ?>"><i class="ion ion-close-circled"></i></a>
          </section>
        <?php endif; ?>

        <?php if ($current_client->can_pay ) : ?>
        <section id="dashPayment" class="dash-payment" style="">
          <?php

          if ($options['yakassa_position'] === $current_client->can_pay) {
            require_once(TT_CLIENT_DIR . 'includes/form_yakassa.php');
          }

          if ($options['openbank_position'] === $current_client->can_pay) {
            require_once(TT_CLIENT_DIR . 'includes/form_openbank.php');
          }

          ?>
        </section>
        <?php endif; ?>
      <?php endif; ?>


			<?php if ( $user->user_login == 'tt_admin' ) : ?>
				<!-- ЛИЧНЫЙ КАБИНЕТ АДМИНИСТРАТОРА -->
				<div id="dashCoaches" class="dash-coaches jstree">
					<ul>
						<?php foreach ($crm_coaches as $item) : ?>
              <?php if (isset($item->extend)) : ?>
                <?php
                $photo = get_template_directory_uri() . '/timthumb.php?src=' . get_the_post_thumbnail_url($item->extend, 'original') . '&w=60&h=60&a=t';
                $name = $item->extend->post_title;
                $specialization = get_post_meta($item->extend->ID, 'spec', true);
                $clients_cnt = count($item->clients);
                $clients_wrd = ['ученик', 'ученика', 'учеников'];
                ?>
  							<li class="coach-row is-admin">
                  <figure class="photo hidden-xx">
                    <img src="<?php echo $photo; ?>" alt="<?php echo $name; ?>">
                  </figure>
                  <div class="data">
                    <h3 class="data-title">
                      <span class="data-title__name"><?php echo $name; ?></span>
                    </h3>
                    <div class="data-meta">
                      <i class="ion ion-person-stalker"></i>&nbsp;
                      <span>
                        <?php echo $clients_cnt; ?> <span class="hidden-xx"><?php echo numToWord($clients_cnt, $clients_wrd); ?></span>
                      </span>
                      <i class="ion ion-card"></i>&nbsp;
                      <span>
                        <span class="hidden-xx hidden-sm">Вознаграждение: </span>
                        <?php echo number_format_i18n($item->reward, 0); ?>
                        <span class="hidden-xx"> руб.</span>
                      </span>
                      <i class="ion ion-calculator"></i>&nbsp;
                      <span>
                        <span class="hidden-xx hidden-sm">Налог: </span>
                        <?php echo number_format_i18n($item->tax, 0); ?>
                        <span class="hidden-xx"> руб.</span>
                      </span>
                    </div>
                  </div>
                  <button class="coach-row__action-reward" data-reward="<?php echo $item->coach_id; ?>">
                    <figure class="dash-header__tile-icon">
                      <img src="<?php echo TT_CLIENT_ICONS_URL; ?>wallet.svg" alt="Вознаграждение">
                    </figure>
                    <span>Выплата</span>
                  </button>
                  <button class="coach-row__action-tax" data-tax="<?php echo $item->coach_id; ?>">
                    <figure class="dash-header__tile-icon">
                      <img src="<?php echo TT_CLIENT_ICONS_URL; ?>budget.svg" alt="Налог">
                    </figure>
                    <span>Налог</span>
                  </button>

                  <?php if ($clients_cnt > 0) : ?>
                    <ul>
                      <?php foreach ($item->clients as $client) : ?>
                        <?php
                          $pay_class = (time() >= strtotime($client->pay_date)) ? 'danger' : 'success';
                          $noty_icon = ($client->notify == 1) ? 'ion-android-notifications' : 'ion-android-notifications-off';
                          $client->history = $wpdb->get_results("SELECT coach_id, client_id, date, period, wage ".
                                                        "FROM " . $wpdb->prefix . "orders ".
                                                        "WHERE coach_id = '" . $item->coach_id . "' ".
                                                        "AND client_id = '" . $client->client_id . "' "
                                                      . "AND reward_id = '0' "
                                                      . "ORDER BY date DESC");
                        ?>
                        <!-- КЛИЕНТЫ ТРЕНЕРА В КАБИНЕТЕ АДМИНИСТРАТОРА -->
                        <li class="client-row">
                          <span class="fix-2"><?php echo $client->client_name; ?></span>
                          <span class="fix-2 hidden-xm hidden-sm hidden-xs hidden-xx"><?php echo $client->client_id_name; ?></span>
                          <span class="fix-flex hidden-sm hidden-xs hidden-xx"><?php echo $client->client_tarif_name; ?></span>
                          <span class="fix-1 visible-sm visible-xs visible-xx"><?php echo $client->tarif_cost; ?></span>
                          <span class="fix-1 fix-xs-1 fix-xx-2 <?php echo $pay_class; ?>"><?php echo date("d.m.Y", strtotime($client->pay_date)); ?></span>
                          <span class="<?php if($client->notify == 1) : ?>active<?php else: ?>disable<?php endif; ?>">
                            <i class="ion <?php echo $noty_icon; ?>"></i>
                          </span>
                          <?php if (empty($client->history)) : ?>
                            <span class="client-row__btn">
                              <img src="<?php echo TT_CLIENT_ICONS_URL; ?>credit-card-1.svg" alt="История платежей" />
                            </span>
                          <?php else : ?>
                            <button class="jstree-toggle client-row__btn">
                              <img src="<?php echo TT_CLIENT_ICONS_URL; ?>credit-card-2.svg" alt="История платежей" />
                            </button>
                            <!-- ИСТОРИЯ ОПЛАТ КЛИЕНТА ТРЕНЕРА В КАБИНЕТЕ АДМИНИСТРАТОРА -->
                            <ul>
                              <?php foreach($client->history as $history_row) : ?>
                              <li class="client-row">
                                <span class="fix-2 client-row__placeholder"></span>
                                <span class="fix-2">
                                  <?php echo number_format_i18n($history_row->wage, 0); ?> руб.
                                </span>
                                <span class="fix-flex">
                                  <?php echo $history_row->period; ?>&nbsp;
                                  <?php echo ttcli_num2word($history_row->period, ['месяц', 'месяца', 'месяцев']); ?>
                                </span>
                                <span class="fix-2"><?php echo date("d.m.Y", strtotime($history_row->date)); ?></span>
                              </li>
                              <?php endforeach; ?>
                            </ul>
                          <?php endif; ?>

                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
  							</li>
              <?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php elseif ( !empty($current_coach) ) : ?>
        <!-- ЛИЧНЫЙ КАБИНЕТ ТРЕНЕРА -->


        <?php
        $sql_pays = "SELECT o.coach_id, o.client_id, o.date, o.period, o.wage, o.reward_id, c.client_name "
                  . "FROM " . $wpdb->prefix . "orders o "
                  . "LEFT JOIN ". $wpdb->prefix . "clients c ON o.client_id = c.client_id "
                  . "WHERE o.coach_id = '" . $crm_coach->coach_id . "' AND o.reward_id = '0' "
                  . "ORDER BY o.date DESC";

        $coach_pays = $wpdb->get_results($sql_pays);
        ?>

        <!-- ОБЩАЯ ИСТОРИЯ ПЛАТЕЖЕЙ ПО ВСЕМ КЛИЕНТАМ ТРЕНЕРА -->
        <?php if (empty($coach_pays)) : ?>
          <p id="totalPayHistory" style="display: none;">История платежей пуста</p>
        <?php else : ?>
          <div id="totalPayHistory" class="dash-coaches" style="display: none;" data-list="" data-list-pages="5"
               data-list-options='{
                "valueNames": ["date", "sum"],
                "listClass": "history-list"
               }'
          >
            <ul class="history-list">
              <?php foreach($coach_pays as $coach_pay) : ?>
              <li id="coach_pay_<?php echo $coach_pay->id; ?>" class="client-row history-row">
                <i class="jstree-icon jstree-themeicon" role="presentation"></i>
                <span class="fix-2">
                  <i class="ion ion-calendar"></i>&nbsp;
                  <span class="date"><?php echo date("d.m.Y", strtotime($coach_pay->date)); ?></span>
                </span>
                <span class="fix-2">
                  <i class="ion ion-person"></i>&nbsp;
                  <span class="date"><?php echo $coach_pay->client_name; ?></span>
                </span>
                <span class="fix-flex sum">
                  <?php echo number_format_i18n($coach_pay->wage, 0); ?> руб.
                </span>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div id="dashCoaches" class="dash-coaches is-coach jstree">
          <?php if (!empty($current_coach_clients)) : ?>
            <ul>
              <?php foreach ($current_coach_clients as $client) : ?>
                <?php
                  $pay_class = (time() >= strtotime($client->pay_date)) ? 'danger' : 'success';
                  $noty_icon = ($client->notify == 1) ? 'ion-android-notifications' : 'ion-android-notifications-off';
                  $history = false;
                  $history = $wpdb->get_results("SELECT id, coach_id, client_id, date, period, wage, reward_id ".
                                                "FROM " . $wpdb->prefix . "orders ".
                                                "WHERE coach_id = '" . $crm_coach->coach_id . "' ".
                                                "AND client_id = '" . $client->client_id . "' "
                                              . "ORDER BY date DESC");
                ?>
                <li  id="coach_client_<?php echo $client->client_id; ?>"  class="client-row<?php if (!empty($history)) : ?> has-history<?php endif; ?>">
                  <span class="fix-2"><?php echo $client->client_name; ?></span>
                  <span class="fix-2 hidden-xm hidden-sm hidden-xs hidden-xx"><?php echo $client->client_id_name; ?></span>
                  <span class="fix-flex hidden-sm hidden-xs hidden-xx"><?php echo $client->client_tarif_name; ?></span>
                  <span class="fix-1 visible-sm visible-xs visible-xx"><?php echo $client->tarif_cost; ?></span>
                  <span class="fix-1 fix-xs-1 fix-xx-2 <?php echo $pay_class; ?>"><?php echo temptraining_date_format(strtotime($client->pay_date)); ?></span>
                  <span class="<?php if($client->notify == 1) : ?>active<?php else: ?>disable<?php endif; ?>">
                    <i class="ion <?php echo $noty_icon; ?>"></i>
                  </span>
                  <?php if (empty($history)) : ?>
                    <span class="client-row__btn">
                      <img src="<?php echo TT_CLIENT_ICONS_URL; ?>credit-card-1.svg" alt="История платежей" />
                    </span>
                  <?php else : ?>
                    <button class="jstree-toggle client-row__btn">
                      <img src="<?php echo TT_CLIENT_ICONS_URL; ?>credit-card-2.svg" alt="История платежей" />
                    </button>
                    <ul>
                      <?php foreach($history as $history_row) : ?>
                      <li class="client-row<?php if (!$history_row->reward_id) { ?> is-waiting<?php } ?>">
                        <span class="fix-2 client-row__placeholder"></span>
                        <span class="fix-2">
                          <?php echo number_format_i18n($history_row->wage, 0); ?> руб.
                        </span>
                        <span class="fix-2">
                          <?php echo $history_row->period; ?>&nbsp;
                          <?php echo ttcli_num2word($history_row->period, ['месяц', 'месяца', 'месяцев']); ?>
                        </span>
                        <span class="fix-flex"><?php echo date("d.m.Y", strtotime($history_row->date)); ?></span>
                      </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else : ?>
            Нет клиентов
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <header class="entry-header">
				<h2 class="entry-title">Предложения для наших учеников</h2>
			</header>
      <?php
      $args_offers = array(
        'numberposts'	=> -1,
        'post_status' => 'publish',
        'post_type'   => 'offers',
        'order'       => 'DESC',
      );
      $offers = get_posts($args_offers);
      if ($offers) :
      ?>
      <div class="archive-thumbs small-thumbs">
        <div class="row-flex">
          <?php foreach ($offers as $offer) : ?>
            <div class="col-flex is-1-3 is-1-2-lap is-1-2-tab">
              <article id="post-<?php echo $offer->ID; ?>" class="<?php echo implode(" ", get_post_class("post", $offer->ID)); ?>">
                <div class="post-header" data-magnific-popup="" data-magnific-popup-options='{
                    "type": "image",
                    "delegate": "a",
                    "mainClass": "mfp-fade",
                    "gallery": {
                      "enabled": false
                    }
                  }'>
                  <figure class="post-thumbnail" style="background-image: url()">
                    <a href="<?php echo get_the_post_thumbnail_url($offer); ?>">
                      <img class="img-responsive" src="<?php echo get_the_post_thumbnail_url($offer); ?>" alt="<?php echo $offer->post_title; ?>">
                    </a>
                  </figure>
                  <h3 class="post-title"><?php echo $offer->post_title; ?></h3>
                </div>


              	<div class="post-body">

              		<div class="post-text">
              			<?php echo $offer->post_content; ?>
              		</div>

              	</div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
		<?php else : ?>

		<?php endif; ?>
	</div>
</div>
