<?php 
// external language strings for tiny mce
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( '_WP_Editors' ) ) {
    require( ABSPATH . WPINC . '/class-wp-editor.php' );
}

function ss_tinymce_strings() {
    $strings = array(
        'helloworld' => __('Hello World!','ss_song_requester'),
        'insert_shortcode' => __('Insert Song Requests Shortcode','ss_song_requester'),
    );
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.ss_song_requester", ' . json_encode( $strings ) . ");\n";

     return $translated;
}

$strings = ss_tinymce_strings();