(function( $ ) {
	'use strict';

	$(function() {
	   //alert('loaded');
        
        var ss_working_indicator = '<span class="fas fa-spinner fa-pulse fa-fw"></span>';
        var ss_request_list = $('.ss_song_requester_request_list');
        var reload_in_seconds = ss_song_requester_jsvars.autoreload_seconds;
        var inlineedit_value;
        
        // reload playlist check
        setInterval(function(){reload_request_list();},1000);
        
        $( ss_request_list ).on( 'click', '.request_me_too', function() {
            //alert('Clicked me too button');
            
            // set element vars
            var metoobutton  = $( this );
            
            // set value vars
            var metoo_song_id = metoobutton.data( 'song_id' );
            var metoo_counter = $('.counter_' + metoo_song_id );
            
            // set indicator and title
            metoobutton.html( ss_working_indicator );
            metoobutton.attr( 'title', ss_song_requester_jsvars.ajax_working );
            
            // set ajax data
            var metoo_data = {
                'action'    : 'ss_request_metoo',
                'post_id'   : ss_song_requester_jsvars.post_id,
                'song_id'   : $( this ).data( 'song_id' ),
                'nonce'     : ss_song_requester_jsvars.nonce,
            };
            
            $.post( ss_song_requester_jsvars.ajaxurl, metoo_data, function( response ) {
                //console.log( response );
                
                if ( true === response.success ) {
                    // update the counter
                    metoo_counter.text(response.data);
                    
                    // fade and remove the metoo button
                    metoobutton.fadeOut('slow', function(){ 
                        $(this).remove();
                    });
                } else {
                    metoobutton.html('<span class="fas fa-thumbs-up fa-fw"></span>');
                    metoobutton.attr('title',ss_song_requester_jsvars.metoo_label);
                }
            });
              
        });
        
        $( ss_request_list ).on( 'click', '.request_played', function() {
            //alert('Clicked mark request as played button');
            
            // set indicator and title
            $( this ).html( ss_working_indicator );
            $( this ).attr( 'title', ss_song_requester_jsvars.ajax_working );
            
            // set ajax data
            var played_data = {
                'action'    : 'ss_request_mark_played',
                'post_id'   : ss_song_requester_jsvars.post_id,
                'song_id'   : $( this ).data( 'song_id' ),
                'nonce'     : ss_song_requester_jsvars.nonce,
            };
            
            $.post( ss_song_requester_jsvars.ajaxurl, played_data, function( response ) {
                console.log( response );
                
                if ( true === response.success ) {
                    // reload the request list
                    reload_request_list_now();
                } else {
                    $( this ).html( '<span class="fas fa-volume-up fa-fw"></span>' );
                    $( this ).attr( 'title', ss_song_requester_jsvars.markplayed_label );
                }
            });
              
        });
        
        $( ss_request_list ).on( 'click', '.request_unplayed', function() {
            //alert('Clicked mark request as not yet played button');
            
            // set indicator and title
            $( this ).html( ss_working_indicator );
            $( this ).attr( 'title', ss_song_requester_jsvars.ajax_working );
            
            // set ajax data
            var unplayed_data = {
                'action'    : 'ss_request_unmark_played',
                'post_id'   : ss_song_requester_jsvars.post_id,
                'song_id'   : $( this ).data( 'song_id' ),
                'nonce'     : ss_song_requester_jsvars.nonce,
            };
            
            $.post( ss_song_requester_jsvars.ajaxurl, unplayed_data, function( response ) {
                //console.log( response );
                
                if ( true === response.success ) {
                    // reload the request list
                    reload_request_list_now();
                } else {
                    $( this ).html( '<span class="fas fa-volume-down fa-fw"></span>' );
                    $( this ).attr( 'title', ss_song_requester_jsvars.msrkplayed_label );
                }
            });
              
        });
        
        $( ss_request_list ).on( 'click', '.request_delete', function() {
          if ( confirm( ss_song_requester_jsvars.confirm_delete ) ) {
            //alert('Clicked delete request button');

            // set indicator and title
            $( this ).html( ss_working_indicator );
            $( this ).attr( 'title', ss_song_requester_jsvars.ajax_working );

            // set ajax data
            var delete_data = {
                'action'    : 'ss_request_delete',
                'post_id'   : ss_song_requester_jsvars.post_id,
                'song_id'   : $( this ).data( 'song_id' ),
                'nonce'     : ss_song_requester_jsvars.nonce,
            };

            $.post( ss_song_requester_jsvars.ajaxurl, delete_data, function( response ) {
                //console.log( response );

                if ( true === response.success ) {
                    // reload the request list
                    reload_request_list_now();
                } else {
                    $( this ).html( '<span class="fas fa-trash-alt fa-fw"></span>' );
                    $( this ).attr( 'title', ss_song_requester_jsvars.request_delete_label );
                }
            });
          }
        });
        
        $( ss_request_list ).on( 'click', '.request_empty', function() {
          if ( confirm( ss_song_requester_jsvars.confirm_empty ) ) {
            //alert('Clicked empty request button');

            // set indicator and title
            $( this ).html( ss_working_indicator );
            $( this ).attr( 'title', ss_song_requester_jsvars.ajax_working );

            // set ajax data
            var delete_data = {
                'action'    : 'ss_request_empty',
                'post_id'   : ss_song_requester_jsvars.post_id,
                'nonce'     : ss_song_requester_jsvars.nonce,
            };

            $.post( ss_song_requester_jsvars.ajaxurl, delete_data, function( response ) {
                //console.log( response );

                if ( true === response.success ) {
                    // reload the request list
                    reload_request_list_now();
                }
                
                $( this ).html( ss_song_requester_jsvars.empty_label );
                $( this ).attr( 'title', ss_song_requester_jsvars.empty_title);
            });
          }
        });
            
        $( '.ss_song_requester_request' ).on( 'click', '.show-form', function() {
            //alert('Clicked form opener button');
            
            // set element vars
            var formopener  = $( this );
            var formdiv     = $( '.ss_song_requester_request .form' );
            var messagebox  = $( '.ss_song_requester-response' );
            

            // show form, change class of wrapper and text of opener button
            if(formdiv.hasClass( 'closed' )){
                formdiv.removeClass( 'closed' ).slideDown( 'slow' ).addClass( 'open' );
                formopener.html( ss_song_requester_jsvars.hide_form_label );
            } else {
                formdiv.removeClass( 'open' ).slideUp( 'slow' ).addClass( 'closed' );
                formopener.html( ss_song_requester_jsvars.request_label );  
                messagebox.text( '' );
            }
        });
        
        $( '.ss_song_requester_request' ).on( 'click', '.request_now', function() {
            //alert('Clicked request button');
            
            // set element vars
            var sendbutton  = $( this );
            var titlefield  = $( '.ss_song_requester_request .form .title' );
            var artistfield = $( '.ss_song_requester_request .form .artist' );

            // set indicator
            sendbutton.html( ss_working_indicator );
            
            // set ajax data
            var request_data = {
                'action'    : 'ss_request_song',
                'post_id'   : ss_song_requester_jsvars.post_id,
                'title'     : titlefield.val(),
                'artist'    : artistfield.val(),
                'nonce'     : ss_song_requester_jsvars.nonce,
            };
            
            $.post( ss_song_requester_jsvars.ajaxurl, request_data, function( response ) {
                
                // set element vars
                var messagebox  = $( '.ss_song_requester-response' );
                var formdiv     = $( '.ss_song_requester_request .form' );
                var formopener  = $( '.ss_song_requester_request .show-form' );
                var sendbutton  = $( '.ss_song_requester_request .request_now' );
                
                //console.log( response );
                
                if ( true === response.success ) {
                    messagebox.removeClass( 'error' ).addClass( 'success' );
                    
                    titlefield.removeClass( 'error' );
                    artistfield.removeClass( 'error' );
                    
                    // remove values from fields and restore button label
                    titlefield.val(''),
                    artistfield.val(''),        
                    sendbutton.html(ss_song_requester_jsvars.request_now_label);
                    
                    // close form div and restore button label
                    //formdiv.removeClass( 'open' ).slideUp( 'slow' ).addClass( 'closed' );
                    //formopener.text(ss_song_requester_jsvars.request_label);
                    
                    // reload the request list
                    reload_request_list_now();
                    
                } else {
                    if(response.data.errorfields.indexOf('title') !== -1){
                        // error in title field
                        titlefield.addClass( 'error' );
                    }
                    
                    if(response.data.errorfields.indexOf('artist') !== -1){
                        // error in artist field
                        artistfield.addClass( 'error' );
                    }
                    
                    messagebox.removeClass( 'success' ).addClass( 'error' ); 
                   
                    sendbutton.html(ss_song_requester_jsvars.tryagain_label);
                }
                
                // print out response data
                messagebox.text( response.data.text );

            });   
        });
        
        
        $( ss_request_list ).on( 'click', '.request_reload_now', function() {
            reload_request_list_now();
        });
        
        ss_edit_in_place();
        
        function reload_request_list(){
            if(reload_in_seconds === 0){
                $('.reload_indicator_cell').html(ss_song_requester_jsvars.reloadnow_label);
                reload_request_list_now();
            } else {
                $('.ss_request_reload_indicator').text(reload_in_seconds);
                reload_in_seconds --;
            }
        }
        
        function reload_request_list_now(){
            ss_request_list.slideUp( 'slow' );
            
             // set ajax data
            var reload_data = {
                'action'    : 'ss_request_reload_list',
                'post_id'   : ss_song_requester_jsvars.nonce,
                'nonce'     : ss_song_requester_jsvars.post_id,
            };

            $.post( ss_song_requester_jsvars.ajaxurl, reload_data, function( response ) {
                //console.log( response );

                if ( true === response.success ) {
                    // reload the request list
                    ss_request_list.html( response.data );
                    ss_request_list.slideDown( 'slow' );
                    reload_in_seconds = ss_song_requester_jsvars.autoreload_seconds;
                    
                    ss_edit_in_place();
                    
                }
            });
        }
        
        function ss_edit_in_place(){

           var submitdata = {};
           submitdata.slow = false; // for testing the saving indicator
           submitdata.action = 'ss_request_change_attribute_frontend';
            
           $('span.inlineedit').editable(ss_song_requester_jsvars.ajaxurl, {
                type    : 'text',
                submit  : ss_song_requester_jsvars.inlineedit_ok_label,
                cancel  : ss_song_requester_jsvars.inlineedit_cancel_label,
                cssclass : 'inlineedit_field',
                cancelcssclass : 'inlineedit_cancel',
                submitcssclass : 'inlineedit_submit',
                indicator : ss_song_requester_jsvars.inlineedit_saving,
                tooltip : ss_song_requester_jsvars.inlineedit_title,
                showfn : function(elem) {
                    var trigger = elem.closest('span.inlineedit');
                
                    submitdata.post_id = ss_song_requester_jsvars.post_id;
                    submitdata.nonce = ss_song_requester_jsvars.nonce;
                
                    submitdata.song_id = trigger.data('song_id');
                    submitdata.attribute = trigger.data('attribute');
                    submitdata.old_value = trigger.data('original');
                    
                    //console.log(submitdata);
                    
                    elem.fadeIn();
                },
                submitdata : submitdata,
                callback : function(result, settings, submitdata) {
                    //console.log('Triggered after submit');
                    //console.log('Result: ' + result);
                    //console.log('Submitdata: ' + submitdata.attribute);
                }
            });

        }

    });

	$( window ).load(function() {
    
    });

})( jQuery );