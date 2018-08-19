</div>
<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-sm-6 col-xm-4">
        <div class="row">
          <div class="col-xx-6">
            <nav class="footer-menu">
              <?php	wp_nav_menu(array('theme_location'	=> 'footer_menu_left', 'container' => false, 'menu_id' => 'footer-menu-left')); ?>
            </nav>
          </div>
          <div class="col-xx-6">
            <nav class="footer-menu">
              <?php	wp_nav_menu(array('theme_location'	=> 'footer_menu_right', 'container' => false, 'menu_id' => 'footer-menu-right')); ?>
            </nav>
          </div>
        </div>
<a href="https://temptraining.ru/privacy-policy/">Политика конфиденциальности</a>
      </div>
      <div class="col-sm-6 col-xm-8">
        <div class="row">
          <?php if (is_active_sidebar('footer')) : ?>
            <?php dynamic_sidebar('footer'); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</footer>
<button data-toggle="modal" data-target="#callback-modal" class="pulse-button" title="Написать директору">
  <i class="ion ion-chatboxes"></i>
</button>
<?php ttcli_get_form('training'); ?>

<?php if (is_active_sidebar('callback')) : dynamic_sidebar('callback'); endif; ?>
<?php wp_footer(); ?>
</body>
</html>
