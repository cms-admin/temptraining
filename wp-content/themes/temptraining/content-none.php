<?php
/**
 * The template part for displaying a message that posts cannot be found
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title">Ничего не найдено</h1>
	</header><!-- .page-header -->

	<div class="page-content">

		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<p><?php printf( __( 'Готовы опубликовать свой первый пост? <a href="%1$s">Начать</a>.', 'temptraining' ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>

		<?php elseif ( is_search() ) : ?>

			<p><?php _e( 'Извините, но по вашему запросу ничего не найдено. Попробуйте поискать что-то другое.', 'temptraining' ); ?></p>
			<?php get_search_form(); ?>

		<?php else : ?>

			<p><?php _e( 'Мы не можем найти запрашиваемую страницу. Попробуйте воспользоваться поиском.', 'temptraining' ); ?></p>
			<?php get_search_form(); ?>

		<?php endif; ?>

	</div><!-- .page-content -->
</section><!-- .no-results -->
