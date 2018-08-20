+function()
{
  'use_strict';

  $.button = {
    /**
     * Кнопка в ожидании ответа сервера
     * @param  {[type]} $btn [description]
     * @return {[type]}      [description]
     */
    loading: function ($btn) {
      let $icon = $btn.find('.icon i'),
        $text = $btn.find('.text'),
        iconClass = $icon.attr('class'),
        loadingText = $btn.data('loading-text'),
        originalText = $text.text();

      $btn.prop('disabled', true);

      if (typeof (loadingText) != 'undefined') {
        $text.attr('data-original-text', originalText).text(loadingText);
      }

      $icon.attr('data-class', iconClass).removeAttr('class').addClass('ion ion-spin ion-loop');
    },

    reset: function ($btn) {
      let $icon = $btn.find('.icon i'),
        $text = $btn.find('.text'),
        iconClass = $icon.data('class'),
        originalText = $text.data('original-text');

      $btn.prop('disabled', false);
      $icon.attr('class', iconClass);
      $text.text(originalText);
    }
  };

  $.form = {
    /**
     * Сбрасывает поля формы
     * @param $form
     */
    reset: function ($form) {
      setTimeout(function () {
        $form.find('[data-ttcli="form-loader"]').removeClass('hidden');
        $form.fadeIn(300);
        $form[0].reset();
      }, 900);

      setTimeout(function () {
        $form.find('select').trigger('change').niceSelect('update');
        $form.find('[data-ttcli="form-loader"]').addClass('hidden');
        $form.closest('.modal-body').find('[data-ttcli="from-response"]').fadeOut(300);
      }, 1800);
    },

    initCheckbox($form) {
      const $selector = ($form) ? $form.find('input[type="checkbox"][aria-transform]') : $('input[type="checkbox"][aria-transform]');
      if ($selector.length > 0){
        $.each($selector, function(){
          let checkbox = new Switchery(this, {
            size: 'small'
          });
        });
      }
    },

    initSelect($form) {
      const $selector = ($form) ? $form.find('select[aria-transform]') : $('select[aria-transform]');
      $.each($selector, function(){
        const $this = $(this);
        $this.niceSelect();
      });
    }
  };

  $.form.initSelect();
  $.form.initCheckbox();

  $(document).on('change', '[data-ttcli="form-prices-select"]', function(){
    const $this = $(this);
    let currentVal = $this.val();
    let $dataContainer = $('[data-ttcli="form-prices"]');

    $dataContainer.find('p').addClass('hidden');
    $dataContainer.find('#'+currentVal).removeClass('hidden');
  });

  $(document).on('submit', '[data-ttcli="form"]', function(e){
    e.preventDefault();
    var $form = $(this);
    let $btn = $form.find('button[type="submit"]');

    $.button.loading($btn);

    $.ajax({
      url: feedback_ajax.url,
      type: "POST",
      data: {
        action: $form.attr('action'),
        data: $form.serialize()
      },
      success: function(json){
        $form.find('[data-control]').empty();
        if (json.success) {
          $form.fadeOut();
          $form.closest('.modal-body').find('[data-ttcli="from-response"]').empty().html(json.message);
          $form.find('.switchery').click();
          $.form.reset($form);
        } else{
          $form.closest('.modal-body').find('[data-ttcli="from-response"]').empty().html(json.message);
        }

        if (json.errors) {
          $.each(json.errors, function(index, value) {
            $('[data-control="'+index+'"]').text(value);
          });
        }
        $.button.reset($btn);
      }
    });
  });
}(jQuery);