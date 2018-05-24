<?php
    global $wppcp,$wppcp_private_page_params;
    extract($wppcp_private_page_params);
    $user_query = new WP_User_Query( array( 'exclude' => array( 1 ) ) );

    $message = isset($message) ? $message : '';
    $message_status = isset($message_status) ? $message_status : '';

    $display_css = "display:none;";
    $message_css = '';
    if($message != ''){
        $display_css = "display:block;";
        if($message_status){
            $message_css = 'wppcp-message-info-success';
        }else{
            $message_css = 'wppcp-message-info-error';
        }
    }
?>
<div class="wrap">
    <h2><?php echo __('Private User Page Contents','wppcp'); ?></h2>
    
    <div class="wppcp-setting-panel">

        <?php echo wppcp_display_info_buttons('http://www.wpexpertdeveloper.com/private-page-for-users/',
     'private-page-screen'); ?>

        <div style="<?php echo $display_css; ?>" id="wppcp-message" class="<?php echo $message_css; ?>" ><?php echo $message; ?></div>
        
        <form method="post" id="wppcp_private_page_user_load_form">
            <div class="wppcp-row">
                <div class="wppcp-label"><?php echo __('Select User','wppcp'); ?></div>
                <div class="wppcp-field">
                    <select name="wppcp_private_page_user" id="wppcp_private_page_user" style="width:75%;" class=""  >
                        <option value="0"><?php echo __('Select','wppcp'); ?></option>
                    </select>
                    <input type="submit" name="wppcp_private_page_user_load" id="wppcp_private_page_user_load" value="<?php _e('Load User','wppcp'); ?>" class="wppcp-button-primary" />
                </div>
                <div class="wppcp-clear"></div>
            </div>
         </form>   
            
        
    </div>
    
    <div class="wppcp-setting-panel">

        <?php 

            $pro_info_message_status = isset($wppcp->settings->wppcp_options['information']['pro_info_private_page']) ?
    $wppcp->settings->wppcp_options['information']['pro_info_private_page'] : '1';

            if($pro_info_message_status == '1'){
    
                $message = '<div class="wppcp-pro-info-message-header">'.sprintf(__('Tired of using seperate channels to manage private user data and comminications?')).'</div>';
                $message .= sprintf(__('%sGo PRO%s and get the advantage of private discussions, private file sharing, private notifications and custom private tabs
                    , all in one place.','wppcp'), '<strong>','</strong>' );
                $message .= ' <a target="_blank" href="http://www.wpexpertdeveloper.com/private-page-dashboard-users/?ref=pro-private-page" >'.
                            __('View More','wppcp'). '</a>';
                echo wppcp_display_pro_info_box($message,'post_meta_boxes','wppcp_pro_info_private_page');
            }
        ?>
        <form method="post" id="" >
            
        <?php 
            wp_nonce_field( 'wppcp_private_page_nonce', 'wppcp_private_page_nonce_field' );
            if($_POST && isset($_POST['wppcp_private_page_user_load'])){ 
        ?> 
            <div class="wppcp-row" >
                <div class="wppcp-label"><?php echo __('Name','wppcp'); ?></div>
                <div class="wppcp-field"><?php echo $display_name; ?></div>
                <input type="hidden" name="wppcp_user_id" value="<?php echo $user_id; ?>" />
                <div class="wppcp-clear"></div>
            </div>
            <div class="wppcp-row" >
                <div class="wppcp-label"><?php echo __('Private content','wppcp'); ?></div>
                <div class="wppcp-field"><?php wp_editor($private_content, 'wppcp_private_page_content'); ?></div>
                <div class="wppcp-clear"></div>
            </div>
            <div class="wppcp-row">
                <div class="wppcp-label">&nbsp;</div>
                <div class="wppcp-field">
                    
                    <input type="submit" name="wppcp_private_page_content_submit" id="wppcp_private_page_content_submit" value="<?php _e('Save','wppcp'); ?>" class="wppcp-button-primary" />
                </div>
                <div class="wppcp-clear"></div>
            </div>
            <div class="wppcp-clear"></div>
        <?php } ?>
        </form>
    </div>
</div>