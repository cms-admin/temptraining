<?php
/**
* Don't display if wordpress admin class is not found
* Protects code if wordpress breaks
* @since 0.2
*/
if ( ! function_exists( 'is_admin' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

include('privWidget.php');

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

/**
 * Initialize privWidget class
 * @var Ambiguous $myprivWidgetClass
 * @since 1.7.1
 */
$myprivWidgetClass = new privWidget();

$allOptions = wp_load_alloptions();
foreach ($allOptions as $option_name => $option_value) {
	if (strpos($option_name, $myprivWidgetClass->privWidgetOption) !== FALSE) {
		delete_option( $option_name );
	}
}


?>
