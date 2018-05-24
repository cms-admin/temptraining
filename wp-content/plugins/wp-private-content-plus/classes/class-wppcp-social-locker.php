<?php

/* Manage social locking related features */
class WPPCP_Social_Locker{

	/* Initialize menu related acttions and filters */
	public function __construct(){
		global $wppcp;

		$this->private_content_settings  = get_option('wppcp_options'); 		

		add_shortcode('wppcp_social_locker', array($this,'social_locker'));
	}

	public function social_locker($atts,$content){
		global $wppcp,$wppcp_social_locker_params;

		if(!isset($this->private_content_settings['general']['private_content_module_status'])){
            return __('Private content module is disabled.','wppcp');        
        }


        $message = apply_filters('wppcp_social_locker_default_message',__('Choose any Social Social Network from below to share our content and get access.','wppcp'),$atts);

        $unique_id = mt_rand(10, 15);
		extract(shortcode_atts(array(
			'id' => $unique_id,
            'display_facebook_share' => 'yes',
            'display_twitter_tweet' => 'yes',
            'message' => $message,

     	), $atts));

     	wp_register_style('wppcp_shareit_css', WPPCP_PLUGIN_URL . 'js/shareIt-js/assets/css/shareIt.css');
        wp_enqueue_style('wppcp_shareit_css');

     	wp_register_script('wppcp_shareit_js', WPPCP_PLUGIN_URL . 'js/shareIt-js/assets/js/shareIt.js', array('jquery'));
        wp_enqueue_script('wppcp_shareit_js');

        wp_register_script('wppcp_social_locker_js', WPPCP_PLUGIN_URL . 'js/wppcp-social-locker.js', array('jquery'));
        wp_enqueue_script('wppcp_social_locker_js');
        
        $custom_js_strings = array( 
        	'id' =>  $unique_id,      
            'twitter_username' => 'innovativephp',
            'fb_app_id' => '1763236177288574',
            'fb_page_id' => 'https://www.facebook.com/InnovativePHP-206165769442411/',
        );

        wp_localize_script('wppcp_social_locker_js', 'WPPCPSocialLocker', $custom_js_strings);
        // $wppcp_social_locker_params['post'] = $post;

        ob_start();
        $wppcp->template_loader->get_template_part('default-social-locker');    
        $display = ob_get_clean();  
        echo $display;

		
	}

}