<?php

/**
 * Lists song requests in admin in a proper way
 *
 * @link       https://www.sixtyseven.info
 * @since      1.0.0
 *
 * @package    ss_song_requester
 * @subpackage ss_song_requester/includes
 */

/**
 * Lists song requests in admin in a proper way
 *
 * @since      1.0.0
 * @package    ss_song_requester
 * @subpackage ss_song_requester/includes
 * @author     André R. Kohl <code@sixtyseven.info>
 */

class ss_song_requester_List_Table extends WP_List_Table {
    
    private $wp_screen;
    
    /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    * @since    1.0.0
    */
 
    function __construct() {
 
       parent::__construct( array(
 
            'singular'  => 'song_request',     //singular name of the listed records
 
            'plural'    => 'song_requests',    //plural name of the listed records
 
            'ajax'      => false 
 
        ) );
        
        $screen = $this->get_wp_screen();
        add_filter( 'manage_' . $screen->id . '_columns' , array( $this , 'manage_columns') );
 
    }
    
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     * @since    1.0.0
     */
    public function prepare_items(){
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $per_page = $this->get_items_per_page( 'requests_per_page', 5 );
        $current_page = $this->get_pagenum();

        $this->set_pagination_args( array(
            'total_items' => count($data),
            'per_page'    => $per_page
        ) );

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;     
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     * @since    1.0.0
     */
    public function get_columns(){
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'id'          => __('ID','ss_song_requester'),
            'title'       => __('Title','ss_song_requester'),
            'artist'      => __('Artist','ss_song_requester'),
            'played'      => __('Played','ss_song_requester'),
            'count'       => __('Whished','ss_song_requester'),
            'action'      => __('Action','ss_song_requester')
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     * @since    1.0.0
     */
    public function get_hidden_columns(){
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns(){
        return array(
            'title'  => array('title', false),
            'artist' => array('artist', false),
            'id'     => array('id', false),
            'count'  => array('count', false),
            'played' => array('played', false),
        );
    }
    
    /**
    * Set up our screen columns.
    *
    * Impacts screen options column list.
    *
    * @param   array   columns     the existing columns
    * @return  array   columns     the modified columns
    **/
    public function manage_columns( $columns ) {
        unset($columns['action']);
        
        return $columns;
    }
    
    /**
     * Get the table data
     *
     * @return Array
     * @since    1.0.0
     */
    private function table_data(){
        global $wpdb;
 
        $table_name = $wpdb->prefix . 'ss_song_requester_current_requests';
 
        $data = array();
        
        if(!empty($_REQUEST['s'])){
           
            $search = $_REQUEST['s'];
            $search = trim($search);
            $search = esc_sql($search);
 
            $requests = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE (id LIKE \'%' . $search . '%\' OR title LIKE \'%' . $search . '%\' OR artist LIKE \'%' . $search . '%\')');
 
        } else {
            $requests = $wpdb->get_results('SELECT * FROM ' . $table_name);
        }

        foreach ($requests as $request) {
            $the_request = array();
            if($request->count){
                $the_request['count'] = $request->count;
            } else {
                $the_request['count'] = 0;
            }
            if($request->played){
                $the_request['played'] = $request->played;
            } else {
                $the_request['played'] = 0;
            }
            $the_request['artist'] = $request->artist;
            $the_request['title'] = $request->title;
            $the_request['id'] = $request->id;
            
            $data[] = $the_request;
        }
        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     * @since    1.0.0
     */
    public function column_default( $item, $column_name ){
        // create a nonce
        $delete_nonce = wp_create_nonce( 'ss_delete_request' );
        $mark_nonce = wp_create_nonce( 'ss_mark_request_played' );
        $unmark_nonce = wp_create_nonce( 'ss_mark_request_unplayed' );
        
         // prepare data
        $list_id = absint( $item['id'] );
        $list_title = stripslashes ( $item[ 'title' ] );
        $list_artist = stripslashes ( $item[ 'artist'] );
        $list_count = intval( $item[ 'count' ] );
        
        // actions
        $actions = array();
        $actions['delete'] = sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s" onclick="return confirm(\'' . __('Are you sure to delete this song request? This can not be undone!','ss_song_requester') . '\')">' . __('Delete','ss_song_requester') . '</a>', esc_attr( $_REQUEST['page'] ), 'delete', $list_id, $delete_nonce );
        
        if($item['played'] > 0) {
           $actions['mark_unplayed'] = sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">' . __('Mark as not yet played','ss_song_requester') . '</a>', esc_attr( $_REQUEST['page'] ), 'mark_unplayed', $list_id, $unmark_nonce );  
        } else {
           $actions['mark_played'] = sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">' . __('Mark as played','ss_song_requester') . '</a>', esc_attr( $_REQUEST['page'] ), 'mark_played', $list_id, $mark_nonce ); 
        }
        
        switch( $column_name ) {
            case 'played':
                if($item[ $column_name ] > 0) {
                    // get timestamp
                    return sprintf( __('%s ago','ss_song_requester'), human_time_diff( $item[ $column_name ], time() ) );
                } else {
                    return __('Not yet','ss_song_requester');
                }
            break;
            case 'action':
                return $this->row_actions( $actions, true );
            break;
            case 'artist':
                return '<span class="inlineedit" data-song_id="' . $list_id . '" data-attribute="artist" data-original="' . $list_artist . '">' . $list_artist . '</span>';
            break;
            case 'title':
                return '<span class="inlineedit" data-song_id="' . $list_id . '" data-attribute="title" data-original="' . $list_title . '">' . $list_title . '</span>';
            break;
            case 'count':
                return $list_count;
            break;
            case 'id':
                return $list_id;
            break;
            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     * @since    1.0.0
     */
    private function sort_data( $a, $b ){
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby'])){
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order'])){
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc'){
            return $result;
        }

        return -$result;
    }
    
    /**
     * Populates the bulk action dropdown
     *
     * @return Array
     * @since    1.0.0
     */
    public function get_bulk_actions(){
 
        $actions = array(
            'bulk-delete'       => __('Delete requests','ss_song_requester'),
            'bulk-mark_played'  => __('Mark requests as played','ss_song_requester'),
            'bulk-mark_unplayed'  => __('Mark requests as not yet played','ss_song_requester'),
        );
 
        return $actions;
    }
    
    /**
     * Processes the bulk actions from dropdown and the single actions from links
     *
     * @since    1.0.0
     */
    public function process_actions(){
        global $wpdb;
        
        // holds the admin notices
        $transient = array();
        
        // 1.: Single link actions
        
        // a) delete song
        if ('delete' === $this->current_action()) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( ! wp_verify_nonce( $nonce, 'ss_delete_request' ) ) {
                die(__('Cheating, huh?', 'ss_song_requester'));
            } else {
                $song_id = intval($_REQUEST['id']);
                $songdata = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id .'"');

                if(false === $wpdb->query('DELETE FROM '.$wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id . '"')){
                    //set error message
                    $admin_notice = array();
                    $admin_notice['type'] = 'error';
                    $admin_notice['text'] = sprintf(__( 'Sorry, the song &quot;%s&quot; by %s could not be deleted.','ss_song_requester' ), $songdata->title, $songdata->artist);
                } else {
                   //set success message
                    $admin_notice = array();
                    $admin_notice['type'] = 'success';
                    $admin_notice['text'] = sprintf(__( 'The song &quot;%s&quot; by %s was successfully deleted.','ss_song_requester' ), $songdata->title, $songdata->artist); 
                }
               
                $transient[] = $admin_notice;  
                
                // set the transient for the admin messages
                set_transient( 'ss_song_requester_admin_notices', $transient );

                // redirect and exit function
                wp_redirect( esc_url( add_query_arg([]) ) );
                exit;   
                
            }
        }
        
        // b) mark song as played
        if ('mark_played' === $this->current_action()) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( ! wp_verify_nonce( $nonce, 'ss_mark_request_played' ) ) {
                die(__('Cheating, huh?', 'ss_song_requester'));
            } else {
                $song_id = intval($_REQUEST['id']);
                $songdata = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id .'"');

                if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('played' => ( time() ) ), array('id' => $song_id) ) ) {
                    //set error message
                    $admin_notice = array();
                    $admin_notice['type'] = 'error';
                    $admin_notice['text'] = sprintf(__( 'Sorry, the song &quot;%s&quot; by %s could not be marked as played.','ss_song_requester' ), $songdata->title, $songdata->artist);
                } else {
                   //set success message
                    $admin_notice = array();
                    $admin_notice['type'] = 'success';
                    $admin_notice['text'] = sprintf(__( 'The song &quot;%s&quot; by %s was successfully marked as played.','ss_song_requester' ), $songdata->title, $songdata->artist); 
                }
               
                $transient[] = $admin_notice;  
                
                // set the transient for the admin messages
                set_transient( 'ss_song_requester_admin_notices', $transient );

                // redirect and exit function
                wp_redirect( esc_url( add_query_arg([]) ) );
                exit;   
                
            }
        }
        
        // c) mark song as not yet played
        if ('mark_unplayed' === $this->current_action()) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( ! wp_verify_nonce( $nonce, 'ss_mark_request_unplayed' ) ) {
                die(__('Cheating, huh?', 'ss_song_requester'));
            } else {
                $song_id = intval($_REQUEST['id']);
                $songdata = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id .'"');

                if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('played' => ( '' ) ), array('id' => $song_id) ) ) {
                    //set error message
                    $admin_notice = array();
                    $admin_notice['type'] = 'error';
                    $admin_notice['text'] = sprintf(__( 'Sorry, the song &quot;%s&quot; by %s could not be marked as not yet played.','ss_song_requester' ), $songdata->title, $songdata->artist);
                } else {
                   //set success message
                    $admin_notice = array();
                    $admin_notice['type'] = 'success';
                    $admin_notice['text'] = sprintf(__( 'The song &quot;%s&quot; by %s was successfully marked as not yet played.','ss_song_requester' ), $songdata->title, $songdata->artist); 
                }
               
                $transient[] = $admin_notice;  
                
                // set the transient for the admin messages
                set_transient( 'ss_song_requester_admin_notices', $transient );

                // redirect and exit function
                wp_redirect( esc_url( add_query_arg([]) ) );
                exit;   
                
            }
        }
        
        // 2.: Bulk actions
        
        // a) delete song
        if ('bulk-delete' === $this->current_action()) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] )) {
                die(__('Cheating, huh?', 'ss_song_requester'));
            } else {
                if(is_array($_REQUEST['ss-song-request-bulk'])){
                    // initiate success indicator and delete counter
                    $success = true;
                    $deleted = 0;
                    foreach($_REQUEST['ss-song-request-bulk'] as $id){
                        $song_id = intval($id);
                        if(false === $wpdb->query('DELETE FROM '.$wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id . '"')){
                            $songdata = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id .'"');
                            
                            // set error message
                            $admin_notice = array();
                            $admin_notice['type'] = 'error';
                            $admin_notice['text'] = sprintf(__( 'Sorry, the song &quot;%s&quot; by %s could not be deleted.','ss_song_requester' ), $songdata->title, $songdata->artist);
                           
                            $success = false;
                            $transient[] = $admin_notice;
                        } else {
                            $deleted ++;
                        }
                    }
                    
                    if(true === $success){
                        //set success message:
                        $admin_notice = array();
                        $admin_notice['type'] = 'success';
                        $admin_notice['text'] = sprintf(_n( 'One request was successfully deleted.', '%s requests were successfully deleted.', $deleted, 'ss_song_requester'  ) , intval($deleted) );
                        
                        $transient[] = $admin_notice;
                    }
                    
                     // set the transient for the admin messages
                    set_transient( 'ss_song_requester_admin_notices', $transient );

                    // redirect and exit function
                    wp_redirect( esc_url( add_query_arg([]) ) );
                    exit;    
                }
                
            }
        }
        
        // b) mark song as played
        if ('bulk-mark_played' === $this->current_action()) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] )) {
                die(__('Cheating, huh?', 'ss_song_requester'));
            } else {
                if(is_array($_REQUEST['ss-song-request-bulk'])){
                    // initiate success indicator and mark_played counter
                    $success = true;
                    $marked = 0;
                    
                    $played_timestamp = time();
                    
                    foreach($_REQUEST['ss-song-request-bulk'] as $id){
                        $song_id = intval($id);
                        if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('played' => ( $played_timestamp ) ), array('id' => $song_id) ) ) {
                            $songdata = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id .'"');
                            
                            // set error message
                            $admin_notice = array();
                            $admin_notice['type'] = 'error';
                            $admin_notice['text'] = sprintf(__( 'Sorry, the song &quot;%s&quot; by %s could not be marked as played.','ss_song_requester' ), $songdata->title, $songdata->artist);
                           
                            $success = false;
                            $transient[] = $admin_notice;
                        } else {
                            $marked ++;
                        }
                    }
                    
                    if(true === $success){
                        //set success message:
                        $admin_notice = array();
                        $admin_notice['type'] = 'success';
                        $admin_notice['text'] = sprintf(_n( 'One request was successfully marked as played.', '%s requests were successfully marked as played.', $marked, 'ss_song_requester'  ) , intval($marked) );
                        
                        $transient[] = $admin_notice;
                    }
                    
                     // set the transient for the admin messages
                    set_transient( 'ss_song_requester_admin_notices', $transient );

                    // redirect and exit function
                    wp_redirect( esc_url( add_query_arg([]) ) );
                    exit;    
                }
                
            }
        }
        
        // c) mark song as not yet played
        if ('bulk-mark_unplayed' === $this->current_action()) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] )) {
                die(__('Cheating, huh?', 'ss_song_requester'));
            } else {
                if(is_array($_REQUEST['ss-song-request-bulk'])){
                    // initiate success indicator and mark_played counter
                    $success = true;
                    $marked = 0;
                    
                    foreach($_REQUEST['ss-song-request-bulk'] as $id){
                        $song_id = intval($id);
                        if(false === $wpdb->update($wpdb->prefix . 'ss_song_requester_current_requests', array('played' => ( '' ) ), array('id' => $song_id) ) ) {
                            $songdata = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'ss_song_requester_current_requests WHERE id="' . $song_id .'"');
                            
                            // set error message
                            $admin_notice = array();
                            $admin_notice['type'] = 'error';
                            $admin_notice['text'] = sprintf(__( 'Sorry, the song &quot;%s&quot; by %s could not be marked as not yet played.','ss_song_requester' ), $songdata->title, $songdata->artist);
                           
                            $success = false;
                            $transient[] = $admin_notice;
                        } else {
                            $marked ++;
                        }
                    }
                    
                    if(true === $success){
                        //set success message:
                        $admin_notice = array();
                        $admin_notice['type'] = 'success';
                        $admin_notice['text'] = sprintf(_n( 'One request was successfully marked as not yet played.', '%s requests were successfully marked as not yet played.', $marked, 'ss_song_requester'  ) , intval($marked) );
                        
                        $transient[] = $admin_notice;
                    }
                    
                     // set the transient for the admin messages
                    set_transient( 'ss_song_requester_admin_notices', $transient );

                    // redirect and exit function
                    wp_redirect( esc_url( add_query_arg([]) ) );
                    exit;    
                }
                
            }
        }
    }
    
    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     * @return string
     * @since    1.0.0
     */
    public function column_cb( $item ) {
      return sprintf(
        '<input type="checkbox" name="ss-song-request-bulk[]" value="%s" />', $item['id']
      );
    }
    
    /** Text displayed when no request data is available */
    public function no_items() {
      _e( 'Currently there are no song requests to display.', 'ss_song_requester' );
    }
    
    /**
    * Get the wp_screen property.
    *
    * @return object
    * @since    1.0.0
    */
    private function get_wp_screen() {
        if ( empty( $this->wp_screen ) ) {
            $this->wp_screen = get_current_screen();
        }
        return $this->wp_screen;
    }
}