<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.sixtyseven.info
 * @since             1.0.0
 * @package           ss_song_requester
 *
 * @wordpress-plugin
 * Plugin Name:       The sixtyseven song requester
 * Plugin URI:        https://www.sixtyseven.info
 * Description:       Request a song via your website and let your DJ mark them as played
 * Version:           1.0.3
 * Author:            André R. Kohl
 * Author URI:        http://www.andre-kohl.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ss_song_requester
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Starts at version 1.0.0 and uses SemVer - https://semver.org
 */
if(!defined('SS_SONG_REQUESTER_VERSION')){
    define( 'SS_SONG_REQUESTER_VERSION', '1.0.3' );
}

// Path to plugin
if(!defined('SS_SONG_REQUESTER_DIR')){
    define('SS_SONG_REQUESTER_DIR', plugin_dir_path( __FILE__ ) );
}

 // URL to plugin
if(!defined('SS_SONG_REQUESTER_URL')){
    define('SS_SONG_REQUESTER_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin basename
if(!defined('SS_SONG_REQUESTER_BASENAME')){
    define('SS_SONG_REQUESTER_BASENAME', plugin_basename(  __FILE__  ) );
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ss_song_requester-activator.php
 */
function activate_ss_song_requester() {
	require_once SS_SONG_REQUESTER_DIR . 'includes/class-ss_song_requester-activator.php';
	ss_song_requester_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ss_song_requester-deactivator.php
 */
function deactivate_ss_song_requester() {
	require_once SS_SONG_REQUESTER_DIR . 'includes/class-ss_song_requester-deactivator.php';
	ss_song_requester_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ss_song_requester' );
register_deactivation_hook( __FILE__, 'deactivate_ss_song_requester' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require SS_SONG_REQUESTER_DIR . 'includes/class-ss_song_requester.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ss_song_requester() {
	$plugin = ss_song_requester::instance();
	$plugin->run();
}
run_ss_song_requester();
