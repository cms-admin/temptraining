var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;

+function($){
  if(isMobile.any){
    $('body').addClass('is-mobile');
  }
  if(isMobile.apple.device){
    $('body').addClass('apple-device');
  }
  /* iOS switches */
  if (isset('.js-switch')) {
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));

    elems.forEach(function(html) {
      var switchery = new Switchery(html, {secondaryColor: '#ececec'});
    });
  }
  /* Скрипт-ловушка слайдера owl-carousel */
  if($('[data-owl-carousel]')){
		$.each($('[data-owl-carousel]'), function(){
			//console.log('OWL Init');
			var $this = $(this);
			var options = $this.data('owl-carousel-options');
			var navigation = $this.data('navigation');
			var activeClass = $this.data('owl-active-item');

			$(document).on('ready', function(){
				if(options.length != 0){
					$this.on('initialized.owl.carousel', function(event){
						if(event.page.size >= event.item.count){
							$this.parents('.container').siblings('.nav').hide();
							// console.log($this.parents('.container'));
						}else{
							$this.parents('.stoned-container').siblings('.nav').show();
						}

					});

					$this.owlCarousel(options);
					if(navigation == 'nav1'){

						// initialize carousel to variable
						var slider = $this, nav;

						// find slider navigation
						if(slider.parents('.owl-container').siblings('.nav').length){
							nav = slider.parents('.owl-container').siblings('.nav');
						}else if(slider.parents('.owl-container').find('.nav').length){
							nav = slider.parents('.owl-container').find('.nav');
						}

						// console.log(nav);

						nav.find('.prev').on('click', function(){
							// slider.prev();  // prev slide
							if($('body').hasClass('rtl')){
								slider.trigger('next.owl.carousel', [300]);
							}else{
								slider.trigger('prev.owl.carousel', [300]);
							}
						});

						nav.find('.next').on('click', function(){
							// slider.next();  // next slide
							slider.trigger('next.owl.carousel', [300]);
						});

						slider.on('resized.owl.carousel', function(event){
							if(event.page.size >= event.item.count){
								nav.hide();
							}else{
								nav.show();
							}

						});
					} else if(navigation == 'nav2'){
						// initialize carousel data to variable
						var slider = $this;
						var sliderNav = $this.siblings('.nav2')

						sliderNav.find('.prev').on('click', function(){
							if($('body').hasClass('rtl')){
								slider.trigger('next.owl.carousel', [300]);
							}else{
								slider.trigger('prev.owl.carousel', [300]);
							}
						});

						sliderNav.find('.next').on('click', function(){
							if($('body').hasClass('rtl')){
								slider.trigger('prev.owl.carousel', [300]);
							}else{
								slider.trigger('next.owl.carousel', [300]);
							}
						});

						sliderNav.on('resized.owl.carousel', function(event){
							if(event.page.size >= event.item.count){
								$this.parents('.stoned-container').siblings('.nav').hide();
								// console.log($this.parents('.stoned-container'));
							}else{
								$this.parents('.stoned-container').siblings('.nav').show();
							}

						});
					}

				}else{
					// console.log(options.length);
					$this.owlCarousel({
						nav : true,
						slideSpeed : 300,
						paginationSpeed : 400,
						items : true,
						transitionStyle: "fade"
					});
				}
			});
		});
	}

  /* Скрипт-ловушка аккордеона */
	if($('[data-toggle="accordion"]')){
		$.each($('[data-toggle="accordion"]'), function(){
			var $this = $(this);
      var options = $this.data('accordion-options');

      if (typeof(options) != 'undefined'){
        $this.accordion(options);
      } else {
        $this.accordion();
      }
		});
	}

	/* Скрипт задержки-появления при попадании в область просмотра */
	if($('[data-scroll-reveal]')){
		$.each($('[data-scroll-reveal]'), function(){
			var $this = $(this);
			var config = $this.data('scroll-reveal-options');

			$(window).on('load', function(){
				window.sr = new scrollReveal(config);
			});
		});
	}

  /* Скрипт ловушка всплывающих изображений */
  if($('[data-magnific-popup]')){
		$.each($('[data-magnific-popup]'), function(){
			var $this = $(this);
			var options = $this.data('magnific-popup-options');
			var callbacks = $this.data('magnific-popup-callbacks');

			var new_options = {};
			$.each(options, function(index, value) {
    		new_options[index] = value;
			});
			if (callbacks == 'buildControls'){
				var callbacks_obj = {};
				callbacks_obj["buildControls"] = function() {
		      this.contentContainer.append(this.arrowLeft.add(this.arrowRight));
		    };

				new_options["callbacks"] = callbacks_obj;
			}
			$this.magnificPopup(new_options);
		});
	}

  /**
   * Функции анимирования блоков
   */
  if($('[data-animate]')){
		$.each($('[data-animate]'), function(){
      var $this = $(this);
			var options = $this.data('animate');
      var type = options['type'];
      var del = Number(options['wait']) * 1000;

      $this.css("opacity", "0").addClass("animated");

      $this.waypoint(function() {
        setTimeout(function() {
          $this.addClass(type).css("opacity", "1");
        }, del)
      }, { offset: '100%'});
    });
	}
  if(!isMobile.any){
    var wow = new WOW({
      live: true
    });
    wow.init();
  }

  /** модальные окна */
  $(document).on('click', 'a[href="#begin-modal"]', function(event){
    event.preventDefault();

    var $this = $(this);
    var rel = $this.attr('rel');
    rel = (typeof(rel) == "undefined") ? $this.attr('data-rel') : rel;
    var $modal = $($this.attr("href"));

    $modal.toggleClass('open').modal('show');

    formSportSeletc(rel, $modal);
  });


  $(document).on('click', '[data-toggle="modal"]', function(event){
    event.preventDefault();

    var $this = $(this);
    var rel = $this.attr('rel');
    rel = (typeof(rel) == "undefined") ? $this.attr('data-rel') : rel;
    var $modal = $($this.data("target"));

    $modal.toggleClass('open').modal('show');

    formSportSeletc(rel, $modal);
  });

  $(document).on('change', '.wpcf7 .wpcf7-select', function(){
    var $this = $(this);
    var $form = $this.closest('.wpcf7-form');
    var value = $this.val();

    switch (value) {
      case "Бег":
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-3').removeClass('hidden');
        break;

      case "Велоспорт":
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-2').removeClass('hidden');
        break;

      case "Два спорта":
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-2').removeClass('hidden');
        break;

      default:
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-1').removeClass('hidden');
    }
  });

  function formSportSeletc(str, $form){
    switch (str) {
      case "Бег":
        $form.find('.wpcf7-select').find('option').removeAttr('selected');
        $form.find('.wpcf7-select').find('option[value="Бег"]').attr('selected', 'selected');
        $form.find('.wpcf7-select').niceSelect('update');
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-3').removeClass('hidden');
        break;

      case "Велоспорт":
        $form.find('.wpcf7-select').find('option').removeAttr('selected');
        $form.find('.wpcf7-select').find('option[value="Два спорта"]').attr('selected', 'selected');
        $form.find('.wpcf7-select').niceSelect('update');
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-2').removeClass('hidden');
        break;

      case "Два спорта":
        $form.find('.wpcf7-select').find('option').removeAttr('selected');
        $form.find('.wpcf7-select').find('option[value="Два спорта"]').attr('selected', 'selected');
        $form.find('.wpcf7-select').niceSelect('update');
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-2').removeClass('hidden');
        break;

      default:
        $form.find('.wpcf7-select').find('option').removeAttr('selected');
        $form.find('.wpcf7-select').find('option[value="Триатлон"]').attr('selected', 'selected');
        $form.find('.wpcf7-select').niceSelect('update');
        $form.find('.prices p').addClass('hidden');
        $form.find('.prices #price-1').removeClass('hidden');
    }
  }

  /* Авто-загрузка новых постов */
  if($('div').is('[data-loadmore]')){
    var $this = $('[data-loadmore]');

    var offset = $this.offset();
    var docHeight = $(document).height();
    var startLoad = docHeight - offset.top + 500;

    loadmore($this, startLoad);
  }

  /* Функция прилипания сайдбара */
  if($('[data-fixed-sidebar]')){
    if (x > 1200){
      var $this = $('[data-fixed-sidebar]');

      $this.affix({
        offset: {
          top: 100,
          bottom: function () {
            return (this.bottom = $('.footer').outerHeight(true))
          }
        }
      });
    }
  }

  /* Ориентация изображений */
  $.each($('.type-post img, .type-page img'), function(){
    var image = $(this);
    var w = parseInt(image.attr('width'));
    var h = parseInt(image.attr('height'));
    var imgClass = image.attr('class');

    image.parent(':not([data-self-size])').addClass('has-image');

    if (imgClass.indexOf('alignleft') === 0){
      image.parent().addClass('has-alignleft');
    } else if(typeof(w) != 'undefined' && typeof(h) != 'undefined'){
      if(w > h){
        image.addClass('is-horizontal');
      } else if (w < h) {
        image.addClass('is-vertical');
      } else {
        image.addClass('is-square');
      }
    }
  });

  $('.type-post a[rel*="attachment"], .type-page a[rel*="attachment"]').magnificPopup({type:'image'});

  $('.wpcf7-select').niceSelect();

  $('.plans-begin__button').hover(
    function(){
      var price = $(this).data('price');
      var currency = $(this).data('currency');

      $('#plansBeginPrice .default').hide();
      $('#plansBeginPrice .price').show().append('<span>'+price+'</span><span>'+currency+'</span>')
    },
    function(){
      $('#plansBeginPrice .price').hide().empty();
      $('#plansBeginPrice .default').show();
    }
  )

  if(isMobile.any){
    $('.triangle-down-right').css('border-right-width', '100%');
    $('.triangle-up-left').css('border-left-width', '100%');
  }

  if(isset('.round-list') && (x > 767)){
    $.each($('.round-list'), function(){
      var $this = $(this),
          list = $this.find('ul'),
          radius = list.data('radius'),
          items = list.find('li'),
          width = $this.width(),
          height = $this.height(),
          angle = 0,
          step = (2*Math.PI) / items.length;

      list.find('li:first').addClass('active');

      items.each(function() {
        var x = Math.round(width/2 + radius * Math.cos(angle) - $(this).width()/2),
            y = Math.round(height/2 + radius * Math.sin(angle) - $(this).height()/2);
        $(this).css({
          left: x + 'px',
          top: y + 'px'
        });
        angle += step;
      });
    });
  }

  $(document).on('click', '.round-list ul li a', function(event){
    event.preventDefault();
    event.stopPropagation();

    var target    = $($(this).attr('href')),
        list      = $(this).closest('ul'),
        listItem  = $(this).parent('li'),
        container = $($(this).closest('.round-list').data('view'));

    list.find('li').removeClass('active');
    listItem.addClass('active');

    container.find('.round-view__item').addClass('hidden');
    target.removeClass('hidden');
  });

  if (isset('[data-clone-xx]') && (x < 480)) {
    $.each($('[data-clone-xx]'), function(){
      var $this = $(this);
      var $target = $($this.data('clone-xx'));
      var content = $target[0].innerHTML.trim();

      $target.empty();
      $this.html(content);
    });
  }

  if (isset('ul.table')) {
    $.each($('ul.table'), function(){
      var $list = $(this);
      var $header1 = $list.find('li.header + .header').html();
      var $header2 = $list.find('li.header + .header + .header').html();
      var $header3 = $list.find('li.header + .header + .header + .header').html();

      $list.find('.title + .cell').addClass('cell-1');
      $list.find('.title + .cell + .cell').addClass('cell-2');
      $list.find('.title + .cell + .cell + .cell').addClass('cell-3');

      $list.find('.cell-1').before('<li class="header bg-green visible-xs visible-xx">'+$header1+'</li>');
      $list.find('.cell-2').before('<li class="header bg-blue visible-xs visible-xx">'+$header2+'</li>');
      $list.find('.cell-3').before('<li class="header bg-orange visible-xs visible-xx">'+$header3+'</li>');
    });
  }



  if ($('#select-payment-client').length > 0 && $('#button-payment-client').length > 0){

    var period = $('#select-payment-client');
    var button = $('#button-payment-client');
    var form = button.closest('form');

    $(period).change(function(){

      //Если изменили количество месяцев для оплаты, цена умножается на количество месяцев.
      var total = Number(button.attr('month-price'))*period.val();

      button.attr('data-price',total);
      button.attr('data-month',period.val());
      button.text('Оплатить '+total.toString()+' рублей');
      form.find('#yandex_payment_sum_for_client').attr('value',total);
      form.find('#yandex_order_details_for_client').attr('value','Оплата тарифа ' + button.data('tarif-name') +' на '+button.attr('data-month') + ' месяц(а/ев)');
      form.find('#month_count').attr('value',button.attr('data-month'));
    });
  }

  $(document).on('ready', function(){
    $('.placeholder-for-hentry').addClass('hentry');
    $('.placeholder-for-hentry').removeClass('placeholder-for-hentry');
  });
}(jQuery);

$(document).load(function(){
  gridCheck();
});

window.onresize = function(event) {
  gridCheck();
};

function loadmore(el, startLoad){

    $(window).scroll(function(){
      var offset = el.offset();
      var bottomOffset = 1000;
      var data = {
        'action': 'loadmore',
        'query': true_posts,
        'page' : current_page
      };
      var masonry_container = el.parent('.loadmore-container').find('.masonry-container');

      if(parseInt(current_page) < parseInt(max_pages) && come(el) && !$('body').hasClass('loading')){
        console.log('Гружу посты...');
        var endScroll = $(document).scrollTop();
        $.ajax({
          url:ajaxurl,
          data:data,
          type:'POST',
          beforeSend: function( xhr){
            $('body').addClass('loading');
            el.html('<figure class="fixed-loading"><span class="vertical-container"><span class="vertical-content">Загрузка...</span></span></figure>');
          },
          success:function(json){
            if(json.success) {
              masonry_container.append(json.data);
              el.empty();

              var options = masonry_container.data('masonry-options');
              masonry_container.masonry('destroy').masonry(options);

              $('body').removeClass('loading');
              $(document).scrollTop(endScroll);
              if (x > 1200){
                $('#sidebar .affix-bottom').removeClass('affix-bottom').addClass('affix').removeAttr('style');
              }
              current_page++;
            } else {
              $('body').removeClass('loading');
              $(el).remove();
            }
          }
        });
      }
    });
};

function come(elem) {
  var docViewTop = $(window).scrollTop(),
    docViewBottom = docViewTop + $(window).height(),
    elemTop = $(elem).offset().top,
    elemBottom = elemTop + $(elem).height();

  return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}

function gridCheck(){
  if($('div').is('.masonry-container')){
    $.each($('.masonry-container'), function(){
      var options = $(this).data('masonry-options');
      if(x < 768){
        $(this).masonry('destroy');
      } else {
        $(this).masonry(options);
      }
    });
  }
}

/**
 * Проверяет наличие элемента на странице
 * @param {string} el селектор искомого элемента
 */
function isset(el){
  var obj = $(document).has(el);

  return (obj.length == 0) ? false : true;
}

function hide_wpcf7(e){
  var response = JSON.parse(e.target.response);
  if(response.mailSent){
    $(response.into).find('form .row').fadeOut();
  }
  console.log();
}
