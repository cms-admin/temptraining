<?php
// Формат записей: ФОРМА ---------------------------------------------------------------------------------------------->
add_action('init', 'add_post_type_uniforms');
function add_post_type_uniforms(){
  register_post_type( 'uniform',
    array(
      'labels'  => array(
        'name'          => __( 'Uniforms', 'temptraining' ),
        'singular_name' => __( 'Uniform', 'temptraining' ),
        'add_new'       => __( 'New uniform', 'temptraining' ),
      ),
      'public'        => true,
      'publicly_queryable' => true,
      'exclude_from_search' => true,
      'menu_position' => 5,
      'menu_icon'     => 'none',
      'has_archive' => false,
      'capability_type' => 'post',
      'supports'  => array('title', 'editor', 'thumbnail'),
      'register_meta_box_cb' => 'add_uniform_meta',
      'rewrite' => array(
        'slug' => 'uniform',
        'with_front' => false,
      ),
    )
  );
}
add_action( 'add_meta_boxes', 'add_uniform_meta' );
function add_uniform_meta(){
  add_meta_box('uniform_custom_meta', __('Additional fields', 'temptraining'), 'uniform_custom_meta', 'uniform', 'side', 'default');
}
function uniform_custom_meta($post){
  // Стоимость формы
  echo '<h4>'. __('Uniform\'s price', 'temptraining') .'</h4>';
  echo '<input type="text" name="extra[price]" value="' . get_post_meta($post->ID, 'price', 1) . '" style="width:100%" />';

  echo '<input type="hidden" name="extra_fields_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
}

// Сохранение мета-данных для баннеров
function coach_uniform_meta_box_save($post_id, $post) {
  if(isset($_POST['extra_fields_nonce'])){
    if ( ! wp_verify_nonce($_POST['extra_fields_nonce'], __FILE__) ) return false; // проверка
  }
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false; // выходим если это автосохранение
  if ( !current_user_can('edit_post', $post_id) ) return false; // выходим если юзер не имеет право редактировать запись

  if( !isset($_POST['extra']) ) return false; // выходим если данных нет

  // Все ОК! Теперь, нужно сохранить/удалить данные
  $_POST['extra'] = array_map('trim', $_POST['extra']); // чистим все данные от пробелов по краям
  foreach( $_POST['extra'] as $key=>$value ){
    if( empty($value) ){
      delete_post_meta($post_id, $key); // удаляем поле если значение пустое
      continue;
    }

    update_post_meta($post_id, $key, $value); // add_post_meta() работает автоматически
  }
  return $post_id;
}

add_action('save_post', 'coach_uniform_meta_box_save', 1, 2); // save the custom fields
// <-- Формат записей: ФОРМА

// Формат записей: СТАРТЫ --------------------------------------------------------------------------------------------->
add_action('init', 'add_post_type_starts');
function add_post_type_starts(){
  register_post_type( 'starts',
    array(
      'labels'  => array(
        'name'          => __( 'Старты', 'temptraining' ),
        'singular_name' => __( 'Старт', 'temptraining' ),
        'add_new'       => __( 'Добавить старт', 'temptraining' ),
      ),
      'public'        => false,
      'publicly_queryable' => true,
      'show_in_nav_menus' => true,
      'show_ui' => true,
      'menu_position' => 6,
      'menu_icon'     => 'dashicons-clock',
      'has_archive' => false,
      'capability_type' => 'post',
      'supports'  => array('title', 'editor', 'thumbnail'),
      'register_meta_box_cb' => 'add_starts_meta',
      'rewrite' => array(
        'slug' => 'start',
        'with_front' => false,
      ),
    )
  );
}
add_action( 'add_meta_boxes', 'add_starts_meta' );
function add_starts_meta(){
  add_meta_box('starts_custom_meta', __('Дополнительные поля', 'temptraining'), 'starts_custom_meta', 'starts', 'side', 'default');
}
function starts_custom_meta($post){
  echo '<h4>'. __('Дата мероприятия', 'temptraining') .'</h4>';
  echo '<input type="text" name="extra[date]" value="' . get_post_meta($post->ID, 'date', 1) . '" style="width:100%" />';

  echo '<h4>'. __('Место проведения', 'temptraining') .'</h4>';
  echo '<textarea type="text" name="extra[place]" style="width:100%">' . get_post_meta($post->ID, 'place', 1) . '</textarea>';

  echo '<h4>'. __('Статус мероприятия', 'temptraining') .'</h4>';
  echo '<select name="extra[status]" style="width:100%">';
  $status_open = (get_post_meta($post->ID, 'status', 1) == 'open') ? 'selected="selected"' : '';
  echo '<option value="open"'.$status_open.' >'.__( 'Регистрация открыта', 'temptraining' ).'</option>';
  $status_wait = (get_post_meta($post->ID, 'status', 1) == 'wait') ? 'selected="selected"' : '';
  echo '<option value="wait"'.$status_wait.' >'.__( 'Ожидание регистрации', 'temptraining' ).'</option>';
  $status_close = (get_post_meta($post->ID, 'status', 1) == 'close') ? 'selected="selected"' : '';
  echo '<option value="close"'.$status_close.' >'.__( 'Регистрация закрыта', 'temptraining' ).'</option>';
  echo '</select>';

  echo '<h4>'. __('Ссылка для регистрации', 'temptraining') .'</h4>';
  echo '<input type="text" name="extra[link]" value="' . get_post_meta($post->ID, 'link', 1) . '" style="width:100%" />';

  echo '<h4>'. __('Примечание к мероприятию', 'temptraining') .'</h4>';
  echo '<textarea type="text" name="extra[note]" style="width:100%">' . get_post_meta($post->ID, 'note', 1) . '</textarea>';

  echo '<input type="hidden" name="extra_fields_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
}

add_action( 'add_meta_boxes', 'add_starts_meta_bottom' );
function add_starts_meta_bottom(){
  add_meta_box('starts_custom_meta_bottom', __('Список участников', 'temptraining'), 'starts_custom_meta_bottom', 'starts', 'advanced', 'default');
}
function starts_custom_meta_bottom($post){
  echo '<textarea type="text" name="extra[users]" style="width:100%">' . get_post_meta($post->ID, 'users', 1) . '</textarea>';
  echo '<p>'.__('Через запятую', 'temptraining').'</p>';

  echo '<input type="hidden" name="extra_fields_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
}
// <-- Формат записей: СТАРТЫ


// Формат записей: КЛИЕНТЫ --------------------------------------------------------------------------------------------->
add_action('init', 'add_post_type_clients');
function add_post_type_clients(){
  register_post_type( 'clients',
    array(
      'labels'  => array(
        'name'          => __( 'Клиенты', 'temptraining' ),
        'singular_name' => __( 'Клиент', 'temptraining' ),
        'add_new'       => __( 'Добавить клиента', 'temptraining' ),
      ),
      'public'        => false,
      'publicly_queryable' => true,
      'show_in_nav_menus' => true,
      'show_ui' => true,
      'menu_position' => 7,
      'menu_icon'     => 'dashicons-id',
      'has_archive' => false,
      'capability_type' => 'post',
      'supports'  => array('title', 'thumbnail'),
      'register_meta_box_cb' => 'add_clients_meta',
      'rewrite' => array(
        'slug' => 'clients',
        'with_front' => false,
      ),
    )
  );
}
add_action( 'add_meta_boxes', 'add_clients_meta' );
function add_clients_meta(){
  add_meta_box('clients_custom_meta', __('Данные клиента', 'temptraining'), 'clients_custom_meta', 'clients', 'normal', 'default');
}
function clients_custom_meta($post){

  # Client ID
  echo '<h4>'. __('ID клиента', 'temptraining') .'</h4>';
  echo '<input type="text" name="extra[uid]" value="' . get_post_meta($post->ID, 'uid', 1) . '" style="width:100%" />';

  # Client TARIFF
  echo '<h4>'. __('Тариф клиента', 'temptraining') .'</h4>';
  echo '<select name="extra[price_var]" style="width:100%">';
  $price_var_1 = (get_post_meta($post->ID, 'price_var', 1) == 1) ? 'selected="selected"' : '';
  echo '<option value="1"'.$price_var_1.' >Триатлон 6000 р./мес</option>';
  $price_var_2 = (get_post_meta($post->ID, 'price_var', 1) == 2) ? 'selected="selected"' : '';
  echo '<option value="2"'.$price_var_2.' >Два спорта 5000 р./мес</option>';
  $price_var_3 = (get_post_meta($post->ID, 'price_var', 1) == 3) ? 'selected="selected"' : '';
  echo '<option value="3"'.$price_var_3.' >Бег 4000 р./мес</option>';
  echo '</select>';

  # Client Price
  echo '<h4>'. __('Стоимость в месяц', 'temptraining') .'</h4>';
  echo '<input type="text" name="extra[price_cost]" value="' . get_post_meta($post->ID, 'price_cost', 1) . '" style="width:100%" />';

  # Client Pay date
  echo '<h4>'. __('Дата оплаты', 'temptraining') .'</h4>';
  echo '<input type="text" name="extra[pay_date]" value="' . get_post_meta($post->ID, 'pay_date', 1) . '" style="width:100%" />';

  # Client Can Pay
  $is_can_pay = (get_post_meta($post->ID, 'can_pay', 1) == 1) ? 'checked="checked"' : '';
  echo '<div style="width:100%"><label>';
  echo '<input type="checkbox" name="extra[can_pay]" value="1" '.$is_can_pay.' />';
  echo ' Разрешить оплату из кабинета</label></div>';

  # Client Coach
  echo '<h4>'. __('Тренер', 'temptraining') .'</h4>';
  echo '<select name="extra[coach_id]" style="width:100%">';
  $coach_args = array(
    'numberposts'	=> -1,
    'post_status' => 'publish',
    'post_type'   => 'coach',
    'order'       => 'ASC',
  );
  $coach_items = get_posts($coach_args);
  echo '<option value="null"'.$coach_id_sel.' >Выберите тренера</option>';
  foreach ($coach_items as $item) {
    $coach_id_sel = (get_post_meta($post->ID, 'coach_id', 1) == $item->ID) ? 'selected="selected"' : '';
    echo '<option value="'.$item->ID.'"'.$coach_id_sel.' >'.$item->post_title.'</option>';
  }
  echo '</select>';

  # Client Notyfy
  $is_notify = (get_post_meta($post->ID, 'notify', 1) == 1) ? 'checked="checked"' : '';
  echo '<div style="width:100%"><label>';
  echo '<input type="checkbox" name="extra[notify]" value="1" '.$is_notify.' />';
  echo ' Разрешить оплату из кабинета</label></div>';

  # Client Notyfy
  $is_oferta = (get_post_meta($post->ID, 'oferta', 1) == 1) ? 'checked="checked"' : '';
  echo '<div style="width:100%"><label>';
  echo '<input type="checkbox" name="extra[oferta]" value="1" '.$is_oferta.' />';
  echo ' Принята оферта</label></div>';

  echo '<input type="hidden" name="extra_fields_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
}
// <-- Формат записей: КЛИЕНТЫ


// Формат записей: КЛИЕНТЫ --------------------------------------------------------------------------------------------->
add_action('init', 'add_post_type_offers');
function add_post_type_offers(){
  register_post_type( 'offers',
    array(
      'labels'  => array(
        'name'          => __( 'Акции', 'temptraining' ),
        'singular_name' => __( 'Акция', 'temptraining' ),
        'add_new'       => __( 'Добавить акцию', 'temptraining' ),
      ),
      'public'        => false,
      'publicly_queryable' => true,
      'show_in_nav_menus' => true,
      'show_ui' => true,
      'menu_position' => 7,
      'menu_icon'     => 'dashicons-megaphone',
      'has_archive' => false,
      'capability_type' => 'post',
      'supports'  => array('title', 'editor', 'thumbnail'),
      'rewrite' => array(
        'slug' => 'offers',
        'with_front' => false,
      ),
    )
  );
}
// <-- Формат записей: ПРЕДЛОЖЕНИЯ
