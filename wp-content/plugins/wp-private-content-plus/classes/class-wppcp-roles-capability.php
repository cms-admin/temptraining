<?php
/* Manage user role and capability functions */
class WPPCP_Roles_Capability {

    private $user_roles;

    public function __construct() { }

    public function wppcp_user_roles(){
        global $wp_roles;

        $roles = $wp_roles->get_names();
        return $roles;
    }
    
    /* Get the roles of the given user */
    public function get_user_roles_by_id($user_id) {
        $user = new WP_User($user_id);
        if (!empty($user->roles) && is_array($user->roles)) {
            $this->user_roles = $user->roles;
            return $user->roles;
        } else {
            $this->user_roles = array();
            return array();
        }
    }

}
