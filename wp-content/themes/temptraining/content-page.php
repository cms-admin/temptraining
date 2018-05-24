<?php
$shortcodes = preg_grep("/(\[special_page.*\])/iU", explode("\n", get_the_content()));
$bg_image = get_the_post_thumbnail_url($post, 'original');

if(is_array($shortcodes) && count($shortcodes) > 0) :
  sort($shortcodes);
  if(count($shortcodes) == 1){
    # если один шорткод и перед ним есть текст
    $content = preg_split("/(\[special_page.*\])/iU", get_the_content());
    if(!empty($content[0])){
      $pos = strpos($shortcodes[0], ' ');
      $class = trim(substr($shortcodes[0], 1, $pos));
      preg_match("/type=\"(.*)\"/iU", $shortcodes[0], $type);
      $class .= '-' . $type[1];

      ?>
      <div class="<?php echo $class; ?>"<?php if ($bg_image) : ?> style="background-image: url(<?php echo $bg_image; ?>)"<?php endif; ?>>
        <div class="container"><?php echo $content[0]; ?></div>
      </div>
      <?php
    }

    echo do_shortcode($shortcodes[0]);
  } else {
    foreach ($shortcodes as $code) {
      echo do_shortcode($code);
    }
  }
else : ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class="container">
    <header class="entry-header">
  		<?php
  			if ( is_single() ) :
  				the_title( '<h1 class="entry-title">', '</h1>' );
  			else :
  				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
  			endif;
  		?>
  	</header>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>

    <footer class="entry-footer">
  		<?php edit_post_link( __( 'Править', 'temptraining' ), '<span class="edit-link">', '</span>' ); ?>
  	</footer><!-- .entry-footer -->
  </div>
</article>
<?php endif; ?>
