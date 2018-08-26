<?php

/**
 * Шорткод для отображения меню футера
 * @param $atts
 */
function theme_menu_shortcode($atts)
{
  $atts['location'] = (isset($atts['location'])) ? $atts['location'] : 'primary';
  $atts['container'] = (isset($atts['container'])) ? $atts['container'] : false;

  $context['menu'] = new TimberMenu($atts['location']);
  $context['atts'] = $atts;

  return Timber::compile('shortcodes/menu.twig', $context);
}
add_shortcode('show_menu','theme_menu_shortcode');