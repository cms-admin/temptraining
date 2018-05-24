+function($)
{
  // Анимированные счетчики
  var counterUp = [];

  $(window).on('load', function(){
    $.each($('[data-countup]'), function(index){
      var $this = $(this),
          id = $this.attr('id'),
          data = $this.data('countup-options'),
          options = {
            useEasing : true, 
            useGrouping : true, 
            separator : ' ', 
            decimal : '.', 
          };

      counterUp[index] = new CountUp(id, data.start, data.end, data.decimals, data.duration, options);

      counterUp[index].start();
    });
  });

  // iOS переключатели
  var ios_switchers = Array.prototype.slice.call(document.querySelectorAll('input[data-js="switch"]'));

  if (ios_switchers != 'undefined'){
    ios_switchers.forEach(function(html) {
      var options = $(html).data('switch-options');

      if (typeof(options) != 'undefined'){
        var switchery = new Switchery(html, options);
      } else {
        var switchery = new Switchery(html);
      }
    });
  }


  $(document).on('click', '#club_membership [data-toggle="close"]', function(){
    $('#club_membership').removeClass('open');
  });

  $(document).on('click', '[data-action="membership"]', function(){

    var button = $(this);

    var modal = $('#' + button.attr('rel'));

    modal.toggleClass('open');

  });
  
    
  var validator = new FormValidator('club_membership', [
    {
      name: 'member_name',
      display: 'Имя',
      rules: 'required'
    },
    {
      name: 'member_soname',
      display: 'Фамилия',
      rules: 'required'
    },
    {
      name: 'member_email',
      display: 'Электронная почта',
      rules: 'required|valid_email'
    },
    {
      name: 'member_rules',
      display: 'Согласие с правилами клуба',
      rules: 'required'
    }
  ], function(errors, event) {
    event.preventDefault();

    var form = $(event.target);
    var button = form.find('button[type="submit"]');

    form.find('.club-form__error').remove();
    form.find('.club-form__message').remove();
    form.find('input').removeClass('has-error');

    button.addClass('is-loading');

    if (errors.length > 0) {
      var inputs      = [],
          containers  = [],
          messages    = [];

      errors.forEach(function(item, i, arr) {
        inputs[i] = $('#' + item.id);
        containers[i] = inputs[i].closest('div');
        messages[i] = '<span class="club-form__error">'+item.message+'</span>';

        inputs[i].addClass('has-error');
        containers[i].append(messages[i]);
      });

      button.removeClass('is-loading');

    } else {

      var formData = form.serializeArray();

      $.ajax({
        url: ttajax.url,
        type: "POST",
        data: {
          action: form.attr('action'),
          data: formData
        },
        success: function(data){
          form.find('.club-form__body').prepend('<div class="club-form__message '+data.status+'">'+data.message+'</div>');
          button.removeClass('is-loading');

          if (data.status == 'success'){
            setTimeout(function(){
              form.toggleClass('open');
              document.getElementById(form.attr('id')).reset();
              form.find('.club-form__error').remove();
              form.find('.club-form__message').remove();
            }, 2000);
          }
        }
      });
    }
  });
    
  validator.setMessage('required', 'Поле %s обязательно для заполнения.');
  validator.setMessage('valid_email', 'Поле %s должно содержать корректный email.');
  
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