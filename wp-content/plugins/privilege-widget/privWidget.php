<?php
/**
 * Plugin Name: Privilege Widget
 * Plugin URI: http://www.fuzzguard.com.au/plugins/privilege-widget
 * Description: Used to provide Widget display to users based on whether the user is logged in, logged out, or has a certain role
 * Version: 1.7.3
 * Author: <a href="http://www.fuzzguard.com.au/"><strong>Benjamin Guy</strong></a>
 * Author URI: http://www.fuzzguard.com.au
 * Text Domain: privilege-widget
 * License: GPL2

    Copyright 2014  Benjamin Guy  (email: beng@fuzzguard.com.au)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/**
* Don't display if wordpress admin class is not found
* Protects code if wordpress breaks
* @since 0.1
*/
if ( ! function_exists( 'is_admin' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}


class privWidget {
	
	/**
	 * Stores the option string name
	 * @var string $privWidgetOption
	 * @since 1.7.1
	 */
	public $privWidgetOption = '_priv_widget';
	
	
        /**
        * Loads localization files for each language
        * @since 1.4
        */
        function _action_init()
        {
                // Localization
                load_plugin_textdomain('privilege-widget', false, 'privilege-widget/lang/');
        }

function privilege_widget_form_extend( $t, $return, $instance ) {

		
	$privWidget_id = $t->id;
	$users_and_roles = get_option($privWidget_id.$this->privWidgetOption);
                global $wp_roles;

                $display_roles = apply_filters( 'priv_widget_roles', $wp_roles->role_names );


                                if( !is_array( $users_and_roles ) ) {
					if ($users_and_roles=="admin") {
						$users = 'in';
						$roles = array('administrator');
					} else {
                                       		$roles = $users_and_roles;
                                        	$users = $users_and_roles;
					}
                                }
                                else {
                                        $roles = $users_and_roles['roles'];
                                        $users = $users_and_roles['users'];
                                }



                $checked_roles = is_array( $roles ) ? $roles : false;
		$logged_in_out = $users;
?>

                <input type="hidden" name="priv-widget-nonce" value="<?php echo wp_create_nonce( 'priv-widget-nonce-name' ); ?>" />
                <div class="field-priv_widget_role priv_widget_logged_in_out_field description-wide" style="width: 100%; margin: 10px 0px; padding: 5px 0px; overflow: hidden; border-bottom: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD;">
                    <span class="description"><?php _e( 'User Restrictions', 'privilege-menu' ); ?></span>
                    <br><br>
                    <input type="hidden" class="widget-id" value="<?php echo $privWidget_id ;?>" />

                    <div class="logged-input-holder" style="float: left; width: 40%;">
                        <input type="radio" class="widget-logged-in-out" name="priv-widget-logged-in-out[<?php echo $privWidget_id ;?>]" id="priv_widget_logged_out-for-<?php echo $privWidget_id ;?>" <?php checked( 'out', $logged_in_out ); ?> value="out" onclick="jQuery('#priv-widget-access-role-div-<?php echo $privWidget_id;?>').hide()"/>
                        <label for="priv_widget_logged_out-for-<?php echo $privWidget_id ;?>">
                            <?php _e( 'Logged Out', 'privilege-menu'); ?>
                        </label>
                    </div>

                    <div class="logged-input-holder" style="float: left; width: 40%;">
                        <input type="radio" class="widget-logged-in-out" name="priv-widget-logged-in-out[<?php echo $privWidget_id ;?>]" id="priv_widget_logged_in-for-<?php echo $privWidget_id ;?>" <?php checked( 'in', $logged_in_out ); ?> value="in" onclick="jQuery('#priv-widget-access-role-div-<?php echo $privWidget_id ;?>').show();"/>
                        <label for="priv_widget_logged_in-for-<?php echo $privWidget_id ;?>">
                            <?php _e( 'Logged In', 'privilege-menu'); ?>
                        </label>
                    </div>

                    <div class="logged-input-holder" style="float: left; width: 20%;">
                        <input type="radio" class="widget-logged-in-out" name="priv-widget-logged-in-out[<?php echo $privWidget_id ;?>]" id="priv_widget_by_role-for-<?php echo $privWidget_id ;?>" <?php checked( '', $logged_in_out ); ?> value="" onclick="jQuery('#priv-widget-access-role-div-<?php echo $privWidget_id ;?>').hide();"/>
                        <label for="priv_widget_by_role-for-<?php echo $privWidget_id ;?>">
                            <?php _e( 'All', 'privilege-menu'); ?>
                        </label>
                    </div>

                </div>

                <div class="field-nav_menu_role nav_menu_role_field description-wide" style="overflow: auto; width: 100%; margin: 5px 0;<?php if( $logged_in_out != 'in' ) { echo ' display: none;'; }?>" id="priv-widget-access-role-div-<?php echo $privWidget_id ;?>">
                    <span class="description" style="display: block;"><?php _e( "Access Role", 'privilege-menu'); ?>: <?php _e( "leave all unchecked to allow all logged in users to see the menu.", 'privilege-menu' ); ?></span>
                    <?php

                    // Loop through each of the available roles.
                    foreach ( $display_roles as $role => $name ) {

                        // If the role has been selected, make sure it's checked.
                        $checked = checked( true, ( is_array( $checked_roles ) && in_array( $role, $checked_roles ) ), false );

                        ?>

                        <div class="role-input-holder" style="display: block; float: left; width: 45%; margin: 2px 2px; white-space: nowrap; text-overflow: ellipsis;">
                        <input type="checkbox" name="priv-widget-role[<?php echo $privWidget_id ;?>][<?php echo $role; ?>]" id="priv_widget_role-<?php echo $role; ?>-for-<?php echo $privWidget_id ;?>" <?php echo $checked; ?> value="<?php echo $role; ?>" />
                        <label for="priv_widget_role-<?php echo $role; ?>-for-<?php echo $privWidget_id ;?>">
                        <?php echo esc_html( $name ); ?>
                        </label>
                        </div>

                    <?php } ?>
                </div>

<?php
		$return = null;
		return array($t,$return,$instance);
	}

/**
* Save the data returned from the users browser in the database
* @since 0.1
* @updated 1.2
*/
function privilege_widget_update($instance, $new_instance, $old_instance) {
	global $wp_roles;
       	$opt_arr = $_POST['priv-widget-logged-in-out'];
	$allowed_roles = apply_filters( 'priv_widget_roles', $wp_roles->role_names );
	if (!empty($opt_arr)) {
		foreach ($opt_arr as $key => $value) {
        $saved_data = array( 'users' => '', 'roles' => '');

        if ( isset( $_POST['priv-widget-logged-in-out'][$key]  )  && in_array( $_POST['priv-widget-logged-in-out'][$key], array( 'in', 'out') ) ) {
              $saved_data['users'] = $_POST['priv-widget-logged-in-out'][$key];
        }

        if ( isset( $_POST['priv-widget-role'][$key] ) ) {
            $custom_roles = array();
            // only save allowed roles
            foreach( $_POST['priv-widget-role'][$key] as $role ) {
                if ( array_key_exists ( $role, $allowed_roles ) ) $custom_roles[] = $role;
            }

            if ( ! empty ( $custom_roles ) ) {
                 $saved_data['roles'] = $custom_roles;
            }
        }
        if ( $saved_data['roles'] != '' || $saved_data['users'] != '' ) {
            update_option( $key.$this->privWidgetOption, $saved_data );
        } else {
            delete_option( $key.$this->privWidgetOption );
        }
		}
	}

	return $instance;
}


	
/**
* Modify's the widget data with the options for privilege widget
* @since 0.1
*/
function privilege_widget_filter( $widget )
{

	foreach($widget as $widget_area => $widget_list)
	{
		
		if ($widget_area=='wp_inactive_widgets' || empty($widget_list)) continue;

		foreach($widget_list as $pos => $widget_id)
		{
			$meta_data = get_option($widget_id.$this->privWidgetOption);
                        // Handle the old format of the meta data.
                        if( !is_array( $meta_data ) ) {
                        	$temp = $meta_data;
                        	$meta_data = array();
                        	$meta_data['users'] = $temp;
                        	$meta_data['roles'] = array();
                        }
                        $visible = true;
                        switch( $meta_data['users'] ) {
                                        case 'admin':
                                                $meta_data['roles'][] = 'administrator';
                                        case 'in' :
                                                if( is_user_logged_in() ) {
                                                        // By default assume a menu with the "logged in" user restriction is visible when a user is logged in.
                                                        $visible = true;

                                                        // Setup for matching of roles if they exist.
                                                        $role_match = false;
                                                        $role_count = 0;

                                                        // Check to see if we have an array of roles or not.
                                                        if ( is_array( $meta_data['roles'] ) ) {
                                                                // Count the number of roles we have to check.
                                                                $role_count = count( $meta_data['roles'] );

                                                                // Loop through each role and check to see if this user has it.
                                                                foreach( $meta_data['roles'] as $role ) {
                                                                        if ( current_user_can( $role ) ) {
                                                                                $role_match = true;
                                                                        }
                                                                }
                                                        }

                                                        // If we haven't match a user role to the current user and we have user roles set, make this menu item invisible.
                                                        if( !$role_match && $role_count > 0 ) { $visible = false; }
                                                }
                                                else {
                                                        $visible = false;
                                                }
                                                break;
                                        case 'out' :
                                                $visible = ! is_user_logged_in() ? true : false;
                                                break;
                                        default:
                                                $visible = true;
                                                break;
                        }

                        // add filter to work with plugins that don't use traditional roles
                        $visible = apply_filters( 'priv_widget_visibility', $visible, $widget_list );

                        if ( ! $visible ) unset($widget_list[$pos]);
/**
			$logged_in_out = get_option($widget_id.$this->privWidgetOption);
                        switch( $logged_in_out ) {
                                case 'admin':
                                        $visible = current_user_can( 'manage_options' ) ? true : false;
                                        break;
                                case 'in' :
                                        $visible = is_user_logged_in() ? true : false;
                                        break;
                                case 'out' :
                                        $visible = ! is_user_logged_in() ? true : false;
                                        break;
                                default:
                                        $visible = true;
			}
			if ( ! $visible ) unset($widget_list[$pos]);
**/
		}
		$widget[$widget_area] = $widget_list;
	}
    return $widget;
}

/**
 * Register the required plugins for this theme.
 *
 * In this example, we register five plugins:
 * - one included with the TGMPA library
 * - two from an external source, one from an arbitrary source, one from a GitHub repository
 * - two from the .org repo, where one demonstrates the use of the `is_callable` argument
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 *
 * @since 1.7
 */
function fuzzguard_plugin_manager_register_required_plugins() {
        /*
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */

        $plugins = array(
                // This is an example of how to include a plugin from the WordPress Plugin Repository.
                array(
                        'name'      => 'Privilege Menu',
                        'slug'      => 'privilege-menu',
                        'required'  => false,
                ),
                array(
                        'name'      => 'Privilege Widget',
                        'slug'      => 'privilege-widget',
                        'required'  => false,
                ),

        );

        /*
         * Array of configuration settings. Amend each line as needed.
         *
         * TGMPA will start providing localized text strings soon. If you already have translations of our standard
         * strings available, please help us make TGMPA even better by giving us access to these translations or by
         * sending in a pull-request with .po file(s) with the translations.
         *
         * Only uncomment the strings in the config array if you want to customize the strings.
         */
        $config = array(
                'id'           => 'fuzzguard_plugin_manager',                 // Unique ID for hashing notices for multiple instances of TGMPA.
                'default_path' => '',                      // Default absolute path to bundled plugins.
                'menu'         => 'fuzzguard-plugin-manager', // Menu slug.
                'parent_slug'  => 'plugins.php',            // Parent menu slug.
                'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
                'has_notices'  => true,                    // Show admin notices or not.
                'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
                'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
                'is_automatic' => false,                   // Automatically activate plugins after installation or not.
                'message'      => '',                      // Message to output right before the plugins table.

        );

        tgmpa( $plugins, $config );
}

}

/**
* Define the Class
* @since 0.1
*/
$myprivWidgetClass = new privWidget();

/**
* Action of what function to call on wordpress initialization
* @since 1.4
*/
add_action('plugins_loaded', array($myprivWidgetClass, '_action_init'));

/**
* Filter of what function to call to modify the widget output before it is returned to the users browser
* @since 0.1
* @updated 1.3
*/
if(!is_admin()) {
	add_filter( 'sidebars_widgets', array($myprivWidgetClass, 'privilege_widget_filter'), 10);
}

/**
* Filter of what function to call to modify widget code
* @since 0.1
* @updated 1.3
*/
add_action('in_widget_form', array($myprivWidgetClass, 'privilege_widget_form_extend'), 5, 3);
/**
 * Include the TGM_Plugin_Activation class.
 * @since 1.6.1
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

/**
* Add required plugins install from TGM
* @since 1.6.1
*/
add_action( 'tgmpa_register', array( $myprivWidgetClass, 'fuzzguard_plugin_manager_register_required_plugins') );


/**
* Filter of what function to call to write returned data to the database
* @since 0.1
*/
add_filter( 'widget_update_callback', array($myprivWidgetClass, 'privilege_widget_update'), 10, 3 );

?>