<?php

class WPPCP_IP_Restrictions{
	public function __construct(){

		add_action('init', array($this, 'validate_ip_restrictions'), 1); 
	}

	public function validate_ip_restrictions(){
		global $wppcp,$wp_query,$wppcp_cpt_id;;
        $private_content_settings  = get_option('wppcp_options');
        if(!isset($private_content_settings['general']['private_content_module_status'])){
            return;        
        }

        $this->current_user = wp_get_current_user();
        if(is_user_logged_in() && ( current_user_can('manage_options') || current_user_can('wppcp_manage_options') )){
            return;
        }

        $data = isset($private_content_settings['security_ip']) ? $private_content_settings['security_ip'] : array();
                
        $restriction_status = isset($data['restriction_status']) ? $data['restriction_status'] : '';
        $allowed_urls = isset($data['allowed_urls']) ? $data['allowed_urls'] : '';
        $whitelisted = isset($data['whitelisted']) ? $data['whitelisted'] : '';
        $redirect_url = isset($data['redirect_url']) ? $data['redirect_url'] : site_url();

        


        if($restriction_status != 'disabled'){

        	$allowed_urls = explode(PHP_EOL, $allowed_urls);
	        $filtered_allowed_urls = array();
	        foreach ($allowed_urls as $url) {
	            if($url != ''){
	            	$url = rtrim($url , '/');
	                array_push($filtered_allowed_urls, $url);
	            }
	        }

	        $skipped_urls = array( $redirect_url , wp_login_url(), wp_registration_url(), wp_lostpassword_url());
        	$filtered_allowed_urls = array_merge($filtered_allowed_urls,$skipped_urls);

	        $whitelisted = explode(PHP_EOL, $whitelisted);
	        $filtered_whitelisted = array();
	        foreach ($whitelisted as $ip) {
	            if($ip != ''){
	                array_push($filtered_whitelisted, $ip);
	            }
	        }

	        $current_page_url = wppcp_current_page_url();

	        $parsed_url = parse_url($current_page_url);
	        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
	        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
	        $pass     = ($user || $pass) ? "$pass@" : '';
	        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	 
	        $current_page_trailing_slash_url = $scheme.$user.$pass.$host.$port.$path;
	        $current_page_url = rtrim($current_page_trailing_slash_url , '/');
	        $redirect_url = rtrim($redirect_url , '/');


        	$client_ip = wppcp_get_client_ip();
        	
        	if($current_page_url != $redirect_url){
	        	switch ($restriction_status) {
	        		case 'guests':
	        			if(!is_admin() && !is_user_logged_in()){
	        				if(!in_array($client_ip, $filtered_whitelisted)
	        					&& !in_array($current_page_url, $filtered_allowed_urls)){
	        					wp_redirect( $redirect_url );
	        					exit;
	        				}
	        			}
	        			break;
	        		
	        		case 'members':	        			
        				if(!in_array($client_ip, $filtered_whitelisted)
        					&& !in_array($current_page_url, $filtered_allowed_urls)){
        					wp_redirect( $redirect_url );
        					exit;
        				}	        			
	        			break;
	        	}
	        }

        
        }
        
	}
}

