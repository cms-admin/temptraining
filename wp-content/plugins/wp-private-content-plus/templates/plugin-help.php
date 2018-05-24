<?php

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'getting_started';

$title = sprintf( __( 'Welcome to WP Private Content Plus %s', 'wppcp' ), WPPCP_VERSION ) ;
$desc = __( 'Thank you for choosing WP Private Content Plus.','wppcp');
$desc .= "<a href='http://goo.gl/A4fnYE' target='_blank'>".__('Visit Plugin Home Page','wppcp')."</a>";

?>

<div class="wrap about-wrap">
	<h1><?php echo $title; ?></h1>
	<div class="about-text">
		<?php echo $desc; ?>
	</div>

	<h2 class="nav-tab-wrapper">
		<!-- <a class="nav-tab <?php echo ($tab == 'new_version') ? 'nav-tab-active' : '' ; ?>" href="<?php echo admin_url( 'admin.php?page=wppcp-help&tab=new_version' ) ?>">
			<?php _e( 'Whats\'s New in', 'wppcp' ); ?>
		</a> -->

		<a class="nav-tab <?php echo ($tab == 'getting_started') ? 'nav-tab-active' : '' ; ?>" href="<?php echo admin_url( 'admin.php?page=wppcp-help&tab=getting_started' ) ?>">
			<?php _e( 'Getting Started', 'wppcp' ); ?>
		</a>
		<a class="nav-tab <?php echo ($tab == 'support_docs') ? 'nav-tab-active' : '' ; ?>" href="<?php echo admin_url( 'admin.php?page=wppcp-help&tab=support_docs' ) ?>">
			<?php _e( 'Documentation and Support', 'wppcp' ); ?>
		</a>
		<a class="nav-tab <?php echo ($tab == 'wpexpert_plugins') ? 'nav-tab-active' : '' ; ?>" href="<?php echo admin_url( 'admin.php?page=wppcp-help&tab=wpexpert_plugins' ) ?>">
			<?php _e( 'Plugins by WP Expert Developer', 'wppcp' ); ?>
		</a>
		
	</h2>

	<?php if($tab == 'new_version'){ ?> 
	<div class="wpexpert-help-tab">
		<div class="feature-section">
			<h2><?php _e( 'What\'s New in 1.10', 'wppcp' );?></h2>
			<p>Restrict menu items for specific users</p>
			<p>Create user groups and add members</p>
			<p>Restrict post/page/custom post type content by user groups</p>
		</div>
		
	</div>
	<?php } ?>


	<?php if($tab == 'getting_started'){ ?> 
	<div class="wpexpert-help-tab">
		<div class="feature-section">

			
			<h2><?php _e( 'Private Content Shortcodes', 'wppcp' );?></h2>

			<p><?php _e( 'First, you have to enable Private Content Module by going into the <b>General Settings</b> section. 
			This settings allows you to use content restriction features of this plugin.', 'wppcp' ); ?></p>

			<div class="wpexpert-help-screenshot">
				<img src="http://www.wpexpertdeveloper.com/wp-content/uploads/2015/12/settings_1.png" />
			</div>


			<h4><?php _e( 'Using Shortcodes', 'wppcp' );?></h4>
			<p><?php _e( 'You can use the private content shortcodes within any post/page/custom post type to retrict content. Place the
			restricted content within opening and closing shortcode tags as shown in the screen. Change the shortcode and parameters based on 
			your requirements', 'wppcp' );?></p>

			<div class="wpexpert-help-screenshot">
				<img src="http://www.wpexpertdeveloper.com/wp-content/uploads/2015/12/shortcodes_1.png" class="wpexpert-help-screenshot"/>
			</div>

			<h4><?php _e( 'Features and Usage', 'wppcp' );?></h4>
			<p>
				<ul class="wpexpert-help-list">
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-posts-pages-custom-post-types/">Restrict entire posts/pages/custom post types</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-content-by-user-roles/">Restrict content by User roles</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-content-guests-members/">Restrict content for Guest or Members</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-content-by-user-role-levels/">Restrict content by User role Levels</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-content-by-capabilities/">Restrict content by WordPress capabilities</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/private-page-for-users/">Private Page for user profiles</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-menu-items/">Restrict menu for members, guests, user roles</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-widgets/">Restrict widgets for members, guests, user roles</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-post-attachments-downloads/">Restrict post attachments and downloads to for members, guests</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-content-by-multiple-user-meta-keys/">Restrict content by multiple user meta keys</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/restrict-content-by-multiple-user-meta-values/">Restrict content by multiple user meta values</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/site-search-restrictions/">Restrict search content by user types</a></li>
					<li><a href="http://www.wpexpertdeveloper.com/user-profiles-made-easy-integration/">Integration with User Profiles Made Easy</a></li>
					<li><a target="_blank" href="http://www.wpexpertdeveloper.com/protect-entire-site-single-password/">Global Site Protection with Single Password</a></li>

				</ul>
			</p>

			<h4><?php _e( 'Video Documentation', 'wppcp' );?></h4>
			<p><ul class="wpexpert-help-list">
				<li><a href="https://www.youtube.com/channel/UCTORWi9WD_qbAotycppJd7A" target="_blank"
				>Youtube Channel</a></li></ul></p>

			

			
		</div>
		
	</div>
	<?php } ?>

	<?php if($tab == 'support_docs'){ ?>
	<div class="wpexpert-help-tab">

		<div class="feature-section">
			<h2><?php _e( 'Documentation', 'wpexpert' );?></h2>

			<p style='text-align:center'>
				<?php _e( 'Complete documentation for this plugin is available at ', 'wpexpert' ); ?>
				<a target="_blank" href="http://goo.gl/A4fnYE">WP Expert Developer</a>.
			</p>

			<h2><?php _e( 'Video Documentation', 'wpexpert' );?></h2>

			<p style='text-align:center'>
				<?php _e( 'Video documentation for this plugin is available at ', 'wpexpert' ); ?>
				<a target="_blank" href="https://www.youtube.com/channel/UCTORWi9WD_qbAotycppJd7A">WP Expert Developer Youtube Channel</a>.
			</p>

			<h2><?php _e( 'Support', 'wpexpert' );?></h2>

			<p style='text-align:center'><?php _e('You can get free support fot this plugin at '); ?>
				<a target="_blank" href="https://wordpress.org/support/plugin/wp-private-content-plus"><?php _e('wordpress.org','wpexpert');?></a>.
			</p>


		</div>
	</div>
	<?php } ?>

	<?php if($tab == 'wpexpert_plugins'){ ?>
	<div class="wpexpert-help-tab">

		<div class="feature-section">

			<h2><?php _e('Explore WP Expert Developer Plugins'); ?></h2>

			<div class="wpexpert-plugins-panel">
				<?php
					global $wppcp,$wpexpert_plugins_data;
					$plugins_json = wp_remote_get( 'http://www.wpexpertdeveloper.com/plugins.json');  
	        
			        if ( ! is_wp_error( $plugins_json ) ) {

			            $plugins = json_decode( wp_remote_retrieve_body($plugins_json) );
			            $plugins = $plugins->featured;

			            
			        }else{
			        	$plugins = array();
			        }

		        	$wpexpert_plugins_data['plugins'] = $plugins;
        
			        ob_start();
			        $wppcp->template_loader->get_template_part('plugins-feed');
			        $display = ob_get_clean();
			        echo $display;
		        ?>
				
			</div>
		</div>
	</div>
	<?php } ?>

</div>
