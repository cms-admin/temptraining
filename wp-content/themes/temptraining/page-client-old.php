<?php
get_header();
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
    "SELECT coach_id_name, coach_id, coach_name FROM " . $wpdb->prefix . "coaches WHERE coach_id_name != 'tt_admin' ORDER BY coach_id DESC"
  )
);

if($user->user_login != 'tt_admin' && !empty($current_coach)){
  $crm_coach = $wpdb->get_results(
    "SELECT coach_id_name, coach_id, coach_name FROM " . $wpdb->prefix . "coaches WHERE coach_id_name = '" . $user->user_email . "' ORDER BY coach_id DESC"
  );
  $current_coach_clients = $wpdb->get_results("SELECT client_id_name, client_id, client_name, client_tarif_id, client_tarif_name, tarif_cost, pay_date, can_pay, notify FROM " . $wpdb->prefix . "clients WHERE coach_id = '" . $crm_coach[0]->coach_id . "' AND client_name != 'tt_admin' ORDER BY pay_date ASC");
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
				$cc_photo = get_the_post_thumbnail_url($current_coach, [100, 100, 'bfi_thumb' => true]);
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
			</section>
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
                  <?php echo temptraining_date_format(strtotime($current_client->pay_date)); ?>
                </span>
              </span>
            </div>
  				</div>
  			</div>

        <?php if ($current_client->can_pay ) : ?>
        <section id="dashPayment" class="dash-payment" style="">
          <form class="wpcf7-form" action="https://money.yandex.ru/eshop.xml" method="post">
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
                  <input name="shopId" value="51113" type="hidden"/>
                  <input name="scid" value="48095" type="hidden"/>
                  <input name="sum" value="<?php echo trim($current_client->tarif_cost); ?>" type="hidden" id="yandex_payment_sum_for_client">
                  <input name="customerNumber" value="<?php echo trim($current_client->client_id_name); ?>" type="hidden"/>
                  <input name="custName" value="<?php echo trim($current_client->client_name); ?>" type="hidden"/>
                  <input name="orderDetails" value="Оплата тарифа <?php echo trim($current_client->client_tarif_name); ?> на 1 месяц" type="hidden" id="yandex_order_details_for_client"/>
                  <input name="paymentType" value="AC" type="hidden"/>
                  <input name="shopFailURL" value="https://temptraining.ru/client/" type="hidden"/>
                  <input name="shopSuccessURL" value="https://temptraining.ru/client/" type="hidden"/>
                  <input name="cps_email" value="<?php echo trim($current_client->client_id_name); ?>" type="hidden"/>
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
        </section>
        <?php endif; ?>
      <?php endif; ?>


			<?php if ( $user->user_login == 'tt_admin' ) : ?>
				<!-- ЛИЧНЫЙ КАБИНЕТ АДМИНИСТРАТОРА -->
				<div id="dashCoaches" class="dash-coaches jstree">
					<ul>
						<?php foreach ($crm_coaches as $item) : ?>
              <?php if ($item->extend) : ?>
                <?php
                $photo = get_the_post_thumbnail_url($item->extend, [60, 60, 'bfi_thumb' => true]);
                $name = $item->extend->post_title;
                $specialization = get_post_meta($item->extend->ID, 'spec', true);
                $clients_cnt = count($item->clients);
                $clients_wrd = ['ученик', 'ученика', 'учеников'];
                ?>
  							<li class="coach-row">
                  <figure class="photo hidden-xx">
                    <img src="<?php echo $photo; ?>" alt="<?php echo $name; ?>">
                  </figure>
                  <div class="data">
                    <h3 class="data-title">
                      <span class="data-title__name"><?php echo $name; ?></span>
                      <?php /*
                      <span class="data-title__separate"></span>
                      <span class="data-title__specialization"><?php echo $specialization; ?></span>
                      */ ?>
                    </h3>
                    <div class="data-meta">
                      <i class="ion ion-person-stalker"></i> <span><?php echo $clients_cnt . ' ' . numToWord($clients_cnt, $clients_wrd); ?></span>
                    </div>
                  </div>
                  <?php if ($clients_cnt > 0) : ?>
                    <ul>
                      <?php foreach ($item->clients as $client) : ?>
                        <?php
                          $pay_class = (time() >= strtotime($client->pay_date)) ? 'danger' : 'success';
                        ?>
                        <li class="client-row">
                          <span class="fix-2"><?php echo $client->client_name; ?></span>
                          <span class="fix-2 hidden-xm hidden-sm hidden-xs hidden-xx"><?php echo $client->client_id_name; ?></span>
                          <span class="fix-flex hidden-sm hidden-xs hidden-xx"><?php echo $client->client_tarif_name; ?></span>
                          <span class="fix-1 visible-sm visible-xs visible-xx"><?php echo $client->tarif_cost; ?></span>
                          <span class="fix-1 fix-xs-1 fix-xx-2 <?php echo $pay_class; ?>"><?php echo temptraining_date_format(strtotime($client->pay_date)); ?></span>
                          <span class="<?php if($client->notify == 1) : ?>active<?php else: ?>disable<?php endif; ?>">
                            <i class="ion ion-checkmark-circled"></i> <span class="hidden-xs hidden-xx">Оповещение</span>
                          </span>
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
        <div id="dashCoaches" class="dash-coaches jstree">
          <?php if (!empty($current_coach_clients)) : ?>
            <ul>
              <?php foreach ($current_coach_clients as $client) : ?>
                <?php
                  $pay_class = (time() >= strtotime($client->pay_date)) ? 'danger' : 'success';
                ?>
                <li class="client-row">
                  <span class="fix-2"><?php echo $client->client_name; ?></span>
                  <span class="fix-2 hidden-xm hidden-sm hidden-xs hidden-xx"><?php echo $client->client_id_name; ?></span>
                  <span class="fix-flex hidden-sm hidden-xs hidden-xx"><?php echo $client->client_tarif_name; ?></span>
                  <span class="fix-1 visible-sm visible-xs visible-xx"><?php echo $client->tarif_cost; ?></span>
                  <span class="fix-1 fix-xs-1 fix-xx-2 <?php echo $pay_class; ?>"><?php echo temptraining_date_format(strtotime($client->pay_date)); ?></span>
                  <span class="<?php if($client->notify == 1) : ?>active<?php else: ?>disable<?php endif; ?>">
                    <i class="ion ion-checkmark-circled"></i> <span class="hidden-xs hidden-xx">Оповещение</span>
                  </span>
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

<?php get_footer(); ?>
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jstree.js"></script>
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/dash.js"></script>
