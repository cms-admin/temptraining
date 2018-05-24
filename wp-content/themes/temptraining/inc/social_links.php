<nav class="social-links">
  <ul class="social-links__list">
    <?php if(!empty(temptraining_opt('social_facebook'))) : ?>
      <li class="social-links__list-item bg-facebook">
        <a href="<?php echo temptraining_opt('social_facebook'); ?>" rel="nofollow" rel="noopener" target="_blank">
          <i class="icon-facebook"></i>
        </a>
      </li>
    <?php endif; ?>

    <?php if(!empty(temptraining_opt('social_vk'))) : ?>
      <li class="social-links__list-item bg-vk">
        <a href="<?php echo temptraining_opt('social_vk'); ?>" rel="nofollow" rel="noopener" target="_blank">
          <i class="icon-vk"></i>
        </a>
      </li>
    <?php endif; ?>

    <?php if(!empty(temptraining_opt('social_twitter'))) : ?>
      <li class="social-links__list-item bg-twitter">
        <a href="<?php echo temptraining_opt('social_twitter'); ?>" rel="nofollow" rel="noopener" target="_blank">
          <i class="icon-twitter"></i>
        </a>
      </li>
    <?php endif; ?>

    <?php if(!empty(temptraining_opt('social_google'))) : ?>
      <li class="social-links__list-item bg-google">
        <a href="<?php echo temptraining_opt('social_google'); ?>" rel="nofollow" rel="noopener" target="_blank">
          <i class="icon-google-plus"></i>
        </a>
      </li>
    <?php endif; ?>

    <?php if(!empty(temptraining_opt('social_youtube'))) : ?>
      <li class="social-links__list-item bg-youtube">
        <a href="<?php echo temptraining_opt('social_youtube'); ?>" rel="nofollow" rel="noopener" target="_blank">
          <i class="icon-youtube-play"></i>
        </a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
