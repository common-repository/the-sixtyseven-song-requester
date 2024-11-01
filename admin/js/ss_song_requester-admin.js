(function( $ ) {
	'use strict';

	 $(function() {
         // initialize color picker
         $('.ss-color-field').wpColorPicker();
         
            ss_edit_in_place();
         
             function ss_edit_in_place(){
                 
               var submitdata = {};
               //submitdata.slow = false; // for testing the saving indicator
               submitdata.action = 'ss_request_change_attribute_backend';

               $('span.inlineedit').editable(ajaxurl, {
                    type    : 'text',
                    submit  : ss_song_requester_jsvars.inlineedit_ok_label,
                    cancel  : ss_song_requester_jsvars.inlineedit_cancel_label,
                    cssclass : 'inlineedit_field',
                    cancelcssclass : 'button-secondary inlineedit_cancel',
                    submitcssclass : 'button-secondary inlineedit_submit',
                    indicator : ss_song_requester_jsvars.inlineedit_saving,
                    tooltip : ss_song_requester_jsvars.inlineedit_title,
                    showfn : function(elem) {
                        var trigger = elem.closest('span.inlineedit');
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
    
    
    
})( jQuery );