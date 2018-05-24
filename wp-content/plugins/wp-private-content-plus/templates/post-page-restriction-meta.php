<?php
	global $wppcp,$post_page_restriction_params;
	extract($post_page_restriction_params);

	$user_roles = $wppcp->roles_capability->wppcp_user_roles();
	$post_type = $post->post_type;

    $visibility = get_post_meta( $post->ID, '_wppcp_post_page_visibility', true );
    $redirection_url = get_post_meta( $post->ID, '_wppcp_post_page_redirection_url', true );

    $visible_roles = get_post_meta( $post->ID, '_wppcp_post_page_roles', true );
    if(!is_array($visible_roles)){
    	$visible_roles = array();
    }

    $allowed_users = get_post_meta( $post->ID, '_wppcp_post_page_allowed_users', true );
    if(!is_array($allowed_users)){
    	$allowed_users = array();
    }

    $show_role_field = '';
    if( $visibility == 'role'){
    	$show_role_field = " style='display:block;' ";
    }

    $show_users_field = '';
    if( $visibility == 'users'){
    	$show_users_field = " style='display:block;' ";
    }

    echo wppcp_display_info_buttons('http://www.wpexpertdeveloper.com/restrict-posts-pages-custom-post-types/',
     'post-restrictions-meta');
?>



<?php 
    $pro_info_message_status = isset($wppcp->settings->wppcp_options['information']['pro_info_post_restrictions']) ?
    $wppcp->settings->wppcp_options['information']['pro_info_post_restrictions'] : '1';

	if($post_type != 'post' && $post_type != 'page' && $pro_info_message_status == '1'){
	    $message = '<div class="wppcp-pro-info-message-header">'. sprintf(__('Tired of adding protection rules for each %s?','wppcp'),
	    	ucfirst($post_type)) .'</div>';
	    $message .= sprintf(__('%sGo PRO%s and save time by adding global protection
	        to your favorite Custom Post Types.','wppcp'), '<strong>','</strong>' );
	    $message .= ' <a target="_blank" href="http://www.wpexpertdeveloper.com/global-custom-post-type-protection/?ref=pro-cpt" >'.
	    			__('View More','wppcp'). '</a>';
	    echo wppcp_display_pro_info_box($message,'post_meta_boxes','wppcp_pro_info_post_restrictions');
    } 

    if( $pro_info_message_status == '1' && ($post_type == 'post' || $post_type == 'page')){
    $message = '<div class="wppcp-pro-info-message-header">'.sprintf(__('Tired of adding group of permitted users to each %s?','wppcp'),ucfirst($post_type)).'</div>';
        $message .= sprintf(__('%sGo PRO%s and add users to user groups or membership levels. Protection rules can be applied on user groups or membership 
        	levels instead of selecting users for each post/page','wppcp'), '<strong>','</strong>' );
        echo wppcp_display_pro_info_box($message,'post_meta_boxes_large','wppcp_pro_info_post_restrictions');
    }
?>

<div class="wppcp_post_meta_row">
	<div class="wppcp_post_meta_row_label"><strong><?php _e('Visibility','wppcp'); ?></strong></div>
	<div class="wppcp_post_meta_row_field">
		<select id="wppcp_post_page_visibility" name="wppcp_post_page_visibility" class="wppcp-select2-setting">
			<option value='none' <?php selected('none',$visibility); ?> ><?php _e('Please Select','wppcp'); ?></option>
			<option value='all' <?php selected('all',$visibility); ?> ><?php _e('Everyone','wppcp'); ?></option>
			<option value='guest' <?php selected('guest',$visibility); ?> ><?php _e('Guests','wppcp'); ?></option>
			<option value='member' <?php selected('member',$visibility); ?> ><?php _e('Members','wppcp'); ?></option>
			<option value='role' <?php selected('role',$visibility); ?> ><?php _e('Selected User Roles','wppcp'); ?></option>
			<option value='users' <?php selected('users',$visibility); ?> ><?php _e('Selected Users','wppcp'); ?></option>
	
		</select>
	</div>
</div>
<div class="wppcp-clear"></div>

<div id="wppcp_post_page_role_panel" class="wppcp_post_meta_row" <?php echo $show_role_field; ?> >
	<div class="wppcp_post_meta_row_label"><strong><?php _e('Allowed User Roles','wppcp'); ?></strong></div>
	<div class="wppcp_post_meta_row_field">
		<?php foreach($user_roles as $role_key => $role){
				$checked_val = ''; 

				if(in_array($role_key, $visible_roles)  ){
					$checked_val = ' checked '; 
	
				}
				if($role_key != 'administrator'){
			?>
			<input type="checkbox" <?php echo $checked_val; ?> name="wppcp_post_page_roles[]" value='<?php echo $role_key; ?>'><?php echo $role; ?><br/>
			<?php } ?>	
		<?php } ?>		
	</div>

</div>

<div id="wppcp_post_page_users_panel" class="wppcp_post_meta_row" <?php echo $show_users_field; ?> >
	<div class="wppcp_post_meta_row_label"><strong><?php _e('Allowed Users','wppcp'); ?></strong></div>
	<div class="wppcp_post_meta_row_field" style="width:500px;">
		
		<select name="wppcp_post_page_users[]" id="wppcp_post_page_users" multiple class="wppcp-select2-setting" placeholder="<?php _e('Select','wppcp'); ?>" >
            <?php foreach ($allowed_users as $user_id) {
            		$user = get_user_by( 'id', $user_id ); 
            		$display_name = $user->data->display_name;
            ?>
            	<option value='<?php echo $user_id; ?>' selected ><?php echo $display_name; ?></option>
            <?php } ?>
		</select>	
	</div>

</div>

<?php 
	$post_visibility_additional_fields = array('visibility' => $visibility, 'post' => $post);
	echo apply_filters('wppcp_post_visibility_additional_fields','', $post_visibility_additional_fields); 
?>

<div class="wppcp-clear"></div>

<div class="wppcp_post_meta_row">
	<div class="wppcp_post_meta_row_label"><strong><?php _e('Redirection URL','wppcp'); ?></strong></div>
	<div class="wppcp_post_meta_row_field">
		<input type='text' id="wppcp_post_page_redirection_url" name="wppcp_post_page_redirection_url" value="<?php echo $redirection_url; ?>" />
			
	</div>
</div>
<div class="wppcp-clear"></div>

<?php wp_nonce_field( 'wppcp_restriction_settings', 'wppcp_restriction_settings_nonce' ); ?>

