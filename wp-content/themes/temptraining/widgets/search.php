<form role="search" method="get" id="searchform" class="search-form" action="<?php echo home_url( '/' ); ?>" >
  <input class="search-form__text" type="text" value="<?php echo get_search_query(); ?>" name="s" id="s"
    placeholder="<?php echo __('Поиск на сайте...', 'temptraining'); ?>" />
  <button class="search-form__submit" type="submit" id="searchsubmit"><i class="icon-search"></i></button>
</form>
