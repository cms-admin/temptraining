<?php 
    global $wppcp_settings_data,$wppcp; 
    extract($wppcp_settings_data);

    $user_roles = $wppcp->roles_capability->wppcp_user_roles();
    foreach($user_roles as $role_key => $role){
        if( !in_array($role_key,$hierarchy) ){
            array_push($hierarchy,$role_key);
        }
    }
?>

<form method="post" action=""  class="wppcp-settings-form">
<table class="form-table">
    
                <tr>
                    <th><label for=""><?php _e('User Role Hierarchy','wppcp'); ?></label></th>
                    <td style="width:500px;">
                        <ul id="wppcp-role-hierarchy-list">
                        <?php foreach($hierarchy as $role){ ?>
                            <li class="wppcp-role-hierarchy-item" data-role="<?php echo $role; ?>">
                            <?php echo $user_roles[$role]; ?>
                            </li>
                        <?php } ?>
                        </ul>
                    
                    </td>
                </tr>                
    
    <input type="hidden" name="wppcp_tab" value="<?php echo $tab; ?>" />    
</table>

    <?php submit_button(); ?>
</form>