(function() {
    'use strict';
    
    tinymce.PluginManager.add('ss_song_requester_button', function( editor, url ) {
        editor.addButton( 'ss_song_requester_button', {
            title: editor.getLang('ss_song_requester.insert_shortcode'),
            icon: 'ss_dashicon dashicons-format-audio',
            onclick: function() {
                editor.insertContent('[ss_song_requester]');
            }
        });
    });
})();