<?php 
    global $wppcp_settings_data,$wppcp; 
    extract($wppcp_settings_data);
    $user_roles = $wppcp->roles_capability->wppcp_user_roles();


    if ( ! defined( 'upme_url' ) ) {
        return;
    }

?>

<form method="post" action=""  class="wppcp-settings-form">
<table class="form-table wppcp-settings-list">
        <tr>
            <th><label for=""><?php echo __('UPME Member List Visibility','wppcp'); ?></label></th>
            <td style="width:500px;">
                <select id="wppcp_upme_member_list_visibility" name="wppcp_upme_member_list[upme_member_list_visibility]" class="wppcp-select2-setting">
                    <option value='all' <?php selected('all',$upme_member_list_visibility); ?> ><?php _e('Everyone','wppcp'); ?></option>
                    <option value='guest' <?php selected('guest',$upme_member_list_visibility); ?> ><?php _e('Guests','wppcp'); ?></option>
                    <option value='member' <?php selected('member',$upme_member_list_visibility); ?> ><?php _e('Members','wppcp'); ?></option>
                    <option value='role' <?php selected('role',$upme_member_list_visibility); ?> ><?php _e('Selected User Roles','wppcp'); ?></option>                            
                </select>
                
                <div class='wppcp-settings-help'><?php _e('This setting is used to specify users allowed to view UPME Member List.','wppcp'); ?></div>
            </td>
            
        </tr>

        <?php 
            $upme_member_list_role_visibility = 'display:none;';
            if($upme_member_list_visibility == 'role'){
                $upme_member_list_role_visibility = 'display:table-row';
            }
        ?>               
        <tr id="upme_member_list_user_roles_panel" style="<?php echo $upme_member_list_role_visibility; ?>">
            <th><label for=""><?php echo __('Allowed User Roles','wppcp'); ?></label></th>
            <td style="width:500px;">
                <?php foreach($user_roles as $role_key => $role){
                        $checked_val = ''; 

                        if(in_array($role_key, $upme_member_list_user_roles)  ){
                            $checked_val = ' checked '; 
            
                        }
                        if($role_key != 'administrator'){
                    ?>
                    <input type="checkbox" <?php echo $checked_val; ?> name="wppcp_upme_member_list[upme_member_list_user_roles][]" value='<?php echo $role_key; ?>'><?php echo $role; ?><br/>
                    <?php } ?>  
                <?php } ?>
                
                <div class='wppcp-settings-help'><?php _e('This setting is used to specify the user roles allowed to view UPME Member List by default.','wppcp'); ?></div>
           
        </td>
        </tr>




                

                
                
    <input type="hidden" name="wppcp_upme_member_list[common_status]"  value="1" />                   
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>