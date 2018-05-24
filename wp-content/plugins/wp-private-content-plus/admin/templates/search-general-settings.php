<?php 
    global $wppcp,$wppcp_search_settings_data; 
    extract($wppcp_search_settings_data);    
        
?>

<?php 
    $pro_info_message_status = isset($wppcp->settings->wppcp_options['information']['pro_info_search_restrictions']) ?
    $wppcp->settings->wppcp_options['information']['pro_info_search_restrictions'] : '1';

    if($pro_info_message_status == '1'){
        $message = '<div class="wppcp-pro-info-message-header">'. __('Do you think adding search support for custom post types will benfit your users?','wppcp').'</div>';
        $message .= sprintf(__('%sGo PRO%s and allow users to search custom post types through your site search.','wppcp'), '<strong>','</strong>' );
        $message .= ' <a target="_blank" href="http://www.wpexpertdeveloper.com/site-advanced-search-restrictions/?ref=pro-search-settings" >'.
                    __('View More','wppcp'). '</a>';
        echo wppcp_display_pro_info_box($message,'post_meta_boxes','wppcp_pro_info_search_restrictions');
    }
?>

<form method="post" action=""  class="wppcp-settings-form">

    <?php echo wppcp_display_info_buttons('http://www.wpexpertdeveloper.com/site-search-restrictions/',
     'search-settings-screen'); ?>

    <table class="form-table wppcp-settings-list">               

                <tr>
                    <th><label for=""><?php echo __('Blocked Posts for Searching','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <select name="wppcp_search_general[blocked_post_search][]" id="wppcp_blocked_post_search" multiple class="wppcp-select2-setting" placeholder="<?php _e('Select','wppcp'); ?>" >
                            
                            <?php 
                                if(is_array($blocked_post_search)){
                                    foreach ($blocked_post_search as $post_id) { ?>
                                    <option selected value="<?php echo $post_id; ?>"><?php echo get_the_title($post_id); ?></option>
                            <?php }}  ?>
                        </select>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to allow all posts search for all the users.','wppcp'); ?></div>
                    </td>
                    
                </tr>

                <tr>
                    <th><label for=""><?php echo __('Blocked Pages for Searching','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <select name="wppcp_search_general[blocked_page_search][]" id="wppcp_blocked_page_search" multiple class="wppcp-select2-setting" placeholder="<?php _e('Select','wppcp'); ?>" >
                            
                            <?php 
                                if(is_array($blocked_page_search)){
                                    foreach ($blocked_page_search as $post_id) { ?>
                                        <option selected value="<?php echo $post_id; ?>"><?php echo get_the_title($post_id); ?></option>
                            <?php }}  ?>
                        </select>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to globally block certain pages from appearing in search.','wppcp'); ?></div>
                    </td>
                    
                </tr>  

                <?php echo apply_filters('wppcp_search_blocked_search_posts','',array('search_data' => $wppcp_search_settings_data)); ?>

                                         
                
    <input type="hidden" name="wppcp_search_general[common_status]"  value="1" />                   
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>