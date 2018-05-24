<?php 
    global $wppcp_settings_data; 
    extract($wppcp_settings_data);

    $checked_pro_info_post_restrictions = '';
    if(isset($pro_info_post_restrictions)){
        $checked_pro_info_post_restrictions = checked( '1', $pro_info_post_restrictions, false );
    }

    $checked_pro_info_post_attachments = '';
    if(isset($pro_info_post_attachments)){
        $checked_pro_info_post_attachments = checked( '1', $pro_info_post_attachments, false );
    }

    $checked_pro_info_search_restrictions = '';
    if(isset($pro_info_search_restrictions)){
        $checked_pro_info_search_restrictions = checked( '1', $pro_info_search_restrictions, false );
    }

    $checked_pro_info_private_page = '';
    if(isset($pro_info_private_page)){
        $checked_pro_info_private_page = checked( '1', $pro_info_private_page, false );
    }
    
?>

<div id="wppcp-main-settings-panel">
<form method="post" action="" class="wppcp-settings-form">
    <table class="form-table wppcp-settings-list">

                <tr id="wppcp_pro_info_post_restrictions">
                    <th><label for=""><?php echo __('Display PRO Information for Post Restrictions','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <input type="checkbox" name="wppcp_information[pro_info_post_restrictions]" <?php echo $checked_pro_info_post_restrictions; ?> value="1" /><br/>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to enable/disable PRO feature highlights on Post Restrictions meta box. By default, this is enabled 
                        and feature highlightes will be displayed. Unchecking this option will remove the feature highlightes.','wppcp'); ?></div>
                    </td>                    
                </tr>

                <tr id="wppcp_pro_info_post_attachments">
                    <th><label for=""><?php echo __('Display PRO Information for Post Attachments','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <input type="checkbox" name="wppcp_information[pro_info_post_attachments]" <?php echo $checked_pro_info_post_attachments; ?> value="1" /><br/>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to enable/disable PRO feature highlights on Manage File Attachments meta box on post/page screen. By default, this is enabled 
                        and feature highlightes will be displayed. Unchecking this option will remove the feature highlightes.','wppcp'); ?></div>
                    </td>                    
                </tr>

                <tr id="wppcp_pro_info_search_restrictions">
                    <th><label for=""><?php echo __('Display PRO Information for Search Settings/ Restrictions','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <input type="checkbox" name="wppcp_information[pro_info_search_restrictions]" <?php echo $checked_pro_info_search_restrictions; ?> value="1" /><br/>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to enable/disable PRO feature highlights on Search Settings section. By default, this is enabled 
                        and feature highlightes will be displayed. Unchecking this option will remove the feature highlightes.','wppcp'); ?></div>
                    </td>                    
                </tr>

                <tr id="wppcp_pro_info_private_page">
                    <th><label for=""><?php echo __('Display PRO Information for Private User Page','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <input type="checkbox" name="wppcp_information[pro_info_private_page]" <?php echo $checked_pro_info_private_page; ?> value="1" /><br/>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to enable/disable PRO feature highlights on Private User Page screen. By default, this is enabled 
                        and feature highlightes will be displayed. Unchecking this option will remove the feature highlightes.','wppcp'); ?></div>
                    </td>                    
                </tr> 
                        
                
    <input type="hidden" name="wppcp_information[private_mod]"  value="1" />                        
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>

</div>
<div id="wppcp-main-settings-sidebar">
    <?php echo wppcp_display_pro_sidebar_info_box(); ?>
</div>
<div class="wppcp-clear"></div>