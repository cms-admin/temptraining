<?php

/**
 * Вывод статьи в подвале
 * @param  array $atts
 * @return html
 */
function temptraining_footer_page($atts) {

	// Attributes
	$atts = shortcode_atts(
		array(
      'id' => '',
		),
		$atts,
		'id'
	);

  if(!empty($atts['id'])) :
    $post = get_post($atts['id']);
    if ($post) :
      $html = '<div class="widget-content__list">';
      $post->image = get_the_post_thumbnail_url($post, array(60, 60));
      $post->class = ($post->image) ? ' has-image' : '';
      $html .= '<article class="post-item'.$post->class.'">';
      if ($post->image) :
        $html .= '<figure class="post-item__image">';
        $html .= '<img src="'.$post->image.'" alt="'.$post->post_title.'" class="img-responsive" />';
        $html .= '</figure>';
      endif;
      $html .= '<h4 class="post-item__title">'.$post->post_title.'</h4>';
      $html .= '<div class="post-item__link">';
      $html .= '<a href="'.get_permalink($post).'">'.__('Read more', 'temptraining').'</a>';
      $html .= '</div>';
      $html .= '</article>';
      $html .= '</div>';

      ob_start();
      echo $html;
      return ob_get_clean();
    endif;
  endif;

}

add_shortcode( 'footer_page', 'temptraining_footer_page' );

/**
 * Вывод статьи в подвале
 * @param  array $atts
 * @return html
 */
function temptraining_sidebar_page($atts) {

	// Attributes
	$atts = shortcode_atts(
		array(
      'id' => '',
		),
		$atts,
		'id'
	);

  if(!empty($atts['id'])) :
    $post = get_post($atts['id']);
    if ($post) :
      ob_start();
      require get_template_directory() . '/widgets/sidebar-page.php';
      $html = ob_get_clean();
      return $html;
    endif;
  endif;

}

add_shortcode( 'sidebar_page', 'temptraining_sidebar_page' );

/**
 * Шорткод для социальных ссылок
 * @return type
 */
function temptraining_social_links() {
  ob_start();
  require get_template_directory() . '/inc/social_links.php';
  return ob_get_clean();
}

add_shortcode( 'social_links', 'temptraining_social_links' );

/**
 * Отображает страницу с тренерами
 * @return html
 */
function temptraining_special_page($atts)
{
	// Attributes
	$atts = shortcode_atts(array('type' => '', 'id' => ''), $atts, 'type');

  if(!empty($atts['type'])) :
		ob_start();
	  require get_template_directory() . '/inc/page-' . $atts['type'] . '.php';
	  return ob_get_clean();
	else :
		return get_template_part('content', 'none');
	endif;
}
add_shortcode( 'special_page', 'temptraining_special_page' );

function temptraining_email($atts)
{
	// Attributes
	$atts = shortcode_atts(array('form_id' => '', 'subject' => '', 'title' => '', 'text' => ''), $atts, 'temptraining_email');

	if(!empty($atts['form_id'])) {
		$context  = Timber::get_context();
		$context['atts'] = $atts;
		$context['shortcode'] = '[contact-form-7 id="'.$atts['form_id'].'" title="'.$atts['title'].'" subject="'.$atts['subject'].'"]';

		ob_start();
		Timber::render('temptraining_email.twig', $context );
		return ob_get_clean();
	}
}
add_shortcode( 'temptraining_email', 'temptraining_email' );


function relap_shortcode($atts)
{
  // Attributes
  $atts = shortcode_atts(array('key' => ''), $atts, 'relap_shortcode');

  return ($atts['key']) ? '<script id="' . $atts['key'] . '">if (window.relap) window.relap.ar(\'' . $atts['key'] . '\');</script>' : false;
}
add_shortcode('relap', 'relap_shortcode');