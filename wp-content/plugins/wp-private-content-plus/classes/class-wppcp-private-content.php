<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Manage content restriction shortcodes */
class WPPCP_Private_Content{
    
    public $current_user;
    public $private_content_settings;
    
    /* intialize the settings and shortcodes */
    public function __construct(){
        global $wppcp;

        add_action('init', array($this, 'init'));            
      
        add_shortcode('wppcp_private_content', array($this,'private_content_block'));
        add_shortcode('wppcp_private_page', array($this,'private_content_page'));
        add_shortcode('wppcp_guest_content', array($this,'guest_content_block'));
        add_shortcode('wppcp_member_content', array($this,'member_content_block'));
        add_shortcode('wppcp_scheduled_content', array($this,'scheduled_content_block'));

        
    }

    public function init(){
        $this->current_user = get_current_user_id(); 

        $this->private_content_settings  = get_option('wppcp_options'); 
        if ( defined( 'upme_url' ) ) {
            if(isset($this->private_content_settings['upme_general']['private_content_tab_status'])){
                add_filter('upme_profile_tab_items', array($this,'profile_tab_items'),10,2);
                add_filter('upme_profile_view_forms',array($this,'profile_view_forms'),10,2);      
            }
        }
    }
    
    /* Display private content for logged in user */
    public function private_content_page($atts,$content){
        global $wppcp,$wpdb;
        if(isset($atts) && is_array($atts))
            extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        if(!isset($this->private_content_settings['general']['private_content_module_status'])){
            return __('Private content module is disabled.','wppcp');        
        }

        if(is_user_logged_in()){
            if(isset($user_id)){
                $user_id =  $user_id;
            }else{
                $user_id =  $this->current_user;
            }

            $sql  = $wpdb->prepare( "SELECT content FROM " . $wpdb->prefix . WPPCP_PRIVATE_CONTENT_TABLE . " WHERE user_id = %d ", $user_id );
            $result = $wpdb->get_results($sql);

            if($result){
                return stripslashes(do_shortcode($result[0]->content));
            }
        }
            
        return apply_filters('wppcp_private_page_empty_message' , __('No content found.','wppcp'));
        
    }

    /* Restrict content based on user roles, capabilities, user meta values */
    public function private_content_block($atts,$content){
        global $wppcp,$wpdb;

        $this->private_content_settings  = get_option('wppcp_options');  

        if(!isset($this->private_content_settings['general']['private_content_module_status'])){
            return __('Private content module is disabled.','wppcp');        
        }

        
        $private_content_result = array('status'=>true, 'type'=>'admin');
        
        extract(shortcode_atts(array(
            'message' => ''

     	), $atts));
        
        $user_id =  $this->current_user;
        
        // Provide permission for admin to view any content
        if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') ){
        	return $this->get_restriction_message($atts,$content,$private_content_result);
        }
        
        $this->status = $this->guest_filter();
        if(!$this->status){
        	$private_content_result['status'] = false;
        	$private_content_result['type'] = 'guest';
        	return $this->get_restriction_message($atts,$content,$private_content_result);
        }
        
        $visibility = TRUE;
        $message    = '';
        
        // Filter conditions
        foreach ($atts as $sh_attr => $sh_value) {
        	switch ($sh_attr) {
	        	case 'allowed_roles':
	        		$this->status = $this->allowed_roles_filter($atts,$sh_value);
	        		$private_content_result['type'] = $sh_attr;
	        		break;

                case 'blocked_roles':
                    $this->status = $this->blocked_roles_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'allowed_capabilities':
                    $this->status = $this->allowed_capabilities_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'blocked_capabilities':
                    $this->status = $this->blocked_capabilities_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'allowed_meta_keys':
                    $this->status = $this->allowed_meta_key_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'allowed_groups':
                    $this->status = $this->allowed_group_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'blocked_groups':
                    $this->status = $this->blocked_group_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'allowed_users':
                    $this->status = $this->allowed_users_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'blocked_users':
                    $this->status = $this->blocked_users_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;
            }

            if(!$this->status){
                break;
            }
        }
        
        if(!$this->status){
            $private_content_result['status'] = false;        		
        }
        
        return $this->get_restriction_message($atts,$content,$private_content_result);
        
    }
    
    /* Check whether user is a guest or member */
    public function guest_filter(){
		if (!is_user_logged_in())
			return false;
		return true;
	}
    
    /* Filter allowed user roles and restrict content */
    public function allowed_roles_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

		$user_roles = $wppcp->roles_capability->get_user_roles_by_id($this->current_user);
        $roles = explode(',',$sh_value);
        
        // Checking for multiple roles
        if(is_array($roles) && count($roles) > 1){
            
            
            if(isset($role_operator) && strtoupper(trim($role_operator)) == 'AND'){
                $role_operator = 'AND';
            }else{
                $role_operator = 'OR';
            }
            
            $multiple_role_checker = 0;
            foreach ($roles as $role) {
                if($role_operator == 'OR'){
                    if(in_array($role, $user_roles)){
                        
                        return true;
                    }
                }else{
                    if(in_array($role, $user_roles)){
                        $multiple_role_checker++;
                        if($multiple_role_checker == count($roles) ){
                            
                            return true;
                        }
                    }
                }                
            }
        }
        
        // Checking for role levels
        if(is_array($roles) && count($roles) == 1){
            
            foreach ($roles as $role) {
                $role_level = explode('-',$role);
                if(count($role_level) == '2'){
                    
                    $role_hierarchy = isset($this->private_content_settings['role_hierarchy']['hierarchy']) ? $this->private_content_settings['role_hierarchy']['hierarchy'] : '';
                    if($role_hierarchy == ''){
                        return false;
                    }
                    
                    $user_role_level = $role_level[0];
                    $key = array_search($user_role_level, $role_hierarchy);
                    
                    switch($role_level[1]){
                        case 'plus':
                            $allowed_roles = array_slice($role_hierarchy, 0, (int)$key + 1);
                        
                            foreach($allowed_roles as $allowed_role){
                                if(in_array($allowed_role, $user_roles)){
                                   return true;
                                }
                            }
                            break;
                        case 'minus':
                            $allowed_roles = array_slice($role_hierarchy, $key);
                            foreach($allowed_roles as $allowed_role){
                                if(in_array($allowed_role, $user_roles)){
                                    return true;
                                }
                            }
                            break;
                    }
                }else{
                    if(in_array($role, $user_roles)){
                        return true;
                    }
                }                                
            }
        }

        
     
		return false;
    }

    /* Filter blocked user roles and restrict content */
    public function blocked_roles_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        $user_roles = $wppcp->roles_capability->get_user_roles_by_id($this->current_user);
        $roles = explode(',',$sh_value);
        
        // Checking for multiple roles
        if(is_array($roles) && count($roles) > 1){
            
            
            if(isset($role_operator) && strtoupper(trim($role_operator)) == 'AND'){
                $role_operator = 'AND';
            }else{
                $role_operator = 'OR';
            }
            
            $multiple_role_checker = 0;
            foreach ($roles as $role) {
                if($role_operator == 'OR'){
                    if(in_array($role, $user_roles)){
                        
                        return false;
                    }
                }else{
                    
                    if(in_array($role, $user_roles)){
                        $multiple_role_checker++;
                  
                        if($multiple_role_checker == count($roles) ){
                            
                            return false;
                        }
                    }
                }                
            }
        }
        
        // Checking for role levels
        if(is_array($roles) && count($roles) == 1){
            
            foreach ($roles as $role) {
                $role_level = explode('-',$role);
                if(count($role_level) == '2'){
                    
                    $role_hierarchy = isset($this->private_content_settings['role_hierarchy']['hierarchy']) ? $this->private_content_settings['role_hierarchy']['hierarchy'] : '';
                    if($role_hierarchy == ''){
                        return false;
                    }
                    
                    $user_role_level = $role_level[0];
                    $key = array_search($user_role_level, $role_hierarchy);
                    
                    switch($role_level[1]){
                        case 'plus':
                            $allowed_roles = array_slice($role_hierarchy, 0, (int)$key + 1);
                        
                            foreach($allowed_roles as $allowed_role){
                                if(in_array($allowed_role, $user_roles)){
                                   return false;
                                }
                            }
                            break;
                        case 'minus':
                            $allowed_roles = array_slice($role_hierarchy, $key);
                            foreach($allowed_roles as $allowed_role){
                                if(in_array($allowed_role, $user_roles)){
                                    return false;
                                }
                            }
                            break;
                    }
                }else{
                    if(in_array($role, $user_roles)){
                        return false;
                    }
                }                                
            }
        }

        
     
        return true;
    }

    /* Filter allowed capabilities and restrict content */
    public function allowed_capabilities_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        //$user_capabilities = $wppcp->roles_capability->get_user_capabilities_by_id($this->current_user);
        $capabilities = explode(',',$sh_value);
        
        // Checking for multiple capabilities
        if(is_array($capabilities) && count($capabilities) > 1){
            
            
            if(isset($capability_operator) && strtoupper(trim($capability_operator)) == 'AND'){
                $capability_operator = 'AND';
            }else{
                $capability_operator = 'OR';
            }
            
            $multiple_capability_checker = 0;
            foreach ($capabilities as $capability) {
                if($capability_operator == 'OR'){
                    if(current_user_can($capability)){                        
                        return true;
                    }
                }else{
                    if(current_user_can($capability)){ 
                        $multiple_capability_checker++;
                        if($multiple_capability_checker == count($capabilities) ){
                            
                            return true;
                        }
                    }
                }                
            }
        }
        
        // Checking for single capability
        if(is_array($capabilities) && count($capabilities) == 1){
            
            foreach ($capabilities as $capability) {
                if(current_user_can($capability)){     
                    return true;
                }
            
            }
        }

        
     
        return false;
    }

    /* Filter blocked capabilities and restrict content */
    public function blocked_capabilities_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $capabilities = explode(',',$sh_value);
        
        // Checking for multiple capabilities
        if(is_array($capabilities) && count($capabilities) > 1){
            
            
            if(isset($capability_operator) && strtoupper(trim($capability_operator)) == 'AND'){
                $capability_operator = 'AND';
            }else{
                $capability_operator = 'OR';
            }
            
            $multiple_capability_checker = 0;
            foreach ($capabilities as $capability) {
                if($capability_operator == 'OR'){
                    if(current_user_can($capability)){                        
                        return false;
                    }
                }else{
                    if(current_user_can($capability)){ 
                        $multiple_capability_checker++;
                        if($multiple_capability_checker == count($capabilities) ){
                            
                            return false;
                        }
                    }
                }                
            }
        }
        
        // Checking for single capability
        if(is_array($capabilities) && count($capabilities) == 1){
            
            foreach ($capabilities as $capability) {
                if(current_user_can($capability)){     
                    return false;
                }
            
            }
        }
     
        return true;
    }

    /* Filter allowed user meta keys and restrict content */
    public function allowed_meta_key_filter($args,$sh_value){
        extract($args);

        $meta_keys = explode(',',$sh_value);
        
        if(is_array($meta_keys)){
            $allowed_meta_values = isset($allowed_meta_values) ? $allowed_meta_values : '';
            $allowed_meta_operator = isset($allowed_meta_operator) ? strtolower($allowed_meta_operator) : 'AND';

            $meta_count = 0;
            $meta_values = explode(',',$allowed_meta_values);
            foreach ($meta_keys as $k => $meta_key) {
                $value = get_user_meta($this->current_user,trim($meta_key),true);

                if(count($meta_keys) == 1 && count($meta_values) > 1){
                    foreach ($meta_values as $meta_values_key => $meta_values_data) {
                        if(strtolower(trim($value)) == strtolower(trim($meta_values_data))){
                            return true;        
                        }
                    }
       
                }else{
     
                    if(strtoupper($allowed_meta_operator) == 'OR'){
                        if(strtolower(trim($value)) == strtolower(trim($meta_values[$k]))){
                            return true;        
                        }
                    }else{
                        if(strtolower(trim($value)) == strtolower(trim($meta_values[$k]))){
                            $meta_count++;
                            if($meta_count == count($meta_keys)){
                                return true;      
                            }        
                        }
                    }
                }
                
            }
        }

        
        
        return false;
    }
    
    /* Generate content restriction message */
    public function get_restriction_message($args,$content,$private_content_result){
		$display = null;

        /* Arguments */
        $defaults = array(
            'message' => ''
        );
        $args = wp_parse_args($args, $defaults);
        extract($args, EXTR_SKIP);

        /* Require login */
        if (!$private_content_result['status']) {

            if ($message != '') {

            	switch ($private_content_result['type']) {
            		case 'guest':
            			$display .= __('Login to access this content','wppcp');
            			break;
            		
            		case 'allowed_roles':
            		case 'blocked_roles':
            		case 'allowed_users':
            		case 'blocked_users':
            		case 'allowed_meta_key':
            		case 'blocked_meta_key':
                    case 'allowed_groups':
                    case 'blocked_groups':
                    case 'start_date':
                    case 'end_date':
		                $display .= $message;
		        		break;
		        	
                    case 'admin':
                        $display .= do_shortcode($content);
                        break;
            	}                

                               
            }else{

                $restriction_params = array( 'args' => $args, 'content' => $content, 'private_content_result' => $private_content_result);
                $display .= apply_filters('wppcp_content_restricted_default_message',__('You don\'t have permission to access this content','wppcp'),$restriction_params);
            }
        } else { 
            $display .= do_shortcode($content);
        }

        $restriction_params = array( 'args' => $args, 'content' => $content, 'private_content_result' => $private_content_result);
        $display = apply_filters('wppcp_content_restricted_message',$display, $restriction_params );

        return $display;
	}    

    public function guest_content_block($atts,$content){
        global $wppcp,$wpdb;

        $this->private_content_settings  = get_option('wppcp_options');  

        if(!isset($this->private_content_settings['general']['private_content_module_status'])){
            return __('Private content module is disabled.','wppcp');        
        }
        
        $private_content_result = array('status'=>true, 'type'=>'admin');
        
        extract(shortcode_atts(array(
            'message' => ''

        ), $atts));
        
        // Provide permission for admin to view any content
        if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') ){
            return $this->get_restriction_message($atts,$content,$private_content_result);
        }
        
        if($this->guest_filter()){
            return $message;
        }else{
            return do_shortcode($content);
        }      
        
    }

    public function member_content_block($atts,$content){
        global $wppcp,$wpdb;

        $this->private_content_settings  = get_option('wppcp_options');  

        if(!isset($this->private_content_settings['general']['private_content_module_status'])){
            return __('Private content module is disabled.','wppcp');        
        }
        
        $private_content_result = array('status'=>true, 'type'=>'admin');
        
        extract(shortcode_atts(array(
            'message' => ''

        ), $atts));
        
        // Provide permission for admin to view any content
        if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') ){
            return $this->get_restriction_message($atts,$content,$private_content_result);
        }
        
        if(!$this->guest_filter()){
            return $message;
        }else{
            return do_shortcode($content);
        }      
        
    }

    public function profile_tab_items($display,$params){
        extract($params);
        
        $userid = get_current_user_id();        
   
        if( is_user_logged_in() && ($userid == $id || current_user_can('manage_options') || current_user_can('wppcp_manage_options') ) ){
            $display .= '<div class="upme-profile-tab" data-tab-id="upme-private-page-panel" >
                        <i class="upme-profile-icon upme-icon-lock"></i>
                        <div class="upme-profile-tab-title">'.apply_filters('wppcp_profile_tab_items_private_page_title', __('My Private Page','upfp'),$params).'</div>
                    
                    </div>';
        }        

        return $display;
    }

    public function profile_view_forms($display,$params){
        extract($params);

        wp_enqueue_script('wppcp_front_js');

        if($view != 'compact'){
                   
            $display .= '<div id="upme-private-page-panel" class="upme-profile-tab-panel upme-private-page-panel upme-private-page-tab-panel" style="display:none;"  >
                            <div style="padding:20px;">'.do_shortcode("[wppcp_private_page user_id='".$id."' ]").'</div>       
                        </div>';
        
        }

        return $display;
    }

    public function allowed_group_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        $user_groups = $wppcp->groups->get_user_groups_by_id($this->current_user);
        $groups = explode(',',$sh_value);

        if(is_array($groups) && count($groups) > 0){  
            foreach ($groups as $group) {
                if(in_array($group, $user_groups)){
                   return true;
                }             
            }
        }        
     
        return false;
    }

    public function blocked_group_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        $user_groups = $wppcp->groups->get_user_groups_by_id($this->current_user);
        $groups = explode(',',$sh_value);

        if(is_array($groups) && count($groups) > 0){
            foreach ($groups as $group) {
                if(in_array($group, $user_groups)){
                   return false;
                }             
            }
        }        
     
        return true;
    }

    public function allowed_users_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        $user_id = get_current_user_id();
        $users = explode(',',$sh_value);

        if(is_array($users) && count($users) > 0){  
            foreach ($users as $user) {
                if($user == $user_id){
                   return true;
                }             
            }
        }        
     
        return false;
    }

    public function blocked_users_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        $user_id = get_current_user_id();
        $users = explode(',',$sh_value);

        if(is_array($users) && count($users) > 0){  
            foreach ($users as $user) {
                if($user == $user_id){
                   return false;
                }             
            }
        }        
     
        return true;
    }

    public function scheduled_content_block($atts,$content){
        global $wppcp,$wpdb;

        $this->private_content_settings  = get_option('wppcp_options');  

        if(!isset($this->private_content_settings['general']['private_content_module_status'])){
            return __('Private content module is disabled.','wppcp');        
        }
        
        $private_content_result = array('status'=>true, 'type'=>'admin');
        
        extract(shortcode_atts(array(
            'message' => ''

        ), $atts));
        
        $user_id =  $this->current_user;
        
        // Provide permission for admin to view any content
        if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') ){
            return $this->get_restriction_message($atts,$content,$private_content_result);
        }

        foreach ($atts as $sh_attr => $sh_value) {
            switch ($sh_attr) {
                case 'start_date':
                    $this->status = $this->start_date_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;

                case 'end_date':
                    $this->status = $this->end_date_filter($atts,$sh_value);
                    $private_content_result['type'] = $sh_attr;
                    break;
            }
        }

        if(!$this->status){
            $private_content_result['status'] = false;              
        }else{
            $content = $this->private_content_block($atts,$content);
            return $content;
        }
        
        return $this->get_restriction_message($atts,$content,$private_content_result);
             
    }

    public function start_date_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        $start_date = date("Y-m-d",strtotime($sh_value));
        $start_date = strtotime($start_date);
        $current_time = strtotime(date("Y-m-d H:i:s"));


        if($current_time >= $start_date){
            return true;
        }        
       
        return false;
    }

    public function end_date_filter($atts,$sh_value){
        global $wppcp;
        extract($atts);

        $this->private_content_settings  = get_option('wppcp_options');  

        $end_date = date("Y-m-d",strtotime($sh_value));
        $end_date = strtotime($end_date);
        $current_time = strtotime(date("Y-m-d"));


        if($current_time <= $end_date){
            return true;
        }        
       
        return false;
    }

}