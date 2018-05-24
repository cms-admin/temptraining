+function($){

  function isset(el){
    var obj = $(document).has(el);
    return (obj.length == 0) ? false : true;
  }

  $(window).on('load', function(){

    // Табы в заголовках форм
    if (isset('.ttcli-form__head-tabs')){
      $.each($('.ttcli-form__head-tabs'), function() {
        var tabs = $(this);
        tabs.find('li:first').addClass('active');
      });
    }

    $(document).on('click', '.ttcli-form__head-tabs li', function(e){
      e.preventDefault();
      var tab = $(this),
          link = tab.find('a'),
          target = $(link.attr('href')),
          container = target.parent('.ttcli-tabs__container');

      container.find('.ttcli-tabs__content').removeClass('is-active');
      tab.parent('ul').find('li').removeClass('active');
      target.addClass('is-active');
      tab.addClass('active');
    });

    // Проверка форм
    if(isset('.ttcli-form')){
      $.validate({
        lang : 'ru',
        modules : 'date, security',
        borderColorOnError: '#e44d32',
        dateFormat: 'dd-mm-yyyy'
      });
    }

    // поля ввода даты и времени
    if(isset('[data-input-datetimepicker]')){

      $.datetimepicker.setLocale('ru');

      $.each($('[data-input-datetimepicker]'), function(){
        var $this = $(this);
        var options = $this.data('input-datetimepicker-options');
        var avalible_time = $this.data('input-datetimepicker-time');

        if (typeof avalible_time != 'undefined'){
          options['allowTimes'] = avalible_time.split(',');
        }

        $this.datetimepicker(options);
      });
    }

    // Отображение / скрытие элементов
    $(document).on('click', '[data-toggle="slide"]', function(){
      var target = $(this).data('rel');

      if(typeof(target) != 'undefined'){
        $(target).slideToggle();
      }
    });

    // iOS переключатели
    if(isset('input[data-js="switch"]')){
      var ios_elems = Array.prototype.slice.call(document.querySelectorAll('input[data-js="switch"]'));

      ios_elems.forEach(function(html) {
        var options = $(html).data('switch-options');

        if (typeof(options) != 'undefined'){
          var switchery = new Switchery(html, options);
        } else {
          var switchery = new Switchery(html);
        }
      });
    }

    // Красивые select'ы
    if (isset('.nice-select')){
      $('.nice-select').niceSelect();
    }

    // СКРОЛЛ БАР
    if(isset('[data-scrollbar]')){
      $.each($('[data-scrollbar]'), function(){
        var $this = $(this);
        var options = $this.data('scrollbar-options');

        if(typeof(options) != 'undefined'){
          $this.scrollbar(options);
        } else {
          $this.scrollbar();
        }
      });
    }

    /* Сортируемый список */
    if(isset('[data-list]')){
      if (typeof(listjs) == 'undefined'){
        var listjs = [];
      }

      $.each($('[data-list]'), function(index){
        var $this = $(this);
        var options = $this.data('list-options');
        var id = $this.attr('id');
        var pagination = $this.data('list-pages');

        if(!options.plugins) options.plugins = [];

        if (typeof(pagination) != 'undefined') {
          options.page = pagination;
          var paginations = ListPagination({
            paginationClass: 'listjs-pagination'
          });
          options.plugins.push(paginations);
        }

        if(typeof(options) != 'undefined'){
          listjs[index] = new List(id, options);
          $this.attr('data-list-id', index);
        }
        //console.log(listjs[index]);
      });
    }

  });


  // AJAX сохранение форм
  $(document).on('click', '[data-save-form]', function(e){

    var button = $(this),
        button_text = button.html(),
        form = $('#' + button.data('save-form')),
        action = form.attr('action'),
        errors = [],
        conf = {
          onElementValidate : function(valid, $el, $form, errorMess) {
            if( !valid ) {
              // gather up the failed validations
              errors.push({el: $el, error: errorMess});
            }
          }
        },
        lang = 'ru';

    $.formUtils.loadModules('security, date');

    if(!form.isValid(lang, conf, false) ) {
      form.submit();
    } else if (typeof(form) != 'undefined') {
      e.preventDefault();

      if (form.find('.wp-editor-area[aria-hidden="true"]').length > 0){
        $.each(form.find('.wp-editor-area'), function(){
          var textarea = $(this),
              editor = tinyMCE.get(textarea.attr('id'))
              content = editor.getContent();

          textarea.val(content);
        });
      }

      var formData = form.serializeArray();

      button.html('<i class="ti-time"></i><span>Подождите...</span>');

      $.ajax({
        url: ttcli_ajax.url,
        type: "POST",
        data: {
          action: action,
          data: formData
        },
        success: function(data){
          if (data.redirect){
            resultAlert(data, function(){
              location.href(data.redirect);
            })
          } else {
            resultAlert(data);
          }

          button.html(button_text);
        }
      });
    }

  });

  // AJAX редактирование статуса
  $(document).on('click', '[data-js="edit-status"]', function(e){
      e.preventDefault();
      var button = $(this),
          id = button.data('id'),
          container = $(button.data('form-container')),
          button_text = button.html();

      button.html('<i class="ti-time"></i>');

      $.ajax({
          url: ercrm_ajax.url,
          type: "POST",
          data: {
            action: 'ercrm_get_status_by_id',
            id: id
          },
          success: function(data){
            container.empty().html(data);
            container.find('.nice-select').niceSelect();
            container.find('[data-scrollbar]').scrollbar();
            button.html(button_text);
          }
        });
  });

  /**
   * Загрузка изображений
   */
  $(document).on('click', '[data-toggle="upload"]', function(e){
    e.preventDefault ? e.preventDefault() : (e.returnValue = false);

    var $button = $(this),
        $container = $('#' + $button.attr('rel')),
        $image = $container.find('img'),
        $input = $container.find('input[type="hidden"]');

    var media_uploader = wp.media({
      frame:    "post", 
      state:    "insert", 
      multiple: false
    });

    media_uploader.on("insert", function(){
      var json = media_uploader.state().get("selection").first().toJSON();
      
      var image_id = json.id;
      var image_url = json.url;
      var image_thumb = json.sizes.thumbnail.url;
      var image_prev = json.sizes.medium.url;
      var image_caption = json.caption;
      var image_title = json.title;

      $input.val(image_id);
      $image.attr('src', image_prev);
      $button.text('Редактировать');
    });

    media_uploader.open();
  });

}(jQuery);

function resultAlert(data, callback = false) {
  if (!callback){
    callback = function(){
      location.reload();
    }
  }

  swal(
    {
      title: data.title,
      text: data.message,
      type: data.type,
      html: (data.html) ? data.html : false
    },
    callback
  );
}
