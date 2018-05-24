<?php
/*  Supported files types
 	jpg,png,gif,pdf,csv,xls,ppt,pptx,xlsx,pdf,
*/
class WPPCP_Post_Attachments{

	public function __construct(){
		add_action( 'add_meta_boxes', array($this,'file_attachments_meta_box'));
		add_action( 'save_post', array($this,'save_file_attachments' ));
		add_filter( 'the_content' , array($this,'display_file_attachments' ));
		add_action( 'init', array( $this, 'file_attachment_download'));
	}

	public function file_attachments_meta_box(){
		$post_types = get_post_types( '', 'names' ); 
		$skipped_types = array('attachment','revision','nav_menu_item','wppcp_group');

        if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') || apply_filters('wppcp_file_attachment_setting_meta_box_visibility',false,array() ) ){
        
            foreach ( $post_types as $post_type ) {
                if(!in_array($post_type, $skipped_types)){

                	add_meta_box(
                        'wppcp-post-file-attachments-general',
                        __( 'WP Private Content Plus - File Attachments Settings', 'wppcp' ),
                        array($this,'file_attachments_settings'),
                        $post_type
                    );

                    add_meta_box(
                        'wppcp-post-file-attachments',
                        __( 'WP Private Content Plus - Manage File Attachments', 'wppcp' ),
                        array($this,'manage_file_attachments'),
                        $post_type
                    );
                }
            }
        }
	}

	public function manage_file_attachments($post){
        global $wppcp,$wppcp_attachments_params;

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');

        $wppcp_attachments_params['post'] = $post;

        ob_start();
        $wppcp->template_loader->get_template_part('manage-file-attachments');    
        $display = ob_get_clean();  
        echo $display;
    }

    public function file_attachments_settings($post){
        global $wppcp,$wppcp_attachments_params;

        $wppcp_attachments_params['post'] = $post;

        ob_start();
        $wppcp->template_loader->get_template_part('file-attachments-settings');    
        $display = ob_get_clean();  
        echo $display;
    }

    public function save_file_attachments($post_id){

    	$skipped_types = array('attachment','revision','nav_menu_item','forum','topic','reply','product','shop_order','download');
        if ( ! isset( $_POST['wppcp_file_attachment_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['wppcp_file_attachment_nonce'], 'wppcp_file_attachment_settings' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_posts', $post_id ) ) {
            return;
        }

        $wppcp_post_files_list_title = isset($_POST['wppcp_post_files_list_title']) ? $_POST['wppcp_post_files_list_title'] : '';
		$wppcp_post_files_list_description = isset($_POST['wppcp_post_files_list_description']) ? $_POST['wppcp_post_files_list_description'] : '';
		update_post_meta( $post_id, '_wppcp_post_files_list_title', $wppcp_post_files_list_title );
		update_post_meta( $post_id, '_wppcp_post_files_list_description', $wppcp_post_files_list_description );

        $wppcp_attachments = isset($_POST['wppcp_attachments']) ? $_POST['wppcp_attachments'] : array();
        $wppcp_post_attachments = array();
		if(is_array($wppcp_attachments)){
			foreach ($wppcp_attachments as $key => $value) {
				$value['attach_id'] = $key;
				array_push($wppcp_post_attachments,$value);
			}

			update_post_meta( $post_id, '_wppcp_post_attachments', $wppcp_post_attachments );
		}

        
      
    }

    public function display_file_attachments($content){
    	global $post,$wppcp;

    	$wppcp->include_styles();

    	$skipped_types = array('attachment','revision','nav_menu_item','forum','topic','reply','product','shop_order','download');
        
    	if(is_single() || is_page() ){
    		if(!in_array($post->post_type, $skipped_types)){
    			$post_id = $post->ID;
    			$post_attachments = get_post_meta( $post_id, '_wppcp_post_attachments', true );

    			if(is_array($post_attachments)){

    				$wppcp_post_files_list_title = get_post_meta( $post_id, '_wppcp_post_files_list_title', true );
					$wppcp_post_files_list_description = get_post_meta( $post_id, '_wppcp_post_files_list_description', true );


    				$attachment_content = "<div class='wppcp-attachments-display-panel'>";

    				if($wppcp_post_files_list_title != ''){
    					$attachment_content .= "<div class='wppcp-attachments-display-panel-title'>".$wppcp_post_files_list_title."</div>";
    				}

    				if($wppcp_post_files_list_description != ''){
    					$attachment_content .= "<div class='wppcp-attachments-display-panel-desc'>".$wppcp_post_files_list_description."</div>";
    				}

    				$attachment_status = FALSE;
	                foreach($post_attachments as $attach_data){
	                    if($attach_data['attach_id'] != ''){
	                    	

	                    	if($this->verify_attachment_permission($attach_data)){
	                    		$attachment_status = TRUE;
	                    		$attachment = wp_get_attachment_url( $attach_data['attach_id'] );

	                    		if($this->verify_download_permission($attach_data)){

	                    			$url = $_SERVER['REQUEST_URI'];
									$url = wppcp_add_query_string($url,'wppcp_file_download=yes');
    								$url = wppcp_add_query_string($url,'wppcp_file_id='.$attach_data['attach_id']);
    								$url = wppcp_add_query_string($url,'wppcp_post_id='.$post_id);


	                    			$attachment_content .= "<div class='wppcp-attachments-display-panel-file' ><img src='".WPPCP_PLUGIN_URL  . "images/file-mini.png' />
	                    			<a href='".$url."'>" . $attach_data['name']. "</a></div>";

	                    		}else{
	                    			$attachment_content .= "<div class='wppcp-attachments-display-panel-file' ><img src='".WPPCP_PLUGIN_URL  . "images/file-mini.png' />" . $attach_data['name']. "</div>";
	                    		}

	                    	}
	                    }
	                }

	                $attachment_content .= "</div>";

	                if($attachment_status){
	                	$content .= $attachment_content;
	                }	                
	            }
    		}    		
    	}

    	return $content;
    }

    public function verify_attachment_permission($attach_data){
    	$visibility = isset($attach_data['visibility']) ? $attach_data['visibility'] : 'all';
    	$visibility_status = FALSE;
    	switch ($visibility) {
    		case 'all':
    			$visibility_status = TRUE;
    			break;
    		
    		case 'guest':
    			if(!is_user_logged_in() || current_user_can( 'manage_options') || current_user_can('wppcp_manage_options') ){
    				$visibility_status = TRUE;
    			}
    			break;

    		case 'member':
    			if(is_user_logged_in()){
    				$visibility_status = TRUE;
    			}
    			break;
    	}

        $visibility_status = apply_filters('wppcp_attachment_view_permission_status', $visibility_status , array('attach_data' => $attach_data ));

    	return $visibility_status;
    }

    public function verify_download_permission($attach_data){
    	$download_permission = isset($attach_data['download_permission']) ? $attach_data['download_permission'] : 'all';
    	$download_permission_status = FALSE;
    	switch ($download_permission) {
    		case 'all':
    			$download_permission_status = TRUE;
    			break;
    		
    		case 'guest':
    			if(!is_user_logged_in() || current_user_can( 'manage_options') || current_user_can('wppcp_manage_options') ){
    				$download_permission_status = TRUE;
    			}
    			break;

    		case 'member':
    			if(is_user_logged_in()){
    				$download_permission_status = TRUE;
    			}
    			break;
    	}

        $download_permission_status = apply_filters('wppcp_attachment_download_permission_status', $download_permission_status , array('attach_data' => $attach_data ));

    	return $download_permission_status;
    }

    public function file_attachment_download(){
    	if(isset($_GET['wppcp_file_download']) && $_GET['wppcp_file_download'] =='yes'){
			$wppcp_file_download = $_GET['wppcp_file_download'];
			$wppcp_file_id = isset($_GET['wppcp_file_id']) ? $_GET['wppcp_file_id'] : '';
			$wppcp_post_id = isset($_GET['wppcp_post_id']) ? $_GET['wppcp_post_id'] : '';

			if($wppcp_file_id != '' && $wppcp_post_id != ''){
				$file_link = wp_get_attachment_url($wppcp_file_id);

				$upload_dir = wp_upload_dir(); 
				$file_dir =  str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_link);

				$post_attachments = get_post_meta( $wppcp_post_id, '_wppcp_post_attachments', true );
				foreach ($post_attachments as $key => $attach_data) {
					if($attach_data['attach_id'] == $wppcp_file_id){
						// echo "<pre>";print_r($attach_data);exit;

                        if ($this->verify_download_permission($attach_data)){

    						$file_mime_type = isset($attach_data['mime']) ? $attach_data['mime'] : '';
    						if($file_mime_type != ''){

    							header('Cache-Control: public');
    							header('Content-Description: File Transfer');
    							header('Content-disposition: attachment;filename='.basename($file_dir));

    						
    							header('Content-Type: '. $file_mime_type);
    							header('Content-Transfer-Encoding: binary');
    							header('Content-Length: '. filesize($file_dir));
    							readfile($file_dir);
    							exit;
    						}
                        }else {
                            echo sprintf(__('You need to <a href="%s">login</a> before downloading this file.','wppcp'),wp_login_url());
                            exit();
                        }
						
					}
				}
				
			}			
		}
    }
}

