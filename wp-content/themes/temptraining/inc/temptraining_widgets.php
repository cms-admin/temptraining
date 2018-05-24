<?php
/**
 * Виджет тарифов
 */
class temptraining_price_widget extends WP_Widget
{
  function __construct() {
    parent::__construct(
      // Base ID of your widget
      'temptraining_price_widget',
      // Widget name will appear in UI
      __('Price table Widget', 'temptraining'),
      // Widget description
      array( 'description' => __( 'Widget for site price table', 'temptraining' ), )
    );

  }

  // Creating widget front-end
  // This is where the action happens
  public function widget($args, $instance)
  {
    require get_template_directory() . '/widgets/price-table.php';
  }

  // Widget Backend
  public function form($instance)
  {
    $title = (isset($instance['title'])) ? $instance['title'] : __('Price table title', 'temptraining');
    $sum = (isset($instance['sum'])) ? $instance['sum'] : __('Price table sum', 'temptraining');
    $currency = (isset($instance['currency'])) ? $instance['currency'] : __('Price table currency', 'temptraining');
    $text = (isset($instance['text'])) ? $instance['text'] : __('Price table text', 'temptraining');
    $link = (isset($instance['link'])) ? $instance['link'] : __('Price table link', 'temptraining');

    // Widget admin form
    $admin_html = '<p>';
    $admin_html .= '<label for="'.$this->get_field_id('title').'">'.__( 'Price title', 'temptraining' ).'</label>';
    $admin_html .= '<input class="widefat" id="'.$this->get_field_id( 'title' ).'"    name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr( $title ).'" />';
    $admin_html .= '</p>';

    $admin_html .= '<p>';
    $admin_html .= '<label for="'.$this->get_field_id('sum').'">'.__( 'Price sum', 'temptraining' ).'</label>';
    $admin_html .= '<input class="widefat" id="'.$this->get_field_id( 'sum' ).'"    name="'.$this->get_field_name('sum').'" type="text" value="'.esc_attr($sum).'" />';
    $admin_html .= '</p>';

    $admin_html .= '<p>';
    $admin_html .= '<label for="'.$this->get_field_id('currency').'">'.__( 'Price currency', 'temptraining' ).'</label>';
    $admin_html .= '<input class="widefat" id="'.$this->get_field_id('currency').'"    name="'.$this->get_field_name('currency').'" type="text" value="'.esc_attr($currency).'" />';
    $admin_html .= '</p>';

    $admin_html .= '<p>';
    $admin_html .= '<label for="'.$this->get_field_id('text').'">'.__( 'Price text', 'temptraining' ).'</label>';
    $admin_html .= '<input class="widefat" id="'.$this->get_field_id('text').'"    name="'.$this->get_field_name('text').'" type="text" value="'.esc_attr($text).'" />';
    $admin_html .= '</p>';

    $admin_html .= '<p>';
    $admin_html .= '<label for="'.$this->get_field_id('link').'">'.__( 'Price link', 'temptraining' ).'</label>';
    $admin_html .= '<input class="widefat" id="'.$this->get_field_id('link').'"    name="'.$this->get_field_name('link').'" type="text" value="'.esc_attr($link).'" />';
    $admin_html .= '</p>';

    echo $admin_html;
  }

  // Updating widget replacing old instances with new
  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    $instance['sum'] = (!empty($new_instance['sum'])) ? strip_tags($new_instance['sum']) : '';
    $instance['currency'] = (!empty($new_instance['currency'])) ? strip_tags($new_instance['currency']) : '';
    $instance['text'] = (!empty($new_instance['text'])) ? strip_tags($new_instance['text']) : '';
    $instance['link'] = (!empty($new_instance['link'])) ? strip_tags($new_instance['link']) : '';

    return $instance;
  }
} // Class wpb_widget ends here

/**
 * Последние новости (список)
 */
class temptraining_content_list_widget extends WP_Widget
{
  function __construct() {
    parent::__construct(
      // Base ID of your widget
      'temptraining_content_list_widget',
      // Widget name will appear in UI
      __('Latest news', 'temptraining'),
      // Widget description
      array( 'description' => __( 'Shows a list of latest news', 'temptraining' ), )
    );

  }

  // Creating widget front-end
  // This is where the action happens
  public function widget($args, $instance)
  {
    
    $tpl = ($instance['type']) ? $instance['type'] : 'content-list';
    $before = (!empty($args['before_widget'])) ? $args['before_widget'] : '';
    $after = (!empty($args['after_widget'])) ? $args['after_widget'] : '';
    
    echo $before;
    require get_template_directory() . '/widgets/'.$tpl.'.php';
    echo $after;
    
    
  }

  // Widget Backend
  public function form($instance)
  {
    $title = (isset($instance['title'])) ? $instance['title'] : __('Widget title', 'temptraining');
    $text = (isset($instance['text'])) ? $instance['text'] : __('Widget description', 'temptraining');
    $cat = (isset($instance['cat'])) ? $instance['cat'] : __('Category', 'temptraining');
    $limit = (isset($instance['limit'])) ? $instance['limit'] : '';
    $type = (isset($instance['type'])) ? $instance['type'] : 'content-list';
    
    echo '<input type="hidden" id="'.$this->get_field_id( 'id' ).'" name="'.$this->get_field_name('id').'" value="' . $this->id . '" />';

    // Widget admin form
    $control_title = '<p>';
    $control_title .= '<label for="'.$this->get_field_id('title').'">'.__( 'Widget title', 'temptraining' ).'</label>';
    $control_title .= '<input class="widefat" id="'.$this->get_field_id( 'title' ).'"    name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr( $title ).'" />';
    $control_title .= '</p>';

    echo $control_title;

    $control_text = '<p>';
    $control_text .= '<label for="'.$this->get_field_id('text').'">'.__( 'Widget description', 'temptraining' ).'</label>';
    $control_text .= '<textarea class="widefat" id="'.$this->get_field_id('text').'" name="'.$this->get_field_name('text').'" rows="3">'.esc_attr( $text ).'</textarea>';
    $control_text .= '</p>';

    echo $control_text;

    $cat_args = array(
      'id'        => $this->get_field_id('cat'),
      'class'     => 'widefat',
      'echo'      => 0,
      'name'      => $this->get_field_name('cat'),
      'selected'  => esc_attr($cat),
      'show_count'  => 1,
      'hierarchical'  => 1
    );
    $c_cat = '<p>';
    $c_cat .= '<label for="'.$this->get_field_id('cat').'">'.__( 'Category', 'temptraining' ).'</label>';
    $c_cat .= wp_dropdown_categories($cat_args);
    $c_cat .=  '</p>';

    echo $c_cat;

    $control_limit = '<p>';
    $control_limit .= '<label for="'.$this->get_field_id('limit').'">'.__( 'Posts limit', 'temptraining' ).'</label>';
    $control_limit .= '<input class="widefat" id="'.$this->get_field_id('limit').'" name="'.$this->get_field_name('limit').'" type="text" value="'.esc_attr($limit).'" />';
    $control_title .= '</p>';

    echo $control_limit;

    $c_type = '<p>';
    $c_type .= '<label for="'.$this->get_field_id('type').'">'. __('Widget type', 'temptraining').'</label>';
    $c_type .= '<select class="widefat" id="'.$this->get_field_id('type').'" name="'.$this->get_field_name('type').'">';
    // Option: content-list
    $c_type .= '<option value="content-list" ';
    if (esc_attr($type) == 'content-list') $c_type .= 'selected="selected"';
    $c_type .= '>'.__('Content list', 'temptraining').'</option>';
    // Option: content-accordeon
    $c_type .= '<option value="content-accordeon" ';
    if (esc_attr($type) == 'content-accordeon') $c_type .= 'selected="selected"';
    $c_type .= '>'.__('Content accordeon', 'temptraining').'</option>';
    // End select
    $c_type .= '</select>';
    $c_type .= '</p>';

    echo $c_type;
  }

  // Updating widget replacing old instances with new
  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['id'] = (!empty($new_instance['id'])) ? strip_tags($new_instance['id']) : '';
    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    $instance['text'] = (!empty($new_instance['text'])) ? strip_tags($new_instance['text']) : '';
    $instance['cat'] = (!empty($new_instance['cat'])) ? strip_tags($new_instance['cat']) : '';
    $instance['limit'] = (!empty($new_instance['limit'])) ? strip_tags($new_instance['limit']) : '';
    $instance['type'] = (!empty($new_instance['type'])) ? strip_tags($new_instance['type']) : '';

    return $instance;
  }
} // Class wpb_widget ends here


// Register and load the widget
function temptraining_load_widget() {
	register_widget( 'temptraining_price_widget' );
	register_widget( 'temptraining_content_list_widget' );
}
add_action( 'widgets_init', 'temptraining_load_widget' );
?>
