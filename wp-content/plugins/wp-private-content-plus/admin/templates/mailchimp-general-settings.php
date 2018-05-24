<?php echo display_pro_block(); ?>
<table class="form-table wppcp-settings-list">
    <tr>
        <th><label for=""><?php echo __('Enable Mailchimp Module','wppcp_mailchimp'); ?></label></th>
        <td style="width:500px;">
            <input type="checkbox" name="mailchimp_module_status]"  value="1" /><br/>
            <div class='wppcp-settings-help'><?php _e('This setting is used to enable/disable features of Mailchimp module.','wppcp_mailchimp'); ?></div>
        </td>
        
    </tr> 

    <tr>
        <th><label for=""><?php echo __('Mailchimp API Key','wppcp'); ?></label></th>
        <td style="width:500px;">
            <input type="text" name="wppcp_mailchimp_general[api_key]"  value="" /><br/>
            <div class='wppcp-settings-help'><?php _e('This setting is used to specify the API key for Mailchimp.','wppcp_mailchimp'); ?></div>
        </td>
        
    </tr>              
                
   
</table>
</form>