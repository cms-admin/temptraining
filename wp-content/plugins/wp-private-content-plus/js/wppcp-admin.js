jQuery(document).ready(function($) {
    if($("#wppcp_private_page_user").length){
        $("#wppcp_private_page_user").wppcp_select2({
          ajax: {
            url: WPPCPAdmin.AdminAjax,
            dataType: 'json',
            delay: 250,
            method: "POST",
            data: function (params) {
              return {
                q: params.term, // search term
                page: params.page,
                action: 'wppcp_load_private_page_users',
              };
            },
            processResults: function (data, page) {
              return {
                results: data.items
              };
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, 
          minimumInputLength: 1,
          templateResult: wppcp_formatRepo, 
          templateSelection: wppcp_formatRepoSelection 
        });
    }
    
    $("#wppcp_private_page_user_load_form").submit(function(e){
        
        $("#wppcp-message").removeClass('wppcp-message-info-error').removeClass('wppcp-message-info-success').hide();
        
        if($("#wppcp_private_page_user").val() == '0'){
            e.preventDefault();
            $("#wppcp-message").addClass('wppcp-message-info-error');
            $("#wppcp-message").html(WPPCPAdmin.Messages.userEmpty).show();
        }
    });
    
    if($("#wppcp-role-hierarchy-list").length > 0){
        $( "#wppcp-role-hierarchy-list" ).sortable({
            update: function(e,ui){
                
                var user_role_hierarchy = new Array();
                $( "#wppcp-role-hierarchy-list li" ).each(function(){
                    var role = $(this).attr('data-role');
                    user_role_hierarchy.push(role);
                });


                $.post(
                    WPPCPAdmin.AdminAjax,
                    {
                        'action': 'wppcp_save_user_role_hierarchy',
                        'user_role_hierarchy':   user_role_hierarchy,
                    },
                    function(response){

                    }
                );

                
            },
        });
    }

    $("#wppcp_post_page_visibility").change(function(e){        
        if($(this).val() == 'role'){
            $("#wppcp_post_page_role_panel").show();
        }else{
            $("#wppcp_post_page_role_panel").hide();
        }

        if($(this).val() == 'users'){
            $("#wppcp_post_page_users_panel").show();
        }else{
            $("#wppcp_post_page_users_panel").hide();
        }
    });

    $("#wppcp_global_post_restriction_visibility").change(function(e){        
        if($(this).val() == 'role'){
            $("#all_post_user_roles_panel").show();
        }else{
            $("#all_post_user_roles_panel").hide();
        }
    });

    $("#wppcp_global_page_restriction_visibility").change(function(e){        
        if($(this).val() == 'role'){
            $("#all_page_user_roles_panel").show();
        }else{
            $("#all_page_user_roles_panel").hide();
        }
    });
    
    $("#wppcp_upme_member_list_visibility").change(function(e){        
        if($(this).val() == 'role'){
            $("#upme_member_list_user_roles_panel").show();
        }else{
            $("#upme_member_list_user_roles_panel").hide();
        }
    });

    $("#wppcp_upme_search_visibility").change(function(e){        
        if($(this).val() == 'role'){
            $("#upme_search_user_roles_panel").show();
        }else{
            $("#upme_search_user_roles_panel").hide();
        }
    });


    if($("#wppcp_blocked_post_search").length){
        $("#wppcp_blocked_post_search").wppcp_select2({
          ajax: {
            url: WPPCPAdmin.AdminAjax,
            dataType: 'json',
            delay: 250,
            method: "POST",
            data: function (params) {
              return {
                q: params.term, // search term
                action: 'wppcp_load_published_posts',
              };
            },
            processResults: function (data, page) {
              return {
                results: data.items
              };
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 1,
          templateResult: wppcp_formatRepo, // omitted for brevity, see the source of this page
          templateSelection: wppcp_formatRepoSelection // omitted for brevity, see the source of this page
        });
    }

    if($("#wppcp_blocked_page_search").length){
        $("#wppcp_blocked_page_search").wppcp_select2({
          ajax: {
            url: WPPCPAdmin.AdminAjax,
            dataType: 'json',
            delay: 250,
            method: "POST",
            data: function (params) {
              return {
                q: params.term, // search term
                action: 'wppcp_load_published_pages',
              };
            },
            processResults: function (data, page) {
              return {
                results: data.items
              };
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 1,
          templateResult: wppcp_formatRepo, // omitted for brevity, see the source of this page
          templateSelection: wppcp_formatRepoSelection // omitted for brevity, see the source of this page
        });
    }

    if($("#wppcp_everyone_search_types").length){
        $("#wppcp_everyone_search_types").wppcp_select2();
    }
    if($("#wppcp_guests_search_types").length){$("#wppcp_guests_search_types").wppcp_select2();}
    if($("#wppcp_members_search_types").length){$("#wppcp_members_search_types").wppcp_select2();}
    if($(".wppcp-select2-role-search-types").length){$(".wppcp-select2-role-search-types").wppcp_select2();}
    if($(".wppcp-select2-post-type-setting").length){$(".wppcp-select2-post-type-setting").wppcp_select2();}


    if($(".wppcp-select2-post-type-setting").length){
        $(".wppcp-select2-post-type-setting").each(function(){
            var post_type = $(this).attr('data-post-type');
            $(this).wppcp_select2({
              ajax: {
                url: WPPCPAdmin.AdminAjax,
                dataType: 'json',
                delay: 250,
                method: "POST",
                data: function (params) {
                  return {
                    q: params.term, // search term
                    post_type : post_type,
                    action: 'wppcp_load_published_cpt',
                  };
                },
                processResults: function (data, page) {
                  return {
                    results: data.items
                  };
                },
                cache: true
              },
              escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
              minimumInputLength: 1,
              templateResult: wppcp_formatRepo, // omitted for brevity, see the source of this page
              templateSelection: wppcp_formatRepoSelection // omitted for brevity, see the source of this page
            });
        });
        
    }

    if($("#wppcp_post_page_users").length){
        $("#wppcp_post_page_users").wppcp_select2({
          ajax: {
            url: WPPCPAdmin.AdminAjax,
            dataType: 'json',
            delay: 250,
            method: "POST",
            data: function (params) {
              return {
                q: params.term, // search term
                page: params.page,
                action: 'wppcp_load_restriction_users',
              };
            },
            processResults: function (data, page) {
              return {
                results: data.items
              };
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, 
          minimumInputLength: 1,
          templateResult: wppcp_formatRepo, 
          templateSelection: wppcp_formatRepoSelection 
        });
    }

    if($("#wppcp_post_page_visibility").length){
        $("#wppcp_post_page_visibility").wppcp_select2({});
    }

    $(".wppcp_widget_visibility").change(function(){

        if($(this).val() == '3'){
          $(this).parent().parent().find('.wppcp_widget_visibility_roles').show();
        }else{

          $(this).parent().parent().find('.wppcp_widget_visibility_roles').hide();
        }
    });

    if($("#wppcp-attachments-panel-upload").length){
      $('#wppcp-attachments-panel-upload').click(function() {
          wppcp_renderMediaUploader( $);
      });
    }

    $('#wppcp-attachments-panel').on('click','.wppcp-attachment-delete',function() {
        var attachment_id = $(this).parent().parent().find('.wppcp-attachment-preview').attr('data-attachment-id');
        $(this).parent().parent().parent().remove();

        
    });

    $('#wppcp-attachments-panel').on('click','.wppcp-attachment-edit',function() {
        var attachment_id = $(this).parent().parent().find('.wppcp-attachment-preview').attr('data-attachment-id');
        wppcp_renderMediaUploader
        ( $ , attachment_id);
    });

    if($(".wppcp_menu_user_restrictions").length){
          $(".wppcp_menu_user_restrictions").wppcp_select2({
            ajax: {
              url: WPPCPAdmin.AdminAjax,
              dataType: 'json',
              delay: 250,
              method: "POST",
              data: function (params) {
                return {
                  q: params.term, // search term
                  page: params.page,
                  action: 'wppcp_load_restriction_users',
                };
              },
              processResults: function (data, page) {
                return {
                  results: data.items
                };
              },
              cache: true
            },
            escapeMarkup: function (markup) { return markup; }, 
            minimumInputLength: 1,
            templateResult: wppcp_formatRepo, 
            templateSelection: wppcp_formatRepoSelection 
          });

    }

    $(document).on('change','.wppcp_menu_visibility',function(){

      if($(this).val() == '3'){
        $(this).closest('.menu-item-settings').find('.wppcp-menu-role-display-panel').show();
        $(this).closest('.menu-item-settings').find('.wppcp-menu-users-display-panel').hide();
      }else if($(this).val() == '4'){
        $(this).closest('.menu-item-settings').find('.wppcp-menu-users-display-panel').show();
        $(this).closest('.menu-item-settings').find('.wppcp-menu-role-display-panel').hide();

        if($(".wppcp_menu_user_restrictions").length){
            $(".wppcp_menu_user_restrictions").wppcp_select2({
              ajax: {
                url: WPPCPAdmin.AdminAjax,
                dataType: 'json',
                delay: 250,
                method: "POST",
                data: function (params) {
                  return {
                    q: params.term, // search term
                    page: params.page,
                    action: 'wppcp_load_restriction_users',
                  };
                },
                processResults: function (data, page) {
                  return {
                    results: data.items
                  };
                },
                cache: true
              },
              escapeMarkup: function (markup) { return markup; }, 
              minimumInputLength: 1,
              templateResult: wppcp_formatRepo, 
              templateSelection: wppcp_formatRepoSelection 
            });

      }
      }else{
        $(this).closest('.menu-item-settings').find('.wppcp-menu-role-display-panel').hide();
        $(this).closest('.menu-item-settings').find('.wppcp-menu-users-display-panel').hide();
      }

    });

    $('#update-nav-menu').submit(function(e){
      //e.preventDefault();
      $('.wppcp_menu_user_restrictions').each(function(){
        
        $(this).parent().find('.wppcp_menu_user_restrictions_hidden').val($(this).val());
        console.log($(this).parent().find('.wppcp_menu_user_restrictions_hidden').val());
      });

      // return false;
      // console.log($('#wppcp_menu_users316').val());return;
    });



    if($("#wppcp_backend_group_add_new_member").length){
        var group_id = $("#wppcp_backend_group_add_new_member").attr('data-group-id');
        
        $("#wppcp_backend_group_add_new_member").wppcp_select2({
          ajax: {
            url: WPPCPAdmin.AdminAjax,
            dataType: 'json',
            delay: 250,
            method: "POST",
            data: function (params) {
              return {
                q: params.term, // search term
                page: params.page,
                group_id : group_id,
                action: 'wppcp_load_group_setting_users',
              };
            },
            processResults: function (data, page) {
              return {
                results: data.items
              };
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, 
          minimumInputLength: 1,
          templateResult: wppcp_formatRepo, 
          templateSelection: wppcp_formatRepoSelection 
        });
    }

    $(".wppcp-admin-group-list-remove").click(function(){

        $(this).html(WPPCPAdmin.Messages.removeGroupUser);
        var group_row = $(this).parent().parent();
        // Nonce validation
        $.post(
            WPPCPAdmin.AdminAjax,
            {
                'action': 'wppcp_remove_group_setting_users',
                'user_id':   $(this).attr('data-user-id'),
                'group_id':   $(this).attr('data-group-id'),
            },
            function(response){

                if(response.status == 'success'){
                    console.log(group_row);
                    group_row.remove();
                }      
            },"json"
        );
    });


    $('.deactivate a').click(function(e){

        if(WPPCPAdmin.init_deactivation == 'no'){

          e.preventDefault();
          var slug = $(this).closest('tr').attr('data-slug');
          var url = $(this).attr('href');
          if(slug == 'wp-private-content-plus'){
              // add activation occurence using localize script and check for init deactivation
              var appendthis =  "<div class='wppcp-modal-overlay wppcp-js-modal-close'></div>";
              $("body").append(appendthis);
              $(".wppcp-modal-overlay").fadeTo(400, 0.8);
              window.scrollTo(0,0);
              $('#wppcp-deactivate-popup').attr('data-deactivate-url',url);
              $('#wppcp-deactivate-popup').fadeIn();

          }else{
            window.location.href = url;
          }
        }
    });

    $('#wppcp-deactivate-step1-submit').click(function(e){
      $('#wppcp-modal-body-step2').show();
      $('#wppcp-modal-body-step1').hide();
    });
   

   
    
    $(".wppcp-js-modal-close, .wppcp-modal-overlay").click(function() {
        $(".wppcp-modal-box, .wppcp-modal-overlay").fadeOut(500, function() {
            $(".wppcp-modal-overlay").remove();
        });
     
    });
     
    $(window).resize(function() {
        $(".wppcp-modal-box").css({
            top: - 50 + ($(window).height() - $(".wppcp-modal-box").outerHeight()) / 2,
            left: ($(window).width() - $(".wppcp-modal-box").outerWidth()) / 2
        });
    });
     
    $(window).resize();

    $('.wppcp_deactivate_reason').change(function(){
      $('.wppcp_deactivate_input').hide();
      var selected_val = $(this).val();
      if(selected_val == '2' || selected_val == '4' || selected_val == '5' ||
         selected_val == '6' || selected_val == '7'   ){
        $(this).closest('li').find('.wppcp_deactivate_input').show();
      }
    });

    $('.wppcp_deactivate_input').focus(function(){
      $(this).val('');
    });

    $('#wppcp-deactivate-reasons-submit').click(function(){
      var url = $('#wppcp-deactivate-popup').attr('data-deactivate-url');
      var admin_email = '';
      if($("#wppcp_deactivate_admin_email").is(':checked')){
        admin_email = $('#wppcp_init_admin_email').val();
      }

      var data = {

            code: $("input[name=wppcp_deactivate_reason]:checked").val(),
            plugin_name: $('#wppcp_deactivate_plugin_name').val(),
            error: $('#wppcp_deactivate_plugin_error').val(),
            feature: $('#wppcp_deactivate_plugin_feature').val(),
            price: $('#wppcp_deactivate_pro_price').val(),
            other: $('#wppcp_deactivate_other').val(),
            init_version : $('#wppcp_init_version').val(),
            init_date   : $('#wppcp_init_date').val(),
            admin_email : admin_email
          }

      $(this).val('Deactivating..');
      var submit_data = $.post('http://www.test.wpexpertdeveloper.com?wppcp_deactivate=1', data);
      submit_data.always(function() {
            window.location.href = url;
      });
      
    });

    $('#wppcp-deactivate-submit').click(function(){
      var url = $('#wppcp-deactivate-popup').attr('data-deactivate-url');
      window.location.href = url;
    });

    if(window.location.hash) {
      var hash_key = window.location.hash;
      // alert(hash_key);
      $(hash_key).addClass('wppcp-info-setting-active');
    }

});

function wppcp_formatRepo (repo) {
    if (repo.loading) return repo.text;

    var markup = '<div class="clearfix">' +
    '<div class="col-sm-1">' +
    '' +
    '</div>' +
    '<div clas="col-sm-10">' +
    '<div class="clearfix">' +
    '<div class="col-sm-6">' + repo.name + '</div>' +
    '</div>';


    markup += '</div></div>';

    return markup;
}

function wppcp_formatRepoSelection (repo) {
    return repo.name || repo.text;
}

function wppcp_renderMediaUploader( $ , attachment_id) {
    'use strict';

    var file_frame, image_data, json , attachment_id;
    if (!attachment_id) { attachment_id = 0; }

    if ( undefined !== file_frame ) {
        file_frame.open();
        return;
    }

    file_frame = wp.media.frames.file_frame = wp.media({
        frame:    'post',
        title: WPPCPAdmin.Messages.insertToPost,
          button: {
            text: WPPCPAdmin.Messages.addToPost
          },
        multiple: true
    });

    file_frame.on( 'insert', function() {

        // Read the JSON data returned from the Media Uploader
        var selection = file_frame.state().get( 'selection' );
        json = file_frame.state().get( 'selection' ).toJSON();
        
        $.each(json, function(index,obj){
            console.log(obj);
            if ( 0 > $.trim( obj.id.length ) && 0 > $.trim( obj.url.length ) ) {
                return;
            }
            
            var thumbnail_url = obj.url;

            if(! (obj.mime == 'image/jpeg' || obj.mime == 'image/gif' || obj.mime == 'image/png' || obj.mime == 'image/bmp'
              || obj.mime == 'image/tiff' || obj.mime == 'image/x-icon' ) ){
                thumbnail_url = WPPCPAdmin.images_path + 'file.png';
            }else if(obj.sizes.thumbnail){
                thumbnail_url = obj.sizes.thumbnail.url;
            }

            


            var image_icons = "<img class='wppcp-attachment-edit' src='" + WPPCPAdmin.images_path + "edit.png' /><img class='wppcp-attachment-delete' src='" + WPPCPAdmin.images_path + "delete.png' />";

            if(attachment_id != obj.id){
                

                var wppcp_attachment_template = $("#wppcp_attachment_template").html();
                var template = wppcp_attachment_template.wppcp_format(thumbnail_url, obj.alt,obj.id,image_icons,obj.id,obj.mime,obj.name);
                $("#wppcp-attachments-panel-files").append(template);
                
                
            }

            
        });


    });

    file_frame.on('open',function() {
        var selection = file_frame.state().get('selection');
        if(attachment_id != 0){
            var attachment = wp.media.attachment(attachment_id);
            attachment.fetch();
            selection.add( attachment ? [ attachment ] : [] );
        }
    });

    file_frame.open();

}

String.prototype.wppcp_format = function() {
  var args = arguments;
  return this.replace(/{(\d+)}/g, function(match, number) { 
    return typeof args[number] != 'undefined'
      ? args[number]
      : match
    ;
  });
};