<?php

if (!function_exists('wppcp_add_query_string')) {

    function wppcp_add_query_string($link, $query_str) {

        $build_url = $link;

        $query_comp = explode('&', $query_str);

        foreach ($query_comp as $param) {
            $params = explode('=', $param);
            $key = isset($params[0]) ? $params[0] : '';
            $value = isset($params[1]) ? $params[1] : '';
            $build_url = esc_url_raw(add_query_arg($key, $value, $build_url));
        }

        return $build_url;
    }

}

function display_donation_block(){
    $display = '<div class="wppcp_donation_box">
                <div style="    float: left;
    width: 80%;
    line-height: 25px;">WP Private Content Plus is offered as a free plugin. Please consider a small $1 donation to continue the development and support of this plugin and keep it alive.</div>
                
                <div style="float:left">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input name="cmd" type="hidden" value="_s-xclick" />
                
                    <input name="hosted_button_id" type="hidden" value="C8RF32ZZW6PVL" />
                    <input alt="PayPal — The safer, easier way to pay online." name="submit" src="https://www.paypalobjects.com/en_AU/i/btn/btn_donateCC_LG.gif" type="image" />
                    <img src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></form>
                </div>
                <div style="clear:both"></div>
                </div>';
    return $display;
}

function display_pro_block(){
    $display = '<div class="wppcp_donation_box">
                <div style="    float: left;
    width: 80%;
    line-height: 25px;">This feature is only available in PRO version. You can check more about these features at <a style="color:#FFF;" href="http://goo.gl/2Zr089">WPExpert Developer</a></div>
                
                
                <div style="clear:both"></div>
                </div>';
    return $display;
}

if (!function_exists('wppcp_current_page_url')) {

    function wppcp_current_page_url() {
      $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
      $url .= $_SERVER["REQUEST_URI"];
      return $url;
    }

}


if (!function_exists('wppcp_display_pro_info_box')) {

    function wppcp_display_pro_info_box($message,$location,$settings_path) {

        $locations = array('post_meta_boxes' => 'http://bit.ly/2fGOsDf','post_meta_boxes_large' => 'http://bit.ly/2fGOsDf');
        $style = '';
        if($location == 'post_meta_boxes_large'){
            $style = 'height:120px !important;padding: 25px 20px !important;';
        }

        $settings_path = admin_url( 'admin.php?page=wppcp-settings&tab=wppcp_section_information#' ) . $settings_path;
        $display = '<div class="wppcp-pro-info-box">
                        <div class="wppcp-pro-info-box-col1" >
                            <img src="'.WPPCP_PLUGIN_URL.'images/icon-128x128.png" /></div>
                        <div class="wppcp-pro-info-box-col2">'.$message.'</div>
                        <div class="wppcp-pro-info-box-col3" style="'.$style.'">
                            <a href="'.$locations[$location] .'" class="wppcp-pro-info-notice button-primary" target="_blank">
                            '. __('Go PRO License','wppcp') .'</a>
                            <a href="'.$settings_path.'" style="margin-top:10px;" class="wppcp-pro-info-notice button-primary">
                            '. __('Hide Info','wppcp') .'</a>
                        </div>
                        <div class="wppcp-clear"></div>
                    </div>';
        return $display;
    }
}

if (!function_exists('wppcp_display_pro_sidebar_info_box')) {

    function wppcp_display_pro_sidebar_info_box() {

        $tick_url = WPPCP_PLUGIN_URL. 'images/tick.png';

        $display = '<div id="wppcp-pro-version-sidebar-panel">
                        <div id="wppcp-pro-version-sidebar-header">
                            '. __('Why Go PRO License?','wppcp').'
                        </div>
                        <div id="wppcp-pro-version-sidebar-features">
                            <ul>
                                <li><img src="'.$tick_url.'" /><span>'. __('Membership Level Management','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Sell Memberships with Woocommerce','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Private Page Discussions','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Private Page File Sharing','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Mailchimp Content Locker','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Awesome Frontend User Groups','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Woocommerce Product Protection','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('bbPress Forums and Topics Protection','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Advanced Search Restrictions','wppcp').'</span></li>
                                <li><img src="'.$tick_url.'" /><span>'. __('Complete Protection for Post Attachments','wppcp').'</span></li>
                            </ul>
                        </div>
                        <div id="wppcp-pro-version-sidebar-buy">
                            <a class="wppcp-upgrading-pro-button" style="margin:10px auto" href="http://bit.ly/2fGOsDf">'. __('Upgrade to PRO License','wppcp').'</a>
                        </div>
                    </div>';
        return $display;
    }
}


if (!function_exists('wppcp_addons_feed')) {
    function wppcp_addons_feed() {
        global $wppcp,$wppcp_addon_template_data;

        $wppcp_addon_template_data['active_plugins'] = get_option('active_plugins');
        
        $addons_json = wp_remote_get( 'http://www.wpexpertdeveloper.com/addons.json');          

        if ( ! is_wp_error( $addons_json ) ) {
            $addons = json_decode( wp_remote_retrieve_body($addons_json) );
            $addons = $addons->featured;            
        }else{
            $addons = array();
        }
        
        $wppcp_addon_template_data['addons'] = $addons;
        
        ob_start();
        $wppcp->template_loader->get_template_part('addons','feed');
        $display = ob_get_clean();
        echo $display;
    }
}

if (!function_exists('wppcp_info_button_labels')) {
    function wppcp_info_button_labels(){
        $labels = array();
        $labels['help'] = apply_filters('wppcp_info_button_label_help', __('Help', 'wppcp'));
        $labels['docs'] = apply_filters('wppcp_info_button_label_docs', __('Documentation', 'wppcp'));
    
        $labels['help_link'] = apply_filters('wppcp_info_button_help_link', "http://www.wpexpertdeveloper.com/support/");
    
        
        return $labels;
    }
}

if (!function_exists('wppcp_display_info_buttons')) {
    function wppcp_display_info_buttons($url,$type){

        ob_start();
        $info_button_data = wppcp_info_button_labels();
    ?>

        <div class="wppcp-post-meta-info-buttons">
            <a target="_blank" href="<?php echo $info_button_data['help_link']; ?>?ref=<?php echo $type; ?>">
                <div class="wppcp-post-meta-info-button wppcp-post-meta-info-help">
                    <span class="dashicons dashicons-editor-help"></span>
                    <?php echo $info_button_data['help']; ?></div>
            </a>
            <a target="_blank" href="<?php echo $url; ?>?ref=<?php echo $type; ?>" >
                <div class="wppcp-post-meta-info-button wppcp-post-meta-info-docs">
                <span class="dashicons  dashicons-book-alt"></span>
                <?php echo $info_button_data['docs']; ?></div>
            </a>
        </div>
        <div class="wppcp-clear"></div>

    <?php 
        $display = ob_get_clean();
        return $display;
    }
}

if (!function_exists('wppcp_get_client_ip')) {
    function wppcp_get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}