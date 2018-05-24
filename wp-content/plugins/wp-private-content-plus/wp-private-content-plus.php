<?php
/*
  Plugin Name: WP Private Content Plus
  Plugin URI: http://www.wpexpertdeveloper.com/wp-private-content-plus/
  Description: Advanced private content restrictions for WordPress
  Version: 1.22
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'WPPCP_VERSION' ) ) {
    define( 'WPPCP_VERSION', '1.22' );
}

register_activation_hook( __FILE__, 'wppcp_install_db_tables' );
register_deactivation_hook( __FILE__, 'wppcp_uninstall' );

function wppcp_get_plugin_version() {
    $default_headers = array('Version' => 'Version');
    $plugin_data = get_file_data(__FILE__, $default_headers, 'plugin');
    return $plugin_data['Version'];
}

/* Intializing the plugin on plugins_loaded action */
add_action( 'plugins_loaded', 'wppcp_plugin_init' );

function wppcp_plugin_init(){
    WP_Private_Content_Plus();
}


/* Install database tables required for the plugin */
function wppcp_install_db_tables(){
    global $wpdb,$wp_roles;

    $table_name = $wpdb->prefix . 'wppcp_private_page';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id int(11) NOT NULL AUTO_INCREMENT,
              user_id int(11) NOT NULL,
              content longtext NOT NULL,
              type varchar(20) NOT NULL,
              updated_at datetime NOT NULL,
              PRIMARY KEY (id)
            );";

    $table_groups = $wpdb->prefix . 'wppcp_group_users';

    $sql_groups = "CREATE TABLE IF NOT EXISTS $table_groups (
              id int(11) NOT NULL AUTO_INCREMENT,
              user_id int(11) NOT NULL,
              group_id int(11) NOT NULL,
              updated_at datetime NOT NULL,
              PRIMARY KEY (id)
            );";


    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    dbDelta( $sql_groups );

    wppcp_save_plugin_data();

    if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
        return;
    }
    set_transient( 'wppcp_welcome_redirect', true, 30 );

    $wp_roles->add_cap( 'administrator', 'wppcp_manage_options' );
}

/* Main Class for WP Private Content Plus */
if( !class_exists( 'WP_Private_Content_Plus' ) ) {
    
    class WP_Private_Content_Plus{
    
        private static $instance;

        /* Create instances of plugin classes and initializing the features  */
        public static function instance() {
            
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Private_Content_Plus ) ) {
                self::$instance = new WP_Private_Content_Plus();
                self::$instance->setup_constants();

                add_action('wp_enqueue_scripts',array(self::$instance,'load_scripts'),9);

                add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
                self::$instance->includes();

                add_action('init', array( self::$instance, 'init_actions' ) );                
                 
                self::$instance->settings           = new WPPCP_Settings();
                self::$instance->template_loader    = new WPPCP_Template_Loader();
                self::$instance->private_content    = new WPPCP_Private_Content();
                self::$instance->roles_capability   = new WPPCP_Roles_Capability();
                self::$instance->menu               = new WPPCP_Menu();
                self::$instance->private_posts_pages= new WPPCP_Private_Posts_Pages();
                self::$instance->posts              = new WPPCP_Posts();
                self::$instance->search             = new WPPCP_Search();
                self::$instance->password_protected_content  = new WPPCP_Password_Protected_Content();
                self::$instance->widgets            = new WPPCP_Widgets();
                self::$instance->post_attachments   = new WPPCP_Post_Attachments();
                self::$instance->groups             = new WPPCP_Groups();
                self::$instance->ip_restrictions    = new WPPCP_IP_Restrictions();

                self::$instance->admin_stats        = new WPPCP_Admin_Stats();
                // self::$instance->social_locker       = new WPPCP_Social_Locker();
                if ( defined( 'upme_url' ) ) {
                    self::$instance->upme           = new WPPCP_UPME();
                }

                add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( self::$instance, 'plugin_listing_links' )  );
                
            }
            return self::$instance;
        }

        public function init_actions(){
            self::$instance->private_content_settings  = get_option('wppcp_options');
        }

        /* Setup constants for the plugin */
        private function setup_constants() {
            
            // Plugin version
            

            // Plugin Folder Path
            if ( ! defined( 'WPPCP_PLUGIN_DIR' ) ) {
                define( 'WPPCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            // Plugin Folder URL
            if ( ! defined( 'WPPCP_PLUGIN_URL' ) ) {
                define( 'WPPCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }
            
            if ( ! defined( 'WPPCP_PRIVATE_CONTENT_TABLE' ) ) {
                define( 'WPPCP_PRIVATE_CONTENT_TABLE', 'wppcp_private_page' );
            }
            
            if ( ! defined( 'WPPCP_GROUPS_POST_TYPE' ) ) {
                define( 'WPPCP_GROUPS_POST_TYPE', 'wppcp_group' );
            }
        }
             
        /* Define the locations for template files */  
        public function template_loader_locations($locations){
            $location = trailingslashit( WPPCP_PLUGIN_DIR ) . 'templates/';
            array_push($locations,$location);
            return $locations;
        }
        
        /* Include class files */
        private function includes() {

            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-settings.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-template-loader.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-private-content.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-roles-capability.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-menu.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-private-posts-pages.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-posts.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-search.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-password-protected-content.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-widgets.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-post-attachments.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-groups.php';
            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-ip-restrictions.php';
            // require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-social-locker.php';

            require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-admin-stats.php';

            if ( defined( 'upme_url' ) ) {
                require_once WPPCP_PLUGIN_DIR . 'classes/class-wppcp-upme.php';
            }

            require_once WPPCP_PLUGIN_DIR . 'functions.php';
            if ( is_admin() ) {}
        }

        public function load_scripts(){
            wp_register_script('wppcp_front_js', WPPCP_PLUGIN_URL . 'js/wppcp-front.js', array('jquery'));
        }

        public function include_styles(){

            wp_register_style('wppcp_front_css', WPPCP_PLUGIN_URL . 'css/wppcp-front.css');
            wp_enqueue_style('wppcp_front_css');
            
        }

        public function plugin_listing_links($links){
            $links[] = '<a href="http://goo.gl/A4fnYE"><b>' . __( 'Documentation', 'wppcp' ) . '</b></a>';
            $links[] = '<a href="http://goo.gl/2Zr089"><b>' . __( 'Upgrade to PRO', 'wppcp' ) . '</b></a>';

            return $links;
        }
        
    }
}

/* Intialize WP_Private_Content_Plus instance */
function WP_Private_Content_Plus() {
    global $wppcp;    
	$wppcp = WP_Private_Content_Plus::instance();
}

add_action('init', 'wppcp_load_textdomain');
function wppcp_load_textdomain() {
    load_plugin_textdomain( 'wppcp', false,dirname(plugin_basename(__FILE__)).'/lang');
}

function wppcp_save_plugin_data(){

    $wppcp_plugin_data = (array) get_option('wppcp_plugin_data');
    if(!isset($wppcp_plugin_data['init_version'])){
        $wppcp_plugin_data['init_version'] = WPPCP_VERSION;
        $wppcp_plugin_data['init_date'] = date('Y-m-d H:i:s');
    }

    $wppcp_plugin_data['current_version'] = WPPCP_VERSION;
    $wppcp_plugin_data['current_activation_date'] = date('Y-m-d H:i:s');
    update_option('wppcp_plugin_data', $wppcp_plugin_data);
}

function wppcp_uninstall(){
    $wppcp_plugin_data = (array) get_option('wppcp_plugin_data');
    if(!isset($wppcp_plugin_data['init_deactivation'])){
        $wppcp_plugin_data['init_deactivation'] = 'yes';
        $wppcp_plugin_data['init_deactivation_date'] = date('Y-m-d H:i:s');
    }
    update_option('wppcp_plugin_data', $wppcp_plugin_data);
}

add_filter('wppcp_main_settings_page_capability','wppcp_settings_page_capability',10,2);
add_filter('wppcp_global_restrictions_settings_page_capability','wppcp_settings_page_capability',10,2);
add_filter('wppcp_search_settings_page_capability','wppcp_settings_page_capability',10,2);
add_filter('wppcp_password_settings_page_capability','wppcp_settings_page_capability',10,2);
add_filter('wppcp_private_page_settings_page_capability','wppcp_settings_page_capability',10,2);
add_filter('wppcp_security_settings_page_capability','wppcp_settings_page_capability',10,2);
add_filter('wppcp_upme_settings_page_capability','wppcp_settings_page_capability',10,2);
function wppcp_settings_page_capability($capability,$params){
    global $wp_roles;
    $private_content_settings = get_option('wppcp_options');
    $data = isset($private_content_settings['restriction_permissions']) ? $private_content_settings['restriction_permissions'] : array();
    $wppcp_feature_permission_roles = isset($data['wppcp_feature_permission_roles']) ? $data['wppcp_feature_permission_roles'] : array();
  
    if(is_user_logged_in() && count($wppcp_feature_permission_roles) > 0){
        if(current_user_can('wppcp_manage_options') || current_user_can('manage_options')){
            return 'wppcp_manage_options';
        }
    }
    return $capability;
}


