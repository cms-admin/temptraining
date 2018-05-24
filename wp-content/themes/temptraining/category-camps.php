<?php get_header(); ?>

<?php if (is_active_sidebar('camps_top')) : ?>
  <?php dynamic_sidebar('camps_top'); ?>
<?php endif; ?>

<div class="container">
	<div id="content" role="main" class="wrap-flex">
    <div class="row-flex">
      <div class="col-flex is-3-4 is-3-4-lap is-12-tab">

        <header class="archive-header">
          <h1 class="archive-title"><?php echo single_cat_title('', false); ?></h1>
          <?php
            // Show an optional term description.
            $term_description = term_description();
            if ( ! empty( $term_description ) ) :
              printf( '<div class="taxonomy-description">%s</div>', $term_description );
            endif;
          ?>
  			</header><!-- .page-header -->
        <?php
        $today = date('Ymd', time());
        $sort_args = [
          'post_type'   => 'post',
          'post_status' => 'publish',
          'order'       => 'ASC',
          'meta_key'    => 'event_date',
          'orderby'     => 'meta_value',
          'meta_query'  => [
            'key' => 'event_date',
            'value' => $today,
            'compare' => '>'
          ]
        ];
        $sort_query = new WP_Query( $sort_args );
        ?>
        <?php if ( $sort_query->have_posts() ) : ?>
          <div class="small-tickets">
            <?php
  					// Start the Loop.
  					while ( $sort_query->have_posts() ) : $sort_query->the_post();
  						/*
  						 * Include the Post-Format-specific template for the content.
  						 * If you want to override this in a child theme, then include a file
  						 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
  						 */
  						$grid_size = array();
  						get_template_part('content-sm-tickets');
  	 				endwhile; ?>
          </div>
        <?php else: ?>
          <?php get_template_part( 'content', 'none' ); ?>
        <?php endif; ?>
      </div>

      <div class="col-flex is-1-4 is-1-4-lap is-12-tab">
        <header class="archive-header">
          <h2 class="archive-title"><?php echo __('Прошедшие сборы', 'temptraining'); ?></h2>
        </header>
        <?php
        $archive_args = [
          'posts_per_page' => -1,
          'post_type'   => 'post',
          'post_status' => 'publish',
          'order'       => 'DESC',
          'meta_key'    => 'event_date',
          'orderby'     => 'meta_value',
          'meta_query'  => [
            'key' => 'event_date',
            'value' => $today,
            'compare' => '<'
          ]
        ];
        $archive_query = new WP_Query( $archive_args );
        while ( $archive_query->have_posts() ) : $archive_query->the_post();
          $event_date = strtotime(get_post_meta($post->ID, 'event_date', true));
          if ($event_date && $event_date < time()) :
            $add = array('title' => $post->post_title, 'link' => get_the_permalink ($post->ID));
            $Y = date('Y', $event_date);
            if(isset($archive->{$Y})){
              array_push($archive->{$Y}, $add);
            } else {
              $archive->{$Y}[0] = $add;
            }
          endif;
        endwhile;
        ?>
        <div class="camps-accordeon" data-toggle="accordion" data-accordion-options='{"heightStyle": "content"}'>
          <?php foreach ($archive as $key => $val) : ?>
            <h3 class="camps-accordeon__title"><?php echo $key; ?></h3>
            <div class="camps-accordeon__body">
              <?php foreach ($val as $item) : ?>
                <a href="<?php echo $item['link']; ?>"><?php echo $item['title']; ?></a>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (is_active_sidebar('camps_bottom')) : ?>
  <?php dynamic_sidebar('camps_bottom'); ?>
<?php endif; ?>

<?php get_footer(); ?>
