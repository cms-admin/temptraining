<?php
/**
 * Специальный формат записей для тренеров
 * @package temptrainig
 * @author A.Erko <erkoam@mail.ru>
 *  
 */

/**
 * Регистрирует новый тип постов - Тренеры
 */
function add_post_type_coaches() 
{
  $labels = [
    'name'                => tlang('Тренеры'),                        // основное название для типа записи
    'singular_name'       => tlang('Тренер'),                         // название для одной записи этого типа
    'add_new'             => tlang('Добавить тренера'),               // для добавления новой записи
    'add_new_item'        => tlang('Создание тренера'),               // заголовка у вновь создаваемой записи в админ-панели.
    'edit_item'           => tlang('Редактирование тренера'),         // для редактирования типа записи
    'new_item'            => tlang('Новый тренер'),                   // текст новой записи
    'view_item'           => tlang('Смотреть тренера'),               // для просмотра записи этого типа.
    'search_items'        => tlang('Искать тренера'),                 // для поиска по этим типам записи
    'not_found'           => tlang('Не найдено тренеров'),            // если в результате поиска ничего не было найдено
    'not_found_in_trash'  => tlang('Не найдено тренеров в корзине'),  // если не было найдено в корзине
    'parent_item_colon'   => tlang('Позиция'),                        // для родителей (у древовидных типов)
    'menu_name'           => tlang('Тренеры'),                        // название меню
    'name_admin_bar'      => tlang('Тренера'),                        // Название в меню "Добавить" админ-бара
  ];

  $args = [
    'label'                 => tlang('Тренеры'),
    'labels'                => $labels,
    'description'           => tlang('Управление тренерами'),
    'public'                => false,
    'show_ui'               => true,
    'show_in_nav_menus'     => false,
    'menu_position'         => 5,
    'menu_icon'             => 'none',
    'capability_type'       => 'post',
    'supports'              => ['title', 'editor', 'thumbnail'],
    'register_meta_box_cb'  => 'add_coach_metaboxes'
  ];

  register_post_type('coach', $args);
}
add_action('init', 'add_post_type_coaches');

/**
 * Настройка колонок для списка тренеров в админке
 */
function theme_coach_columns($columns) 
{
  if (is_array($columns)){
    foreach($columns as $key=>$value){
      $new_columns[$key] = $value;
      if($key == 'cb' && !isset($columns['thumbnail'])) $new_columns['thumbnail'] = tlang('Фото');
      if($key == 'title' && !isset($columns['coach_id'])) $new_columns['coach_id'] = tlang('ID Тренера');
      if($key == 'title' && !isset($columns['spec'])) $new_columns['spec'] = tlang('Специализация тренера');
    }
  }

  return $new_columns;
}
add_filter('manage_coach_posts_columns', 'theme_coach_columns');

/**
 * Содержимое кастомных колонок
 */
function theme_coach_columns_content($column, $post_id)
{
  switch ($column) {
    case 'thumbnail':
      $no_image = '<img src="'.get_template_directory_uri().'/images/icons/whistle.svg" width="60" height="60" />';
      echo (has_post_thumbnail($post_id)) ? get_the_post_thumbnail($post_id, 'temptraining-flag', array('class' => 'coach-thumb')) : $no_image;
      break;

    case 'coach_id':
      $coach_id = get_post_meta($post_id, 'coach_id', 1);
      echo (trim($coach_id) != '') ? $coach_id : tlang('Не указано');
      break;

    case 'spec':
      $spec = get_post_meta($post_id, 'spec', 1);
      echo (trim($spec) != '') ? $spec : tlang('Не указано');
      break;
  }
}
add_action('manage_coach_posts_custom_column', 'theme_coach_columns_content', 10, 2);


/**
 * Регистрирует блок дополнительных полей для тренера
 */
function add_coach_metaboxes()
{
  add_meta_box(
    'сoach_custom_meta', 
    __('Additional fields', 'temptraining'), 
    'coach_custom_meta_box', 'coach', 'side', 'default'
  );
}

add_action('add_meta_boxes', 'add_coach_metaboxes');

/**
 * бэк-энд для Дополнительных поле тренера
 * @param  [type] $post [description]
 * @return [type]       [description]
 */
function coach_custom_meta_box($post)
{
  $context = Timber::get_context();

  $coach_meta = [];

  foreach (get_post_meta($post->ID) as $key => $value) {
    $coach_meta[$key] = $value[0];
  }

  $context['meta'] = $coach_meta;

  wp_nonce_field('label_image_submit', 'label_image_nonce');

  $context['fields_nonce'] = wp_create_nonce(__FILE__);

  Timber::render('backend/coach_edit_metabox.twig', $context);

  echo '<input type="hidden" name="extra_fields_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
} 

// Сохранение мета-данных для баннеров
function coach_custom_meta_box_save($post_id, $post) {
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

add_action('save_post', 'coach_custom_meta_box_save', 1, 2); // save the custom fields