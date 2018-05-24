<?php get_header(); ?>

<?php require get_template_directory() . '/inc/home_slider.php'; ?>

<?php if (is_active_sidebar( 'prices-table' )) : ?>
  <div class="home-prices">
    <div class="container">
      <div class="row">
        <?php dynamic_sidebar('prices-table'); ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require get_template_directory() . '/inc/home_coaches.php'; ?>

<div class="home-news">
  <div class="container">
    <div class="row">
      <?php if (is_active_sidebar('news-left')) : ?>
        <div class="col-md-6">
          <?php dynamic_sidebar('news-left'); ?>
        </div>
      <?php endif; ?>
      <?php if (is_active_sidebar('news-right')) : ?>
        <div class="col-md-6">
          <?php dynamic_sidebar('news-right'); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
if (temptraining_opt('home_uniform', false) == true){
  require get_template_directory() . '/inc/home_uniform.php';
}
?>

<?php require get_template_directory() . '/inc/home_partners.php'; ?>

<?php if (is_active_sidebar('mb-left') || dynamic_sidebar('mb-right')) : ?>
<section class="home-feedback">
  <div class="container">
    <div class="row">
      <?php if (is_active_sidebar('mb-left')) : ?>
        <div class="col-md-8">
          <?php dynamic_sidebar('mb-left'); ?>
        </div>
      <?php endif; ?>
      <?php if (is_active_sidebar('mb-right')) : ?>
        <div class="col-md-4">
          <?php dynamic_sidebar('mb-right'); ?>
          <?php require get_template_directory() . '/inc/social_links.php'; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
