+function($){
  if(isset('.jstree')){
    $('.jstree').jstree({"core" : {"check_callback" : true}}).on('changed.jstree', function (e, data) {
      var tree = $(this).jstree(true);
      if (data.action == "select_node") {

      }
    });
  }

  if (isset('#tt-client-openbank')){
    $(document).on('change', '#select-payment-client', function(){
      var $this = $(this);
      var $form = $this.closest('form');

      var period = $this.val();
      var price = $form.find('button[type="submit"]').attr('month-price');
      var jsonParams = JSON.parse($form.find('input[name="jsonParams"]').val());

      jsonParams.month_count = parseInt(period);

      var total = parseInt(period) * parseInt(price);

      $form.find('input[name="amount"]').val(parseInt(total) * 100);
      $form.find('input[name="jsonParams"]').val(JSON.stringify(jsonParams));

      $form.find('button[type="submit"]').text('Оплатить ' + total + ' рублей');
    });
  }

  $(document).on('click', '.jstree-showall', function(event){
    event.preventDefault;
    var button = $(this);
    var tree = $(button.attr('rel')).jstree(true);
    tree.open_all();
  });

  $(document).on('click', '.jstree-hideall', function(event){
    event.preventDefault;
    var button = $(this);
    var tree = $(button.attr('rel')).jstree(true);
    tree.close_all();
  });
}(jQuery);
