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

  if ($('select[aria-transform]').length > 0){
    $.each($('select[aria-transform]'), function(){
      const $this = $(this);
      $this.niceSelect();
    });
  }

  if ($('input[type="checkbox"][aria-transform]').length > 0){
    $.each($('input[type="checkbox"][aria-transform]'), function(){
      let init = new Switchery(this, {
        size: 'small'
      });
    });
  }

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
        if (json.success) {
          $form.fadeOut();
          $form.closest('.modal-body').find('[data-ttcli="from-response"]').empty().html(json.message);
        } else{
          $form.closest('.modal-body').find('[data-ttcli="from-response"]').empty().html(json.message);
        }

        if (json.errors) {
          $.each(json.errors, function(index, value) {
            $('[data-control="'+index+'"]').empty().text(value);
          });
        }

        $.button.reset($btn);
      }
    });
  });
}(jQuery);