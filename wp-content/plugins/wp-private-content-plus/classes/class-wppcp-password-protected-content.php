<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Manage content restriction shortcodes */
class WPPCP_Password_Protected_Content{
    
    public $current_user;
    public $private_content_settings;
    
    /* intialize the settings and shortcodes */
    public function __construct(){
        global $wppcp;

        add_action('init', array($this, 'init'));           
        add_filter('template_include', array($this, 'validate_restrictions'),99);         
    }

    public function init(){
        $this->current_user = get_current_user_id(); 
    }

    public function validate_restrictions($template){
        global $wppcp,$wp_query,$wppcp_password_protect_data;

        $private_content_settings  = get_option('wppcp_options');
        $password_settings = isset($private_content_settings['password_global']) ? $private_content_settings['password_global'] : array();
        $global_password_protect = isset($password_settings['global_password_protect']) ? $password_settings['global_password_protect'] : 'disabled';
        $site_password = isset($password_settings['global_protect_password']) ? $password_settings['global_protect_password'] : '';
        $protected_form_header = isset($password_settings['password_form_title']) ? $password_settings['password_form_title'] : __('Protected Content','wppcp');
        $protected_form_message = isset($password_settings['password_form_message']) ? $password_settings['password_form_message'] : __('This content is password protected. Please enter the password to view the content.','wppcp');
         
        $site_password_status = isset( $_COOKIE['wppcp_global_password_protected_status'] ) ? $_COOKIE['wppcp_global_password_protected_status'] : 'INACTIVE';
     

        if(!isset($private_content_settings['general']['private_content_module_status'])){
            return $template;        
        }

        if($global_password_protect == 'disabled'){
            return $template;
        }

        $this->current_user = wp_get_current_user();

        if(current_user_can('manage_options') || current_user_can('wppcp_manage_options') ){
            return $template;
        } 

        $allowed_urls = isset($password_settings['allowed_urls']) ? $password_settings['allowed_urls'] : '';        
        $allowed_urls = explode(PHP_EOL, $allowed_urls );
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
   
        if(in_array($current_page_url, $allowed_urls) || in_array($current_page_trailing_slash_url, $allowed_urls)){
            return $template;
        }               


        if(is_home() || is_page() || is_single() || is_archive() || is_feed() || is_search() || is_404() ){
            if($global_password_protect == 'enabled_all_users'){
                $this->verify_global_password_protection($site_password);
                if($this->password_protect_status){
                    $site_password_status = 'ACTIVE';
                }

            }else if($global_password_protect == 'enabled_guest_users'){
                if(is_user_logged_in()){
                    return $template;
                }else{
                    $this->verify_global_password_protection($site_password);
                    if($this->password_protect_status){
                        $site_password_status = 'ACTIVE';
                    }
                }
            }

            if($site_password == ''){
            
            }else{
                if($site_password_status == 'ACTIVE'){
                    
                }else{
                    $wppcp_password_protect_data['protected_form_header'] = $protected_form_header;
                    $wppcp_password_protect_data['password_protect_error'] = $this->password_protect_error;
                    $wppcp_password_protect_data['protected_form_message'] = $protected_form_message;
                    $template = WPPCP_PLUGIN_DIR.'templates/global-password-form.php';
                }    
                
            }

        }

        
     
        return $template;
    }

    public function verify_global_password_protection($site_password){
        $site_password_status = isset( $_COOKIE['wppcp_global_password_protected_status'] ) ? $_COOKIE['wppcp_global_password_protected_status'] : 'INACTIVE';
           
        $this->password_protect_error = '';
        $this->password_protect_status = FALSE;
        if ( isset( $_POST['site_protect_password_submit'] ) ) {
            if ( ! isset( $_POST['wppcp_password_protect_nonce'] ) ) {
                $this->password_protect_status = FALSE;
                return;
            }

            if ( ! wp_verify_nonce( $_POST['wppcp_password_protect_nonce'], 'wppcp_password_protect' ) ) {
                $this->password_protect_status = FALSE;
                return;
            }

            $site_protect_password = isset($_POST['site_protect_password']) ? $_POST['site_protect_password'] : '';

            if(trim($site_password) == trim($site_protect_password) ){
                setcookie( 'wppcp_global_password_protected_status' , 'ACTIVE' , strtotime( '+1 year' ) , "/");
                $site_password_status = 'ACTIVE';
                $this->password_protect_status = TRUE;
            }else{
                $this->password_protect_error = __('Please enter valid password.','wppcp');
                $this->password_protect_status = FALSE;
            }
        }
    }
}