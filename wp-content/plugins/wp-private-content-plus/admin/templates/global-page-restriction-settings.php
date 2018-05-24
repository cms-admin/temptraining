<?php 
    global $wppcp_settings_data,$wppcp; 
    extract($wppcp_settings_data);
    $user_roles = $wppcp->roles_capability->wppcp_user_roles();


    $checked_restrict_all_pages_status = '';
    if(isset($restrict_all_pages_status)){
        $checked_restrict_all_pages_status = checked( '1', $restrict_all_pages_status, false );
    }
    
?>
<form method="post" action=""  class="wppcp-settings-form">
    <?php echo wppcp_display_info_buttons('http://www.wpexpertdeveloper.com/global-post-page-protection/',
     'global-page-restrictions'); ?>

    <table class="form-table wppcp-settings-list">
                <tr>
                    <th><label for=""><?php echo __('Enable Global Page Restrictions','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <input type="checkbox" name="wppcp_global_page_restriction[restrict_all_pages_status]" <?php echo $checked_restrict_all_pages_status; ?> value="1" /><br/>
                        <div class='wppcp-settings-help'><?php _e('This setting is used to enable/disable restrictions globally on all pages by default.','wppcp'); ?></div>
                    </td>
                    
                </tr> 


                <tr>
                    <th><label for=""><?php echo __('Visibility','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <select id="wppcp_global_page_restriction_visibility" name="wppcp_global_page_restriction[all_page_visibility]" class="wppcp-select2-setting">
                            <option value='all' <?php selected('all',$all_page_visibility); ?> ><?php _e('Everyone','wppcp'); ?></option>
                            <option value='guest' <?php selected('guest',$all_page_visibility); ?> ><?php _e('Guests','wppcp'); ?></option>
                            <option value='member' <?php selected('member',$all_page_visibility); ?> ><?php _e('Members','wppcp'); ?></option>
                            <option value='role' <?php selected('role',$all_page_visibility); ?> ><?php _e('Selected User Roles','wppcp'); ?></option>                            
                        </select>
                        
                        <div class='wppcp-settings-help'><?php _e('This setting is used to specify users allowed to view all pages by default.','wppcp'); ?></div>
                    </td>
                    
                </tr>

                <?php 
                    $all_page_role_visibility = 'display:none;';
                    if($all_page_visibility == 'role'){
                        $all_page_role_visibility = 'display:table-row';
                    }
                ?>
                <tr id="all_page_user_roles_panel" style="<?php echo $all_page_role_visibility; ?>">
                    <th><label for=""><?php echo __('Allowed User Roles','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <?php foreach($user_roles as $role_key => $role){
                                $checked_val = ''; 

                                if(in_array($role_key, $all_page_user_roles)  ){
                                    $checked_val = ' checked '; 
                    
                                }
                                if($role_key != 'administrator'){
                            ?>
                            <input type="checkbox" <?php echo $checked_val; ?> name="wppcp_global_page_restriction[all_page_user_roles][]" value='<?php echo $role_key; ?>'><?php echo $role; ?><br/>
                            <?php } ?>  
                        <?php } ?>
                        
                        <div class='wppcp-settings-help'><?php _e('This setting is used to specify the user roles allowed to view all pages by default.','wppcp'); ?></div>
                   
                </td>
                </tr>            
                
    <input type="hidden" name="wppcp_global_page_restriction[private_mod]"  value="1" />                        
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>