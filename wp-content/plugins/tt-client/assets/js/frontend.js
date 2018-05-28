+ function ($) {
  if (typeof (isset) === "undefined") {
    function isset(el) {
      var obj = $(document).has(el);

      return (obj.length == 0) ? false : true;
    }
  }

  /* Выплата тренеру */
  $(document).on('click', '[data-reward]', function () {
    var btn = $(this);
    var btnContent = btn.text();
    var coach_id = btn.data('reward');

    btn.text('Подождите...');

    $.ajax({
      url: ttajax.url, //url, к которому обращаемся
      type: "POST",
      data: {
        action: 'ttclient_do_reward',
        coach_id: coach_id
      },
      success: function (data) {
        if (data.error) {
          alert(data.error);
        }
        if (data.message) {
          var reloadConfirm = confirm(data.message);
          if (reloadConfirm) {
            location.reload();
          }
        }
      }
    });

  });

  /* Выплата налога */
  $(document).on('click', '[data-tax]', function () {
    var btn = $(this);
    var btnContent = btn.text();
    var coach_id = btn.data('tax');

    btn.text('Подождите...');

    $.ajax({
      url: ttajax.url, //url, к которому обращаемся
      type: "POST",
      data: {
        action: 'ttclient_do_tax',
        coach_id: coach_id
      },
      success: function (data) {
        if (data.error) {
          alert(data.error);
        }
        if (data.message) {
          var reloadConfirm = confirm(data.message);
          if (reloadConfirm) {
            location.reload();
          }
        }
      }
    });

  });

  if ($('form#tt-client-yakassa').length > 0) {
    var $form = $('form#tt-client-yakassa');
    var $monthControl = $form.find('[data-month-count]');
    var $sumControl = $form.find('[data-tariff-sum]');
    var $btnSubmit = $form.find('button[type="submit"]');

    // Пересчет суммы при изменении кол-ва месяцев
    $monthControl.on('change', function () {
      var period = $monthControl.val();
      var price = $btnSubmit.attr('month-price');
      var total = parseInt(period) * parseInt(price);

      $sumControl.val(total);
      $btnSubmit.text('Оплатить ' + total + ' р.');
    });

    // Оплата 
    $form.on('submit', function (event) {
      event.preventDefault ? event.preventDefault() : (event.returnValue = false);

      var button_text = $btnSubmit.html(),
        action = $form.attr('action'),
        formData = $form.serializeArray();

      $btnSubmit.text('Подождите...');

      $.ajax({
        url: ttajax.url,
        type: "POST",
        data: {
          action: action,
          data: formData
        },
        success: function (data) {
          console.log(data);
          if (data.error) {
            swal({
              title: 'Ошибка',
              text: data.error,
              type: 'error'
            });
          }

          $btnSubmit.text(button_text);

          if (data.success) {
            window.location = data.redirect;
          }
        },
        dataType: "JSON"
      });
    });
  }

  if ($('#tt-client-openbank').length > 0) {
    $(document).on('change', '#select-payment-client', function () {
      var $this = $(this);
      var $form = $this.closest('form');

      var period = $this.val();
      var price = $form.find('button[type="submit"]').attr('month-price');

      var jsonParamsMonthCount = parseInt(period);

      var total = parseInt(period) * parseInt(price);

      $form.find('input[name="amount"]').val(parseInt(total) * 100);
      $form.find('input[name="jsonParams:month_count"]').val(jsonParamsMonthCount);

      $form.find('button[type="submit"]').text('Оплатить ' + total + ' рублей');
    });

    /* Оплата заказа */
    $(document).on('submit', '#tt-client-openbank', function (e) {
      e.preventDefault ? e.preventDefault() : (e.returnValue = false);

      var form = $(this),
        button = form.find('button[type="submit"]'),
        button_text = button.html(),
        action = form.attr('action'),
        formData = form.serializeArray();

      button.text('Подождите...');

      $.ajax({
        url: ttajax.url,
        type: "POST",
        data: {
          action: action,
          data: formData
        },
        success: function (data) {
          //console.log(data);
          if (data.success) {
            window.location = data.redirect;
          } else {
            swal({
              title: 'Ошибка',
              text: data.error,
              type: 'error'
            });
          }
          button.text(button_text);
        }
      });

    });

    // Отключение автоплатежа
    $(document).on('click', '#openbank_cancel_binding', function (e) {
      e.preventDefault ? e.preventDefault() : (e.returnValue = false);

      var button = $(this),
        button_text = button.html(),
        action = 'ttcli_openbank_cancel_recurring',
        client = button.data('client-id');

      button.text('Подождите...');

      $.ajax({
        url: ttajax.url,
        type: "POST",
        data: {
          action: action,
          client_id: client
        },
        success: function (data) {
          button.text(button_text);

          resultAlert(data);
        }
      });
    });

  }

  /**
   * Переключение платежного периода в форме оплаты премиум подписки
   */
  $(document).on('change', '#membershipPremiumFormSelect', function () {
    var $select = $(this),
      $form = $select.closest('form'),
      $button = $form.find('button[type="submit"]'),
      $inputAmount = $form.find('input[name="amount"]'),
      $inputDescription = $form.find('input[name="description"]'),
      $inputPeriod = $form.find('input[name="jsonParams:payment_months"]');

    var descriptionValue = $select.data('description'),
      priceValue = parseInt($button.data('price')),
      periodValue = parseInt($select.val()),
      periodText = $select.find('option:selected').text();


    $button.text('Оплатить ' + priceValue * periodValue + ' рублей');
    $inputPeriod.val(periodValue);
    $inputDescription.val(descriptionValue + ' ' + periodText);
    $inputAmount.val(priceValue * periodValue * 100);
  });

  $(document).on('submit', '#membershipPremiumForm', function (e) {
    e.preventDefault ? e.preventDefault() : (e.returnValue = false);

    var $form = $(this),
      $button = $form.find('button[type="submit"]'),
      button_text = $button.html(),
      action = $form.attr('action'),
      formData = $form.serializeArray();

    $button.text('Подождите...');

    $.ajax({
      url: ttajax.url,
      type: "POST",
      data: {
        action: action,
        data: formData
      },
      success: function (data) {
        //console.log(data);
        if (data.success) {
          window.location = data.redirect;
        } else {
          swal({
            title: 'Ошибка',
            text: data.error,
            type: 'error'
          });
        }
        button.text(button_text);
      }
    });
  });

  if ($('.jstree').length > 0) {
    console.log('Init JSTree');
    $('.jstree').jstree({
      "core": {
        "check_callback": true
      }
    }).on('changed.jstree', function (e, data) {
      var tree = $(this).jstree(true);

      if (data.action == "select_node") {

      }
    });
  }

  $(document).on('click', '.jstree-toggle', function () {
    var button = $(this);
    var tree = button.closest('.jstree').jstree(true);
    var node = tree.get_selected();

    tree.toggle_node(node);
  });

  $(document).on('click', '.jstree-showall', function (e) {
    e.preventDefault ? e.preventDefault() : (e.returnValue = false);

    var button = $(this);
    var tree = $(button.attr('rel')).jstree(true);
    tree.select_all();

    var nodes = tree.get_top_selected();

    nodes.forEach(function (item, i, nodes) {
      var node = tree.get_node(item);
      tree.open_node(node);
    });

  });

  $(document).on('click', '.jstree-hideall', function (e) {
    e.preventDefault ? e.preventDefault() : (e.returnValue = false);
    var button = $(this);
    var tree = $(button.attr('rel')).jstree(true);

    tree.select_all();

    var nodes = tree.get_top_selected();

    nodes.forEach(function (item, i, nodes) {
      var node = tree.get_node(item);
      tree.close_node(node);
    });
  });

  $(document).on('click', '.slide-toggle', function (e) {
    e.preventDefault ? e.preventDefault() : (e.returnValue = false);

    var button = $(this);
    var target = $(button.attr("rel"));

    button.toggleClass("active");
    target.slideToggle();
  });

  /* Сортируемый список */
  if ($('[data-list]').length > 0) {
    if (typeof (listjs) == 'undefined') {
      var listjs = [];
    }

    $.each($('[data-list]'), function (index) {
      var $this = $(this);
      var options = $this.data('list-options');
      var id = $this.attr('id');
      var pagination = $this.data('list-pages');

      if (!options.plugins) options.plugins = [];

      if (typeof (pagination) != 'undefined') {
        options.page = pagination;
        var paginations = ListPagination({
          paginationClass: 'listjs-pagination'
        });
        options.plugins.push(paginations);
      }

      if (typeof (options) != 'undefined') {
        listjs[index] = new List(id, options);
        $this.attr('data-list-id', index);
      }
      //console.log(listjs[index]);
    });
  }

  if ($('#coach_noty_toggle').length > 0) {
    var elems = Array.prototype.slice.call(document.querySelectorAll('#coach_noty_toggle input'));

    elems.forEach(function (html) {
      var switchery = new Switchery(html, {
        secondaryColor: '#d6d6d6',
        size: 'small'
      });
    });

    $(document).on('click', '#coach_noty_toggle .switchery', function () {
      var input = $(this).prev('input');
      var state = (input.is(":checked")) ? 0 : 1;
      var coach = input.val();

      $.ajax({
        url: ttajax.url, //url, к которому обращаемся
        type: "POST",
        data: {
          action: 'ttclient_toggle_coach_noty',
          coach_id: coach,
          state: state
        },
        success: function (data) {
          sweetAlert(data.title, data.message, data.type);
        }
      });
    });

  }

  if ($('#loginform .login-remember input[name="rememberme"]').length > 0) {
    var elems = Array.prototype.slice.call(document.querySelectorAll('#loginform .login-remember input[name="rememberme"]'));

    elems.forEach(function (html) {
      var switchery = new Switchery(html, {
        secondaryColor: '#d6d6d6',
        size: 'small'
      });
    });
  }

  if ($('#member_training_peaks').length > 0) {
    var membership_premium_switcher = document.querySelector('#member_training_peaks input[name="membership_premium"]');

    var switchery = new Switchery(membership_premium_switcher, {
      secondaryColor: '#d6d6d6',
      size: 'small'
    });

    $(document).on('click', '#member_training_peaks .switchery', function () {
      var input = $(this).prev('input');
      var input_sweet = $(input.next('.switchery'));
      var input_label = $(input_sweet.next('span'));
      var state = (input.is(":checked")) ? 0 : 1;
      var isEnabled = $(switchery.element)[0].checked;
      var options = input.data('form');

      if (isEnabled) {
        // получает выбранный период подписки
        var monthCount = $("#membershipPremiumFormSelect option:selected").text();
        var monthCosts = $("#membershipPremiumFormButton").text();

        swal({
            title: "Оплата подписки TrainingPeaks Premium?",
            text: monthCosts + " за " + monthCount,
            type: "warning",
            showCancelButton: true,
            cancelButtonText: "Нет",
            confirmButtonColor: "#F2784B",
            confirmButtonText: "Да",
            closeOnConfirm: false
          },
          function (isConfirm) {
            $("#membershipPremiumFormButton").trigger("click");
          });

      } else {
        if (options == 'undefined') {
          input_label.text('Ошибка. Не заданы параметры');
          return;
        }

        var input_label_original = input_label.text();

        if (state == 1) {
          input_label.text('Подождите...');

          $.ajax({
            url: ttajax.url, //url, к которому обращаемся
            type: "POST",
            dataType: "json",
            data: options,
            success: function (data) {
              console.log(options);
              if (data.success) {
                window.location = data.redirect;
              } else {
                swal({
                  title: 'Ошибка',
                  text: data.error,
                  type: 'error'
                });
              }
              input_label.text(input_label_original);
            }
          });
        } else {
          swal({
              title: "Вы уверены?",
              text: "Отмена подписки на " + input.data('tariff'),
              type: "warning",
              showCancelButton: true,
              cancelButtonText: "Я передумал",
              confirmButtonColor: "#F2784B",
              confirmButtonText: "Да, отменить!",
              closeOnConfirm: false
            },
            function (isConfirm) {
              if (isConfirm) {
                $.ajax({
                  url: ttajax.url, //url, к которому обращаемся
                  type: "POST",
                  dataType: "json",
                  data: {
                    action: 'ttcli_member_cancel_premium',
                    member_id: input.val()
                  },
                  success: function (data) {
                    resultAlert(data);
                  }
                });
              } else {
                location.reload();
              }
            });
        }
      }
    });
  }

}(jQuery);

function resultAlert(data, callback) {
  if (!callback) {
    callback = function () {
      location.reload();
    }
  }

  swal({
      title: data.title,
      text: data.message,
      type: data.type,
      html: (data.html) ? data.html : false
    },
    callback
  );
}