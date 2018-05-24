<?php
	global $wppcp,$wppcp_attachments_params;
	extract($wppcp_attachments_params);

    $wppcp_post_files_list_title = get_post_meta( $post->ID, '_wppcp_post_files_list_title', true );
	$wppcp_post_files_list_description = get_post_meta( $post->ID, '_wppcp_post_files_list_description', true );
?>


<div class="wppcp_post_meta_row">
	<div class="wppcp_post_meta_row_label"><strong><?php _e('File List Title','wppcp'); ?></strong></div>
	<div class="wppcp_post_meta_row_field">
		<input type="text" id="wppcp_post_files_list_title" name="wppcp_post_files_list_title" value="<?php echo $wppcp_post_files_list_title; ?>" />
	</div>
</div>
<div class="wppcp-clear"></div>

<div class="wppcp_post_meta_row">
	<div class="wppcp_post_meta_row_label"><strong><?php _e('File List Description','wppcp'); ?></strong></div>
	<div class="wppcp_post_meta_row_field">
		<textarea id="wppcp_post_files_list_description" name="wppcp_post_files_list_description" ><?php echo $wppcp_post_files_list_description; ?></textarea>
	</div>
</div>
<div class="wppcp-clear"></div>
