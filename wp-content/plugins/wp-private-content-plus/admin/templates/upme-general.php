<?php 
    global $wp_roles,$wppcp_settings_data; 
    extract($wppcp_settings_data);

    if ( ! defined( 'upme_url' ) ) {
        return;
    }

    $checked_private_content_tab_status = '';
    if(isset($private_content_tab_status)){
        $checked_private_content_tab_status = checked( '1', $private_content_tab_status, false );
    }
?>

<form method="post" action=""  class="wppcp-settings-form">

    
    <table class="form-table wppcp-settings-list">
                <tr>
                    <th><label for=""><?php echo __('Enable UPME Private Content Profile Tab','upcp'); ?></label></th>
                    <td style="width:500px;">
                        <input type="checkbox" name="wppcp_upme_general[private_content_tab_status]" <?php echo $checked_private_content_tab_status; ?> value="1" />
                        <div class='wppcp-settings-help'><?php _e('This setting is used to enable/disable private page content on UPME profile tab.','wppcp'); ?></div>
                   
                </td>
                </tr>

                <tr>
                    <th><label for=""><?php echo __('Redirect Restricted Users to UPME Login','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <select name="wppcp_upme_general[redirect_to_upme_login]" id="wppcp_redirect_to_upme_login" class="wppcp-select2-setting"  placeholder="<?php _e('Select','wppcp'); ?>" >
                            <option <?php echo selected('disabled',$redirect_to_upme_login); ?> value="disabled" ><?php echo __('Disabled','wppcp'); ?></option>
                            <option <?php echo selected('enabled',$redirect_to_upme_login); ?> value="enabled" ><?php echo __('Enabled','wppcp'); ?></option>
                            
                        </select>
                        <br/>
                        <div class='wppcp-settings-help'><?php _e('This setting is used type of posts allowed in search for guest users.','wppcp'); ?></div>
                    </td>
                    
                </tr>





                

                
                
    <input type="hidden" name="wppcp_upme_general[common_status]"  value="1" />                   
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>