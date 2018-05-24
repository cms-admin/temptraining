<?php

class WPPCP_Search{

	public function __construct(){
		

		add_action('pre_get_posts', array($this,'search_restrictions'));
	}

	public function search_restrictions($query) {
		
		$wppcp_options = get_option('wppcp_options');

		if(isset($wppcp_options['general']['search_restrictions_module_status'])){    
         
			if ($query->is_search && $query->is_main_query() && !is_admin()) {	
				$search_blocked_ids = $this->get_globally_blocked_posts();
				$search_allowed_types = $this->verify_search_restrictions();

		   		$query->set('post__not_in', $search_blocked_ids );
		   		$query->set('post_type', $search_allowed_types );
			}
		}

		$query = apply_filters('wppcp_search_restrictions_query',$query, array('wppcp_options' => $wppcp_options) );

		return $query;
	}

	public function get_globally_blocked_posts(){
		global $wppcp;

		$wppcp_options = get_option('wppcp_options');

		if(!isset($wppcp_options['general']['private_content_module_status'])){
            return;        
        }


		$general_options =  isset($wppcp_options['search_general']) ? (array) $wppcp_options['search_general'] : array();
		$search_blocked_ids = array();

		$blocked_posts = isset( $general_options['blocked_post_search'] ) ? (array) $general_options['blocked_post_search'] : array();
		$blocked_pages = isset( $general_options['blocked_page_search'] ) ? (array) $general_options['blocked_page_search'] : array();

		$search_blocked_ids = array_merge($search_blocked_ids, $blocked_posts , $blocked_pages);

        $search_blocked_ids = apply_filters('wppcp_search_blocked_post_ids',$search_blocked_ids, array('general_options' => $general_options));
		
        
        return $search_blocked_ids;
	}

	public function verify_search_restrictions(){
		global $wppcp;

		$wppcp_options = get_option('wppcp_options');
		$data = isset($wppcp_options['search_restrictions']) ? $wppcp_options['search_restrictions'] : array() ;

		if(!isset($wppcp_options['general']['private_content_module_status'])){
            return;        
        }

        $allowed_types = array();
        if(!is_user_logged_in()){
        	// Guest
        	$everyone_search_types = isset($data['everyone_search_types']) ? (array) $data['everyone_search_types'] :array();
            $guests_search_types = isset($data['guests_search_types']) ? (array) $data['guests_search_types'] :array();
            $allowed_types = array_merge($allowed_types,$guests_search_types,$everyone_search_types);
        }else{
        	$user_id = get_current_user_id();
        	$roles = $wppcp->roles_capability->get_user_roles_by_id($user_id);

        	foreach ($roles as $role ) {
        		$role_search_types = isset($data[$role.'_search_types']) ? (array) $data[$role.'_search_types'] :array();
        	
                $allowed_types = array_merge($allowed_types,$role_search_types);
        	}

        	$everyone_search_types = isset($data['everyone_search_types']) ? (array) $data['everyone_search_types'] :array();
            
            $members_search_types = isset($data['members_search_types']) ? (array) $data['members_search_types'] :array();
            $allowed_types = array_merge($allowed_types,$members_search_types,$everyone_search_types);
        }

        $allowed_types = array_unique($allowed_types);
        
        return $allowed_types;

    }

}