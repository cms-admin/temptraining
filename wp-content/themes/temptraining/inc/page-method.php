<?php
$user = wp_get_current_user();
$allowed_roles = array('shop_manager', 'administrator');
if( array_intersect($allowed_roles, $user->roles ) ) {  ?> 
    <a href="https://docs.google.com/document/d/12WHcQ0DbJE20qbXfEEgmGfnLH6Z9Rd0YzW2s1oDvArg/edit" target="_blank">Методичка</a>
<?php } ?>
