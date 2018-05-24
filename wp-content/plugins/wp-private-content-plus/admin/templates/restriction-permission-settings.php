<?php 
    global $wppcp_settings_data,$wppcp; 
    extract($wppcp_settings_data);
    $user_roles = $wppcp->roles_capability->wppcp_user_roles();

   
?>

<div id="wppcp-main-settings-panel">
<form method="post" action="" class="wppcp-settings-form">
    <table class="form-table wppcp-settings-list">
        <tr id="">
            <th><label for=""><?php echo __('User Roles with Admin Permissions to WPPCP Restrictions','wppcp'); ?></label></th>
            <td style="width:500px;">
                <?php foreach($user_roles as $role_key => $role){
                        $checked_val = ''; 

                        if(in_array($role_key, $wppcp_feature_permission_roles)  ){
                            $checked_val = ' checked '; 
            
                        }
                        if($role_key != 'administrator'){
                    ?>
                    <input type="checkbox" <?php echo $checked_val; ?> name="wppcp_feature_restrictions[wppcp_feature_permission_roles][]" value='<?php echo $role_key; ?>'><?php echo $role; ?><br/>
                    <?php } ?>  
                <?php } ?>
                
                <div class='wppcp-settings-help'><?php _e('This setting is used to specify the user roles allowed to add restrictions on WP Private Content Plus.','wppcp'); ?></div>
           
        </td>
        </tr>           
                
    <input type="hidden" name="wppcp_feature_restrictions[private_mod]"  value="1" />                        
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>

</div>
<div id="wppcp-main-settings-sidebar">
    <?php echo wppcp_display_pro_sidebar_info_box(); ?>
</div>
<div class="wppcp-clear"></div>