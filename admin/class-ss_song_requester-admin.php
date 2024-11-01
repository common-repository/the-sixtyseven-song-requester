<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.sixtyseven.info
 * @since      1.0.0
 *
 * @package    Ss_song_requester
 * @subpackage Ss_song_requester/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ss_song_requester
 * @subpackage Ss_song_requester/admin
 * @author     André R. Kohl <code@sixtyseven.info>
 */
class ss_song_requester_admin {

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
	 * Option key to save settings
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
     * Holds the values and names of the allowed musical genres
     *
     * @var array
     */
    public $genres = array();
    
    /**
     * Holds the values and names of the allowed countries to get charts from
     *
     * @var array
     */
    public $countries = array();
	
    /**
	 * Default settings
	 *
	 * @var array
	 */
	protected static $defaults = array(
        'form_status' => 'active',
        'uninstall_behaviour' => 'keep_data',
        'access_settings' => array('administrator'),
        'manage_requests' => array('administrator'),
		'allow_backlink' => 'allow_backend',
		'autoreload_seconds' => 130,
        'bubble_background' => '#ff8a4a',
        'bubble_text' => '#ffffff',
        'requests_per_page' => 20,
        'frontend_sort_type' => 'artist',
        'frontend_sort_direction' => 'DESC',
	);
    
    /**
	 * The current requests.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $current_requests   The current song requests on page load.
	 */
    public $current_requests;
    
    /**
	 * The list table object
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      object    $request_table   The class responsible for the admin list view.
	 */
    public $request_table;
    

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Get saved settings
	 *
     * @since    1.0.0
	 * @return array
	 */
	public static function get_settings(){
		$saved = get_option( self::$option_key, array() );
		if( ! is_array( $saved ) || empty( $saved )){
			return self::$defaults;
		}
		return wp_parse_args( $saved, self::$defaults );
	}
    
     /**
     * Set requests array etc.
     */
    public function init(){
        global $wpdb;
        
        // get all requests
        $this->current_requests = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests');
        
         // Set class property
        $this->options = self::get_settings();
        
        add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
        
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
        
          // valid countries to get charts from
        $countries = array(
            'AR' => __('Argentina', 'ss_song_requester'),
            'AU' => __('Australia', 'ss_song_requester'),
            'AT' => __('Austria', 'ss_song_requester'),
            'BE' => __('Belgium', 'ss_song_requester'),
            'BR' => __('Brazil', 'ss_song_requester'),
            'CA' => __('Canada', 'ss_song_requester'),
            'CL' => __('Chile', 'ss_song_requester'),
            'CO' => __('Colombia', 'ss_song_requester'),
            'CR' => __('Costa Rica', 'ss_song_requester'),
            'HR' => __('Croatia', 'ss_song_requester'),
            'CZ' => __('Czech Republic', 'ss_song_requester'),
            'DK' => __('Denmark', 'ss_song_requester'),
            'SV' => __('El Salvador', 'ss_song_requester'),
            'FI' => __('Finland', 'ss_song_requester'),
            'FR' => __('France', 'ss_song_requester'),
            'DE' => __('Germany', 'ss_song_requester'),
            'GR' => __('Greece', 'ss_song_requester'),
            'GT' => __('Guatemala', 'ss_song_requester'),
            'HK' => __('Hong Kong', 'ss_song_requester'),
            'HU' => __('Hungary', 'ss_song_requester'),
            'IN' => __('India', 'ss_song_requester'),
            'ID' => __('Indonesia', 'ss_song_requester'),
            'IE' => __('Ireland', 'ss_song_requester'),
            'IL' => __('Israel', 'ss_song_requester'),
            'IT' => __('Italy', 'ss_song_requester'),
            'JP' => __('Japan', 'ss_song_requester'),
            'KR' => __('Republic of Korea', 'ss_song_requester'),
            'KW' => __('Kuwait', 'ss_song_requester'),
            'LB' => __('Lebanon', 'ss_song_requester'),
            'LU' => __('Luxembourg', 'ss_song_requester'),
            'MY' => __('Malaysia', 'ss_song_requester'),
            'MX' => __('Mexico', 'ss_song_requester'),
            'NL' => __('Netherlands', 'ss_song_requester'),
            'NZ' => __('New Zealand', 'ss_song_requester'),
            'NO' => __('Norway', 'ss_song_requester'),
            'PK' => __('Pakistan', 'ss_song_requester'),
            'PA' => __('Panama', 'ss_song_requester'),
            'PE' => __('Peru', 'ss_song_requester'),
            'PH' => __('Philippines', 'ss_song_requester'),
            'PL' => __('Poland', 'ss_song_requester'),
            'PT' => __('Portugal', 'ss_song_requester'),
            'QA' => __('Qatar', 'ss_song_requester'),
            'RO' => __('Romania', 'ss_song_requester'),
            'RU' => __('Russia', 'ss_song_requester'),
            'SA' => __('Saudi Arabia', 'ss_song_requester'),
            'SG' => __('Singapore', 'ss_song_requester'),
            'SK' => __('Slovakia', 'ss_song_requester'),
            'SI' => __('Slovenia', 'ss_song_requester'),
            'ZA' => __('South Africa', 'ss_song_requester'),
            'ES' => __('Spain', 'ss_song_requester'),
            'LK' => __('Sri Lanka', 'ss_song_requester'),
            'SE' => __('Sweden', 'ss_song_requester'),
            'CH' => __('Switzerland', 'ss_song_requester'),
            'TW' => __('Taiwan', 'ss_song_requester'),
            'TH' => __('Thailand', 'ss_song_requester'),
            'TR' => __('Turkey', 'ss_song_requester'),
            'GB' => __('United Kingdom', 'ss_song_requester'),
            'US' => __('United States', 'ss_song_requester'),
            'AE' => __('United Arab Emirates', 'ss_song_requester'),
            'VE' => __('Venezuela', 'ss_song_requester'),
            'VN' => __('Vietnam', 'ss_song_requester'),
        );
        asort( $countries );
        $this->countries = $countries;

        // valid genres to get charts from
        $genres = array(
            20       => __('Alternative', 'ss_song_requester'),
			29       => __('Anime', 'ss_song_requester'),
			2        => __('Blues', 'ss_song_requester'),
			1122     => __('Brazilian', 'ss_song_requester'),
			4        => __('Children Music', 'ss_song_requester'),
			1232     => __('Chinese', 'ss_song_requester'),
			22       => __('Christian Gospel', 'ss_song_requester'),
			5        => __('Classical', 'ss_song_requester'),
			3        => __('Comedy', 'ss_song_requester'),
			6        => __('Country', 'ss_song_requester'),
			17       => __('Dance', 'ss_song_requester'),
			50000063 => __('Disney', 'ss_song_requester'),
			25       => __('Easy Listening', 'ss_song_requester'),
			7        => __('Electronic', 'ss_song_requester'),
			28       => __('Enka', 'ss_song_requester'),
			50       => __('Fitness and Workout', 'ss_song_requester'),
			50000064 => __('French Pop', 'ss_song_requester'),
			50000068 => __('German Folk', 'ss_song_requester'),
			50000066 => __('German Pop', 'ss_song_requester'),
			18       => __('Hip-Hop, Rap', 'ss_song_requester'),
			8        => __('Holiday music', 'ss_song_requester'),
			1262     => __('Indian', 'ss_song_requester'),
			53       => __('Instrumental', 'ss_song_requester'),
			27       => __('J-Pop', 'ss_song_requester'),
			11       => __('Jazz', 'ss_song_requester'),
			51       => __('K-Pop', 'ss_song_requester'),
			52       => __('Karaoke', 'ss_song_requester'),
			30       => __('Kayokyoku', 'ss_song_requester'),
			1243     => __('Korean', 'ss_song_requester'),
			12       => __('Latino', 'ss_song_requester'),
			13       => __('New Age', 'ss_song_requester'),
			9        => __('Opera', 'ss_song_requester'),
			14       => __('Pop', 'ss_song_requester'),
			15       => __("R'n'B, Soul", 'ss_song_requester'),
			24       => __('Reggae', 'ss_song_requester'),
			21       => __('Rock', 'ss_song_requester'),
			10       => __('Singer Songwriter', 'ss_song_requester'),
			16       => __('Soundtrack', 'ss_song_requester'),
			50000061 => __('Spoken Word', 'ss_song_requester'),
			23       => __('Vocal', 'ss_song_requester'),
			19       => __('World', 'ss_song_requester')
        );
        asort( $genres );
        $this->genres = $genres;
    }
    
     /**
     * Add options page
     * @since    1.0.0
     */
    public function add_plugin_page(){
        global $wpdb;
            
        if(!empty($this->current_requests)){
            $bubble = ' <span class="update-plugins" style="background-color: ' . $this->options['bubble_background'] . '; color: ' . $this->options['bubble_text'] . '"><span class="plugin-count">' . count($this->current_requests) . '</span></span>';
        } else {
            $bubble = '';
        }
            
        // This page will be most likely the last in menu
        $mainmenu = add_menu_page(
            __('Welcome to the sixtyseven song requester','ss_song_requester'), 
            __('Song requests','ss_song_requester'). $bubble, 
            'ss_manage_song_requests', 
            'ss_song_requester_admin', 
            array( $this, 'display_requests' ),
            'dashicons-format-audio',
            99.99
        );
        
        $requestsmenu = add_submenu_page(  
            'ss_song_requester_admin',                  
            __('Currently requested songs','ss_song_requester'),       
            __('Current requests','ss_song_requester'),                  
            'ss_manage_song_requests',            
            'ss_song_requester_admin',    
            array( $this, 'display_requests' )
        ); 
        
        $settingsmenu = add_submenu_page(  
            'ss_song_requester_admin',                  
            __('Settings for the sixtyseven song requester','ss_song_requester'),       
            __('Settings','ss_song_requester'),                  
            'ss_manage_song_request_settings',            
            'ss_song_requester_admin_settings',    
            array( $this, 'create_admin_page_settings' )
        ); 
        
        $aboutmenu = add_submenu_page(  
            'ss_song_requester_admin',                  
            __('About the sixtyseven song requester','ss_song_requester'),       
            __('About','ss_song_requester'),                  
            'read',            
            'ss_song_requester_admin_about',    
            array( $this, 'create_admin_page_about' )
        );
        
        $demomenu = add_submenu_page(  
            'ss_song_requester_admin',                  
            __('Get some demo data to play with','ss_song_requester'),       
            __('Demo data','ss_song_requester'),                  
            'ss_manage_song_request_settings',            
            'ss_song_requester_admin_demo',    
            array( $this, 'create_admin_page_demo' )
        );
        
        // Load the CSS and JS conditionally
        $menupages = array(
            $mainmenu, 
            $settingsmenu, 
            $requestsmenu, 
            $aboutmenu,
            $demomenu,
        );
        foreach($menupages as $menupage) {
            add_action( 'load-' . $menupage, array($this, 'load_assets') );
        }
        
        add_action( 'load-' . $requestsmenu, array($this, 'requests_table_page') );
    }
    
    
    public function requests_table_page(){
        // set the pagination in help tab
        $screen_option = 'per_page';
        $args   = array(
            'label'   => __('Requests per page','ss_song_requester'),
            'default' => 5,
            'option'  => 'requests_per_page'
        );

        add_screen_option( $screen_option, $args );
       
        $this->request_table = new ss_song_requester_list_table();
        $this->request_table->prepare_items();
        $this->request_table->process_actions();
    }
    
    /**
     * About page callback
     * @since    1.0.0
     */
    public function create_admin_page_about(){
        
        // determine active tab
        if ( isset( $_GET['tab'] ) ) {
            $active_tab = trim(strtolower($_GET['tab']));
        }
        else {
            $active_tab = 'welcome';
        }
        
        // start page
        echo '<div class="wrap">';
        
        // tabs
        echo '<h2 class="nav-tab-wrapper">';
            // welcome
            echo '<a href="?page=ss_song_requester_admin_about&tab=welcome" class="nav-tab';
            if ( $active_tab === 'welcome' ) {
                echo ' nav-tab-active';
            }
            echo '"><span class="fas fa-bullhorn"></span> ' . __('Welcome','ss_song_requester') . '</a>';
            
            // How to use
            echo '<a href="?page=ss_song_requester_admin_about&tab=howto" class="nav-tab';
            if ( $active_tab === 'howto' ) {
                echo ' nav-tab-active';
            }
            echo '"><span class="fas fa-question"></span> ' . __('How to use','ss_song_requester') . '</a>';
            
            // support
            echo '<a href="?page=ss_song_requester_admin_about&tab=support" class="nav-tab';
            if ( $active_tab === 'support' ) {
                echo ' nav-tab-active';
            }
            echo '"><span class="far fa-life-ring""></span> ' . __('Support','ss_song_requester') . '</a>';
            
            // credits
            echo '<a href="?page=ss_song_requester_admin_about&tab=credits" class="nav-tab';
            if ( $active_tab === 'credits' ) {
                echo ' nav-tab-active';
            }
            echo '"><span class="far fa-thumbs-up"></span> ' . __('Credits','ss_song_requester') . '</a>';
        echo '</h2>';
        

        // content of tabs
        if ( $active_tab == 'welcome' ) {
            echo '<div class="ss_song_requester_welcome clearfix">';
            
            // Badge
            echo '<div class="wp-badge"><div>' . __('Version','ss_song_requester') . ' ' . $this->version . '</div></div>';
           
            echo '<h1>' . __('Welcome to the sixtyseven song requester', 'ss_song_requester') . '</h1>';
            
            echo '<p class="ss_about_text">' . __("The sixtyseven song requester plugin was originally created as a tool to manage the song requests on the programmer's wedding. The goal was to give the attending guests a nice way to make a song request to the DJ without even leaving the table. It offers the ability to mark songs as played by the DJ and some basic settings. Everything can be fully achieved in the frontend, so you do not need to give your DJ access to the WordPress admin area, but the plugin has of course the typical admin view of the song requests as well.", 'ss_song_requester') . '</p>';
            
            echo '</div>';
            
            echo '<hr />';
            
        } elseif ( $active_tab == 'howto' ) {
            echo '<div class="ss_song_requester_howto clearfix">';
            echo '<h1>' . __('The sixtyseven song requester is easy to use!', 'ss_song_requester') . '</h1>';
        
            echo '<p>' . __('The frontend output of this plugin is completely driven by one single shortcode: [ss_song_requester]. No need for any parameter or complicated stuff. You can either put this shortcode into a page by hand, or simply click on the icon <i class="fas fa-music"></i> in the editor to put in the shortcode automatically.', 'ss_song_requester') . '</p>';
            
            echo '<p>' . __('To override the optical appearance of the output, copy the file ss_song_requester-public.css from the public/css directory of this plugin to the directory wp-content/themes/yourtheme/css and change everything to your liking.', 'ss_song_requester') . '</p>';
            
            
            echo '</div>';
            
            echo '<hr />';
            
        } elseif ( $active_tab == 'support' ) {
            echo '<div class="ss_song_requester_support clearfix">';
            echo '<h1>' . __('Support this Plugin', 'ss_song_requester') . '</h1>';
        
            echo '<p>' . __('It was a great pleasure to develop this plugin, and I gladly give it to the WordPress community. If you want to support this, please be so kind and give it a good rating. And while most programmers seem to run only on coffee, I really like to have a nice german beer every now and then. Feel free to click the button below', 'ss_song_requester') . ' <span class="far fa-smile"></span></p>';
            
            $current_lang = $this->get_current_lang();
            
            switch ($current_lang) {
                case 'de-DE':
                    $donation_image_src = 'https://www.sixtyseven.info/assets/paypal_donation_de.png';
                    break;
                default:
                    $donation_image_src = 'https://www.sixtyseven.info/assets/paypal_donation.png';
            }
            
            echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" id="donationform">
        	<input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="GC9UYJLWH4X7J">
            <input type="image" src="' . $donation_image_src . '" border="0" name="submit" alt="' . __('Buy me a beer','ss_song_requester') . '" title="' . __('Buy me a beer','ss_song_requester') . '" class="donationbutton">
            <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
		    </form>';
            
            echo '</div>';
            
            echo '<hr />';
        } elseif ( $active_tab == 'credits' ) {
            echo '<div class="ss_song_requester_credits clearfix">';
            echo '<h1>' . __('Credits where credit is due!', 'ss_song_requester') . '</h1>';
        
            echo '<p>' . __('Plugin development is literally beeing on your own spending a lot of time in front of a computer screen, forgetting everything else. It takes a special kind of people to put heartblood into an open source project that most likely never will give you anything in return, just for the fun of it. Thanks to the wonderful community around WordPress, I was able to learn a lot and improved my skills to a level, that I really never expected myself. So without further ado, here is a shoutout to the people, that are a valuable part of my development team, presumably without even knowing it.', 'ss_song_requester') . '</p>';
            
            echo '<ul>';
            
            // questioner on FB
            echo '<li>';
            echo '<a href="https://www.facebook.com/MarkEllisHancock" target="_blank">Mark Hancock</a> ';
            _e('for asking a question regarding song requesting functionality in one of my facebook groups as the ignitor for this project', 'ss_song_requester');
            echo '</li>';
            
            // original developer
            echo '<li>';
            echo '<a href="https://profiles.wordpress.org/scriptonite" target="_blank">Scriptonite</a> ';
            echo sprintf(__('for coding the plugin %sMusic Request Manager%s years ago, which gave me a good starting point for the development of my song requester. As we germans say: No reason for reinventing the wheel', 'ss_song_requester') . ' <span class="far fa-smile"></span>', '<a href="https://wordpress.org/plugins/music-request-manager/" target="_blank">', '</a>');
            
            // font awesome founder
            echo '<li>';
            echo '<a href="https://www.linkedin.com/in/davegandy" target="_blank">Dave Gandy</a> ';
            echo sprintf(__('for inventing the fantastic %sfont awesome%s, it almost always satisfies my icon needs and should be incorporated into the WordPress core, if you ask me.', 'ss_song_requester'), '<a href="https://fontawesome.com/" target="_blank">', '</a>'). ' <span class="fab fa-font-awesome-flag"></span>';
            
            // pagination class
            echo '<li>';
            echo '<a href="https://gist.github.com/wolffe" target="_blank">Ciprian Popescu</a> ';
            _e('a.k.a. Wolfe for his pagination class which was the foundation for the frontend pagination of this plugin.', 'ss_song_requester');
            echo '</li>';
            
            // plugin boilerplate
            echo '<li>';
            echo '<a href="https://tommcfarlin.com/" target="_blank">Tom McFarlin</a> ';
            echo sprintf(__('for his work with the %sWordPress plugin boilerplate%s, which was the basic layout structure for all of my plugins ever since.', 'ss_song_requester'), '<a href="https://wppb.io/" target="_blank">', '</a>');
            
            // plugin boilerplate generator
            echo '<li>';
            echo '<a href="https://github.com/DevinVinson/" target="_blank">Devin Winson</a> ';
            echo sprintf(__('for the %sWordPress plugin boilerplate generator%s, which made working with Tom McFarlins code even more easy. In former times I used a self coded generator, but this one is not only more beatiful, but better as well.', 'ss_song_requester'), '<a href="https://wppb.me/" target="_blank">', '</a>');
            
            // plugin header image
            echo '<li>';
            echo '<a href="https://pixabay.com/users/pasevichbogdan-5169192/" target="_blank">Bogdan Pasevich</a> ';
            echo sprintf(__('for the plugin header image. Bogdan is a young but very talented photographer, who gives some of his work generously away for free on %spixabay%s, which is in fact a fantastic ressource for pictures published under the Creative Commons licence.', 'ss_song_requester'), '<a href="https://pixabay.com/" target="_blank">', '</a>');
            echo '</li>';
            
            echo '</ul>';
            
            echo '</div>';
            
            echo '<hr />';
        }

        // proudly present by
        if($this->options['allow_backlink'] === 'allow' OR $this->options['allow_backlink'] === 'allow_backend'){
             echo '<p class="proudly_presented_by_sixtyseven_mutlimedia">' . sprintf(__('The sixtyseven song requester v. %s is proudly presented by','ss_song_requester'), $this->version) . ' <a href=" https://www.sixtyseven.info" target="_blank">sixtyseven &reg; multimedia</a></p>';
        }
        
        // end page
        echo '</div>';
    }
    
    /**
     * Settings page callback
     * @since    1.0.0
     */
    public function create_admin_page_settings(){
       
        // start page
        echo '<div class="wrap">';
        
   
        echo '<form method="post" action="options.php">';
        settings_fields( 'ss_song_requester' );
        do_settings_sections( 'ss_song_requester_admin' );
        echo '<hr />';
        submit_button();
        echo '</form>';

        echo '<hr />';

        // proudly present by
        if($this->options['allow_backlink'] === 'allow' OR $this->options['allow_backlink'] === 'allow_backend'){
             echo '<p class="proudly_presented_by_sixtyseven_mutlimedia">' . sprintf(__('The sixtyseven song requester v. %s is proudly presented by','ss_song_requester'), $this->version) . ' <a href=" https://www.sixtyseven.info" target="_blank">sixtyseven &reg; multimedia</a></p>';
        }
        
        // end page
        echo '</div>';
    }
    
     /**
     * Demo page callback
     * @since    1.0.0
     */
    public function create_admin_page_demo(){
        
        // url to this page
        $url = admin_url('admin.php?page=ss_song_requester_admin_demo');
        
        // get the desired action
        $action = isset($_POST['ss_song_requester_action']) ? trim($_POST['ss_song_requester_action']) : false;
        
         // get the number of entries
        $number_of_entries = isset($_POST['entries']) ? absint($_POST['entries']) : 10;
        
        // get the country
        $chart_country = isset($_POST['country']) ? htmlentities($_POST['country']) : 'US';
        $chart_country = strtoupper($chart_country);
        if(!array_key_exists($chart_country,$this->countries)) {
            $chart_country = 'US';
        }

        // get the genre
        $chart_genre = isset($_POST['genre']) ? absint($_POST['genre']) : 0;
        if(!array_key_exists($chart_genre,$this->genres)) {
            $chart_genre = 'all';
        }
            
        // start page
        echo '<div class="wrap">';
        
        echo '<h1>' . __('Get some demo data to play with','ss_song_requester') . '</h1>';
        echo '<p>' . sprintf(__("When you first install and activate this plugin, you most likely will not have any song requests. But don't worry, we got you covered. On this page you can get some song data that you can configure to your liking from Apple's itunes store's top sellers. Select the country to pull the charts from, the musical genre and the number of entries and click on the button labeled %s. You will then see a generated List of entries, that you can store as current requests by clicking on the button labeled %s.", 'ss_song_requester'), '<b>&quot;' . __('Get chart data','ss_song_requester') . '&quot;</b>', '<b>&quot;' . __('Store demo data in table','ss_song_requester') . '&quot;</b>' ) . '</p>';
        
        echo '<form method="post" action="'.$url.'">';
        
        echo '<table>';
        echo '<tr><td>' . __('Select country','ss_song_requester') . '</td>';
        echo '<td><select name="country">';
        foreach ($this->countries as $value => $name){
            echo '<option value="' . $value . '" ' . selected($chart_country, $value, false) . '>' . $name . '</option>';
        }
        echo '<tr><td>' . __('Select genre','ss_song_requester') . '</td>';
        echo '<td><select name="genre">';
        echo '<option value="all" ' . selected($chart_genre, 'all', false) . '>' . __('All genres','ss_song_requester') . '</option>';
        foreach ($this->genres as $value => $name){
            echo '<option value="' . absint($value) . '" ' . selected($chart_genre, $value, false) . '>' . $name . '</option>';
        }
        echo '</select></td></tr>';

        echo '<tr><td>' . __('Number of Entries','ss_song_requester') . '</td><td><input type="number" name="entries" min="10" max="50" step="10" value="' . $number_of_entries . '"  style="width: 50px;" /></td></tr>';
        echo '</table>';
        echo '<input type ="hidden" name ="ss_song_requester_action" value="get_data">';
        submit_button( __('Get chart data','ss_song_requester') );
        echo '</form>';
        
        if( 'get_data' === $action ){
            
            // build charts output
            $count = 0;
            $results = '';
            $xml = $this->get_itunes_data($number_of_entries, $chart_country, $chart_genre);
            if($xml){
                
                echo '<hr />';

                foreach ($xml->entry as $val) {
                    $count ++;
                    $cover = $val->imimage[0];

                    $results .= '<tr>
                        <td style="width:70px"><span class="badge">'.$count.'</span></td>
                        <td style="width:80px"><img src="'.$cover.'" alt="'.$val->title.'" /></td>
                        <td><b>&quot;'.$val->imname.'&quot;</b> ' . __('performed by','ss_song_requester') . ' ' .$val->imartist.'</td>
                        </tr>';

                }
                
                if(count($this->current_requests) > 0){
                    echo '<div class="notice notice-info is-dismissible"><p>' . sprintf( __('There are currently %d requests in your database. If you decide to install the demo data, the entries will be appended.','ss_song_requester'), count($this->current_requests) ) . '</p></div>';
                }

                 // output results table  
                echo '<table>' . $results . '</table>';
                
                // output store demo data form
                echo '<form method="post" action="'.$url.'">';
                echo '<input type ="hidden" name ="country" value="' . $chart_country . '">';
                echo '<input type ="hidden" name ="entries" value="' . $number_of_entries . '">';
                echo '<input type ="hidden" name ="genre" value="' . $chart_genre . '">';
                echo '<input type ="hidden" name ="ss_song_requester_action" value="store_data">';
                submit_button( __('Store demo data in table','ss_song_requester') );
                echo '</form>';

            } else {
                echo sprintf(__('Sorry, no entries for the genre %s in the country %s found. Try another combination.','ss_song_requester'), '&quot;'.$this->genres[$chart_genre].'&quot;', $this->countries[$chart_country]);
            }
        } else if( 'store_data' === $action ){
            global $wpdb;
            
            $inserted = 0;
            $updated = 0;
            
            $xml = $this->get_itunes_data($number_of_entries, $chart_country, $chart_genre);
            if($xml){
                foreach ($xml->entry as $val) {
                    $newentry = array();
                    
                    $newentry['title'] = (string) $val->imname;
                    $newentry['artist'] = (string) $val->imartist;
                    
                    // make a random whish count
                    $whishcount = absint(rand(1,10));
                    $newentry['count'] = $whishcount;
                    
                    // random played or not
                    $played = absint(rand(0,1));
                    $played_minutes = absint(rand(2,59));
                    $played_timestamp = time() - ($played_minutes * 60);
                    if(1 === $played) {
                        $newentry['played'] = $played_timestamp;
                    } else {
                        $newentry['played'] = '';
                    }

                    if(empty($this->current_requests)){
                        // table is empty, insert first record
                        $wpdb->insert($wpdb->prefix . 'ss_song_requester_current_requests',array('title' => $newentry['title'], 'artist' => $newentry['artist'], 'count' => $newentry['count'], 'played' => $newentry['played']), array('%s','%s', '%d', '%s'));
                        
                        $inserted ++;
                    } else {
                        // records found, check for doubles
                        $song_id = 0;
                        $check_title  = preg_replace( '#\W+#', '', strtolower($newentry['title']) );
                        $check_artist = preg_replace( '#\W+#', '', strtolower($newentry['artist']) );
                        foreach ( $this->current_requests as $existing_requests ){
                            $check_for_title  = preg_replace( '#\W+#', '', strtolower($existing_requests->title) );
                            $check_for_artist = preg_replace( '#\W+#', '', strtolower($existing_requests->artist) );
                            if( $check_title === $check_for_title && $check_artist === $check_for_artist ){
                                // found a request, set as song id
                                $song_id = $existing_requests->id;
                                break;
                            }
                        }

                        if($song_id >= 1 ){
                            // double found
                            $allready_played = $wpdb->get_var('SELECT played FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id .'"');
                            if(empty($allready_played)){
                                // song was not yet played, update request counts
                                 $request_counts = $wpdb->get_var('SELECT count FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id . '"');
                                $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('count' =>( $request_counts + $newentry['count'] ) ), array('id' => $song_id) );
                                $updated ++;
                            } else if(! empty($newentry['played']) ){
                                // song was allready played, update playtime
                                $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('played' =>( $newentry['played'] ) ), array('id' => $song_id), array('%s', '%d') );
                                $updated ++;
                            }
                        } else {
                            // no double found, insert additional record
                            $wpdb->insert($wpdb->prefix . 'ss_song_requester_current_requests',array('title' => $newentry['title'], 'artist' => $newentry['artist'], 'count' => $newentry['count'], 'played' => $newentry['played']));
                            $inserted ++;
                        }
                    }
                }
                
                if($inserted > 0 || $updated > 0){
                    echo '<div class="notice notice-success is-dismissible">';
                    if($inserted > 0) {
                        echo '<p>' .sprintf( __('%d new requests have been insertetd into your database.','ss_song_requester'), $inserted ) . '</p>';
                    }
                    if($updated > 0) {
                        echo '<p>' .sprintf( __('%d requests have been updated in your database.','ss_song_requester'), $updated ) . '</p>';
                    }
                    echo '</div>';
                }
            }
        }

        echo '<hr />';

        // proudly present by
        if($this->options['allow_backlink'] === 'allow' OR $this->options['allow_backlink'] === 'allow_backend'){
             echo '<p class="proudly_presented_by_sixtyseven_mutlimedia">' . sprintf(__('The sixtyseven song requester v. %s is proudly presented by','ss_song_requester'), $this->version) . ' <a href=" https://www.sixtyseven.info" target="_blank">sixtyseven &reg; multimedia</a></p>';
        }
        
        // end page
        echo '</div>';
    }
    
    /**
     * Register and add settings
     * @since    1.0.0
     */
    public function register_settings(){        
        register_setting(
            'ss_song_requester', // Option group
            self::$option_key, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
        
        add_settings_section(
            'ss_song_requester_general', // ID
            __('General settings','ss_song_requester'), // Title
            array( $this, 'general_section_info' ), // Callback
            'ss_song_requester_admin' // Page
        );
        
        // Request song form active?
        add_settings_field(
            'form_status', // ID
            __('Request form','ss_song_requester'), // Title 
            array( $this, 'form_status_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        ); 
        
        // Keep data on uninstall
        add_settings_field(
            'uninstall_behaviour', // ID
            __('Uninstall behaviour','ss_song_requester'), // Title 
            array( $this, 'uninstall_behaviour_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        ); 
        
        // Access to the plugin settings
        add_settings_field(
            'access_settings_page', // ID
            __('Access to settings page','ss_song_requester'), // Title 
            array( $this, 'access_settings_page_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );  
        
         // Access to the request management tools
        add_settings_field(
            'manage_requests', // ID
            __('Manage requests','ss_song_requester'), // Title 
            array( $this, 'manage_requests_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );  
        
        // Autoreload Intervall
        add_settings_field(
            'autoreload_seconds', // ID
            __('Autoload intervall','ss_song_requester'), // Title 
            array( $this, 'autoreload_seconds_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );    
        
        // bubble background color
        add_settings_field(
            'bubble_background', // ID
            __('Notification bubble background color','ss_song_requester'), // Title 
            array( $this, 'bubble_background_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );  
        
         // bubble text color
        add_settings_field(
            'bubble_text', // ID
            __('Notification bubble text color','ss_song_requester'), // Title 
            array( $this, 'bubble_text_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );    
        
        // Allow link to sixtyseven
        add_settings_field(
            'allow_backlink', // ID
            __('Allow backlink','ss_song_requester'), // Title 
            array( $this, 'allow_backlink_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );
        
        // requests per page in frontend
        add_settings_field(
            'requests_per_page', // ID
            __('Requests per page','ss_song_requester'), // Title 
            array( $this, 'requests_per_page_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );
        
         // Sort order in frontend
        add_settings_field(
            'frontend_sort_type', // ID
            __('Frontend Sort type','ss_song_requester'), // Title 
            array( $this, 'frontend_sort_type_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );    
        
         // Sort direction in frontend
        add_settings_field(
            'frontend_sort_direction', // ID
            __('Frontend Sort order direction','ss_song_requester'), // Title 
            array( $this, 'frontend_sort_direction_callback' ), // Callback
            'ss_song_requester_admin', // Page
            'ss_song_requester_general' // Section           
        );    
    }
    
    /** 
     * Print the text for the general settings section
     * @since    1.0.0
     */
    public function general_section_info(){
        _e('Manage the settings of the plugin here.','ss_song_requester');
    }
    
     /** 
     * Get the settings option array and print the uninstall behaviour
     * @since    1.0.0
     */
    public function form_status_callback(){
        $checkvalue = isset( $this->options['form_status'] ) ? esc_attr( $this->options['form_status']) : self::$defaults['form_status'];

        echo '<input id="form_status_active" name="'.self::$option_key.'[form_status]" type="radio" value="active" ' . checked( 'active', $checkvalue, false  ). ' /> ' . __('Song requests are possible','ss_song_requester') . '<br />';
        
        echo '<input id="form_status_inactive" name="'.self::$option_key.'[form_status]" type="radio" value="inactive" ' . checked( 'inactive', $checkvalue, false  ). ' /> ' . __('Song requests are NOT possible','ss_song_requester');
        
        echo '<p class="description">' . __('Select if you want to allow new song requests. If requests are not possible, only the list of current requests will be shown in the frontend (if any), and the upvoting function will be disabled.','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the uninstall behaviour
     * @since    1.0.0
     */
    public function uninstall_behaviour_callback(){
        $checkvalue = isset( $this->options['uninstall_behaviour'] ) ? esc_attr( $this->options['uninstall_behaviour']) : self::$defaults['uninstall_behaviour'];

        echo '<input id="uninstall_behaviour_keep" name="'.self::$option_key.'[uninstall_behaviour]" type="radio" value="keep_data" ' . checked( 'keep_data', $checkvalue, false  ). ' /> ' . __('Keep options and database tables','ss_song_requester') . '<br />';
        
        echo '<input id="uninstall_behaviour_drop" name="'.self::$option_key.'[uninstall_behaviour]" type="radio" value="drop_data" ' . checked( 'drop_data', $checkvalue, false  ). ' /> ' . __('Drop options and database tables','ss_song_requester');
        
        echo '<p class="description">' . __('Select if you want to keep the plugin options and database tables during uninstall for later use.','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the access to settings page
     * @since    1.0.0
     */
    public function access_settings_page_callback(){
        global $wp_roles;
        $roles = $wp_roles->get_names();
        unset($roles['administrator']);
        
        foreach ($roles as $key => $value) {
            if(in_array($key, $this->options['access_settings'])){
                $checked = ' checked';
            } else {
                $checked = '';
            }
            echo '<input id="access_settings_' . $key . '" name="' . self::$option_key . '[access_settings][' . $key . ']" type="checkbox" value="on"' . $checked . '> ' . translate_user_role($value) . '<br />';
        }
        
        echo '<p class="description">' . __('Check all user roles that should have access to the plugin settings page. As an administrator, you will allways have access to this page.','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the access to settings page
     * @since    1.0.0
     */
    public function manage_requests_callback(){
        global $wp_roles;
        $roles = $wp_roles->get_names();
        unset($roles['administrator']);
        
        foreach ($roles as $key => $value) {
            if(in_array($key, $this->options['manage_requests'])){
                $checked = ' checked';
            } else {
                $checked = '';
            }
            echo '<input id="manage_requests_' . $key . '" name="' . self::$option_key . '[manage_requests][' . $key . ']" type="checkbox" value="on"' . $checked . '> ' . translate_user_role($value) . '<br />';
        }
        
        echo '<p class="description">' . __('Check all user roles that should manage the song requests. As an administrator, you can allways manage the requests.','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the autoreload value
     * @since    1.0.0
     */
    public function autoreload_seconds_callback(){
        echo sprintf(
            '<input type="text" id="autoreload_seconds" name="'.self::$option_key.'[autoreload_seconds]" value="%s" /> <b>' . __('seconds','ss_song_requester') . '</b>',
            isset( $this->options['autoreload_seconds'] ) ? esc_attr( $this->options['autoreload_seconds']) : ''
        );
        
        echo '<p class="description">' . __('The autoload intervall is the time that passes by before the song request list is reloaded via ajax to show the newest requests. However, if you have sufficient rights, you can allways reload this list as well with a click on a button.','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the bubble background value
     * @since    1.0.0
     */
    public function bubble_background_callback(){
        echo sprintf(
            '<input type="text" id="bubble_background" name="'.self::$option_key.'[bubble_background]" value="%s" class="ss-color-field" />',
            isset( $this->options['bubble_background'] ) ? esc_attr( $this->options['bubble_background']) : ''
        );
        
        echo '<p class="description">' . __('Select the background color of the song requests notification bubble','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the bubble text value
     * @since    1.0.0
     */
    public function bubble_text_callback(){
        $value = isset( $this->options['bubble_text'] ) ? esc_attr( $this->options['bubble_text']) : self::$defaults['bubble_text'];
        
        echo sprintf(
            '<input type="text" id="bubble_text" name="'.self::$option_key.'[bubble_text]" value="%s" class="ss-color-field" />',
            $value
        );
        
        echo '<p class="description">' . __('Select the text color of the song requests notification bubble','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the allow backlink value
     * @since    1.0.0
     */
    public function allow_backlink_callback(){
        $checkvalue = isset( $this->options['allow_backlink'] ) ? esc_attr( $this->options['allow_backlink']) : self::$defaults['allow_backlink'];
        
        echo '<input id="backlink_allow" name="'.self::$option_key.'[allow_backlink]" type="radio" value="allow" ' . checked( 'allow', $checkvalue, false  ). ' /> <span class="far fa-smile fa-fw ample_green"></span> ' . sprintf(__('Yes, I support the plugin and allow a small link to the %sdeveloper%s at the footer of the plugin\'s output, in the frontent as well as in the backend','ss_song_requester'), '<a href="https://www.sixtyseven.info" target="_blank">','</a>') . '<br />';
        
        echo '<input id="backlink_allow_backend" name="'.self::$option_key.'[allow_backlink]" type="radio" value="allow_backend" ' . checked( 'allow_backend', $checkvalue, false  ). ' /> <span class="far fa-meh fa-fw ample_yellow"></span> ' . __('Yes, I support the plugin, but do not show that link on my public facing website','ss_song_requester') . '<br />';
        
        
        echo '<input id="backlink_allow_frontend" name="'.self::$option_key.'[allow_backlink]" type="radio" value="allow_frontend" ' . checked( 'allow_frontend', $checkvalue, false  ). ' /> <span class="far fa-meh fa-fw ample_yellow"></span> ' . __('Yes, I support the plugin, but do not show that link in the plugin\'s admin pages','ss_song_requester') . '<br />';
        
        echo '<input id="backlink_disallow" name="'.self::$option_key.'[allow_backlink]" type="radio" value="disallow" ' . checked( 'disallow', $checkvalue, false  ). ' /> <span class="far fa-frown fa-fw ample_red"></span> ' . __('No, please do not put any additional link on my page','ss_song_requester');
    }
    
     /** 
     * Get the settings option array and print the requests per page
     * @since    1.0.0
     */
    public function requests_per_page_callback(){
        $value = isset( $this->options['requests_per_page'] ) ? esc_attr( $this->options['requests_per_page']) : self::$defaults['requests_per_page'];
        
        echo sprintf(
            '<input type="number" id="requests_per_page" name="'.self::$option_key.'[requests_per_page]" min="1" max="99" value="%s"  style="width: 50px;" />',
            $value
        );
        
        echo '<p class="description">' . __('Select the number of requests on one page in the frontend. Minimal value is 1, 99 means all requests on one page.','ss_song_requester') . '</p>';
    }
    
     /** 
     * Get the settings option array and print the frontend sort type
     * @since    1.0.0
     */
    public function frontend_sort_type_callback(){
        $checkvalue = isset( $this->options['frontend_sort_type'] ) ? esc_attr( $this->options['frontend_sort_type']) : self::$defaults['frontend_sort_type'];
        
        $select = '<select id="frontend_sort_type" name="'.self::$option_key.'[frontend_sort_type]">';
        $select .= '<option value="title" '.selected($checkvalue, 'title', false).'>' . __('Title of the song','ss_song_requester') . '</option>';
        $select .= '<option value="artist" '.selected($checkvalue, 'artist', false).'>' . __('Name of the Artist','ss_song_requester') . '</option>';
        $select .= '<option value="count" '.selected($checkvalue, 'count', false).'>' . __('Count of requests','ss_song_requester') . '</option>';
        $select .= '<option value="played" '.selected($checkvalue, 'played', false).'>' . __('Last played','ss_song_requester') . '</option>';
        $select .= '<option value="id" '.selected($checkvalue, 'id', false).'>' . __('Order of requests','ss_song_requester') . '</option>';
        $select .= '</select>';
        
        echo $select . '<p class="description">' . __('According to what database field should the requests list be sorted in the frontend as default?','ss_song_requester') . '</p>';
    }
    
    /** 
     * Get the settings option array and print the frontend sort direction
     * @since    1.0.0
     */
    public function frontend_sort_direction_callback(){
        $checkvalue = isset( $this->options['frontend_sort_direction'] ) ? esc_attr( $this->options['frontend_sort_direction']) : self::$defaults['frontend_sort_direction'];

        echo '<input id="frontend_sort_direction_asc" name="'.self::$option_key.'[frontend_sort_direction]" type="radio" value="ASC" ' . checked( 'ASC', $checkvalue, false  ). ' /> <span class="fas fa-sort-alpha-down fa-fw"></span> <span class="fas fa-sort-numeric-down fa-fw"></span> <span class="fas fa-sort-amount-down fa-fw"></span> ' . __('Ascending','ss_song_requester') . '<br />';
        
        echo '<input id="frontend_sort_direction"_desc name="'.self::$option_key.'[frontend_sort_direction]" type="radio" value="DESC" ' . checked( 'DESC', $checkvalue, false  ). ' /> <span class="fas fa-sort-alpha-up fa-fw"></span> <span class="fas fa-sort-numeric-up fa-fw"></span> <span class="fas fa-sort-amount-up fa-fw"></span> ' . __('Descending','ss_song_requester') . '<br />';
    }
    
    /**
     * Sanitize each setting field as needed
     *
     * @since    1.0.0
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ){
        
        global $wp_roles;
        $roles = $wp_roles->get_names();
        $roles_names = array();
        foreach ($roles as $key => $value){
            $roles_names[] = $key;
        }
        
         if( isset( $input['form_status'] ) ) {
            $allowed_form_status_values = array('active', 'inactive');
            if(in_array($input['form_status'], $allowed_form_status_values)){
                $new_input['form_status'] = $input['form_status'];
            } else {
                $new_input['form_status'] = self::$defaults['form_status'];
            }
        }
        
        if( isset( $input['uninstall_behaviour'] ) ) {
            $allowed_uninstall_behaviour_values = array('keep_data', 'drop_data');
            if(in_array($input['uninstall_behaviour'], $allowed_uninstall_behaviour_values)){
                $new_input['uninstall_behaviour'] = $input['uninstall_behaviour'];
            } else {
                $new_input['uninstall_behaviour'] = self::$defaults['uninstall_behaviour'];
            }
        }
        
        $new_input['access_settings'] = array('administrator');
        if( isset( $input['access_settings'] ) && is_array( $input['access_settings'] ) ) {
            foreach ($input['access_settings'] as $key => $value){
                if ( 'on' === $value && in_array( $key,$roles_names ) ){
                    $new_input['access_settings'][] = $key;
                }
            }
        }
        
        $new_input['manage_requests'] = array('administrator');
        if( isset( $input['manage_requests'] ) && is_array( $input['manage_requests'] ) ) {
            foreach ($input['manage_requests'] as $key => $value){
                if ( 'on' === $value && in_array( $key,$roles_names ) ){
                    $new_input['manage_requests'][] = $key;
                }
            }
        }
        
        if( isset( $input['autoreload_seconds'] ) ) {
            $seconds = trim( $input['autoreload_seconds'] );
            $seconds = strip_tags( stripslashes( $seconds ) );
            
            $new_input['autoreload_seconds'] = absint( $seconds );
        }
        
        if( isset( $input['bubble_background'] ) ) {
            $bubble_background = trim( $input['bubble_background'] );
            $bubble_background = strip_tags( stripslashes( $bubble_background ) );

            // Check if is a valid hex color
            if( FALSE === $this->check_color( $bubble_background ) ) {
                $new_input['bubble_background'] = self::$defaults['bubble_background'];
            } else {
                $new_input['bubble_background'] = $bubble_background;
            }
        }
        
         if( isset( $input['bubble_text'] ) ) {
            $bubble_text = trim( $input['bubble_text'] );
            $bubble_text = strip_tags( stripslashes( $bubble_text ) );

            // Check if is a valid hex color
            if( FALSE === $this->check_color( $bubble_text ) ) {
                $new_input['bubble_text'] = self::$defaults['bubble_text'];
            } else {
                $new_input['bubble_text'] = $bubble_text;
            }
        }
        
        if( isset( $input['allow_backlink'] ) ) {
            $allowed_values = array('allow', 'disallow', 'allow_frontend', 'allow_backend');
            if(in_array($input['allow_backlink'], $allowed_values)){
                $new_input['allow_backlink'] = $input['allow_backlink'];
            } else {
                $new_input['allow_backlink'] = self::$defaults['allow_backlink'];;
            }
        }
        
        if( isset( $input['requests_per_page'] ) ) {
            $rpp = trim( $input['requests_per_page'] );
            $rpp = strip_tags( stripslashes( $rpp ) );
            $rpp = absint( $rpp );

            $new_input['requests_per_page'] = $rpp;
        }
        
        if( isset( $input['frontend_sort_type'] ) ) {
            $allowed_values = array('id', 'title', 'artist', 'count', 'played');
            if(in_array($input['frontend_sort_type'], $allowed_values)){
                $new_input['frontend_sort_type'] = $input['frontend_sort_type'];
            } else {
                $new_input['frontend_sort_type'] = self::$defaults['frontend_sort_type'];
            }
        }
        
        if( isset( $input['frontend_sort_direction'] ) ) {
            $frontend_sort_direction = strtoupper($input['frontend_sort_direction']);
            $allowed_values = array('ASC', 'DESC');
            if(in_array($frontend_sort_direction, $allowed_values)){
                $new_input['frontend_sort_direction'] = $frontend_sort_direction;
            } else {
                $new_input['frontend_sort_direction'] = self::$defaults['frontend_sort_direction'];
            }
        }

        return $new_input;
    }
    
    /**
     * Function that will check if value is a valid HEX color.
     * @since    1.0.0
	 * @param      string    $value       color value.
     */
    public function check_color( $value ) {
        if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { 
            // if user insert a HEX color with #
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Hook into the proper actions only when the plugin admin page loads
     * @since    1.0.0
     */
    public function load_assets(){
        // Unfortunately we can't just enqueue our scripts and styles here - it's too early. So register against the proper action hook to do it.
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_page_assets') );
    }
    
    /**
     *  Enqueue CSS and JS if needed
     * @since    1.0.0
     */
    public function enqueue_page_assets(){
        // create the nonce
        $nonce = wp_create_nonce( 'ss_song_requester_admin' );
        
        // css
        wp_enqueue_style( $this->plugin_name.'_pages', plugin_dir_url( __FILE__ ) . 'css/ss_song_requester-admin-pages.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'ss_song_requester_shortcode_style' );
        
        // JS
        wp_enqueue_script( 'ss_song_requester_inline_edit' );
        wp_enqueue_script( $this->plugin_name.'_pages', plugin_dir_url( __FILE__ ) . 'js/ss_song_requester-admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, false );
        wp_localize_script( $this->plugin_name.'_pages', 'ss_song_requester_jsvars', array(
            'inlineedit_ok_label'     => __('OK','ss_song_requester'),
            'inlineedit_cancel_label' => __('Cancel','ss_song_requester'),
            'inlineedit_saving'       => __('Saving ...','ss_song_requester'),
            'inlineedit_title'        => __('Click to correct this','ss_song_requester'),
            'nonce'                   => $nonce,
        ) );
        
        // external
        wp_enqueue_script( 'ss_song_requester_font-awesome' );
       
    }
    
    /**
     *  Enqueue CSS and JS for admin globally
     * @since    1.0.0
     */
    public function enqueue_global_assets(){
        // css
        wp_enqueue_style( $this->plugin_name.'_global', plugin_dir_url( __FILE__ ) . 'css/ss_song_requester-admin.css', array(), $this->version, 'all' );
        
        // JS
        #wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ss_song_requester-admin.js', array( 'jquery' ), $this->version, false );

    }
    
    /**
     * Settings page callback
     * @since    1.0.0
     */
    public function display_requests(){

        // start page
        echo '<div class="wrap">';
        
        // process admin notices
        self::transient_admin_notices();
        
        echo '<h1>' . __('Current song requests', 'ss_song_requester') . '</h1>';
        
        echo '<form method="post">';
        
        $this->request_table->search_box(__('Search request', 'ss_song_requester'), 'ss_search');
        
        $this->request_table->display();
        
        echo '</form>';
        
         // proudly present by
        if($this->options['allow_backlink'] === 'allow' OR $this->options['allow_backlink'] === 'allow_backend'){
             echo '<p class="proudly_presented_by_sixtyseven_mutlimedia">' . sprintf(__('The sixtyseven song requester v. %s is proudly presented by','ss_song_requester'), $this->version) . ' <a href=" https://www.sixtyseven.info" target="_blank">sixtyseven &reg; multimedia</a></p>';
        }
        
        // End page
        echo '</div>';
        
    }
    
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    
    /**
     * Plugin meta links
     * @since    1.0.0
	 * @param    array    $plugin_meta       The currently available plugin meta data.
     * @param    string   $plugin_file       The currently targetted plugin.
     * @param    array    $plugin_data       The data parsed from the plugin header.
     * @param    string   $plugin_status     The plugin status like 'All', 'Active', 'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use', 'Drop-ins', 'Search'
     */
    public function plugin_meta_links($plugin_meta, $plugin_file, $plugin_data, $plugin_status){
         if ( SS_SONG_REQUESTER_BASENAME === $plugin_file ) {
            $support_link = '<span class="dashicons dashicons-sos ss_meta_icons"></span> <a href="admin.php?page=ss_song_requester_admin_about&tab=support">' . __( 'Support', 'ss_song_requester' ) . '</a>';
            array_push( $plugin_meta, $support_link );
            
            $credits_link = '<span class="dashicons dashicons-thumbs-up ss_meta_icons"></span> <a href="admin.php?page=ss_song_requester_admin_about&tab=credits">' . __( 'Thanks', 'ss_song_requester' ) . '</a>';
            array_push( $plugin_meta, $credits_link ); 
         }  
        return $plugin_meta;    
    }
    
    /**
     * Plugin meta links
     * @since    1.0.0
	 * @param    array    $links       The currently available plugin action links.
     */
    public function plugin_action_links($links){
        $settings_link = '<a href="admin.php?page=ss_song_requester_admin_settings">' . __( 'Settings' ) . '</a>';
        
        array_push( $links, $settings_link );    
        return $links;    
    }
    
    
    /**
     * Admin notices delivered via transient, i.e. from ss_song_requester_List_Table
     * @since    1.0.0
     */
    public static function transient_admin_notices() {
        $messages = get_transient( 'ss_song_requester_admin_notices' );
        $allowed_types = array('error', 'warning', 'success', 'info');
        if($messages && count($messages) > 0){
            foreach($messages as $message){
                $type = esc_attr($message['type']);
                $text = esc_html($message['text']);
                if(in_array($type,$allowed_types)){
                    $css_class = 'notice-'.$type;
                } else {
                    $css_class = 'notice-info';
                }
                
                echo '<div class="notice ' . $css_class . ' is-dismissible"><p>' . $text . '</p></div>';
            }
            
            // free the transient ;-)
            delete_transient( 'ss_song_requester_admin_notices' );
        }
    }
    
    /**
	 * Change Artist or title via ajax by a given ID
	 *
	 * @since    1.0.0
	 */
    public static function change_attribute() {
        global $wpdb;
        
        $error = false;
        
        // just for testing the saving message
        if (isset($_POST['slow']) && true === $_POST['slow']) {
            usleep(500000);
        }

        
        
        // get values
        $song_id = (int)$_POST['song_id'];
        
        $allowed_attributes = array('artist','title');
        $attribute = esc_attr( trim ( strtolower( $_POST['attribute'] ) ) );
        
        $old_value = ucfirst ( sanitize_text_field ( $_POST['old_value'] ) );
        $new_value = ucwords ( sanitize_text_field ( $_POST['value'] ) );
        
         // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_admin', 'nonce', false ) == false ) {
            $error = true;
        }
        
        
        if(!in_array($attribute,$allowed_attributes)){
            $error = true;
        }
        
        if(false === $error){ 
            if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array($attribute => ( $new_value ) ), array('id' => $song_id) ) ) {
                $error = true;
            } 
        }
        
        if(ob_get_contents()){
            ob_clean();
        }
        
        if(false === $error){ 
            echo stripslashes($new_value);
        } else {
            echo stripslashes($old_value);  
        }
        
  
        
        wp_die();
        
    }
    
    /**
	 * Creates the help tabs
	 *
	 * @since    1.0.0
	 */
	public function help_tabs(){
		$screen = get_current_screen();
		//var_dump($screen->id);
		
		if('toplevel_page_ss_song_requester_admin' === $screen->id){
			$tabs = array(
					array(
						'title'    => __('General', 'ss_song_requester'),
						'id'       => 'ss_song_requester_help_tab1',
						'content'  => '<p>'.__('Here you can manage your current song requests in the typical wordpress way.', 'ss_song_requester').'</p>',
						'callback' => array($this, 'help_tab1_content')
					),
                    array(
						'title'    => __('Edit', 'ss_song_requester'),
						'id'       => 'ss_song_requester_help_tab2',
						'content'  => '<p>'.__('You can edit the artist name and the title without leaving the list view.', 'ss_song_requester').'</p>',
						'callback' => array($this, 'help_tab2_content')
					),
			);
			
			$tabs = apply_filters('ss_song_requester_help_tabs', $tabs);
				
			foreach($tabs as $tab) {
				$screen->add_help_tab($tab);
			}
			
            if($this->options['allow_backlink'] === 'allow' OR $this->options['allow_backlink'] === 'allow_backend'){
			     $screen->set_help_sidebar('<p style="text-align:center"><strong>'.__('Presented by:', 'ss_song_requester' ).'</strong></p><p style="text-align:center"><img src="'.SS_SONG_REQUESTER_URL.'admin/img/help-sidebar-logo.png" width="146" height="29" alt="' . __('Logo sixtyseven multimedia', 'ss_song_requester') . '" /></p><p style="text-align:center"><a href="https://www.sixtyseven.info" target="_blank">'.__('Visit Website', 'ss_song_requester').'</a></p>');
            }
		}
	}
    
    /**
	 * populates the general help tab
	 *
	 * @since    1.0.0
	 */
	public function help_tab1_content() {
		$output = '';
		
		$output .= '<p>'.__('Normally it is not nesseccary to give your DJ access to the WordPress administration area. Everything you can do here can as well be done via Ajax calls in the frontend. But hey, now that you are here, we can as well build a nice list table. You can sort the song requests ascending or decending by clicking on the respective column header. When hovering over the headings, you will see a little arrow as an indicator of the sort direction.', 'ss_song_requester').'</p>';
		
		echo $output;
	}
    
    /**
	 * populates the edit help tab
	 *
	 * @since    1.0.0
	 */
	public function help_tab2_content() {
		$output = '';
		
		$output .= '<p>'.__('Click on the artist name or title that you want to change. Immediatlely an input field and two buttons appear. Make your desired changes and click on the button labeled OK to save the new value. Click on the button labeled cancel to abbort the action and close the edit mode.','ss_song_requester').'</p>';
		
		echo $output;
	}
    
    /**
	 * Checks if a tinymce button should be enabeled
	 *
	 * @since    1.0.0
	 */
    public function add_tinymce_button() {
        global $typenow;
        
        // check user permissions
        if ( !current_user_can('edit_pages') && !current_user_can('edit_posts')  ) {
            return;
        }
        // verify the post type
        if( !in_array( $typenow, array( 'post', 'page' ) ) ) {
            return;
        }
            
        // check if WYSIWYG is enabled
        if ( get_user_option('rich_editing') == 'true') {
            add_filter( 'mce_external_plugins', array($this, 'add_tinymce_plugin') );
            add_filter( 'mce_buttons', array($this, 'register_tinymce_button') );
        }
    }
    
    /**
	 * Register external Tinymce button script
	 *
	 * @since    1.0.0
	 */
    public function add_tinymce_plugin($plugin_array) {
        $plugin_array['ss_song_requester_button'] = SS_SONG_REQUESTER_URL.'admin/js/tinymce_button.js'; 
        return $plugin_array;
    }
    
    /**
	 * Register external Tinymce button itself
	 *
	 * @since    1.0.0
	 */
    function register_tinymce_button($buttons) {
       array_push($buttons, 'ss_song_requester_button');
       return $buttons;
    }
    
    /**
	 * Localize external Tinymce button
	 *
	 * @since    1.0.0
	 */
	public function add_tinymce_lang( $arr ){
		$arr[] = SS_SONG_REQUESTER_DIR . 'admin/ss_song_requester_mcelang.php';
		return $arr;
	}
    
    
    /**
	 * Get the current language based on bloginfo or installed plugins
	 *
	 * @since    1.0.0
     * @access  private
     * @return  $current_lang   string  the currently selected language
	 */
    private function get_current_lang(){
         // get current lang from bloginfo
        $current_lang = get_bloginfo('language');


        // overwrite lang from a multilang plugin (if any)
        if(class_exists('WPGlobus')){
            // WPGlobus found
            $current_lang = WPGlobus::Config()->language;
        } else if(defined('ICL_LANGUAGE_CODE')){
            // Perhaps WPML??
            $current_lang = ICL_LANGUAGE_CODE;
        } else if(function_exists('pll_current_language')){
            // maybe polylang?
            $current_lang = pll_current_language( 'slug' );
        } else if(function_exists('qtrans_getLanguage')){
            // could it be qtranslate X?
            $current_lang = qtrans_getLanguage();
        }
        
        return $current_lang;

    }
    
    /**
	 * Get data from itunes
	 *
	 * @since    1.0.0
     * @access  private
     * @return  $xml  bool  the data provided by Apple or false on error
	 */
    private function get_itunes_data($num = 50, $country_code = 'US', $genre = 'all'){
        
        // $num should be max 50 and min 10
        if($num < 10){
            $num = 10;
        }
        
        if($num > 50){
            $num = 50;
        }
        
        // $country_code should be uppercase
        $country_code = strtoupper($country_code);

        
        if('all' === $genre || 0 === absint($genre)) {
            $string = file_get_contents('https://itunes.apple.com/' . $country_code . '/rss/topsongs/limit=' . $num .'/xml');
        } else {
            $string = file_get_contents('https://itunes.apple.com/' . $country_code . '/rss/topsongs/limit=' . $num .'/genre=' . absint($genre) . '/xml');
        }

        
        // Remove the colon ":" in the <xxx:yyy>
        $string = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $string);
        
        $xml = simplexml_load_string($string);
        
        if($xml){
            return $xml;
        } else {
            return false;
        }
    }
}