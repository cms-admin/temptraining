<?php

class WPPCP_Widgets{

	public function __construct(){
		add_filter('in_widget_form', array($this, 'widget_custom_options'), 10, 3 );

		add_filter( 'widget_update_callback', array($this, 'save_widget_options'), 10, 2 );

		add_filter( 'widget_display_callback', array($this, 'content_visibility'),10,3 );
	}

	public function widget_custom_options( $widget, $return, $instance ) {
 		global $wp_roles;
	        // Display the description option.
	    $visibility_level = isset( $instance['wppcp_visibility'] ) ? $instance['wppcp_visibility'] : '';
	    $visible_roles = isset( $instance['wppcp_visibility_roles'] ) ? (array) $instance['wppcp_visibility_roles'] : array();

	    $role_visibility = "display:none;";
	    if($visibility_level == '3'){
	    	$role_visibility = "display:block;";
	    }

	    

	    $display = '<div ><p>
	    				<label for="'.$widget->get_field_id('wppcp_visibility').'">'.__('Visibility','wppcp') .':</label>
						<select class="widefat wppcp_widget_visibility" id="'. $widget->get_field_id('wppcp_visibility').'" name="'.$widget->get_field_name('wppcp_visibility').'" >
							<option value="0" '. selected("0",$visibility_level,false) .' >'. __("Everyone","wppcp").' </option>
		        			<option value="1" '. selected("1",$visibility_level,false) .' >'. __("Members","wppcp").' </option>
		        			<option value="2" '. selected("2",$visibility_level,false) .' >'. __("Guests","wppcp").' </option>
		        			<option value="3" '. selected("3",$visibility_level,false) .' >'. __("By User Role","wppcp").' </option>
		        
						</select>
					</p>';

		
		$display .= '<div style="'.$role_visibility.'" class="wppcp_widget_visibility_roles"><p >
	    				<label for="'.$widget->get_field_id('wppcp_visibility_roles').'">'.__('Visibility Roles','wppcp') .':</label></p><p>';

	    		$user_roles = $wp_roles->role_names;
		    	foreach ( $user_roles as $role => $name ) {

		        	$checked = checked( true, in_array( $role, $visible_roles ) , false );
		   
					$display .= '<input   type="checkbox" name="'.$widget->get_field_name('wppcp_visibility_roles').'[]" id="'.$widget->get_field_id('wppcp_visibility_roles').'" '.$checked.' value="'.$role.'" />
						        	<label for="">
						        	'.$name .'
						        </label><br/>';
	

				}
						
		$display .= '</p></div></div>';
	    echo $display;

	}

	public function save_widget_options( $instance, $new_instance ) {

	    if ( empty( $new_instance['wppcp_visibility'] ) ) {
	        $new_instance['wppcp_visibility'] = 0;
	    }

	    if ( empty( $new_instance['wppcp_visibility_roles'] ) ) {
	        $new_instance['wppcp_visibility_roles'] = array();
	    }
	 
	    return $new_instance;
	}


	public function content_visibility($instance, $current_obj, $args){
			//unset( $sidebars_widgets[ '$sidebar_id' ] );
		$visible = true;
		if(isset($instance['wppcp_visibility'])){

			$visibility_level = $instance['wppcp_visibility'];
			
			switch( $visibility_level ) {
					case '0' :
						$visible = true;
						break;
					case '1' :
						$visible = is_user_logged_in() ? true : false;
						break;
					case '2' :
						$visible = ! is_user_logged_in() ? true : false;
						break;
					case '3' :
						$visibility_roles = isset($instance['wppcp_visibility_roles']) ? (array)  $instance['wppcp_visibility_roles'] : array();
						$visible = false;
						foreach ( $visibility_roles as $role ) {
							if ( current_user_can( $role ) ) 
								$visible = true;
						}
						break;
				}
		}

		$visible = apply_filters('wppcp_widget_visibility', $visible , array('instance' => $instance, 'current_obj' => $current_obj, 'args' => $args) );

		if($visible){
			return $instance;	
		}else{
			return false;
		}
		

	}
}


