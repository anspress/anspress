<?php // -*- coding: utf-8 -*-
/**
 * AnsPress tinymce translations.
 *
 * @package   AnsPress
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @since     4.1.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '_WP_Editors' ) ) {
	require ABSPATH . WPINC . '/class-wp-editor.php';
}

/**
 * Tinymce translations.
 *
 * @return array
 * @since 4.1.5
 */
function ap_tinymce_translations() {
	$strings = array(
		'i18n_insert_image'         => __( 'Insert image', 'anspress-question-answer' ),
		'i18n_insert_media'         => __( 'Insert Media (AnsPress)', 'anspress-question-answer' ),
		'i18n_close'                => __( 'Close', 'anspress-question-answer' ),
		'i18n_select_file'          => __( 'Select File', 'anspress-question-answer' ),
		'i18n_browse_from_computer' => __( 'Browse from computer', 'anspress-question-answer' ),
		'i18n_image_title'          => __( 'Image title', 'anspress-question-answer' ),
		'i18n_media_preview'        => __( 'Media preview', 'anspress-question-answer' ),
		'i18n_insert_code'          => __( 'Insert code', 'anspress-question-answer' ),
		'i18n_insert_codes'         => __( 'Insert codes (AnsPress)', 'anspress-question-answer' ),
		'i18n_insert'               => __( 'Insert', 'anspress-question-answer' ),
		'i18n_inline'               => __( 'Inline?', 'anspress-question-answer' ),
		'i18n_insert_your_code'     => __( 'Insert your code.', 'anspress-question-answer' ),
	);

	$locale     = _WP_Editors::$mce_locale;
	$translated = 'tinyMCE.addI18n("' . $locale . '.anspress", ' . wp_json_encode( $strings ) . ");\n";

	return $translated;
}

$strings = ap_tinymce_translations();
