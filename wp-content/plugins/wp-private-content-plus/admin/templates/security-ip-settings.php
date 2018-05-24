<?php 
    global $wppcp,$wppcp_security_settings_data;
    extract($wppcp_security_settings_data);    
        
?>
<form method="post" action=""  class="wppcp-settings-form">

    <table class="form-table wppcp-settings-list">               

                <tr>
                    <th><label for=""><?php echo __('IP Based Restrictions','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <select name="wppcp_security_ip[restriction_status]" id="wppcp_security_ip_restriction_status" class="wppcp-select2-setting" placeholder="<?php _e('Select','wppcp'); ?>" >
                            
                            <option <?php echo selected('disabled',$restriction_status,false); ?> value="disabled"><?php echo __('Disabled','wppcp'); ?></option>
                            <option <?php echo selected('guests',$restriction_status,false); ?> value="guests"><?php echo __('Enabled for Guests','wppcp'); ?></option>
                            <option <?php echo selected('members',$restriction_status,false); ?> value="members"><?php echo __('Enabled for Guests and Members','wppcp'); ?></option>
                        </select>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to define status of IP restrictions.','wppcp'); ?></div>
                    </td>
                    
                </tr>

                <tr>
                    <th><label for=""><?php echo __('Allowed URL\'s','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <textarea rows="5" name="wppcp_security_ip[allowed_urls]" id="wppcp_security_ip_allowed_urls"  ><?php echo $allowed_urls; ?></textarea>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to define the URL\'s accessible without checking the IP. Use new line for each URL.','wppcp'); ?></div>
                    </td>
                    
                </tr>

                <tr>
                    <th><label for=""><?php echo __('Whitelisted IP\'s','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <textarea rows="5" name="wppcp_security_ip[whitelisted]" id="wppcp_security_ip_whitelisted"  ><?php echo $whitelisted; ?></textarea>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to define the IP\'s allowed to acccess the site without restrictions. Use new line for each IP','wppcp'); ?></div>
                    </td>
                    
                </tr>  
                <tr>
                    <th><label for=""><?php echo __('Redirect URL','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <input value="<?php echo $redirect_url; ?>" type='text' name="wppcp_security_ip[redirect_url]" id="wppcp_security_ip_redirect_url"  />
                        <div class='wppcp-settings-help'><?php _e('This setting is used to define the URL to redirect the users with unauthorised access.','wppcp'); ?></div>
                    </td>
                    
                </tr>  
                
                                         
                
    <input type="hidden" name="wppcp_security_ip[common_status]"  value="1" />                   
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>