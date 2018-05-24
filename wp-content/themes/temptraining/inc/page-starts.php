<?php
// Массив со стартами
$resultArgs = array(
  'numberposts'	=> -1,
  'post_status' => 'publish',
  'post_type'   => 'starts',
  'orderby'     => 'meta_date',
  'order'       => 'ASC',
);
$resultItems = get_posts($resultArgs);
?>
<div class="page-starts big-tickets">
  <div class="container">
    
    <?php if ($resultItems) : ?>
      <?php
        $month = '';
        $month_arr = [
          '01'=>'январь',
          '02'=>'февраль',
          '03'=>'март',
          '04'=>'апрель',
          '05'=>'май',
          '06'=>'июнь',
          '07'=>'июль',
          '08'=>'август',
          '09'=>'сентябрь',
          '10'=>'октябрь',
          '11'=>'ноябрь',
          '12'=>'декабрь'
        ];
      ?>
    
      <?php 
      $months = [];
      foreach ($resultItems as $post){
        $post_month = date('m', strtotime(get_post_meta($post->ID, 'date', true)));
        
        if(array_key_exists($post_month, $months)){
          array_push($months[$post_month], $post->ID);
        } else {
          $months[$post_month] = [$post->ID];
        }
        
        
      }
      $user = wp_get_current_user();
        
      foreach ($months as $key=>$value) : ?>
        <header class="entry-header">
          <h1 class="entry-title"><?php echo $month_arr[$key]; ?></h1>
        </header>
          
        <?php 
        foreach ($value as $id) :
          $post = get_post( $id );
          $post_class = implode(" ", get_post_class("ticket", $post->ID));
          $post_date = temptraining_short_date(strtotime(get_post_meta($post->ID, 'date', true)));
          $post_action_class = (get_post_meta($post->ID, 'status', true) == 'open') ? 'active' : 'disable';
          if (empty(get_post_meta($post->ID, 'users', true))) {
            $post_users_count = 0;
            $post_users_class = 'disable';
          } else {
            $post_users_count = count(explode(',', get_post_meta($post->ID, 'users', true)));
            $post_users_class = 'active';
          }
        ?>
          <article id="post-<?php $post->ID; ?>" class="<?php echo $post_class; ?>">
            <div class="row-flex">
              <div class="col-flex is-9 is-9-lap is-12-tab">
                <div class="post-main">
                  <figure class="post-thumbnail">
                    <?php echo get_the_post_thumbnail($post->ID, 'original'); ?>
                  </figure>
                  <div class="post-date">
                    <span><?php echo $post_date['m']; ?></span>
                    <span class="day"><?php echo $post_date['d']; ?></span>
                    <span><?php echo $post_date['y']; ?></span>
                  </div>
                  <div class="post-data">
                    <h3 class="post-title"><?php echo $post->post_title; ?></h3>
                    <div class="post-excerpt">
                      <?php echo mb_substr(strip_tags($post->post_content), 0, 250, 'utf-8') . '...'; ?>
                    </div>
                  </div>
                </div>

                <div class="post-addons">
                  <div class="post-addons__count">
                    <i class="ion ion-person-stalker"></i> <span><?php echo __('Уже участвуют', 'temptraining'); ?></span>
                    <label class="post-addons__label <?php echo $post_users_class; ?>"><?php echo $post_users_count; ?></label>
                  </div>
                  <div class="post-addons__list">
                    <?php if ($post_users_count > 0) {
                      echo get_post_meta($post->ID, 'users', true);
                    } else {
                      echo __('Пока нет участников', 'temptraining');
                    }
                    ?>
                  </div>
                </div>
              </div>
              <div class="col-flex is-3 is-3-lap is-12-tab post-separate">
                <div class="post-place">
                  <i class="ion ion-location"></i>
                  <span><?php echo nl2br(get_post_meta($post->ID, 'place', true)); ?></span>
                </div>
                <div class="post-action <?php echo $post_action_class; ?>">
                  <?php if ($post_action_class == 'active'): ?>
                    <a class="post-button" href="<?php echo get_post_meta($post->ID, 'link', true);?>">
                      <?php echo __('Регистрация', 'temptraining'); ?>
                    </a>
                  <?php else : ?>
                    <span class="post-button">
                      <?php
                        if (get_post_meta($post->ID, 'status', true) == 'wait') :
                          echo __('Регистрация', 'temptraining');
                        else :
                          echo __('Закрыто', 'temptraining');
                        endif;
                      ?>
                    </span>
                  <?php endif; ?>
                  <?php if(!empty(get_post_meta($post->ID, 'note', true))): ?>
                    <div class="post-note">
                      <?php echo nl2br(get_post_meta($post->ID, 'note', true)); ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
        <?php
        endforeach;

      endforeach;
      ?>
     
    <?php endif; ?>
  </div>
</div>
