<?php
$articles = explode(',', $atts['id']);
foreach ($articles as $key => $value) {
  $articles[$key] = trim($value);
}
$masonry = '{"itemSelector": ".masonry-item" }';
?>
<div class="container">
  <header class="archive-header">
    <?php the_title( '<h1 class="archive-title">', '</h1>' ); ?>
  </header><!-- .page-header -->

  <?php if(is_array($articles)) : ?>
  <div class="archive-thumbs full-width page-articles">
    <div class="row">
      <div class="masonry-container js-masonry" data-masonry-options='<?php echo $masonry; ?>'>
        <?php foreach ($articles as $id) : ?>
          <?php $page = get_post($id); ?>
          <div class="masonry-item">
          	<article id="post-<?php echo $page->ID; ?>" class="<?php echo implode(' ', get_post_class('', $page->ID)); ?>">
          		<a class="post-thumbnail" href="<?php echo get_permalink($page); ?>"
          			style="background-image: url(<?php echo get_the_post_thumbnail_url($page); ?>)">
          		</a>

          		<div class="post-container">
          			<h3 class="post-title"><?php echo $page->post_title; ?></h3>
          			<div class="post-text">
          				<?php
                  if (stripos( $page->post_content, '<!--more-->' ) > 0) :
                    $echo = preg_grep("/(<!--more-->)/iU", explode("\n", $page->post_content));
                    echo str_replace('<!--more-->', '', $echo[0]);
                  else :
                    echo mb_substr(strip_tags($page->post_content), 0, 250, 'utf-8') . '...';
                  endif;
                  ?>
                  <div class="post-link">
                    <a href="<?php echo get_permalink($page); ?>">Читать далее</a>
                  </div>
          			</div>

          		</div>
          	</article>
          </div>
        <?php endforeach; ?>
        <?php dynamic_sidebar('articles'); ?>
      </div>
    </div>
  </div>
  <?php else :
    get_template_part( 'content', 'none' );
  endif; ?>
</div>
