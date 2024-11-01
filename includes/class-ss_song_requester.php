<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, global hooks, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin and its options.
 *
 * @since      1.0.0
 * @package    ss_song_requester
 * @subpackage ss_song_requester/includes
 * @author     André R. Kohl <code@sixtyseven.info>
 */
class ss_song_requester {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ss_song_requester_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
    
    /**
	 * The options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options   The settings of this plugin.
	 */
	private $options;
    
    
    /**
	 * Instance of this class.
     *
	 * @since    1.0.0
	 * @access   protected static
	 * @var      object    $_instance    The class instance.
	 */
	protected static $_instance = null;
    
    /**
	 * Return an instance of this class.
     *
	 * @since    1.0.0
	 * @return   object    A single instance of this class.
	 */
	public static function instance(){
		// If the single instance hasn't been set, set it now.
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 * @since 1.0.0
	 */
	public function __clone(){
		_doing_it_wrong(__FUNCTION__, __('Cheating, huh?', 'ss_song_requester'), '1.0');
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 1.0.0
	 */
	public function __wakeup(){
		_doing_it_wrong(__FUNCTION__, __('Cheating, huh?', 'ss_song_requester'), '1.0');
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SS_SONG_REQUESTER_VERSION' ) ) {
			$this->version = SS_SONG_REQUESTER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ss_song_requester';

		$this->load_dependencies();
		$this->set_locale();
        
        $this->define_global_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ss_song_requester_Loader. Orchestrates the hooks of the plugin.
	 * - Ss_song_requester_i18n. Defines internationalization functionality.
	 * - Ss_song_requester_Admin. Defines all hooks for the admin area.
	 * - Ss_song_requester_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SS_SONG_REQUESTER_DIR . 'includes/class-ss_song_requester-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SS_SONG_REQUESTER_DIR . 'includes/class-ss_song_requester-i18n.php';


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SS_SONG_REQUESTER_DIR . 'public/class-ss_song_requester-public.php';
            
        
        /**
		 *  WP_List_Table is not loaded automatically so we need to load it in our application
         */
        if( ! class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        
        /**
		 * The class responsible for listing the song requests in admin
		 */
		require_once SS_SONG_REQUESTER_DIR . 'includes/class-ss_song_requester-list-table.php';
        
        /**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SS_SONG_REQUESTER_DIR . 'admin/class-ss_song_requester-admin.php';
        
		$this->loader = new ss_song_requester_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ss_song_requester_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new ss_song_requester_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}
    
    /**
	 * Register all of the global hooks (used in front- and as well in backend).
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_global_hooks() {
        // constants
		$this->loader->add_action( 'init', $this, 'define_template_constants' );
        
        // shortcodes
		$this->loader->add_action( 'init', $this, 'shortcodes' );
        
        // defer tag for font awesome
        $this->loader->add_filter( 'script_loader_tag', $this, 'filter_script_tag', 10, 2 );
        
        // globally used scripts and styles
        $this->loader->add_action( 'wp_enqueue_scripts', $this, 'process_scripts' );
        $this->loader->add_action( 'admin_enqueue_scripts', $this, 'process_scripts' );
    }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new ss_song_requester_admin( $this->get_plugin_name(), $this->get_version() );
        
        // settings
        $this->options = $plugin_admin::get_settings();
        
        // admin pages
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_page' );
        
        // register settings
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        
        // set the requests and settings variables etc.
        $this->loader->add_action( 'init', $plugin_admin, 'init' );
        
        // enqueue global css and js
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_global_assets' );
        
        // plugin action links
        $this->loader->add_filter( 'plugin_action_links_' . SS_SONG_REQUESTER_BASENAME, $plugin_admin, 'plugin_action_links' );
        
        // plugin meta links
        $this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_meta_links', 10, 4 );
        
        // help tabs
        $this->loader->add_action( 'current_screen', $plugin_admin, 'help_tabs' );
        
        // edit in place ajax
        $this->loader->add_action( 'wp_ajax_ss_request_change_attribute_backend', $plugin_admin, 'change_attribute' );
        
        // tinymce button
        $this->loader->add_action('admin_head', $plugin_admin, 'add_tinymce_button');
        $this->loader->add_filter( 'mce_external_languages', $plugin_admin, 'add_tinymce_lang');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new ss_song_requester_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'process_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'process_scripts' );
        
        // init
		$this->loader->add_action( 'init', $plugin_public, 'init' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ss_song_requester_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
    
    /**
	 * Generates all shortcodes
	 *
	 * @since    1.0.0
	 */
	public function shortcodes() {		
		// ss_song_requester
		if ( ! shortcode_exists( 'ss_song_requester' ) ) {
			require_once(SS_SONG_REQUESTER_DIR . 'includes/shortcodes/shortcode.song_request.php');
			ss_song_request_shortcode::init();
		}
    }
    
    /**
     * Filter the HTML script tag
     *
     * @param string $tag    The <script> tag for the enqueued script.
     * @param string $handle The script's registered handle.
     *
     * @return   Filtered HTML script tag.
     */
    public function filter_script_tag( $tag, $handle ) {
        if ( 'ss_song_requester_font-awesome' === $handle ) {
            // add defer attribute, integrity and crossorigin
            $tag = str_replace( ' src', ' defer  integrity="sha384-GqVMZRt5Gn7tB9D9q7ONtcp4gtHIUEW/yG7h98J7IpE3kpi+srfFyyB/04OV6pG0" crossorigin="anonymous" src', $tag );
        }

        return $tag;
    }
    
    /**
	 * Register the JavaScript that is globally used.
	 *
	 * @since    1.0.0
	 */
	public function process_scripts() {
		#wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ss_song_requester-public.js', array( 'jquery' ), $this->version, false );
        
        // register scripts, will be enqueued by shortcodes, public or admin class themselves
        wp_register_script( 'ss_song_requester_inline_edit', SS_SONG_REQUESTER_URL . 'global/js/inlineedit.js', array('jquery'), '1.0.0');
        
        // register external scripts
        wp_register_script( 'ss_song_requester_font-awesome', 'https://use.fontawesome.com/releases/v5.5.0/js/all.js', array(), '5.2.0');

	}
    
    /**
	 * Define constants for the theme dir and theme url
	 *
	 * @since    1.0.0
	 */
    public function define_template_constants(){
        // theme path and URL
        if(is_child_theme()){
            $theme_path = get_stylesheet_directory();
            $theme_url = get_stylesheet_directory_uri();
        } else {
            $theme_path = get_template_directory();
            $theme_url = get_template_directory_uri();
        }
        if(!defined('SS_SONG_REQUESTER_THEME_DIR')){
            define('SS_SONG_REQUESTER_THEME_DIR', $theme_path);
        }
        if(!defined('SS_SONG_REQUESTER_THEME_URL')){
            define('SS_SONG_REQUESTER_THEME_URL', $theme_url);
        }
    }

}
