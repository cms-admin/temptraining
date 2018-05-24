<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

<!--	<link rel="profile" href="http://gmpg.org/xfn/11"> -->
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div class="wrapper" data-scroll-reveal="" data-scroll-reveal-options='{"delay": "onload"}'>
		<header class="header hidden-xx hidden-xs">
			<div class="container">
				<div class="row">
					<div class="col-sm-5 col-xm-4 col-md-4">
						<section class="header-logo">
							<h2 class="header-logo__text"><?php echo temptraining_br2span(temptraining_opt('header_title')); ?></h2>
							<figure class="header-logo__image">
								<a href="<?php echo home_url(); ?>">
									<img src="<?php echo get_template_directory_uri() . '/images/logo.svg'; ?>" alt="logo" />
								</a>
							</figure>
						</section>

					</div>
					<div class="col-sm-7 col-xm-8 col-md-8">
						<div class="header-contacts">
	            <?php if (trim(temptraining_opt('header_phone', ''))) : ?>
	            <div class="header-contacts__item">
	              <figure class="header-contacts__item-icon">
									<i class="icon-smartphone"></i>
								</figure>
	              <div class="header-contacts__item-phone"><?php echo temptraining_opt('header_phone'); ?></div>
	              <div class="header-contacts__item-email">
	              	<?php echo do_shortcode(temptraining_opt('header_email')); ?>
	              </div>
	            </div>
	            <?php endif; ?>

	            <?php if (trim(temptraining_opt('header_time', ''))) : ?>
							<!-- Время работы -->
							<div class="header-contacts__item">
								<figure class="header-contacts__item-icon">
									<i class="icon-stopwatch"></i>
								</figure>
								<div class="header-contacts__item-text">
									<?php echo nl2br(temptraining_opt('header_time')); ?>
								</div>
							</div>
	            <?php endif; ?>

	            <?php require get_template_directory() . '/inc/social_links.php'; ?>

						</div>
					</div>
				</div>
			</div>
		</header>
		<nav class="menu-main" data-spy="affix" data-offset-top="100">
			<div class="container">
				<div class="nav-horizontal clearfix">
					<button class="menu-main__toggle hidden-xm hidden-md hidden-lg" data-toggle="dropdown">
		        <i class="icon-bars"></i>
		      </button>

					<figure class="menu-main__brand visible-xx visible-xs" itemscope itemtype="http://schema.org/ImageObject">
						<a href="<?php echo home_url(); ?>">
							<img src="<?php echo get_template_directory_uri() . '/images/logo.svg'; ?>" alt="logo" />
						</a>
					</figure>
					<?php	wp_nav_menu(array(
						'theme_location'	=> 'primary',
						'container' => false,
					)); ?>
					<?php /* if (trim(temptraining_opt('header_email', ''))) : ?>
						<div class="menu-main__contact-email hidden-sm">
							<a href="mailto:<?php echo temptraining_opt('header_email'); ?>">
								<?php echo temptraining_opt('header_email'); ?>
							</a>
						</div>
					<?php endif; */ ?>
				</div>
			</div>
		</nav>

<?php
		if ( ! function_exists( '_wp_render_title_tag' ) ) :



		   add_action( 'wp_head', 'temptraining_render_title' );

		   function temptraining_render_title() {

		      ?>

		      <title>

		      <?php

		      /**

		       * Print the <title> tag based on what is being viewed.

		       */

		      wp_title( '|', true, 'right' );

		      ?>

		      </title>

		      <?php

		   }



		   add_filter( 'wp_title', 'temptraining_filter_wp_title' );

		   if ( ! function_exists( 'temptraining_filter_wp_title' ) ) :

		      /**

		       * Modifying the Title

		       *

		       * Function tied to the wp_title filter hook.

		       * @uses filter wp_title

		       */

		      function temptraining_filter_wp_title( $title ) {

		         global $page, $paged;



		         // Get the Site Name

		         $site_name = get_bloginfo( 'name' );



		         // Get the Site Description

		         $site_description = get_bloginfo( 'description' );



		         $filtered_title = '';



		         // For Homepage or Frontpage

		         if(  is_home() || is_front_page() ) {

		            $filtered_title .= $site_name;

		            if ( !empty( $site_description ) )  {

		               $filtered_title .= ' &#124; '. $site_description;

		            }

		         }

		         elseif( is_feed() ) {

		            $filtered_title = '';

		         }

		         else{

		            $filtered_title = $title . $site_name;

		         }



		         // Add a page number if necessary:

		         if( $paged >= 2 || $page >= 2 ) {

		            $filtered_title .= ' &#124; ' . sprintf( __( 'Page %s', 'temptraining' ), max( $paged, $page ) );

		         }



		         // Return the modified title

		         return $filtered_title;

		      }

		   endif;



		endif;?>
