<?php

/**
 * ------------------------------------------------------------------------------------------------------------------- *
 * специальный формат записей для слайдера
 * ------------------------------------------------------------------------------------------------------------------- *
 */
function add_post_type_banners() {
  register_post_type( 'banner',
    array(
      'thumbnail',
      'labels'  => array(
        'name'          => __( 'Banners', 'temptraining' ),
        'singular_name' => __( 'Banner', 'temptraining' ),
        'add_new'       => __( 'New banner', 'temptraining' ),
        'featured_image'  => __( 'Banner image', 'temptraining' ),
      ),
      'public'        => false,
      'publicly_queryable'  => true,
      'show_ui' => true,
      'menu_position' => 5,
      'menu_icon'     => 'dashicons-format-gallery',
      'supports'  => array('title', 'excerpt', 'thumbnail'),
      'register_meta_box_cb' => 'add_banners_metaboxes'
    )
  );
}

add_action( 'init', 'add_post_type_banners' );

// группы для баннеров
add_action( 'init', 'add_banners_taxonomies', 0 );
function add_banners_taxonomies(){
  $labels = array(
    'name'              => _x( 'Groups', 'taxonomy general name', 'temptraining' ),
    'singular_name'     => _x( 'Group', 'taxonomy singular name', 'temptraining' ),
    'all_items'         => __( 'All Groups', 'temptraining' ),
    'parent_item'       => null,
    'parent_item_colon' => null,
    'edit_item'         => __( 'Edit Group', 'temptraining' ),
    'update_item'       => __( 'Update Group', 'temptraining' ),
    'add_new_item'      => __( 'Add New Group', 'temptraining' ),
    'new_item_name'     => __( 'New Group Name', 'temptraining' ),
    'menu_name'         => __( 'Groups', 'temptraining' ),
  );

  $args = array(
    'hierarchical'      => false,
    'labels'            => $labels,
    'show_ui'           => true,
    'show_admin_column' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var'         => true,
    'rewrite'           => array( 'slug' => 'group' ),
    'capabilities'      => array('manage_terms', 'edit_terms', 'delete_terms', 'assign_terms')
  );

  register_taxonomy( 'group', array( 'banner' ), $args );
}

// Мета-боксы для баннеров
add_action( 'add_meta_boxes', 'add_banners_metaboxes' );
function add_banners_metaboxes(){
  add_meta_box('banners_custom_meta', __('Additional fields', 'temptraining'), 'banners_custom_meta_box', 'banner', 'normal', 'default');
}
function banners_custom_meta_box($post){
  // ссылка баннера
  echo '<h4>'. __('Banner link', 'temptraining') .'</h4>';
  echo '<input type="text" name="extra[link]" value="' . get_post_meta($post->ID, 'link', 1) . '" style="width:100%" />';

  echo '<input type="hidden" name="extra_fields_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
}

// Сохранение мета-данных для баннеров
function banners_save_meta($post_id, $post) {
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

add_action('save_post', 'banners_save_meta', 1, 2); // save the custom fields