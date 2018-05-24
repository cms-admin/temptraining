<?php

class WPPCP_UPME{

	public function __construct(){
		$this->upme_options = get_option('upme_options');
		$this->private_content_settings  = get_option('wppcp_options');
		
		add_filter('wppcp_global_post_restriction_redirect',array($this,'upme_wppcp_post_restriction_redirect'),100,2);
		add_filter('wppcp_single_post_restriction_redirect',array($this,'upme_wppcp_post_restriction_redirect'),100,2);
		
		add_filter('upme_search_shortcode_display' , array($this,'upme_search_shortcode_display'),100,2);
		add_filter('upme_profile_shortcode_display' , array($this,'upme_profile_shortcode_display'),100,2);
        add_filter('upme_profile_fields_panel',array($this,'upme_profile_fields_shortcode_display'),100,2);
        
		add_action('init', array($this, 'init')); 
	}

    public function init(){
        $this->current_user = get_current_user_id(); 
    }

	public function upme_wppcp_post_restriction_redirect($url,$params){
		
		$login_link_status = $this->private_content_settings['upme_general']['redirect_to_upme_login'];
		if(!is_user_logged_in() && $login_link_status == 'enabled') {
			$url = get_permalink($this->upme_options['login_page_id']);
		}
		return $url;
	}

	public function upme_search_shortcode_display($display,$params){
		global $wppcp;

		if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') ){
			return $display;
		}

		$visibility = isset($this->private_content_settings['upme_search']['upme_search_visibility']) ? $this->private_content_settings['upme_search']['upme_search_visibility'] : 'all';
		$visible_roles = isset($this->private_content_settings['upme_search']['upme_search_user_roles']) ? $this->private_content_settings['upme_search']['upme_search_user_roles'] : array();
	
		switch ($visibility) {
            case 'all':
                break;
            
            case 'guest':
                if(is_user_logged_in()){
                    $display = '';
                }
                break;

            case 'member':
                if(is_user_logged_in()){

                }else{
                    $display = '';
                }
                break;

            case 'role':
                if(is_user_logged_in()){
                    if(count($visible_roles) == 0){
                        $display = '';
                    }else{
                        $user_roles = $wppcp->roles_capability->get_user_roles_by_id($this->current_user);
                        foreach ($visible_roles as  $visible_role ) {
                            if(in_array($visible_role, $user_roles)){
                               return $display;
                            }
                        }
                        $display = '';
                    }
                }else{
                    $display = '';
                }
                
                break;
        }
        return $display;
	}

	public function upme_profile_shortcode_display($display,$params){
		global $wppcp;
		extract($params);

		if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') || (isset($atts['group']) && $atts['group'] != 'all') || !isset($atts['group']) ){
			return $display;
		}

		$visibility = isset($this->private_content_settings['upme_member_list']['upme_member_list_visibility']) ? $this->private_content_settings['upme_member_list']['upme_member_list_visibility'] : 'all';
		$visible_roles = isset($this->private_content_settings['upme_member_list']['upme_member_list_user_roles']) ? $this->private_content_settings['upme_member_list']['upme_member_list_user_roles'] : array();
	
		switch ($visibility) {
            case 'all':
                break;
            
            case 'guest':
                if(is_user_logged_in()){
                    $display = '';
                }
                break;

            case 'member':
                if(is_user_logged_in()){

                }else{
                    $display = '';
                }
                break;

            case 'role':
                if(is_user_logged_in()){
                    if(count($visible_roles) == 0){
                        $display = '';
                    }else{
                        $user_roles = $wppcp->roles_capability->get_user_roles_by_id($this->current_user);
                        foreach ($visible_roles as  $visible_role ) {
                            if(in_array($visible_role, $user_roles)){
                               return $display;
                            }
                        }
                        $display = '';
                    }
                }else{
                    $display = '';
                }
                
                break;
        }
        return $display;
	}

    public function upme_profile_fields_shortcode_display($display,$params){
        global $wppcp;
        extract($params);

        if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') ){
            return $display;
        }

        $visibility = isset($this->private_content_settings['upme_member_profile']['upme_member_profile_visibility']) ? $this->private_content_settings['upme_member_profile']['upme_member_profile_visibility'] : 'all';
        $visible_roles = isset($this->private_content_settings['upme_member_profile']['upme_member_profile_user_roles']) ? $this->private_content_settings['upme_member_profile']['upme_member_profile_user_roles'] : array();
    
        switch ($visibility) {
            case 'all':
                break;
            
            case 'guest':
                if(is_user_logged_in()){
                    $display = '';
                }
                break;

            case 'member':
                if(is_user_logged_in()){

                }else{
                    $display = '';
                }
                break;

            case 'role':
                if(is_user_logged_in()){
                    if(count($visible_roles) == 0){
                        $display = '';
                    }else{
                        $user_roles = $wppcp->roles_capability->get_user_roles_by_id($this->current_user);
                        foreach ($visible_roles as  $visible_role ) {
                            if(in_array($visible_role, $user_roles)){
                               return $display;
                            }
                        }
                        $display = '';
                    }
                }else{
                    $display = '';
                }
                
                break;
        }
        return $display;
    }
}