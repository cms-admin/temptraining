<?php
    global $wppcp_password_protect_data;
    extract($wppcp_password_protect_data);

?>
<style>
    .wppcp_password_protect_panel{ background: #f1f1f1 none repeat scroll 0 0;
    border: 1px solid #cfcfcf;
    margin: 100px auto;
    padding: 20px;
    text-align: center;
    width: 60%;}
    .wppcp_panel_title{ font-size: 30px; font-weight: bold; padding: 10px; }
    .wppcp_panel_message{ padding: 10px; }
    .wppcp_panel_fields{ margin: auto;
    padding: 10px;
    width: 400px; max-width: 94%}
    .wppcp_panel_label{ float: left;
    font-size: 20px;
    font-weight: bold;
    padding: 5px 5px 5px 0; }
    .wppcp_panel_field{ float: left; }
    .wppcp_panel_field input{ border: 1px solid #cfcfcf; height: 35px !important; padding: 5px !important;width: 210px; max-width: 100%;}
    .wppcp_panel_submit input{ background: #0c537e none repeat scroll 0 0;
    border: 1px solid #1e488b;
    border-radius: 0;
    color: #fff ;
    font-size: 16px;
    font-weight: bold;
    height: 34px;
    margin-left: 10px;
    padding: 5px 10px;}
    .wppcp_panel_error{color: red; font-weight: bold; padding: 5px 10px;}


    @media only screen and (min-width: 767px) and (max-width: 980px) {

    }

    @media only screen and (min-width: 480px) and (max-width: 767px) {
        .wppcp_panel_label,.wppcp_panel_field,.wppcp_panel_submit{float: none;margin: 5px 0;}
    }

    @media only screen and (min-width: 261px) and (max-width: 480px) {
        .wppcp_panel_label,.wppcp_panel_field,.wppcp_panel_submit{float: none;margin: 5px 0;}
    }
</style>
<form method="POST" >
<div class="wppcp_password_protect_panel">
    
    
    <div class="wppcp_panel_title"><?php echo $protected_form_header; ?></div>
    <?php if($password_protect_error != ''){ ?>
        <div class="wppcp_panel_error"><?php echo $password_protect_error; ?></div>
    <?php } ?>

    <div class="wppcp_panel_message"><?php echo $protected_form_message; ?></div>
    <div class="wppcp_panel_fields">
        <div class="wppcp_panel_label"><?php _e('Password','wppcp'); ?></div>
        <div class="wppcp_panel_field">
            <input type='text' name='site_protect_password' id='site_protect_password' />
        </div>
        <div class="wppcp_panel_submit">
            <?php wp_nonce_field( 'wppcp_password_protect', 'wppcp_password_protect_nonce' ); ?>
            <input type='submit' name='site_protect_password_submit' value='<?php _e('Submit','wppcp'); ?>' />
        </div>
    </div>
</div>
                    
                    </form>