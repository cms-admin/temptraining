<?php   global $wppcp_addon_template_data;
        extract($wppcp_addon_template_data); 
?>
<div id="wppcp-addons-feed">
    
    <?php 

        foreach($addons as $addon){ 
            $addon = (array) $addon;
            extract($addon);           
            
            if(in_array($name,$active_plugins)){
                $status = __('Activated','wppcp');
                $status_class = 'activated';
            }else{
                $status = __('Deactivated','wppcp');
                $status_class = 'deactivated'; 
            }
    ?>
            <div class="wppcp-addon-single">
                <div class="wppcp-addon-single-title"><?php echo $title; ?></div>
                <div class="wppcp-addon-single-image">
                    <img src="<?php echo $image; ?>" />
                </div>
                <div class="wppcp-addon-single-desc"><?php echo $desc; ?></div>
                <div class="wppcp-addon-single-buttons">
                    <div class="wppcp-addon-single-status <?php echo $status_class; ?> "><?php echo $status; ?></div>
                    <div class="wppcp-addon-single-type"><?php echo $type; ?></div>
                    <div class="wppcp-addon-single-get"><a href="<?php echo $download; ?>"><?php echo __('Purchase','wppcp'); ?></a></div>
                    <div class="wppcp-clear"></div>
                </div>
                
            </div>
    <?php } ?>
    
    
</div>