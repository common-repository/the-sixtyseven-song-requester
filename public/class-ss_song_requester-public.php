<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.sixtyseven.info
 * @since      1.0.0
 *
 * @package    Ss_song_requester
 * @subpackage Ss_song_requester/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ss_song_requester
 * @subpackage Ss_song_requester/public
 * @author     André R. Kohl <code@sixtyseven.info>
 */
class ss_song_requester_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
    
     /**
	 * Option key to get settings
	 *
	 * @var string
	 */
	protected static $option_key = 'ss_song_requester_settings';
    
    
    /**
     * Holds the values of the settings
     *
     * @var array
     */
    private $options = array();
    

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
    
    /**
     * Grant privileges etc.
     */
    public function init(){
         // Set class property
        $this->options = ss_song_requester_admin::get_settings();
        
        // set capabilities
        global $wp_roles;
        foreach (array_keys($wp_roles->roles) as $role) {
            if(in_array($role, $this->options['access_settings'])){
                $wp_roles->add_cap( $role, 'ss_manage_song_request_settings' );
            }
            
            if(in_array($role, $this->options['manage_requests'])){
                $wp_roles->add_cap( $role, 'ss_manage_song_requests' );
            }
        }
    }
    
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function process_styles() {
        
        // register styles for shortcodes, will be enqueued by the shortcodes themselves. Look for existing css file in theme subfolder mytheme/css first.
        if(file_exists(SS_SONG_REQUESTER_THEME_DIR . 'css/ss_song_requester-public.css')){
            wp_register_style( 'ss_song_requester_shortcode_style', SS_SONG_REQUESTER_THEME_URL . 'css/ss_song_requester-public.css', array(), '1.0.0' );
        } else {
            wp_register_style( 'ss_song_requester_shortcode_style', SS_SONG_REQUESTER_URL . 'public/css/ss_song_requester-public.css', array(), '1.0.0' );
        }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function process_scripts() {
		#wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ss_song_requester-public.js', array( 'jquery' ), $this->version, false );
        
        // register scripts for shortcodes, will be enqueued by the shortcodes themselves
        wp_register_script( 'ss_song_requester_shortcode_script', SS_SONG_REQUESTER_URL . 'public/js/ss_song_requester-shortcode.js', array('jquery'), '1.0.0');

	}

}
