<?php

$title = __( 'Welcome to WP Private Content PRO', 'wppcp' ) ;

?>

<div class="wrap about-wrap" style="background:#fff;padding:20px;">
	<h1 style="text-align:center;width:100%;margin:20px 0;"><?php echo $title; ?></h1>
	 <a href="http://goo.gl/2Zr089"><div style="    width: 200px;
    text-align: center;
    margin: auto;
    padding: 20px;
    background: #eee;
    font-size: 20px;
    font-weight: bold;
    border: 1px solid #cfcfcf;">View More</div></a>
	<?php
		global $wppcp,$wpexpert_plugins_data;
		$pro = wp_remote_get( 'http://www.wpexpertdeveloper.com/pro.html');  

        if ( ! is_wp_error( $pro ) ) {
            echo $pro['body'];
        }
        
    ?>		

</div>
