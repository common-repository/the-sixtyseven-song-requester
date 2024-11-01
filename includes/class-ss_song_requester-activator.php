<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.sixtyseven.info
 * @since      1.0.0
 *
 * @package    ss_song_requester
 * @subpackage ss_song_requester/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    ss_song_requester
 * @subpackage ss_song_requester/includes
 * @author     André R. Kohl <code@sixtyseven.info>
 */

class Ss_song_requester_Activator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        global $wpdb;
        global $wp_roles;
        
        // add capabilities
        $wp_roles->add_cap( 'administrator', 'ss_manage_song_requests' );
        $wp_roles->add_cap( 'administrator', 'ss_manage_song_request_settings' );
        
        // table name
        $table_name = $wpdb->prefix . 'ss_song_requester_current_requests';
        
        // check if table exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            
            // get charset and create table
            $charset_collate = $wpdb->get_charset_collate();
 
            $create_sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . '(  
                id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                title varchar(500) NOT NULL,
                artist varchar(50) NOT NULL, 
                count int(50) NOT NULL,
                played varchar(50) NOT NULL
             ) ' . $charset_collate . ';'; 
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($create_sql);
        }
	}

}
