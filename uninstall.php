<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://www.sixtyseven.info
 * @since      1.0.0
 *
 * @package    ss_song_requester
 */

// If uninstall not called from WordPress, then exit. Else perform uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
} else {
    ss_song_requester_uninstall(); 
}


function ss_song_requester_uninstall(){
    // get plugin settings
    $options = get_option('ss_song_requester_settings');
    
    if('drop_data' === $options['uninstall_behaviour']){
        global $wpdb;
        
        // table name
        $table_names = array();
        $table_names[] = $wpdb->prefix . 'ss_song_requester_current_requests';

        // drop table(s);
        foreach($table_names as $table_name){
            $sql = 'DROP TABLE IF EXISTS ' . $table_name . ';';
            $wpdb->query($sql);
        }

        delete_option('ss_song_requester_settings');
    }
}