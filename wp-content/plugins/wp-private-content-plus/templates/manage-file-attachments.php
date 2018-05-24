<?php
    global $wppcp,$wppcp_attachments_params;
    extract($wppcp_attachments_params);

    // Get uploaded images for specific image slider
    $post_attachments = get_post_meta( $post->ID, '_wppcp_post_attachments', true );


    $upload_dir = wp_upload_dir();
    $upload_dir_url = $upload_dir['baseurl']."/";

    echo wppcp_display_info_buttons('http://www.wpexpertdeveloper.com/restrict-post-attachments-downloads/',
     'post-manage-file-attachments');
?>


<div id="wppcp-attachments-panel" >
    <?php 
        $pro_info_message_status = isset($wppcp->settings->wppcp_options['information']['pro_info_post_attachments']) ?
    $wppcp->settings->wppcp_options['information']['pro_info_post_attachments'] : '1';

        if($pro_info_message_status == '1'){
            $message = '<div class="wppcp-pro-info-message-header">'.sprintf(__('Do you want make attachments 
            completely secured?','wppcp')).'</div>';
            $message .= sprintf(__('%sGo PRO%s and secure direct access to your attachments. Post attachments added in free version cannot be converted to PRO version. Free version attchments
         are uploaded to uploads folder and hence you can\'t protect
        direct access to the files.','wppcp'), '<strong>','</strong>' );
            $message .= ' <a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-pro-post-attachments-and-downloads/?ref=pro-attachments" >'.
                        __('View More','wppcp'). '</a>';
            echo wppcp_display_pro_info_box($message,'post_meta_boxes_large','wppcp_pro_info_post_attachments');
 
        }
        
    ?>

    <div id="wppcp-attachments-panel-upload" ><span><?php _e('Add Files','wppcp'); ?></span></div>
    <div id="wppcp-attachments-panel-files" >

        <?php 
            if(is_array($post_attachments)){
                foreach($post_attachments as $attach_data){
                    if($attach_data['attach_id'] != ''){
                        $image_icons = "<img class='wppcp-attachment-edit' src='" . WPPCP_PLUGIN_URL  ."images/edit.png' />
                                        <img class='wppcp-attachment-delete' src='" . WPPCP_PLUGIN_URL  . "images/delete.png' />";

                        $attachment = wp_get_attachment_metadata( $attach_data['attach_id'] );

                        $attached_name = isset($attach_data['name']) ? $attach_data['name'] : '';
                        $attached_desc = isset($attach_data['desc']) ? $attach_data['desc'] : '';
                        $attached_visibility = isset($attach_data['visibility']) ? $attach_data['visibility'] : 'all';
                        $attached_download_permission = isset($attach_data['download_permission']) ? $attach_data['download_permission'] : 'all';
                        $attached_mime = isset($attach_data['mime']) ? $attach_data['mime'] : '';

                        $image_mimes = array('image/jpeg','image/gif','image/png','image/bmp','image/tiff','image/x-icon');

                        if(in_array($attached_mime, $image_mimes)){
                            $attach_image_url = $upload_dir_url.$attachment['file'];
                        }else{
                            $attach_image_url = WPPCP_PLUGIN_URL  . 'images/file.png';
                        }

        ?>
                    <div class='wppcp-attachments-panel-file-single'>
                        <div class='wppcp-attachments-panel-file-left'>
                            <img src="<?php echo $attach_image_url; ?>" data-attachment-id="<?php echo $attach_data['attach_id']; ?>" class='wppcp-attachment-preview' />
                            <div class='wppcp-slider-images-panel-gallery-icons'><?php echo $image_icons; ?></div> 
                        </div>
                        <div class='wppcp-attachments-panel-file-right'>
                            <div class='wppcp-attachments-panel-file-row'>
                                <div class='wppcp-attachments-panel-file-label'><?php _e('File Name','wppcp'); ?></div>
                                <div class='wppcp-attachments-panel-file-field'><input type='text' name='wppcp_attachments[<?php echo $attach_data['attach_id']; ?>][name]' value='<?php echo $attached_name; ?>' /></div>
                            </div>
                            <div class='wppcp-attachments-panel-file-row'>
                                <div class='wppcp-attachments-panel-file-label'><?php _e('File Description','wppcp'); ?></div>
                                <div class='wppcp-attachments-panel-file-field'><textarea name='wppcp_attachments[<?php echo $attach_data['attach_id']; ?>][desc]' ><?php echo $attached_desc; ?></textarea></div>
                            </div>
                            <div class='wppcp-attachments-panel-file-row'>
                                <div class='wppcp-attachments-panel-file-label'><?php _e('File Visibility','wppcp'); ?></div>
                                <div class='wppcp-attachments-panel-file-field'>
                                    <select class='wppcp-attachments-panel-file-visibility' name='wppcp_attachments[<?php echo $attach_data['attach_id']; ?>][visibility]' >
                                        <option <?php echo selected($attached_visibility,'all'); ?> value="all"><?php _e('Everyone','wppcp'); ?></option>
                                        <option <?php echo selected($attached_visibility,'guest'); ?> value="guest"><?php _e('Guests','wppcp'); ?></option>
                                        <option <?php echo selected($attached_visibility,'member'); ?> value="member"><?php _e('Members','wppcp'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class='wppcp-attachments-panel-file-row'>
                                <div class='wppcp-attachments-panel-file-label'><?php _e('Download Permission','wppcp'); ?></div>
                                <div class='wppcp-attachments-panel-file-field'>
                                    <select class='wppcp-attachments-panel-file-download-permission' name='wppcp_attachments[<?php echo $attach_data['attach_id']; ?>][download_permission]' >
                                        <option <?php echo selected($attached_download_permission,'all'); ?> value="all"><?php _e('Everyone','wppcp'); ?></option>
                                        <option <?php echo selected($attached_download_permission,'guest'); ?> value="guest"><?php _e('Guests','wppcp'); ?></option>
                                        <option <?php echo selected($attached_download_permission,'member'); ?> value="member"><?php _e('Members','wppcp'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" value="<?php echo $attached_mime; ?>" name="wppcp_attachments[<?php echo $attach_data['attach_id']; ?>][mime]" />
                    </div>
                    
                
        <?php
                    }
                }
            }
        ?>
    </div>
    <div class='wppcp-clear'></div>
</div>

<?php wp_nonce_field( 'wppcp_file_attachment_settings', 'wppcp_file_attachment_nonce' ); ?>



<script type="text/template" id="wppcp_attachment_template">

<div class='wppcp-attachments-panel-file-single'>
    <div class='wppcp-attachments-panel-file-left'>
        <img src="{0}" alt="{1}" data-attachment-id="{2}" class='wppcp-slider-preview-thumb' />
        <div class='wppcp-slider-images-panel-gallery-icons'>{3}</div> 
    </div>
    <div class='wppcp-attachments-panel-file-right'>
        <div class='wppcp-attachments-panel-file-row'>
            <div class='wppcp-attachments-panel-file-label'><?php _e('File Name','wppcp'); ?></div>
            <div class='wppcp-attachments-panel-file-field'><input type='text' name='wppcp_attachments[{4}][name]' value='{6}' /></div>
        </div>
        <div class='wppcp-attachments-panel-file-row'>
            <div class='wppcp-attachments-panel-file-label'><?php _e('File Description','wppcp'); ?></div>
            <div class='wppcp-attachments-panel-file-field'><textarea name='wppcp_attachments[{4}][desc]' ></textarea></div>
        </div>
        <div class='wppcp-attachments-panel-file-row'>
            <div class='wppcp-attachments-panel-file-label'><?php _e('File Visibility','wppcp'); ?></div>
            <div class='wppcp-attachments-panel-file-field'>
                <select class='wppcp-attachments-panel-file-visibility' name='wppcp_attachments[{4}][visibility]' >
                    <option value="all"><?php _e('Everyone','wppcp'); ?></option>
                    <option value="guest"><?php _e('Guests','wppcp'); ?></option>
                    <option value="member"><?php _e('Members','wppcp'); ?></option>
                </select>
            </div>
        </div>
        <div class='wppcp-attachments-panel-file-row'>
            <div class='wppcp-attachments-panel-file-label'><?php _e('Download Permission','wppcp'); ?></div>
            <div class='wppcp-attachments-panel-file-field'>
                <select class='wppcp-attachments-panel-file-download-permission' name='wppcp_attachments[{4}][download_permission]' >
                    <option value="all"><?php _e('Everyone','wppcp'); ?></option>
                    <option value="guest"><?php _e('Guests','wppcp'); ?></option>
                    <option value="member"><?php _e('Members','wppcp'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <input type="hidden" value="{5}" name="wppcp_attachments[{4}][mime]" />
</div>

</script>