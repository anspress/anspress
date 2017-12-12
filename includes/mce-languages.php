<?php # -*- coding: utf-8 -*-
if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function get_anspress_tinymce_plugin_translations_string() {
    $strings = array(
        'i18n_insert_image' => __( 'Insert image', 'anspress-question-answer' ),
        'i18n_insert_media' => __( 'Insert Media (AnsPress)', 'anspress-question-answer' ),
        'i18n_close' => __( 'Close', 'anspress-question-answer' ),
        'i18n_select_file' => __( 'Select File', 'anspress-question-answer' ),
        'i18n_browse_from_computer' => __( 'Browse from computer', 'anspress-question-answer' ),
        'i18n_image_title' => __( 'Image title', 'anspress-question-answer' ),
        'i18n_media_preview' => __( 'Media preview', 'anspress-question-answer' ),
                     
    );
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.anspress", ' . json_encode( $strings ) . ");\n";

     return $translated;
}

$strings = get_anspress_tinymce_plugin_translations_string();
