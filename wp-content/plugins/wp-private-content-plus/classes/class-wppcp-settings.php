<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Manage settings of WP Private Content Plus plugin */
class WPPCP_Settings{
    
    public $template_locations;
    public $current_user;
    
    /* Intialize actions for plugin settings */
    public function __construct(){
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array(&$this, 'admin_settings_menu'), 9);
        add_action('init', array($this,'save_settings_page') );
        
        add_action('wp_ajax_wppcp_load_private_page_users', array($this, 'wppcp_load_private_page_users'));
        add_action('wp_ajax_wppcp_save_user_role_hierarchy', array($this, 'wppcp_save_user_role_hierarchy'));

        add_action('wp_ajax_wppcp_load_restriction_users', array($this, 'wppcp_load_restriction_users'));
        add_action('admin_enqueue_scripts', array($this,'wppcp_conditional_scripts'));
        add_action('admin_footer', array($this,'wppcp_deactivate_popup'));

        /* Welcome Screen */
        add_action( 'admin_menu', array( $this, 'welcome_plus' ));
        add_action( 'admin_head', array( $this, 'hide_welcome' ));
        add_action( 'admin_init', array( $this, 'display_welcome_screen'  ), 9999 );
    }

    public function welcome_plus(){
        add_dashboard_page(
            __( 'Welcome to WP Private Content Plus', 'wppcp' ),
            __( 'Welcome to WP Private Content Plus', 'wppcp' ),
            apply_filters( 'wppcp_welcome_cap', 'manage_options' ),
            'wppcp-welcome-screen',
            array( $this, 'welcome_screen' )
        );
    }

    public function hide_welcome() {
        remove_submenu_page( 'index.php', 'wppcp-welcome-screen' );
    }

    public function welcome_screen(){
        global $wppcp;

        ob_start();
        $wppcp->template_loader->get_template_part( 'welcome-screen');
        $display = ob_get_clean();
        echo $display;    
    }

    public function display_welcome_screen(){
        if ( ! get_transient( 'wppcp_welcome_redirect' ) ) {
            return;
        }

        delete_transient( 'wppcp_welcome_redirect' );
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'index.php?page=wppcp-welcome-screen' ) );
    }

    public function init(){
        $this->current_user = get_current_user_id();

        $this->wppcp_options = get_option('wppcp_options');        
    }
    
    /*  Save settings tabs */
    public function save_settings_page(){

        if(!is_admin())
            return;
        
        $wppcp_settings_pages = array('wppcp-settings','wppcp-search-settings-page','wppcp-password-settings-page','wppcp-global-restrictions','wppcp-upme-settings',
            'wppcp-security-settings-page');
        if(isset($_POST['wppcp_tab']) && isset($_GET['page']) && in_array($_GET['page'],$wppcp_settings_pages)){
            $tab = '';
            if ( isset ( $_POST['wppcp_tab'] ) )
               $tab = $_POST['wppcp_tab']; 

            if($tab != ''){
                $func = 'save_'.$tab;
                
                if(method_exists($this,$func))
                    $this->$func();
            }
        }
    }
    
    /* Include necessary js and CSS files for admin section */
    public function include_scripts(){

        wp_register_style('wppcp_admin_css', WPPCP_PLUGIN_URL . 'css/wppcp-admin.css');
        wp_enqueue_style('wppcp_admin_css');

        $wppcp_plugin_data = (array) get_option('wppcp_plugin_data');
        if(!isset($wppcp_plugin_data['init_deactivation'])){
            $init_deactivation = 'no';
        }else{
            $init_deactivation = 'yes';
        }
        
        wp_register_script('wppcp_admin_js', WPPCP_PLUGIN_URL . 'js/wppcp-admin.js', array('jquery','jquery-ui-sortable'));
        wp_enqueue_script('wppcp_admin_js');
        
        $custom_js_strings = array(        
            'AdminAjax' => admin_url('admin-ajax.php'),
            'images_path' =>  WPPCP_PLUGIN_URL . 'images/',
            'init_deactivation' => $init_deactivation,
            'Messages'  => array(
                                'userEmpty' => __('Please select a user.','wppcp'),
                                'addToPost' => __('Add to Post','wppcp'), 
                                'insertToPost' => __('Insert Files to Post','wppcp'),   
                                'removeGroupUser' => __('Removing User...','wppcp')     
                            ),
        );

        wp_localize_script('wppcp_admin_js', 'WPPCPAdmin', $custom_js_strings);
    }
    
    /* Intialize settings page and tabs */
    public function admin_settings_menu(){
        global $submenu;

        add_action('admin_enqueue_scripts', array($this,'include_scripts'));
        
        add_menu_page(__('Private Content Settings', 'wppcp' ), __('Private Content Settings', 'wppcp' ),
            apply_filters('wppcp_main_settings_page_capability','manage_options',array()),'wppcp-settings',array(&$this,'settings'));
        
        add_submenu_page('wppcp-settings',__('Global Restrictions', 'wppcp' ), __('Global Restrictions', 'wppcp' ),
            apply_filters('wppcp_global_restrictions_settings_page_capability','manage_options',array()),'wppcp-global-restrictions',array(&$this,'global_restrictions_settings'));
        
        add_submenu_page('wppcp-settings', __('Search', 'wppcp' ), __('Search Settings', 'wppcp'),
            apply_filters('wppcp_search_settings_page_capability','manage_options',array()),'wppcp-search-settings-page',array(&$this,'search_settings'));
       
        add_submenu_page('wppcp-settings', __('Password', 'wppcp' ), __('Password Settings', 'wppcp'),
            apply_filters('wppcp_password_settings_page_capability','manage_options',array()),'wppcp-password-settings-page',array(&$this,'password_settings'));
       
        add_submenu_page('wppcp-settings', __('Private User Page', 'wppcp' ), __('Private User Page', 'wppcp'),
            apply_filters('wppcp_private_page_settings_page_capability','manage_options',array()),'wppcp-private-user-page',array(&$this,'private_user_page'));
        
        add_submenu_page('wppcp-settings', __('Security Settings', 'wppcp' ), __('Security Settings', 'wppcp'),
            apply_filters('wppcp_security_settings_page_capability','manage_options',array()),'wppcp-security-settings-page',array(&$this,'security_settings'));
        
        // add_submenu_page('wppcp-settings', __('Social Locker', 'wppcp' ), __('Social Locker', 'wppcp'),'manage_options','wppcp-social-locker-settings',array(&$this,'social_locker_settings'));
        add_submenu_page('wppcp-settings', __('User Profiles Made Easy', 'wppcp' ), __('User Profiles Made Easy', 'wppcp'),
            apply_filters('wppcp_upme_settings_page_capability','manage_options',array()),'wppcp-upme-settings',array(&$this,'upme_settings'));
       
        add_submenu_page('wppcp-settings', __('Getting Started', 'wppcp' ), __('Getting Started', 'wppcp'),
            'manage_options','wppcp-help',array(&$this,'help'));
       
        add_submenu_page('wppcp-settings', __('PRO Version', 'wppcp' ), __('PRO Version', 'wppcp'),
            'manage_options','wppcp-pro',array(&$this,'pro'));
       
        $url = 'http://www.wpexpertdeveloper.com/request-pro-version-trial/';    
        $label = __('Try PRO Version', 'wppcp');
        $submenu['wppcp-settings'][] = array( $label , 'manage_options', $url);
        
        add_submenu_page('wppcp-settings', __('Addons', 'wppcp' ), __('Addons', 'wppcp'),
            'manage_options','wppcp-pro-addons',array(&$this,'pro_addons'));
       
        $url = 'http://www.wpexpertdeveloper.com/wp-private-content-plus-faq';    
        $label = __('FAQ', 'wppcp');
        $submenu['wppcp-settings'][] = array( $label , 'manage_options', $url);

        
        
    }  
    
    /* Display settings */
    public function settings(){
        global $wppcp,$wppcp_settings_data;
        
        add_settings_section( 'wppcp_section_general', __('General Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-general' );
        add_settings_section( 'wppcp_section_information', __('Information Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-general' );
        add_settings_section( 'wppcp_section_user_role_hierarchy', __('User Role Hierarchy','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-general' );
        add_settings_section( 'wppcp_section_wppcp_permissions', __('Restriction Permissions','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-general' );
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wppcp_section_general';
        $wppcp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('general',$tab);
   
        $wppcp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $wppcp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
		$wppcp->template_loader->get_template_part( 'menu-page-container');
		$display = ob_get_clean();
		echo $display;
        
    
    }

    public function global_restrictions_settings(){
        global $wppcp,$wppcp_settings_data;
        
        add_settings_section( 'wppcp_section_global_post', __('Post Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-general' );
        
        add_settings_section( 'wppcp_section_global_page', __('Page Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-general' );
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wppcp_section_global_post';
        $wppcp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('global_restrictions',$tab);
   
        $wppcp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $wppcp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
        $wppcp->template_loader->get_template_part( 'menu-page-container');
        $display = ob_get_clean();
        echo $display;
        
    
    }
    
    /* Manage settings tabs for the plugin */
    public function plugin_options_tabs($type,$tab) {
        $current_tab = $tab;
        $this->plugin_settings_tabs = array();
        
        switch($type){

            case 'general':
                $this->plugin_settings_tabs['wppcp_section_general']  = __('General Settings','wppcp');
                $this->plugin_settings_tabs['wppcp_section_information']  = __('Information Settings','wppcp');
                $this->plugin_settings_tabs['wppcp_section_user_role_hierarchy']  = __('User Role Hierarchy','wppcp');
                $this->plugin_settings_tabs['wppcp_section_wppcp_permissions']  = __('Restriction Permissions','wppcp');
                break;

            case 'global_restrictions':
                $this->plugin_settings_tabs['wppcp_section_global_post']  = __('Post Settings','wppcp');
                $this->plugin_settings_tabs['wppcp_section_global_page']  = __('Page Settings','wppcp');
                break;   

            case 'search':
                $this->plugin_settings_tabs['wppcp_section_search_general']  = __('Search Settings','wppcp');
                $this->plugin_settings_tabs['wppcp_section_search_restrictions']  = __('Search Restrictions','wppcp');
                break;

            case 'security':
                $this->plugin_settings_tabs['wppcp_section_security_ip']  = __('IP Restrictions','wppcp');
                break;

            case 'password':
                $this->plugin_settings_tabs['wppcp_section_password_global']  = __('Password Settings','wppcp');                
                break;  

            case 'social_locker_general':
                $this->plugin_settings_tabs['wppcp_section_social_locker_general']  = __('General Settings','wppcp');                
                break;       

            case 'upme_general':
                $this->plugin_settings_tabs['wppcp_section_upme_general']  = __('UPME General Settings','wppcp');                
                $this->plugin_settings_tabs['wppcp_section_upme_search']  = __('UPME Search Settings','wppcp');                
                $this->plugin_settings_tabs['wppcp_section_upme_member_list']  = __('UPME Member List Settings','wppcp');                
                $this->plugin_settings_tabs['wppcp_section_upme_member_profile']  = __('UPME Member Profile Settings','wppcp');                
                break;  

              

        }
        
        ob_start();
        ?>

        <h2 class="nav-tab-wrapper">
        <?php 
            foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            $page = isset($_GET['page']) ? $_GET['page'] : '';
        ?>
                <a class="nav-tab <?php echo $active; ?> " href="?page=<?php echo $page; ?>&tab=<?php echo $tab_key; ?>"><?php echo $tab_caption; ?></a>
            
        <?php } ?>
        </h2>

        <?php
                
        return ob_get_clean();
    }
    
    /* Manage settings tab contents for the plugin */
    public function plugin_options_tab_content($tab,$params = array()){
        global $wppcp,$wppcp_settings_data,$wppcp_search_settings_data,$wppcp_password_settings_data,
        $wppcp_security_settings_data;
        
        $post_types = $wppcp->posts->get_post_types();

        $private_content_settings = get_option('wppcp_options');

        $this->load_wppcp_select2_scripts_style();
        
        ob_start();
        switch($tab){
            case 'wppcp_section_general':                
	            $data = isset($private_content_settings['general']) ? $private_content_settings['general'] : array();
      
                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['private_content_module_status'] = isset($data['private_content_module_status']) ? $data['private_content_module_status'] :'0';
                $wppcp_settings_data['post_page_redirect_url'] = isset($data['post_page_redirect_url']) ? $data['post_page_redirect_url'] :'';
                $wppcp_settings_data['search_restrictions_module_status'] = isset($data['search_restrictions_module_status']) ? $data['search_restrictions_module_status'] :'0';
                $wppcp_settings_data['dashboard_restrictions_widget_status'] = isset($data['dashboard_restrictions_widget_status']) ? $data['dashboard_restrictions_widget_status'] :'0';
    
                $wppcp->template_loader->get_template_part('general-settings');            
                break;

            case 'wppcp_section_information':                
                $data = isset($private_content_settings['information']) ? $private_content_settings['information'] : array();
      
                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['pro_info_post_restrictions'] = isset($data['pro_info_post_restrictions']) ? $data['pro_info_post_restrictions'] :'1';
                $wppcp_settings_data['pro_info_post_attachments'] = isset($data['pro_info_post_attachments']) ? $data['pro_info_post_attachments'] :'1';
                $wppcp_settings_data['pro_info_search_restrictions'] = isset($data['pro_info_search_restrictions']) ? $data['pro_info_search_restrictions'] :'1';
                $wppcp_settings_data['pro_info_private_page'] = isset($data['pro_info_private_page']) ? $data['pro_info_private_page'] :'1';
            
                $wppcp->template_loader->get_template_part('information-settings');            
                break;
            
            case 'wppcp_section_user_role_hierarchy':                
                $data = isset($private_content_settings['role_hierarchy']) ? $private_content_settings['role_hierarchy'] : array();
            
                $wppcp_settings_data['hierarchy'] = isset($data['hierarchy']) ? $data['hierarchy'] : array();         
                $wppcp_settings_data['tab'] = $tab;
            
                $wppcp->template_loader->get_template_part('user-role-hierarchy');            
                break;

            case 'wppcp_section_wppcp_permissions':                
                $data = isset($private_content_settings['restriction_permissions']) ? $private_content_settings['restriction_permissions'] : array();
      
                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['wppcp_feature_permission_roles'] = isset($data['wppcp_feature_permission_roles']) ? $data['wppcp_feature_permission_roles'] : array();
                
                $wppcp->template_loader->get_template_part('restriction-permission-settings');            
                break;

            case 'wppcp_section_global_post':                
                $data = isset($private_content_settings['global_post_restriction']) ? $private_content_settings['global_post_restriction'] : array();

                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['restrict_all_posts_status'] = isset($data['restrict_all_posts_status']) ? $data['restrict_all_posts_status'] :'0';
                $wppcp_settings_data['all_post_visibility'] = isset($data['all_post_visibility']) ? $data['all_post_visibility'] :'all';
                $wppcp_settings_data['all_post_user_roles'] = isset($data['all_post_user_roles']) ? $data['all_post_user_roles'] : array();
                             
                $wppcp->template_loader->get_template_part('global-post-restriction-settings');            
                break;

            case 'wppcp_section_global_page':                
                $data = isset($private_content_settings['global_page_restriction']) ? $private_content_settings['global_page_restriction'] : array();

                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['restrict_all_pages_status'] = isset($data['restrict_all_pages_status']) ? $data['restrict_all_pages_status'] :'0';
                $wppcp_settings_data['all_page_visibility'] = isset($data['all_page_visibility']) ? $data['all_page_visibility'] :'all';
                $wppcp_settings_data['all_page_user_roles'] = isset($data['all_page_user_roles']) ? $data['all_page_user_roles'] : array();
                             
                $wppcp->template_loader->get_template_part('global-page-restriction-settings');            
                break;

            // Settings for Search
            case 'wppcp_section_search_general':                
                $data = isset($private_content_settings['search_general']) ? $private_content_settings['search_general'] : array();

                $wppcp_search_settings_data['tab'] = $tab;
                $wppcp_search_settings_data['blocked_post_search'] = isset($data['blocked_post_search']) ? (array) $data['blocked_post_search'] : array();
                $wppcp_search_settings_data['blocked_page_search'] = isset($data['blocked_page_search']) ? (array) $data['blocked_page_search'] : array();
                $wppcp_search_settings_data['post_types'] = $post_types;

                $wppcp_search_settings_data = apply_filters('wppcp_search_setting_data',$wppcp_search_settings_data, array('data' => $data, 'section' => 'wppcp_section_search_general' ) );


                $wppcp->template_loader->get_template_part('search-general-settings');            
                break;

            case 'wppcp_section_search_restrictions':                
                $data = isset($private_content_settings['search_restrictions']) ? $private_content_settings['search_restrictions'] : array();
     // echo "<pre>";print_r($data);exit;
                $wppcp_search_settings_data['tab'] = $tab;
                $wppcp_search_settings_data['everyone_search_types'] = isset($data['everyone_search_types']) ? (array) $data['everyone_search_types'] :array();
                $wppcp_search_settings_data['guests_search_types'] = isset($data['guests_search_types']) ? (array) $data['guests_search_types'] :array();
                $wppcp_search_settings_data['members_search_types'] = isset($data['members_search_types']) ? (array) $data['members_search_types'] :array();
                $wppcp_search_settings_data['data'] = $data; 
                $wppcp_search_settings_data['post_types'] = $post_types;

                $wppcp->template_loader->get_template_part('search-restrictions');            
                break;

            case 'wppcp_section_security_ip':                
                $data = isset($private_content_settings['security_ip']) ? $private_content_settings['security_ip'] : array();

                $wppcp_security_settings_data['tab'] = $tab;
                
                $wppcp_security_settings_data['restriction_status'] = isset($data['restriction_status']) ? $data['restriction_status'] : '';
                $wppcp_security_settings_data['allowed_urls'] = isset($data['allowed_urls']) ? $data['allowed_urls'] : '';
                $wppcp_security_settings_data['whitelisted'] = isset($data['whitelisted']) ? $data['whitelisted'] : '';
                $wppcp_security_settings_data['redirect_url'] = isset($data['redirect_url']) ? $data['redirect_url'] : site_url();

                $wppcp_security_settings_data = apply_filters('wppcp_security_settings_data',$wppcp_security_settings_data, array('data' => $data, 'section' => 'wppcp_section_security_ip' ) );


                $wppcp->template_loader->get_template_part('security-ip-settings');            
                break;

            // Settings for Global Password
            case 'wppcp_section_password_global':                
                $data = isset($private_content_settings['password_global']) ? $private_content_settings['password_global'] : array();

                $wppcp_password_settings_data['tab'] = $tab;
                $wppcp_password_settings_data['global_password_protect'] = isset($data['global_password_protect']) ? $data['global_password_protect'] : 'disabled';
                $wppcp_password_settings_data['global_protect_password'] = isset($data['global_protect_password']) ? $data['global_protect_password'] : '';
                $wppcp_password_settings_data['password_form_title'] = isset($data['password_form_title']) ? $data['password_form_title'] : __('Protected Content','wppcp');
                $wppcp_password_settings_data['password_form_message'] = isset($data['password_form_message']) ? $data['password_form_message'] : __('This content is password protected. Please enter the password to view the content.','wppcp');
                
                $wppcp_password_settings_data['allowed_urls'] = isset($data['allowed_urls']) ? $data['allowed_urls'] : '';
                
                $wppcp_password_settings_data = apply_filters('wppcp_password_setting_data',$wppcp_password_settings_data, array('data' => $data, 'section' => 'wppcp_section_password_global' ) );
                $wppcp->template_loader->get_template_part('password-global-settings');            
                break;

            case 'wppcp_section_social_locker_general':                
                $data = isset($private_content_settings['social_locker_general']) ? $private_content_settings['social_locker_general'] : array();

                // $wppcp_search_settings_data['tab'] = $tab;
                // $wppcp_search_settings_data['everyone_search_types'] = isset($data['everyone_search_types']) ? (array) $data['everyone_search_types'] :array();
                // $wppcp_search_settings_data['guests_search_types'] = isset($data['guests_search_types']) ? (array) $data['guests_search_types'] :array();
                // $wppcp_search_settings_data['members_search_types'] = isset($data['members_search_types']) ? (array) $data['members_search_types'] :array();
                // $wppcp_search_settings_data['data'] = $data; 
                // $wppcp_search_settings_data['post_types'] = $post_types;

                $wppcp->template_loader->get_template_part('social-locker-general');            
                break;

            case 'wppcp_section_upme_general':                
                $data = isset($private_content_settings['upme_general']) ? $private_content_settings['upme_general'] : array();

                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['private_content_tab_status'] = isset($data['private_content_tab_status']) ? $data['private_content_tab_status'] :'0';
                $wppcp_settings_data['redirect_to_upme_login'] = isset($data['redirect_to_upme_login']) ? $data['redirect_to_upme_login'] :'disabled';
                $wppcp->template_loader->get_template_part('upme-general');            
                break;

            case 'wppcp_section_upme_search':                
                $data = isset($private_content_settings['upme_search']) ? $private_content_settings['upme_search'] : array();

                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['upme_search_visibility'] = isset($data['upme_search_visibility']) ? $data['upme_search_visibility'] :'all';
                $wppcp_settings_data['upme_search_user_roles'] = isset($data['upme_search_user_roles']) ? $data['upme_search_user_roles'] :array();
                $wppcp->template_loader->get_template_part('upme-search');            
                break;

            case 'wppcp_section_upme_member_list':                
                $data = isset($private_content_settings['upme_member_list']) ? $private_content_settings['upme_member_list'] : array();

                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['upme_member_list_visibility'] = isset($data['upme_member_list_visibility']) ? $data['upme_member_list_visibility'] :'all';
                $wppcp_settings_data['upme_member_list_user_roles'] = isset($data['upme_member_list_user_roles']) ? $data['upme_member_list_user_roles'] :array();
                $wppcp->template_loader->get_template_part('upme-member-list');            
                break;

            case 'wppcp_section_upme_member_profile':                
                $data = isset($private_content_settings['upme_member_profile']) ? $private_content_settings['upme_member_profile'] : array();

                $wppcp_settings_data['tab'] = $tab;
                $wppcp_settings_data['upme_member_profile_visibility'] = isset($data['upme_member_profile_visibility']) ? $data['upme_member_profile_visibility'] :'all';
                $wppcp_settings_data['upme_member_profile_user_roles'] = isset($data['upme_member_profile_user_roles']) ? $data['upme_member_profile_user_roles'] :array();
                $wppcp->template_loader->get_template_part('upme-member-profile');            
                break;
                
        }
        
        $display = ob_get_clean();
        return $display;
        
    }

    /* Save general settings */
    public function save_wppcp_section_general(){
        global $wppcp;

        if(isset($_POST['wppcp_general'])){
            foreach($_POST['wppcp_general'] as $k=>$v){
                $this->settings[$k] = $v;
            }            
        }
        
        $wppcp_options = get_option('wppcp_options');
        // echo "<pre>";print_r($wppcp_options);exit;
        $wppcp_options['general'] = $this->settings;
        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );  

        
    }

    public function save_wppcp_section_information(){
        global $wppcp;

        if(isset($_POST['wppcp_information'])){
            foreach($_POST['wppcp_information'] as $k=>$v){
                $this->settings[$k] = $v;
            }   

            if(!isset($_POST['wppcp_information']['pro_info_post_restrictions'])){
                $this->settings['pro_info_post_restrictions'] = 0;
            }
            if(!isset($_POST['wppcp_information']['pro_info_post_attachments'])){
                $this->settings['pro_info_post_attachments'] = 0;
            }
            if(!isset($_POST['wppcp_information']['pro_info_search_restrictions'])){
                $this->settings['pro_info_search_restrictions'] = 0;
            }
            if(!isset($_POST['wppcp_information']['pro_info_private_page'])){
                $this->settings['pro_info_private_page'] = 0;
            }         
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['information'] = $this->settings;
        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );  

        
    }

    public function save_wppcp_section_global_post(){
        global $wppcp;

        if(isset($_POST['wppcp_global_post_restriction'])){
            foreach($_POST['wppcp_global_post_restriction'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['global_post_restriction'] = $this->settings;

        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    public function save_wppcp_section_global_page(){
        global $wppcp;

        if(isset($_POST['wppcp_global_page_restriction'])){
            foreach($_POST['wppcp_global_page_restriction'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['global_page_restriction'] = $this->settings;
        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }
    
    
    /* Display private user page add content form */
    public function private_user_page(){
        global $wppcp,$wppcp_private_page_params,$wpdb;
        
        $wppcp_private_page_params = array();
        
        $this->load_wppcp_select2_scripts_style();
        
        $private_page_user = 0;
        if($_POST && isset($_POST['wppcp_private_page_user_load']) && ( current_user_can('manage_options') || current_user_can('wppcp_manage_options') ) ){
            $private_page_user = isset($_POST['wppcp_private_page_user']) ? $_POST['wppcp_private_page_user'] : 0;
            $user = get_user_by( 'id', $private_page_user );
            $wppcp_private_page_params['display_name'] = $user->data->display_name;
            $wppcp_private_page_params['user_id'] = $private_page_user;
        }
        

        
        if($_POST && isset($_POST['wppcp_private_page_content_submit']) && ( current_user_can('manage_options') || current_user_can('wppcp_manage_options') ) ){
            if (isset( $_POST['wppcp_private_page_nonce_field'] ) && wp_verify_nonce( $_POST['wppcp_private_page_nonce_field'], 'wppcp_private_page_nonce' ) ) {
                $user_id = isset($_POST['wppcp_user_id']) ? $_POST['wppcp_user_id'] : 0; 
                $private_content = isset($_POST['wppcp_private_page_content']) ? $_POST['wppcp_private_page_content'] : '';
                $updated_date = date("Y-m-d H:i:s");
                
                $sql  = $wpdb->prepare( "SELECT content FROM " . $wpdb->prefix . WPPCP_PRIVATE_CONTENT_TABLE . " WHERE user_id = %d ", $user_id );
                $result = $wpdb->get_results($sql);
                if($result){
                    $sql  = $wpdb->prepare( "Update " . $wpdb->prefix . WPPCP_PRIVATE_CONTENT_TABLE ." set content=%s,updated_at=%s where user_id=%d ", $private_content,$updated_date, $user_id );
                }else{
                    $sql  = $wpdb->prepare( "Insert into " . $wpdb->prefix . WPPCP_PRIVATE_CONTENT_TABLE ."(user_id,content,type,updated_at) values(%d,%s,%s,%s)", $user_id, $private_content, 'ADMIN', $updated_date );
                }
                
                
                if($wpdb->query($sql) === FALSE){
                    $wppcp_private_page_params['message'] = __('Private content update failed.','wppcp');
                    $wppcp_private_page_params['message_status'] = FALSE;
                }else{
                    $wppcp_private_page_params['message'] = __('Private content updated successfully.','wppcp');
                    $wppcp_private_page_params['message_status'] = TRUE;
                }        
            }
        }
        
        $sql  = $wpdb->prepare( "SELECT content FROM " . $wpdb->prefix . WPPCP_PRIVATE_CONTENT_TABLE . " WHERE user_id = %d ", $private_page_user );
        $result = $wpdb->get_results($sql);
        if($result){
            $wppcp_private_page_params['private_content'] = stripslashes($result[0]->content);
        }else{
            $wppcp_private_page_params['private_content'] = '';
        }
        
        
        
        
        ob_start();
        $wppcp->template_loader->get_template_part('private-user-page');
        $display = ob_get_clean();        
        echo $display;
    }
    
    /* Load Select 2 library for settings section */
    public function load_wppcp_select2_scripts_style(){          

        wp_register_script('wppcp_select2_js', WPPCP_PLUGIN_URL . 'js/select2/wppcp-select2.min.js');
        wp_enqueue_script('wppcp_select2_js');
        
        wp_register_style('wppcp_select2_css', WPPCP_PLUGIN_URL . 'js/select2/wppcp-select2.min.css');
        wp_enqueue_style('wppcp_select2_css');

    }
    
    /* Get the users for the private page content form */
    public function wppcp_load_private_page_users(){
        global $wpdb;
        
        $search_text  = isset($_POST['q']) ? $_POST['q'] : '';
        
        $args = array('number' => 20);
        if($search_text != ''){
            $args['search'] = "*".$search_text."*";
        }
        
        $user_results = array();
        $user_json_results = array();
        
        $user_query = new WP_User_Query( $args );
        $user_results = $user_query->get_results();

        foreach($user_results as $user){
            if($user->ID != $this->current_user){
                array_push($user_json_results , array('id' => $user->ID, 'name' => $user->data->display_name) ) ;
            }
                       
        }
        
        echo json_encode(array('items' => $user_json_results ));exit;
    }  
    
    
    /* Save user role hierarchy of the site */
    public function wppcp_save_user_role_hierarchy(){
        global $wppcp,$user_role_hierarchy_result;
        if(is_user_logged_in() && ( current_user_can('manage_options') || current_user_can('wppcp_manage_options') )){
            $private_content_settings = get_option('wppcp_options');

            $user_role_hierarchy = isset($_POST['user_role_hierarchy']) ? $_POST['user_role_hierarchy'] : array();
            
            $private_content_settings['role_hierarchy']['hierarchy'] = $user_role_hierarchy;
            
            update_option('wppcp_options',$private_content_settings);
            
            $result = array('status' => 'success', 'msg' => __('Role Hierarchy saved succefully.','wppcp'));
            
        }else{
            $result = array('status' => 'error', 'msg' => __('Role Hierarchy save failed.','wppcp'));
        }
        
        echo json_encode($result);exit;
    }

    public function save_wppcp_section_wppcp_permissions(){
        global $wppcp,$wp_roles;

        if(isset($_POST['wppcp_feature_restrictions'])){
            foreach($_POST['wppcp_feature_restrictions'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
            $res_user_roles = isset($_POST['wppcp_feature_restrictions']['wppcp_feature_permission_roles']) ?
                (array) $_POST['wppcp_feature_restrictions']['wppcp_feature_permission_roles'] : array();

            $user_roles = $wppcp->roles_capability->wppcp_user_roles();

            foreach($user_roles as $role_key => $role){
                $wp_roles->remove_cap($role_key, 'wppcp_manage_options');
            }

            foreach ($res_user_roles as $key ) {
                $wp_roles->add_cap( $key, 'wppcp_manage_options' ); 
            }

            $wp_roles->add_cap( 'administrator', 'wppcp_manage_options' ); 
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['restriction_permissions'] = $this->settings;

        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }
    
    /* Display settings saved message */  
    public function admin_notices(){
        ?>
        <div class="updated">
          <p><?php esc_html_e( 'Settings saved successfully.', 'wppcp' ); ?></p>
       </div>
        <?php
    }

    /* Help and information about the plugin */
    public function help(){
        global $wppcp;
        ob_start();
        $wppcp->template_loader->get_template_part('plugin-help');    
        $display = ob_get_clean();  
    
        echo $display;
    }

    public function search_settings(){

        global $wppcp,$wppcp_settings_data;
        
        add_settings_section( 'wppcp_section_search_general', __('Search Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-search-general' );
        
        add_settings_section( 'wppcp_section_search_restrictions', __('Search Restrictions','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-search-general' );
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wppcp_section_search_general';
        $wppcp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('search',$tab);
   
        $wppcp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $wppcp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
        $wppcp->template_loader->get_template_part( 'menu-page-container');
        $display = ob_get_clean();
        echo $display;

    }

    public function security_settings(){

        global $wppcp,$wppcp_settings_data;
        
        add_settings_section( 'wppcp_section_security_ip', __('IP Restrictions','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-security-ip' );
        
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wppcp_section_security_ip';
        $wppcp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('security',$tab);
   
        $wppcp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $wppcp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
        $wppcp->template_loader->get_template_part( 'menu-page-container');
        $display = ob_get_clean();
        echo $display;

    }

    public function save_wppcp_section_security_ip(){
        global $wppcp;

        if(isset($_POST['wppcp_security_ip'])){
            foreach($_POST['wppcp_security_ip'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['security_ip'] = $this->settings;

        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    public function save_wppcp_section_search_general(){
        global $wppcp;

        if(isset($_POST['wppcp_search_general'])){
            foreach($_POST['wppcp_search_general'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['search_general'] = $this->settings;
        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    public function save_wppcp_section_search_restrictions(){
        global $wppcp;

        if(isset($_POST['wppcp_search_restrictions'])){
            foreach($_POST['wppcp_search_restrictions'] as $k=>$v){
                $this->settings[$k] = $v;
            } 

            $wppcp_options = get_option('wppcp_options');
            $wppcp_options['search_restrictions'] = $this->settings;
            update_option('wppcp_options',$wppcp_options);
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );           
        }
        
         
    }

    public function password_settings(){

        global $wppcp,$wppcp_settings_data;
        
        add_settings_section( 'wppcp_section_password_global', __('Global Password Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-password-global' );
        
        //add_settings_section( 'wppcp_section_search_restrictions', __('Search Restrictions','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-search-general' );
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wppcp_section_password_global';
        $wppcp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('password',$tab);
   
        $wppcp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $wppcp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
        $wppcp->template_loader->get_template_part( 'menu-page-container');
        $display = ob_get_clean();
        echo $display;

    }

    public function save_wppcp_section_password_global(){
        global $wppcp;

        if(isset($_POST['wppcp_password_global'])){
            foreach($_POST['wppcp_password_global'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['password_global'] = $this->settings;
        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    /* Get the users for restrictions on various locations */
    public function wppcp_load_restriction_users(){
        global $wpdb;
        
        $search_text  = isset($_POST['q']) ? $_POST['q'] : '';
        
        $args = array('number' => 20);
        if($search_text != ''){
            $args['search'] = "*".$search_text."*";
        }
        
        $user_results = array();
        $user_json_results = array();
        
        $user_query = new WP_User_Query( $args );
        $user_results = $user_query->get_results();

        foreach($user_results as $user){
            if($user->ID != $this->current_user){
                array_push($user_json_results , array('id' => $user->ID, 'name' => $user->data->display_name) ) ;
            }
                       
        }
        
        echo json_encode(array('items' => $user_json_results ));exit;
    } 


    public function pro(){
        global $wppcp;
        ob_start();
        $wppcp->template_loader->get_template_part('plugin-pro');    
        $display = ob_get_clean();  
    
        echo $display;
    }

    public function pro_trial(){
        global $wppcp;
        ob_start();
        $wppcp->template_loader->get_template_part('pro-trial');    
        $display = ob_get_clean();  
    
        echo $display;
    }

    public function mailchimp_settings(){
        global $wppcp,$wppcp_settings_data;
        
       
        ob_start();
        $wppcp->template_loader->get_template_part( 'mailchimp-general-settings');
        $display = ob_get_clean();
        echo $display;
    }

    public function social_locker_settings(){

        global $wppcp,$wppcp_settings_data;
        
        add_settings_section( 'wppcp_section_social_locker_general', __('General Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-social-locker-general' );
        
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wppcp_section_social_locker_general';
        $wppcp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('social_locker_general',$tab);
   
        $wppcp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $wppcp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
        $wppcp->template_loader->get_template_part( 'menu-page-container');
        $display = ob_get_clean();
        echo $display;

    }

    public function upme_settings(){

        global $wppcp,$wppcp_settings_data;
        
        add_settings_section( 'wppcp_section_upme_general', __('UPME General Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-upme-general' );
        add_settings_section( 'wppcp_section_upme_search', __('UPME Search Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-upme-general' );
        add_settings_section( 'wppcp_section_upme_member_list', __('UPME Member List Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-upme-general' );
        add_settings_section( 'wppcp_section_upme_member_profile', __('UPME Member Profile Settings','wppcp'), array( &$this, 'wppcp_section_general_desc' ), 'wppcp-upme-general' );
        
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wppcp_section_upme_general';
        $wppcp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('upme_general',$tab);
   
        $wppcp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $wppcp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
        $wppcp->template_loader->get_template_part( 'menu-page-container');
        $display = ob_get_clean();
        echo $display;

    }

    public function save_wppcp_section_upme_general(){
        global $wppcp;

        if(isset($_POST['wppcp_upme_general'])){
            foreach($_POST['wppcp_upme_general'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['upme_general'] = $this->settings;

        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    public function save_wppcp_section_upme_search(){
        global $wppcp;

        if(isset($_POST['wppcp_upme_search'])){
            foreach($_POST['wppcp_upme_search'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['upme_search'] = $this->settings;

        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    public function save_wppcp_section_upme_member_list(){
        global $wppcp;

        if(isset($_POST['wppcp_upme_member_list'])){
            foreach($_POST['wppcp_upme_member_list'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['upme_member_list'] = $this->settings;

        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    public function save_wppcp_section_upme_member_profile(){
        global $wppcp;

        if(isset($_POST['wppcp_upme_member_profile'])){
            foreach($_POST['wppcp_upme_member_profile'] as $k=>$v){
                $this->settings[$k] = $v;
            }      
               
        }
        
        $wppcp_options = get_option('wppcp_options');
        $wppcp_options['upme_member_profile'] = $this->settings;

        update_option('wppcp_options',$wppcp_options);
        add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
    }

    public function wppcp_conditional_scripts($hook_suffix){
        if($hook_suffix == 'nav-menus.php'){
            $this->load_wppcp_select2_scripts_style();
        }
    }

    public function wppcp_deactivate_popup() {
        global $pagenow,$wppcp;

        $wppcp_plugin_data = (array) get_option('wppcp_plugin_data');
        $wppcp_init_version = isset($wppcp_plugin_data['init_version']) ? $wppcp_plugin_data['init_version'] : '';
        $wppcp_init_date = isset($wppcp_plugin_data['init_date']) ? $wppcp_plugin_data['init_date'] : '';

        if(trim($pagenow) == 'plugins.php'){


            $results = $wppcp->admin_stats->generate_stats();
            $individual_protection = array();
            if($results['single_data']['post_count'] > 0){
                $individual_protection[] = "<span>" . $results['single_data']['post_count'] . __(' Posts ','wppcp')."</span>" ;
            }
            if($results['single_data']['page_count'] > 0){
                $individual_protection[] = "<span>" .$results['single_data']['page_count'] . __(' Pages ','wppcp')."</span>" ;
            }
            if($results['single_data']['cpt_count'] > 0){
                $individual_protection[] = "<span>" .$results['single_data']['cpt_count'] . __(' Custom Post Types ','wppcp')."</span>" ;
            }

            $individual_protection = implode("-", $individual_protection);
            if($individual_protection != ''){
                $individual_protection .= __(' are protected','wppcp');
            }


            $global_protection = array();
            if($results['global_data']['restrict_all_posts_status'] == '1'){
                $global_protection[] = "<span>" .$results['global_data']['post_count'] . __(' Posts ','wppcp')."</span>" ;
            }
            if($results['global_data']['restrict_all_pages_status'] == '1'){
                $global_protection[] = "<span>" .$results['global_data']['page_count'] . __(' Pages ','wppcp')."</span>" ;
            }
            
            $global_protection = implode("-", $global_protection);
            if($global_protection != ''){
                $global_protection .= __(' are protected','wppcp');
            }

            $password_protection = '';      
            if(isset($results['password_data']['status'])){
                $password_protection = "<span>" .$results['password_data']['post_count'] . __(' Posts ','wppcp')."</span>"  . " - " .
                                        "<span>" .$results['password_data']['page_count'] . __(' Pages ','wppcp')."</span>"  ." - " .
                                        "<span>" .$results['password_data']['cpt_count'] . __(' Custom Post Types ','wppcp')."</span>"  ;
                $password_protection .= __(' are protected','wppcp');
            
            }

            $menu_protection = '';
            if($results['menu_data']['count'] > 0){
                $menu_protection = "<span>" .$results['menu_data']['count'] . __(' Menu Items ','wppcp')."</span>"  ;
                $menu_protection .= __(' are protected','wppcp');
            
            }

            $widget_protection = '';
            if($results['widgets_data']['count'] > 0){
                $widget_protection = "<span>" .$results['widgets_data']['count'] . __(' Widgets ','wppcp')."</span>"  ;
                $widget_protection .= __(' are protected','wppcp');
            
            }

            $shortcode_protection = '';
            if($results['shortcode_data']['count'] > 0){
                $shortcode_protection = "<span>" .$results['shortcode_data']['count'] . __(' Post/Page Content Blocks ','wppcp') ."</span>" ;
                $shortcode_protection .= __(' are protected','wppcp');
            
            }

            $private_page_protection = '';
            if($results['private_page_data']['count'] > 0){
                $private_page_protection = "<span>" .$results['private_page_data']['count'].__(' Users ','wppcp') . "</span>" . __('have private page with protected content. ','wppcp') ."</span>" ;
        
            }

            $attachment_protection = array();
            if($results['attachment_data']['post_count'] > 0){
                $attachment_protection[] = "<span>" .$results['attachment_data']['post_count'] . __(' Post ','wppcp')."</span>" ;
            }
            if($results['attachment_data']['page_count'] > 0){
                $attachment_protection[] = "<span>" .$results['attachment_data']['page_count'] . __(' Page ','wppcp')."</span>" ;
            }
            if($results['attachment_data']['cpt_count'] > 0){
                $attachment_protection[] = "<span>" .$results['attachment_data']['cpt_count'] . __(' Custom Post Type ','wppcp')."</span>" ;
            }

            $attachment_protection = implode("-", $attachment_protection);
            if($attachment_protection != ''){
                $attachment_protection .= __(' attachments are protected','wppcp');
            }



            $search_protection = array();
            if($results['search_data']['blocked_posts'] > 0){
                $search_protection[] = "<span>" .$results['search_data']['blocked_posts'] . __(' Posts ','wppcp')."</span>" ;
            }
            if($results['search_data']['blocked_pages'] > 0){
                $search_protection[] = "<span>" .$results['search_data']['blocked_pages'] . __(' Pages ','wppcp')."</span>" ;
            }
            $search_protection = implode("-", $search_protection);
            if($search_protection != ''){
                $search_protection .= __(' are protected from search','wppcp');
            }


            $table = "<table id='wppcp-admin-stats' class='wppcp-admin-stats-deactivate'  border='1'>";

            $protection_count = 0;
            if($individual_protection != ''){
                $table .= "<tr><th>". __('Individual Post/Page Protection','wppcp'). "</th>
                                <td>".$individual_protection."</td>
                            </tr>";
                $protection_count++;
            }

            if($global_protection != ''){ 
                $table .= "<tr><th>". __('Global Post/Page Protection','wppcp'). "</th>
                                        <td>".$global_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            if($password_protection != ''){ 
                $table .= "<tr><th>". __('Password Protection','wppcp'). "</th>
                                        <td>".$password_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            if($menu_protection != ''){ 
                $table .= "<tr><th>". __('Menu Protection','wppcp'). "</th>
                                        <td>".$menu_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            if($widget_protection != ''){ 
                $table .= "<tr><th>". __('Widget Protection','wppcp'). "</th>
                                        <td>".$widget_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            if($shortcode_protection != ''){ 
                $table .= "<tr><th>". __('Shortcode Protection','wppcp'). "</th>
                                        <td>".$shortcode_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            if($attachment_protection != ''){
                $table .= "<tr><th>". __('Attachment Protection','wppcp'). "</th>
                                        <td>".$attachment_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            if($private_page_protection != ''){
                $table .= "<tr><th>". __('Private Page','wppcp'). "</th>
                                        <td>".$private_page_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            if($search_protection != ''){ 
                $table .= "<tr><th>". __('Search Protection','wppcp'). "</th>
                                        <td>".$search_protection. "</td>
                                    </tr>";
                $protection_count++;
            }

            $table .= "</table>";
        
            $display = '  <div id="wppcp-deactivate-popup" class="wppcp-modal-box">
                      <header> <a href="#" class="wppcp-js-modal-close close">×</a>
                        <h3>'.__('Deactivate WP Private Content Plus','wppcp').'</h3>
                      </header>';

            if($protection_count != 0){
                $display .=  '<div id="wppcp-modal-body-step1">
                                <div class="wppcp-modal-body">
                                    <div class="wppcp-deactivate-general-message">'.__('Are you sure that you would like to deactivate the plugin?','wppcp').'</div><br/>
                                    <div style="font-weight:400;font-size:14px;" class="wppcp-deactivate-general-message">'.__('Just a reminder - Currently WP Private Content Plus is protecting your important site content. Following content types are 
                                        protected on your site and these content will be visible to public once you deactivate the plugin.','wppcp').'</div>
                                    
                                    '.$table.'

                                  </div>
                                  <footer> 
                                    <input id="wppcp-deactivate-step1-submit" class="wppcp-modal-btn wppcp-modal-btn-small" type="button" value="'.__('Continue','wppcp').'" />
                                  </footer>
                              </div>
                              <div id="wppcp-modal-body-step2" style="display:none" >';
            }else{
                $display .=  '<div id="wppcp-modal-body-step2" >';
            }

                $display .=  '
                      <div  class="wppcp-modal-body" >
                        <div class="wppcp-deactivate-general-message">'.__('Please help us understand why you are removing our plugin and what we can do to improve our plugin.','wppcp').'</div>
                        
                        <ul class="wppcp-deactivate-reasons">
                            <li><input type="radio" checked value="1" name="wppcp_deactivate_reason" class="wppcp_deactivate_reason" />'.__('I no longer need the plugin.','wppcp').'</li>
                            <li><input type="radio" value="2" name="wppcp_deactivate_reason" class="wppcp_deactivate_reason" />'.__('I found a better plugin.','wppcp').'
                            <div class="wppcp_deactivate_input"><input type="text" id="wppcp_deactivate_plugin_name" name="wppcp_deactivate_plugin_name" placeholder="'.__('Plugin Name','wppcp').'" /></div></li>
                            <li><input type="radio" value="3" name="wppcp_deactivate_reason" class="wppcp_deactivate_reason" />'.__('I only needed the plugin for short period.','wppcp').'</li>
                            <li><input type="radio" value="4" name="wppcp_deactivate_reason" class="wppcp_deactivate_reason" />'.__('The plugin broke my site or stopped working','wppcp').'
                            <div class="wppcp_deactivate_input"><input type="text" id="wppcp_deactivate_plugin_error" name="wppcp_deactivate_plugin_error" placeholder="'.__('What exactly happened?','wppcp').'" /></div></li>
                            <li><input type="radio" value="5" name="wppcp_deactivate_reason" class="wppcp_deactivate_reason" />'.__('The feature I need is in PRO version and its expensive.','wppcp').'
                            <div class="wppcp_deactivate_input"><input type="text" id="wppcp_deactivate_pro_price" name="wppcp_deactivate_pro_price" placeholder="'.__('What\'s your budget?','wppcp').'" /></div></li>
                            <li><input type="radio" value="6" name="wppcp_deactivate_reason" class="wppcp_deactivate_reason" />'.__('The feature I need is not available.','wppcp').'
                            <div class="wppcp_deactivate_input"><input type="text" id="wppcp_deactivate_plugin_feature" name="wppcp_deactivate_plugin_feature" placeholder="'.__('Let us know more about this feature','wppcp').'" /></div></li>
                            <li><input type="radio" value="7" name="wppcp_deactivate_reason" class="wppcp_deactivate_reason" />'.__('Other.','wppcp').'
                            <div class="wppcp_deactivate_input"><input type="text" id="wppcp_deactivate_other" name="wppcp_deactivate_other" placeholder="'.__('Please explain the reason','wppcp').'" /></div></li>
                        </ul>
                        <div class="wppcp-modal-permission-info">
                            <div class="wppcp-modal-permission-message">
                                '.__('<strong>Submit & Deactivate</strong> option will send the following details to developers of the plugin. Your feedback will be 
                                    used to improve the future versions of the plugin.','wppcp').'<br/>
                                <ul>
                                    <li>'.__('Reason for deactivation','wppcp').'</li>
                                    <li>'.__('Plugin Version','wppcp').'</li>
                                    <li>'.__('Activated Date','wppcp').'</li>
                                    <li>'.__('Admin Email (If you tick the following checkbox)','wppcp').'</li>
                                </ul>
                                <div class="wppcp-clear"></div>
                            </div><br/>
                            <input type="checkbox" id="wppcp_deactivate_admin_email" value="1" />
                            '.__('<b>I would like to get a response from developers regarding my feedback.</b>','wppcp').'
                        </div> 
                      </div>
                      <footer> 
                        <input id="wppcp_init_version" type="hidden" value="'.$wppcp_init_version.'" />
                        <input id="wppcp_init_date" type="hidden" value="'.$wppcp_init_date.'" />
                        <input id="wppcp_init_admin_email" type="hidden" value="'.get_option('admin_email').'" />
                        <input id="wppcp-deactivate-reasons-submit" class="wppcp-modal-btn wppcp-modal-btn-small" type="button" value="'.__('Submit & Deactivate','wppcp').'" />
                        <input id="wppcp-deactivate-submit" class="wppcp-modal-btn wppcp-modal-btn-small" type="button" value="'.__('Skip & Deactivate','wppcp').'" />
                        <a href="#" class="wppcp-modal-btn wppcp-modal-btn-small wppcp-js-modal-close">'.__('Close','wppcp').'</a> </footer>
                      </div>
                    </div>';
            echo $display;
        }

        
    }

    public function pro_addons(){
        global $wppcp;
        ob_start();
        $wppcp->template_loader->get_template_part('plugin-pro-addons');    
        $display = ob_get_clean();  
    
        echo $display;
    }

}





