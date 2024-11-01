<?php
class ss_song_request_shortcode {
	
	/**
	 * Initializes the shortcode
	 *
	 * @since    1.0.0
	 */
    static function init() {
        // add the ajax calls
        add_action( 'wp_ajax_nopriv_ss_request_song', array( __CLASS__ , 'request_song' ) );
        add_action( 'wp_ajax_ss_request_song', array( __CLASS__ , 'request_song' ) );
        add_action( 'wp_ajax_nopriv_ss_request_metoo', array( __CLASS__ , 'request_song_metoo' ) );
        add_action( 'wp_ajax_ss_request_metoo', array( __CLASS__ , 'request_song_metoo' ) );
        add_action( 'wp_ajax_nopriv_ss_request_mark_played', array( __CLASS__ , 'request_mark_as_played' ) );
        add_action( 'wp_ajax_ss_request_mark_played', array( __CLASS__ , 'request_mark_as_played' ) );
        add_action( 'wp_ajax_nopriv_ss_request_mark_played', array( __CLASS__ , 'request_mark_as_played' ) );
        add_action( 'wp_ajax_ss_request_mark_played', array( __CLASS__ , 'request_mark_as_played' ) );
        add_action( 'wp_ajax_nopriv_ss_request_unmark_played', array( __CLASS__ , 'request_unmark_as_played' ) );
        add_action( 'wp_ajax_ss_request_unmark_played', array( __CLASS__ , 'request_unmark_as_played' ) );
        add_action( 'wp_ajax_nopriv_ss_request_delete', array( __CLASS__ , 'request_delete' ) );
        add_action( 'wp_ajax_ss_request_delete', array( __CLASS__ , 'request_delete' ) );
        add_action( 'wp_ajax_nopriv_ss_request_empty', array( __CLASS__ , 'request_empty' ) );
        add_action( 'wp_ajax_ss_request_empty', array( __CLASS__ , 'request_empty' ) );
        add_action( 'wp_ajax_nopriv_ss_request_reload_list', array( __CLASS__ , 'build_playlist' ) );
        add_action( 'wp_ajax_ss_request_reload_list', array( __CLASS__ , 'build_playlist' ) );
        add_action( 'wp_ajax_nopriv_ss_request_change_attribute_frontend', array( __CLASS__ , 'change_attribute' ) );
        add_action( 'wp_ajax_ss_request_change_attribute_frontend', array( __CLASS__ , 'change_attribute' ) );
        
        // handle the shortcode
        add_shortcode('ss_song_requester', array( __CLASS__ , 'handle_shortcode' ) );
	}
    
    /**
	 * handles the shortcode output
	 *
	 * @since    1.0.0
	 */
	static function handle_shortcode() {
        global $wpdb;
        
        // plugin instance
        $plugin = ss_song_requester::instance();
        
        // get the settings
        $settings = ss_song_requester_admin::get_settings();
        
        // enqueue the css
        wp_enqueue_style( 'ss_song_requester_shortcode_style' );
        
        // enqueue font awesome
        wp_enqueue_script( 'ss_song_requester_font-awesome' );
        
        // enqueue inlineedit
        wp_enqueue_script( 'ss_song_requester_inline_edit' );
        
        // create the nonce
        $nonce = wp_create_nonce( 'ss_song_requester_' . get_the_id() );

        
        // enqueue the javascript and use wp_localize_script to transfer variables
        wp_enqueue_script( 'ss_song_requester_shortcode_script' );
        wp_localize_script( 'ss_song_requester_shortcode_script', 'ss_song_requester_jsvars', array(
            'hide_form_label'         => '<span class="fas fa-angle-up fa-fw"></span>' . __('Hide request form','ss_song_requester'),
            'request_label'           => '<span class="fas fa-angle-down fa-fw"></span>' . __('Request a song','ss_song_requester'),
            'request_now_label'       => '<span class="far fa-smile fa-fw"></span> ' .  __('Request a song now','ss_song_requester'),
            'tryagain_label'          => __('Try again','ss_song_requester'),
            'request_delete_label'    => __('Delete this request','ss_song_requester'),
            'metoo_label'             => __('I want to hear this, too!','ss_song_requester'),
            'markplayed_label'        => __('Mark song as played','ss_song_requester'),
            'unmarkplayed_label'      => __('Mark song as not yet played','ss_song_requester'),
            'ajax_working'            => __('Working ...','ss_song_requester'),
            'confirm_delete'          => __('Are you sure to delete this song request? This can not be undone!','ss_song_requester'),
            'confirm_empty'           => __('Are you sure to delete the complete request list? This can not be undone!','ss_song_requester'),
            'reloadnow_label'         => __('Reloading now ...','ss_song_requester'),
            'empty_label'             => '<span class="fas fa-trash fa-fw"></span> ' . __('Empty list','ss_song_requester'),
            'empty_title'             => __('Delete the complete list','ss_song_requester'),
            'inlineedit_ok_label'     => __('OK','ss_song_requester'),
            'inlineedit_cancel_label' => __('Cancel','ss_song_requester'),
            'inlineedit_saving'       => __('Saving ...','ss_song_requester'),
            'inlineedit_title'        => __('Click to correct this','ss_song_requester'),
            'post_id'                 => get_the_id(),
            'nonce'                   => $nonce,
            'autoreload_seconds'      => absint($settings['autoreload_seconds']),
            'form_status'             => $settings['form_status'],
            'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
        ) );
        
        //build playlist
        $playlist = '<div class="ss_song_requester_request_list">';
        $playlist .= self::build_playlist();
        $playlist .= '</div>';
        
        $form = '<div class="ss_song_requester_request">
                    <button class="show-form"><span class="fas fa-angle-down fa-fw"></span>' .  __('Request a song','ss_song_requester') . '</button>
                    <div class="form closed">
                        <p class="ss_song_requester-response success"></p>
                        <p><input type="text" class="title" placeholder="' . __( 'Enter song title here','ss_song_requester' ) . ' (' . __( 'if known','ss_song_requester' ) . ')" /></p>
                        <p><input type="text" class="artist" placeholder="' . __( 'Enter artist here','ss_song_requester' ) . ' (' . __( 'if known','ss_song_requester' ) . ')" /></p>
                         <button class="request_now"><span class="far fa-smile fa-fw"></span> ' .  __('Request a song now','ss_song_requester') . '</button>
                    </div>
                </div>';
        
        ob_start();
        
        echo $playlist;
        
        if($settings['form_status'] === 'active'){
            echo $form;
        }
        
        // proudly present by
        if($settings['allow_backlink'] === 'allow' OR $settings['allow_backlink'] === 'allow_frontend'){
            echo '<p class="proudly_presented_by_sixtyseven_mutlimedia">' . sprintf(__('The sixtyseven song requester v. %s is proudly presented by','ss_song_requester'), $plugin->get_version()) . ' <a href=" https://www.sixtyseven.info" target="_blank">sixtyseven &reg; multimedia</a></p>';
        }
        
		return ob_get_clean();
	}
    
    
    /**
	 * Create a request via ajax
	 *
	 * @since    1.0.0
	 */
    public static function request_song() {
        
        $error = false;
        $response = array();
        $response['errorfields'] = array();  // contains the field(s) with errors
        
        // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_' . $_POST['post_id'], 'nonce', false ) == false ) {
            $response['text'] = __( 'No cheating please!','ss_song_requester' );
            $response['errorfields'][] = 'title';
            $response['errorfields'][] = 'artist';
            $error = true;
        }
        
        // sanitize the title and the artist fields
        $title = ucfirst ( sanitize_text_field( $_POST['title'] ) );
        $artist = ucwords ( sanitize_text_field( $_POST['artist'] ) );
        
        // check for errors
        if(empty($title) && empty($artist)){
            // neither title nor artist was provided, no request possible
            $response['text'] = __( 'You have to provide at least a song title or an artist to request a song!','ss_song_requester' );
            $response['errorfields'][] = 'title';
            $response['errorfields'][] = 'artist';
            $error = true;
        } elseif(!empty($title) && empty($artist)){
            // artist was not provided, try to find it out from existing requests
            $artist = self::find_artist_by_title_in_requests ( $title );
            if(false === $artist){
                // no artist found, no request possible
                $response['text'] = sprintf(__( 'Sorry, we could not find any artist for your requested song %s.','ss_song_requester' ), $title );
                $response['errorfields'][] = 'artist';
                $error = true;
            } else {
                // write to DB
                $check_write = self::request_to_db ($title, $artist);
                if(!is_bool ($check_write) || false === $check_write ){
                    $response['text'] = $check_write;
                    $error = true;
                }
            }
        } elseif(empty($title) && !empty($artist)){
            // title was not provided, try to find a title by this artist in existing requests
            $title = self::get_random_artist_title_from_requests ( $artist );
            if(false === $title){
                // no title found, no request possible
                $response['text'] = sprintf(__( 'Sorry, we could not find any title for your requested artist %s.','ss_song_requester' ), $artist );
                $response['errorfields'][] = 'title';
                $error = true;
            } else {
                // write to DB
                $check_write = self::request_to_db ($title, $artist);
                if(!is_bool ($check_write) || false === $check_write ){
                    $response['text'] = $check_write;
                    $error = true;
                }
            }
        } else {
            // fields are okay, write to DB
            $check_write = self::request_to_db ($title, $artist);
            if(!is_bool ($check_write) || false === $check_write ){
                $response['text'] = $check_write;
                $error = true;
            }
        }
        
        if(ob_get_contents()){
            ob_clean();
        }
        
        if(false === $error){
            // all went well: Output a success message
            $response['text'] = sprintf(__( 'Thank you, your request for the song %s by %s was added.','ss_song_requester' ), $title, $artist );
            wp_send_json_success( $response );
        } else {
            // just in case: A general error message
            if(empty($response['text'])){
                $response['text'] = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
            }
             wp_send_json_error( $response );
        }
        
        wp_die();
    }
    
    /**
	 * Increment the counter of a request via ajax by a given ID
	 *
	 * @since    1.0.0
	 */
    public static function request_song_metoo() {
        global $wpdb;
        
        $error = false;
        $response = array();
        
         // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_' . $_POST['post_id'], 'nonce', false ) == false ) {
            $response = __( 'No cheating please!','ss_song_requester' );
            $error = true;
        }
        
        $song_id = (int)$_POST['song_id'];
        
        $current_requests = $wpdb->get_var('SELECT count FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id. '"');
        
        if(empty($current_requests)){
            $response = __( 'Sorry, this song no longer exists.','ss_song_requester' );
            $error = true;
		} else {
            $new_requests = $current_requests + 1;
            if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('count' => ( $new_requests ) ), array('id' => $song_id) ) ) {
                $response = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
            } else {
                $response = (int)$new_requests;
            }
        }
        
        if(ob_get_contents()){
            ob_clean();
        }
        
        if(false === $error){
            wp_send_json_success( $response );
        } else {
            wp_send_json_error( $response );
        }
        
        wp_die();
    }
    
    /**
	 * Search request table by artist for missing title
	 *
	 * @since    1.0.0
	 * @param    string    $artist     The requested artist.
     * @return   string    $title      Title of a song, false on error
	 */
    static function get_random_artist_title_from_requests ( $artist = false ) {
        global $wpdb;
        
        $title = false;
        
        if($artist){
            // find all titles matching the requested artist
            $found_titles = $wpdb->get_results('SELECT title FROM ' . $wpdb->prefix . "ss_song_requester_current_requests WHERE artist LIKE '%".$title."%'");
            if (!empty($found_titles)){
                $the_titles = array();
                foreach ( $found_titles as $row ) {
                    $the_titles[] = $row->title;
                }
                
                // get random title, in case multiple titles were found
                $title = $the_titles[array_rand($the_titles)];
            }
        }
        
        return $title;
    }
    
    /**
	 * Search request table by title for missing artist
	 *
	 * @since    1.0.0
	 * @param    string    $title      The title of the requested song.
     * @return   string    $artist     Name of an artist, false on error
	 */
    static function find_artist_by_title_in_requests ( $title = false ) {
        global $wpdb;
        
        $artist = false;
        
        if($title){
            // find all artists matching the requested title
            $found_artists = $wpdb->get_results('SELECT artist FROM ' . $wpdb->prefix . "ss_song_requester_current_requests WHERE title LIKE '%".$title."%'");
            if (!empty($found_artists)){
                $the_artists = array();
                foreach ( $found_artists as $row ) {
                    $the_artists[] = $row->artist;
                }
                
                // get random artist, in case multiple artists were found
                $artist = $the_artists[array_rand($the_artists)];
            }
        }
        
        return $artist;
    }
    
    /**
	 * Insert or update request count
	 *
	 * @since    1.0.0
	 * @param    string        $title      The title of the requested song.
     * @param    string        $artist     The artist of the requested song.
     * @return   bool/string   $success    message if error, true if success
	 */
    static function request_to_db ($title = false, $artist = false){
        global $wpdb;
        
        $success = false;
        
        $check_title  = preg_replace( '#\W+#', '', strtolower($title) );
        $check_artist = preg_replace( '#\W+#', '', strtolower($artist) );
        
        // get all requests
        $current_requests = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests');
        
        
        if(empty($current_requests)){
            // table is empty, insert first record
            if(false === $wpdb->insert($wpdb->prefix . 'ss_song_requester_current_requests',array('title' => $title, 'artist' => $artist, 'count' => 1), array('%s','%s','%d'))){
                $success = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
            } else {
                $success = true;
            }
        } else {
            // records found, check for doubles
            $song_id = 0;
            foreach ( $current_requests as $existing_requests ){
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
                   
                    if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('count' =>( $request_counts + 1 ) ), array('id' => $song_id) )){
                        $success = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
                    } else {
                        $success = true;
                    }
                } else {
                    // song was allready played, get date and time of latest play
                    $date_format = get_option('date_format') . ' ' . get_option('time_format');
                    //$played_date = sprintf( __(' at %s','ss_song_requester'), date_i18n( $date_format, strtotime($allready_played) );
                    
                    $played_date = sprintf( __('%s ago','ss_song_requester'), human_time_diff( $allready_played, time() ) );
                    
                    $success = sprintf(__( 'Your requested song %s by %s was allready played %s. Please request another song, if you like.','ss_song_requester' ), $title, $artist, $played_date);
                }
            } else {
                // no double found, insert
                if(false === $wpdb->insert($wpdb->prefix . 'ss_song_requester_current_requests',array('title' => $title, 'artist' => $artist, 'count' => 1), array('%s','%s','%d'))){
                    $success = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
                } else {
                    $success = true;
                }
            }
        }
        
        return $success;
    }
    
    /**
	 * Mark a request as played via ajax by a given ID
	 *
	 * @since    1.0.0
	 */
    public static function request_mark_as_played() {
        global $wpdb;
        
        $error = false;
        
         // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_' . $_POST['post_id'], 'nonce', false ) == false ) {
            $response = __( 'No cheating please!','ss_song_requester' );
            $error = true;
        }
        
        $song_id = (int)$_POST['song_id'];
        
        $played_timestamp = time();
        
        if(false === $error){ 
            if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('played' => ( $played_timestamp ) ), array('id' => $song_id) ) ) {
                $response = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
                $error = true;
            }
        }
        
        if(ob_get_contents()){
            ob_clean();
        }
        
        if(false === $error){ 
            wp_send_json_success();
        } else {
            wp_send_json_error( $response );  
        }
        
        wp_die();
    }
    
    /**
	 * Mark an already played request as unplayed via ajax by a given ID
	 *
	 * @since    1.0.0
	 */
    public static function request_unmark_as_played() {
        global $wpdb;
        
        $error = false;
        
         // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_' . $_POST['post_id'], 'nonce', false ) == false ) {
            $response = __( 'No cheating please!','ss_song_requester' );
            $error = true;
        }
        
        $song_id = (int)$_POST['song_id'];
        
        $played_date = null;
        
        if(false === $error){ 
            if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('played' => ( $played_date ) ), array('id' => $song_id) ) ) {
                $response = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
                $error = true;
            }
        }
        
        if(ob_get_contents()){
            ob_clean();
        }
        
        if(false === $error){ 
            wp_send_json_success();
        } else {
            wp_send_json_error( $response );  
        }
        
        wp_die();
    }
    
    /**
	 * Delete a request via ajax by a given ID
	 *
	 * @since    1.0.0
	 */
    public static function request_delete() {
        global $wpdb;
        
        $error = false;
        
         // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_' . $_POST['post_id'], 'nonce', false ) == false ) {
            $response = __( 'No cheating please!','ss_song_requester' );
            $error = true;
        }
        
        $song_id = (int)$_POST['song_id'];
        
        if(false === $error){ 
            if(false === $wpdb->query('DELETE FROM '.$wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id . '"')){
                $response = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
                $error = true;
            }
        }
        
        if(ob_get_contents()){
            ob_clean();
        }
        
        if(false === $error){ 
            wp_send_json_success();
        } else {
            wp_send_json_error( $response );  
        }
        
        wp_die();
    }
    
    /**
	 * Delete all current requests via ajax
	 *
	 * @since    1.0.0
	 */
    public static function request_empty() {
        global $wpdb;
        
        $error = false;
        
         // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_' . $_POST['post_id'], 'nonce', false ) == false ) {
            $response = __( 'No cheating please!','ss_song_requester' );
            $error = true;
        }
        
        if(false === $error){ 
            if(false === $wpdb->query('DELETE FROM '.$wpdb->prefix . 'ss_song_requester_current_requests')){
                $response = __( 'Sorry, there was an error during your song request. Please try again in a few minutes.','ss_song_requester' );
                $error = true;
            }
        }
        
        if(ob_get_contents()){
            ob_clean();
        }
        
        if(false === $error){ 
            wp_send_json_success();
        } else {
            wp_send_json_error( $response );  
        }
        
        wp_die();
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
        if (true === $_POST['slow']) {
            usleep(500000);
        }

        
        
        // get values
        $song_id = (int)$_POST['song_id'];
        
        $allowed_attributes = array('artist','title');
        $attribute = esc_attr( trim ( strtolower( $_POST['attribute'] ) ) );
        
        $old_value = ucfirst ( sanitize_text_field ( $_POST['old_value'] ) );
        $new_value = ucwords ( sanitize_text_field ( $_POST['value'] ) );
        
         // check the nonce
        if ( check_ajax_referer( 'ss_song_requester_' . $_POST['post_id'], 'nonce', false ) == false ) {
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
	 * Build the request list, either on page load or via ajax call
	 *
	 * @since    1.0.0
     * @return   string   $playlist    complete html for the request list table
	 */
    public static function build_playlist () {
        global $wpdb;
        
        // get pagination class
        require_once SS_SONG_REQUESTER_DIR . 'includes/class-ss_song_requester_pagination.php';
        
        // beautify HTML output ;-)
        $tab = "\t";
        $lb = "\n";
        
         // get the settings
        $settings = ss_song_requester_admin::get_settings();
        
        // Get ID and create nonce
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $the_id = (int)$_POST['post_id'];
        } else {
            $the_id = get_the_id();
        }
        $nonce = wp_create_nonce( 'ss_song_requester_' . $the_id );
        
        // count items
        $pagination_items = $wpdb->get_results('SELECT id FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests');
        $pagination_items = $wpdb->num_rows;
        
        
        // prepare pagination
        if ($pagination_items > 0) {
            $requests_per_page = absint($settings['requests_per_page']);
            if($requests_per_page > 98){
                $requests_per_page = -1;
            }
            $p = new ss_song_requester_pagination;
            $p->items($pagination_items);
            $p->target(get_permalink($the_id));
            $p->limit($requests_per_page);
            $p->jumptarget('ss_playlist');
            $p->itemName(__('Entries','ss_song_requester'));
            $p->calculate();
            
            if (!isset($_GET['paging'])) {
                $p->page = 1;
            } else {
                $p->page = (int) $_GET['paging'];
            }

            $limit = 'LIMIT ' . ($p->page - 1) * $p->limit . ', ' . $p->limit;
            
        }
        
        $order_type = esc_sql( $settings['frontend_sort_type'] );
        $order_dir = esc_sql( $settings['frontend_sort_direction'] );
        
        
        // get current requests
        $current_requests = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests ORDER BY ' . $order_type . ' ' . $order_dir . ' ' . $limit );
		
		//build playlist
        if (!empty($current_requests)){
            $table_class = 'full';
        } else {
            $table_class = 'empty';
            
        }
        $playlist =  $lb  . $lb . '<!-- Begin sixtyseven song requester playlist -->' . $lb;
            
        $playlist .= '<table class="' . $table_class . '" id="ss_playlist">' . $lb;

        if (!empty($current_requests)){
            // build table header
            $playlist .= $tab . '<thead>' . $lb;
            $playlist .= $tab . $tab . '<tr>' . $lb;
            $playlist .= $tab . $tab . $tab . '<th>'. __('Song','ss_song_requester') . '</th>' . $lb;
            $playlist .= $tab . $tab . $tab . '<th>'. __('Artist','ss_song_requester') . '</th>' . $lb;
            $playlist .= $tab . $tab . $tab . '<th';
            if(!current_user_can('ss_manage_song_requests') && $settings['form_status'] === 'active'){
               $playlist .= ' colspan="2"';
            }
            $playlist .= '>'. __('Requests','ss_song_requester') . '</th>' . $lb;
            $playlist .= $tab . $tab . $tab . '<th>'. __('Played','ss_song_requester') . '</th>' . $lb;
            if(current_user_can('ss_manage_song_requests')){
                $playlist .= $tab . $tab . $tab . '<th>'. __('Actions','ss_song_requester') . '</th>' . $lb;
            }
            $playlist .= $tab . $tab . '</tr>' . $lb;
            $playlist .= $tab . '</thead>' . $lb;
        }

        $playlist .= $tab . '<tbody>' . $lb;
        
        if (!empty($current_requests)){
            foreach ($current_requests as $request){
                
                //die(var_dump($request));
                
                // prepare data
                $playlist_id = $request->id;
                $playlist_title = stripslashes ( $request->title );
                $playlist_artist = stripslashes ( $request->artist );
                $playlist_count = intval( $request->count );
                if(empty($request->played)){
                    $playlist_played = __('not yet','ss_song_requester');
                } else {
                    /*$date_format = get_option('date_format') . ' ' . get_option('time_format');
                    $played_date = date_i18n( $date_format, $request->played );*/
                    $playlist_played = sprintf( __('%s ago','ss_song_requester'), human_time_diff( $request->played, time() ) );
                }
                
                // build table rows
                $playlist .= $tab . $tab . '<tr class="request_row_' . $playlist_id . '">' . $lb;
                
                if(current_user_can('ss_manage_song_requests')){
                    // edit in place
                    $playlist .= $tab . $tab . $tab . '<td data-label="'. __('Song','ss_song_requester') . ':"><span class="inlineedit" data-song_id="' . $playlist_id . '" data-attribute="title" data-original="' . $playlist_title . '">'. $request->title . '</span></td>' . $lb;
                    $playlist .= $tab . $tab . $tab . '<td data-label="'. __('Artist','ss_song_requester') . ':"><span class="inlineedit" data-song_id="' . $playlist_id . '" data-attribute="artist" data-original="' . $playlist_artist . '">'. $playlist_artist . '</span></td>' . $lb;
                } else {
                    $playlist .= $tab . $tab . $tab . '<td data-label="'. __('Song','ss_song_requester') . ':">'. $playlist_title . '</td>' . $lb;
                    $playlist .= $tab . $tab . $tab . '<td data-label="'. __('Artist','ss_song_requester') . ':">'. $playlist_artist . '</td>' . $lb;
                }
                
                $playlist .= $tab . $tab . $tab . '<td data-label="'. __('Requests','ss_song_requester') . ':"><span class="counter_' . $playlist_id . '">'. $playlist_count . '</span> ' . __('times','ss_song_requester') . '</td>' . $lb;
                if(!current_user_can('ss_manage_song_requests') && $settings['form_status'] === 'active'){
                    // Button for "I want this, too!" ;-)
                    $playlist .=  $tab . $tab . $tab . '<td class="request_me_too_cell" data-label="'. __('Like it','ss_song_requester') . ':"><button class="request_me_too" data-song_id="' . $playlist_id . '" title="' . __('I want to hear this, too!','ss_song_requester') . '"><span class="fas fa-thumbs-up fa-fw"></span></button></td>' . $lb;;
                }
                $playlist .= $tab . $tab . $tab . '<td class="played_marker_' . $playlist_id . '" data-label="'. __('Played','ss_song_requester') . ':">' . $playlist_played . '</td>' . $lb;
                if(current_user_can('ss_manage_song_requests')){
                    $playlist .= $tab . $tab . $tab . '<td class="actioncell" data-label="'. __('Actions','ss_song_requester') . ':">' . $lb;
                    if(empty($request->played)){
                        // Button to mark as played 
                        $playlist .= $tab . $tab . $tab . $tab . '<button class="action request_played" data-song_id="' . $playlist_id . '" title="' . __('Mark song as played','ss_song_requester') . '"><span class="fas fa-volume-up fa-fw"></span></button>' . $lb;
                    } else {
                        // Button to unmark as played 
                        $playlist .= $tab . $tab . $tab . $tab . '<button class="action request_unplayed" data-song_id="' . $playlist_id . '" title="' . __('Mark song as not yet played','ss_song_requester') . '"><span class="fas fa-volume-down fa-fw"></span></button>' . $lb;
                    }

                    // Button to delete the request
                    $playlist .= $tab . $tab . $tab . $tab . '<button class="action request_delete" data-song_id="' . $playlist_id . '" title="' . __('Delete this request','ss_song_requester') . '"><span class="fas fa-trash-alt fa-fw"></span></button>' . $lb;

                    $playlist .= $tab . $tab . $tab . '</td>' . $lb;
                }
                $playlist .= $tab . $tab . '</tr>' . $lb;
            }
        } else {
            $playlist .= $tab . $tab . '<tr>' . $tab . $tab . $tab . '<td colspan="5"><h4 class="error">' . __('There are curently no requests.','ss_song_requester') . '</h4></td>' . $lb . $tab . $tab . '</tr>' . $lb;
        }
        
        $playlist .= $tab . '<tbody>' . $lb;


        $playlist .= $tab . '<tfoot>' . $lb . $tab . $tab . '<tr>' . $lb;
        
        $timer_playpause = '';
        
        /*$timer_playpause = ' &nbsp; ';
        
        $timer_playpause .= '<button class="timer_playpause" title="' . __('Pause timer','ss_song_requester') . '" id="countdown_pause"><span class="far fa-pause-circle fa-fw"></span> ' . __('Pause timer','ss_song_requester') . '</button>';
        
        $timer_playpause .= '<button class="timer_playpause" title="' . __('Resume timer','ss_song_requester') . '" id="countdown_resume"><span class="far fa-play-circle" fa-fw"></span> ' . __('Resume timer','ss_song_requester') . '</button>';*/

        // reload in xxx seconds
        $reload_content = sprintf( __('Auto-reload in %s seconds','ss_song_requester'), '<span class="ss_request_reload_indicator">' . absint($settings['autoreload_seconds']) . '</span>' );

        if(current_user_can('ss_manage_song_requests')){
            $playlist .= $tab . $tab . $tab . '<td colspan="3" class="reload_indicator_cell">' . $reload_content . '</td>' . $lb;

            $playlist .= $tab . $tab . $tab . '<td>';
            $playlist .= $timer_playpause;
            
            // Button to reload the request list now
            $playlist .= ' &nbsp; <button class="request_reload_now" title="' . __('Reload the complete list now','ss_song_requester') . '"><span class="fas fas fa-sync fa-fw"></span> ' . __('Reload now','ss_song_requester') . '</button>';
            $playlist .= '</td>' . $lb;
            
            if (!empty($current_requests)){
                $playlist .= $tab . $tab . $tab . '<td>';
                // Button to delete the complete request list
                $playlist .= ' &nbsp; <button class="request_empty" title="' . __('Delete the complete list','ss_song_requester') . '"><span class="fas fa-trash fa-fw"></span> ' . __('Empty list','ss_song_requester') . '</button>';
                $playlist .= '</td>' . $lb;
            }
        } else {
            $playlist .= $tab . $tab . $tab . '<td colspan="5" class="reload_indicator_cell">' . $reload_content . $timer_playpause .'</td>' . $lb;
        }
        
        
        $playlist .= $tab . $tab . '</tr>' . $lb . $tab . '</tfoot>' . $lb;

        $playlist .= '</table>' . $lb . $lb;
        if ($p->total_pages > 0) {
            $playlist .= '<div class="tablenav">' . $p->return_pagination() . '</div>' . $lb  . $lb;
        }
        $playlist .= '<!-- End sixtyseven song requester playlist -->' . $lb . $lb;
        
        // return json response if ajax
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_success( $playlist );
        } else {
            return $playlist;
        }
    }
	
}