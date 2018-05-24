<?php

class WPPCP_Admin_Stats{


	public function __construct(){
		add_action( 'wp_dashboard_setup', array($this,'register_my_dashboard_widget' ) );
		add_action('init', array($this, 'init'),9999);

	}

	public function init(){
		$this->private_content_settings  = get_option('wppcp_options');      

	}

	public function generate_stats(){

		$results['single_data'] = $this->get_individual_restriction_data();
		// Array
		// (
		//     [cpt_count] => 1
		//     [post_count] => 78
		//     [page_count] => 65
		// )
		$results['global_data'] = $this->get_global_restriction_data();
		// Array
		// (
		//     [restrict_all_posts_status] => 0
		//     [restrict_all_pages_status] => 1
		//     [post_count] => 78
		//     [page_count] => 65
		// )

		$results['password_data'] = $this->get_password_protected_data();
		// Array
		// (
		//     [status] => enabled_all_users
		//     [post_count] => 78
		//     [page_count] => 65
		//     [cpt_count] => 37
		// )

        $results['menu_data'] = $this->get_menu_stats();
  //       Array
		// (
		//     [count] => 3
		// )
        $results['widgets_data'] = $this->get_widget_stats();
  //       Array
		// (
		//     [count] => 5
		//     [widgets] => Array
		//         (
		//             [0] => Array
		//                 (
		//                     [name] => widget_meta
		//                     [visibility] => 5
		//                 )
		//         )
		// )
        $results['search_data'] = $this->get_search_stats();
  //       Array
		// (
		//     [blocked_posts] => 0
		//     [blocked_pages] => 0
		//     [everyone_search_types] => 3
		//     [guests_search_types] => 0
		//     [members_search_types] => 0
		// )
        $results['private_page_data'] = $this->get_private_page_stats();
  //       Array
		// (
		//     [count] => 4
		// )
        $results['attachment_data'] = $this->get_attachments_data();
  //       Array
		// (
		//     [cpt_count] => 1
		//     [post_count] => 9
		//     [page_count] => 2
		// )
        $results['shortcode_data'] = $this->get_private_shortcode_data();
   //      Array
			// (
			//     [count] => 6
			// )
        
    


        return $results;
	}

	public function register_my_dashboard_widget() {
	 	global $wp_meta_boxes;

	 	$wppcp_options = get_option('wppcp_options');


	 	$dashboard_restrictions_widget_status = isset($wppcp_options['general']['dashboard_restrictions_widget_status']) ?
	 	 ($wppcp_options['general']['dashboard_restrictions_widget_status']) : 0;
		if($dashboard_restrictions_widget_status == '1'){    
			wp_add_dashboard_widget(
				'wppcp_dashboard_stats_widget',
				__('WP Private Content Plus - Stats','wppcp'),
				array($this,'wppcp_dashboard_stats_widget_display')
			);

		 	$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

			$wppcp_stats_widget = array( 'wppcp_dashboard_stats_widget' => $dashboard['wppcp_dashboard_stats_widget'] );
		 	unset( $dashboard['wppcp_dashboard_stats_widget'] );

		 	$sorted_dashboard = array_merge( $wppcp_stats_widget, $dashboard );
		 	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
		}
		
	}


	public function wppcp_dashboard_stats_widget_display() {
		?>

		<p>
		<?php _e('Thank you for using <strong>WP Private Content Plus</strong> to protect your site.','wppcp'); ?>
		<?php _e('You are using WP Private Content Plus to protect following data types.','wppcp'); ?>
		</p>

		

		<?php
		$results = $this->generate_stats();
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

		?>
		<table id='wppcp-admin-stats'  border="1">

			<?php if($individual_protection != ''){ ?>
				<tr><th><?php _e('Individual Post/Page Protection','wppcp'); ?></th>
					<td><?php echo $individual_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($global_protection != ''){ ?>
				<tr><th><?php _e('Global Post/Page Protection','wppcp'); ?></th>
					<td><?php echo $global_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($password_protection != ''){ ?>
				<tr><th><?php _e('Password Protection','wppcp'); ?></th>
					<td><?php echo $password_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($menu_protection != ''){ ?>
				<tr><th><?php _e('Menu Protection','wppcp'); ?></th>
					<td><?php echo $menu_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($widget_protection != ''){ ?>
				<tr><th><?php _e('Widget Protection','wppcp'); ?></th>
					<td><?php echo $widget_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($shortcode_protection != ''){ ?>
				<tr><th><?php _e('Shortcode Protection','wppcp'); ?></th>
					<td><?php echo $shortcode_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($attachment_protection != ''){ ?>
				<tr><th><?php _e('Attachment Protection','wppcp'); ?></th>
					<td><?php echo $attachment_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($private_page_protection != ''){ ?>
				<tr><th><?php _e('Private Page','wppcp'); ?></th>
					<td><?php echo $private_page_protection; ?></td>
				</tr>
			<?php } ?>

			<?php if($search_protection != ''){ ?>
				<tr><th><?php _e('Search Protection','wppcp'); ?></th>
					<td><?php echo $search_protection; ?></td>
				</tr>
			<?php } ?>
		</table>
		<?php
	}

	public function get_menu_stats(){
		$args = array(
			'post_type' => 'nav_menu_item',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_key' => 'wppcp_nav_menu_visibility_level',
			'meta_value' => '0',
			'meta_compare' => '!='
		);
		 
		$query = new WP_Query( $args );
		$count = $query->post_count;

		return array('count' => $count);
	}

	public function get_password_protected_data(){
		global $wppcp;
		$password_settings = isset($this->private_content_settings['password_global']) ? $this->private_content_settings['password_global'] : array();
        $global_password_protect = isset($password_settings['global_password_protect']) ? $password_settings['global_password_protect'] : 'disabled';
        
        $password_data = array();
        $cpt_count = 0;
        if($global_password_protect != 'disabled'){
        	$password_data['status'] = $global_password_protect;

        	$p_types = $wppcp->posts->get_post_types();
        	$skipped_types = array('attachment','revision','nav_menu_item');
			foreach ( $p_types as $post_type => $post_type_label ) {
        		if(!in_array($post_type, $skipped_types)){
				   // $password_data[$post_type.'_count'] = wp_count_posts($post_type);
        			$cpt_count += wp_count_posts($post_type)->publish;
				}
			}
        }

        $password_data['post_count'] = wp_count_posts('post')->publish;
        $password_data['page_count'] = wp_count_posts('page')->publish;
        $password_data['cpt_count'] = $cpt_count;


        return $password_data;
	}

	public function get_widget_stats(){
		global $wpdb;

        $sql  = $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE autoload = '%s' and option_name like '%s' ", 'yes' , 'widget_%' );
        $result = $wpdb->get_results($sql);

        $restricted_widgets_data = array();
        $restricted_widgets_data['count'] = 0;

        foreach ($result as $key => $widget_options) {
        	$widget_settings = unserialize($widget_options->option_value);
        	if(is_array($widget_settings)){
	        	foreach ($widget_settings as $key => $widget_setting) {
	        		if(isset($widget_setting['wppcp_visibility']) && $widget_setting['wppcp_visibility'] != '0'){

	        			$restricted_widgets_data['widgets'][] = array('name' => $widget_options->option_name, 'visibility' => $widget_setting['wppcp_visibility']);
	        			$restricted_widgets_data['count']++;
	        		}
	        	}
	        }

        }

        return $restricted_widgets_data;
	}

	public function get_search_stats(){
		$general_options =  isset($this->private_content_settings['search_general']) ? (array) $this->private_content_settings['search_general'] : array();
		$search_data = array();

		$blocked_posts = isset( $general_options['blocked_post_search'] ) ? (array) $general_options['blocked_post_search'] : array();
		$blocked_pages = isset( $general_options['blocked_page_search'] ) ? (array) $general_options['blocked_page_search'] : array();
		$search_data['blocked_posts'] = count($blocked_posts);
		$search_data['blocked_pages'] = count($blocked_pages);

		$search_restrictions = isset($this->private_content_settings['search_restrictions']) ? $this->private_content_settings['search_restrictions'] : array() ;
		$everyone_search_types = isset($search_restrictions['everyone_search_types']) ? (array) $search_restrictions['everyone_search_types'] :array();
        $guests_search_types = isset($search_restrictions['guests_search_types']) ? (array) $search_restrictions['guests_search_types'] :array();
        $members_search_types = isset($search_restrictions['members_search_types']) ? (array) $search_restrictions['members_search_types'] :array();
               
        $search_data['everyone_search_types'] = count($everyone_search_types);
		$search_data['guests_search_types'] = count($guests_search_types);
		$search_data['members_search_types'] = count($members_search_types);
		return $search_data;
	}

	public function get_private_page_stats(){
		global $wpdb;

		$table_private_page = $wpdb->prefix."wppcp_private_page";
        $sql  = $wpdb->prepare( "SELECT * FROM $table_private_page WHERE id != %d ", 0 );
        $result = $wpdb->get_results($sql);
		
		$private_page_data = array();
		$private_page_data['count'] = count($result);
		return $private_page_data;
	}

	public function get_attachments_data(){
		global $wppcp;

		$p_types = $wppcp->posts->get_post_types();
		$skipped_types = array();
        		
        $attachment_data = array();
        $cpt_count = 0;
		foreach ( $p_types as $post_type => $post_type_label ) {
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'meta_key' => '_wppcp_post_attachments',
				'meta_value' => 'a:0:{}',
				'meta_compare' => '!='
			);
			 
			$query = new WP_Query( $args );
			// $attachment_data[$post_type."_count"] = $query->post_count;
			$cpt_count += $query->post_count;
		}

		$attachment_data["cpt_count"] = $cpt_count;

		$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'meta_key' => '_wppcp_post_attachments',
				'meta_value' => 'a:0:{}',
				'meta_compare' => '!='
			);
			 
		$query = new WP_Query( $args );
		$attachment_data["post_count"] = $query->post_count;

		$args = array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_key' => '_wppcp_post_attachments',
			'meta_value' => 'a:0:{}',
			'meta_compare' => '!='
		);
		 
		$query = new WP_Query( $args );
		$attachment_data["page_count"] = $query->post_count;
		
		
		return $attachment_data;
	}

	public function get_private_shortcode_data(){
		global $wpdb;

        $sql  = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_status = '%s' 
        	and post_content like '%s' ", 'publish' , '%[wppcp_private_content%' );
        $result = $wpdb->get_results($sql);

		return array('count' => count($result));
	}

	public function get_global_restriction_data(){
		$post_restrictions = isset($this->private_content_settings['global_post_restriction']) ? $this->private_content_settings['global_post_restriction'] : array();
        $restrict_all_posts_status = isset($post_restrictions['restrict_all_posts_status']) ? $post_restrictions['restrict_all_posts_status'] :'0';
          
        $page_restrictions = isset($this->private_content_settings['global_page_restriction']) ? $this->private_content_settings['global_page_restriction'] : array();
        $restrict_all_pages_status = isset($page_restrictions['restrict_all_pages_status']) ? $page_restrictions['restrict_all_pages_status'] :'0';

        return array('restrict_all_posts_status' => $restrict_all_posts_status ,
        			 'restrict_all_pages_status' => $restrict_all_pages_status ,
        			 'post_count' => wp_count_posts('post')->publish,
        			 'page_count' => wp_count_posts('page')->publish );
	}

	public function get_individual_restriction_data(){
		global $wpdb,$wppcp;
		$args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_key' => '_wppcp_post_page_visibility',
			'meta_value' => 'none',
			'meta_compare' => '!='
		);
		 
		$query = new WP_Query( $args );
		$post_count = $query->post_count;

		$args = array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_key' => '_wppcp_post_page_visibility',
			'meta_value' => 'none',
			'meta_compare' => '!='
		);

		 
		$query = new WP_Query( $args );
		$page_count = $query->post_count;

		$post_data = array('post_count' => $post_count, 'page_count' => $page_count);

		$p_types = $wppcp->posts->get_post_types();
		$skipped_types = array('attachment','revision','nav_menu_item');
        		
        $cpt_count = 0;
		foreach ( $p_types as $post_type => $post_type_label ) {

			if(!in_array($post_type, $skipped_types)){
			   	$args = array(
					'post_type' => $post_type,
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'meta_key' => '_wppcp_post_page_visibility',
					'meta_value' => 'none',
					'meta_compare' => '!='
				);

				 
				$query = new WP_Query( $args );
				$p_count = $query->post_count;
			   	// $post_data[$post_type."_count"] = $p_count;
			   	$cpt_count += $p_count;
		   	}
		}

		$post_data['cpt_count'] = $cpt_count;

		return $post_data;
	}
}





// search wp_options table for widget_ prefix and traverse and check visibility.
// display password protected posts/pages separately