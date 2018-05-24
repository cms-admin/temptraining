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
            <th><label for=""><?php echo __('UPME Search Visibility','wppcp'); ?></label></th>
            <td style="width:500px;">
                <select id="wppcp_upme_search_visibility" name="wppcp_upme_search[upme_search_visibility]" class="wppcp-select2-setting">
                    <option value='all' <?php selected('all',$upme_search_visibility); ?> ><?php _e('Everyone','wppcp'); ?></option>
                    <option value='guest' <?php selected('guest',$upme_search_visibility); ?> ><?php _e('Guests','wppcp'); ?></option>
                    <option value='member' <?php selected('member',$upme_search_visibility); ?> ><?php _e('Members','wppcp'); ?></option>
                    <option value='role' <?php selected('role',$upme_search_visibility); ?> ><?php _e('Selected User Roles','wppcp'); ?></option>                            
                </select>
                
                <div class='wppcp-settings-help'><?php _e('This setting is used to specify users allowed to view UPME Search.','wppcp'); ?></div>
            </td>
            
        </tr>

        <?php 
            $upme_search_role_visibility = 'display:none;';
            if($upme_search_visibility == 'role'){
                $upme_search_role_visibility = 'display:table-row';
            }
        ?>               
        <tr id="upme_search_user_roles_panel" style="<?php echo $upme_search_role_visibility; ?>">
            <th><label for=""><?php echo __('Allowed User Roles','wppcp'); ?></label></th>
            <td style="width:500px;">
                <?php foreach($user_roles as $role_key => $role){
                        $checked_val = ''; 

                        if(in_array($role_key, $upme_search_user_roles)  ){
                            $checked_val = ' checked '; 
            
                        }
                        if($role_key != 'administrator'){
                    ?>
                    <input type="checkbox" <?php echo $checked_val; ?> name="wppcp_upme_search[upme_search_user_roles][]" value='<?php echo $role_key; ?>'><?php echo $role; ?><br/>
                    <?php } ?>  
                <?php } ?>
                
                <div class='wppcp-settings-help'><?php _e('This setting is used to specify the user roles allowed to view UPME Search by default.','wppcp'); ?></div>
           
        </td>
        </tr>




                

                
                
    <input type="hidden" name="wppcp_upme_search[common_status]"  value="1" />                   
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>