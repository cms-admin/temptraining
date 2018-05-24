<?php
$args = array(
  'numberposts'	=> -1,
  'post_status' => 'publish',
  'post_type'   => 'coach',
  'orderby'     => 'date',
  'order'       => 'DESC',
);
$post = get_post(13);
$link = get_permalink($post);
$items = get_posts($args);
if ($items) :
?>

<div class="home-coaches">
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="container">
      <header class="entry-header">
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
      </header><!-- .entry-header -->

      <?php temptraining_post_thumbnail(); ?>

      <div class="entry-content">
        <div class="row">
          <?php foreach ($items as $item) {
            $image = $spec = $label = false;
            $image = array (
              'thumb' => get_the_post_thumbnail_url($item, 'temptraining-normal'),
              'prev' => get_the_post_thumbnail_url($item, 'temptraining-big'),
            );
            $spec = get_post_meta($item->ID, 'spec', true);
            $label = intval(get_post_meta($item->ID, 'label', true));
            ?>
            <div class="col-xs-12 col-sm-6 col-xm-4 col-md-3">
              <div class="home-coaches__item">
                <figure class="home-coaches__item-photo" style="background-image: url(<?php echo $image['thumb']; ?>)"></figure>
                <?php if ($label): ?>
                <div class="home-coaches__item-label" data-self-size>
                  <?php echo wp_get_attachment_image($label, 'coach-label'); ?>
                </div>
                <?php endif; ?>
                <div class="home-coaches__item-data">
                  <div class="home-coaches__item-spec"><?php echo $spec; ?></div>
                  <h4 class="home-coaches__item-title"><?php echo $item->post_title; ?></h4>
                  <div class="home-coaches__item-text"><?php echo $item->post_content; ?></div>
                  <div class="home-coaches__item-link">
                    <?php if (temptraining_count_by_tag(urldecode($item->post_name)) > 0) : ?>
                      <a href="<?php echo site_url('tag/' . $item->post_name . '/'); ?>">
                        <?php echo __('Followers reports', 'temptraining'); ?>
                        (<?php echo temptraining_count_by_tag(urldecode($item->post_name)) ?>)
                      </a>
                    <?php else : ?>
                      <span><?php echo __('Yet reports', 'temptraining'); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
        <?php
        wp_link_pages( array(
        	'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Страницы:', 'temptraining' ) . '</span>',
        	'after'       => '</div>',
        	'link_before' => '<span>',
        	'link_after'  => '</span>',
        	'pagelink'    => '<span class="screen-reader-text">' . __( 'Страница', 'temptraining' ) . ' </span>%',
        	'separator'   => '<span class="screen-reader-text">, </span>',
        ) );

        edit_post_link( __( 'Править', 'temptraining' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' );
        ?>
      </div>
    </div>
  </article>
</div>
<?php endif; ?>
