<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.sixtyseven.info
 * @since      1.0.0
 *
 * @package    ds_song_requester
 * @subpackage ds_song_requester/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    ss_song_requester
 * @subpackage ss_song_requester/includes
 * @author     André R. Kohl <code@sixtyseven.info>
 */

class ss_song_requester_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        global $wp_roles;
        
        // remove capabilities
        $delete_caps = array('ss_manage_song_requests');
        
        foreach ($delete_caps as $cap) {
            foreach (array_keys($wp_roles->roles) as $role) {
                $wp_roles->remove_cap($role, $cap);
            }
        }
	}

}
