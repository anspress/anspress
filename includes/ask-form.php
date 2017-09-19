<?php
/**
 * Form and controls of ask form
 *
 * @link         https://anspress.io
 * @since        2.0.1
 * @license      GPL-3.0+
 * @package      AnsPress
 * @subpackage   Ask Form
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Output new/edit question form.
 * Pass post_id to edit existing question.
 *
 * @return void
 */
function ap_ask_form( $post_id = false ) {
	$editing = false;
	$editing_id = ap_sanitize_unslash( 'id', 'r' );

	// If post_id is empty then its not editing.
	if ( ! empty( $editing_id ) ) {
		$editing = true;
	}

	if ( $editing && ! ap_user_can_edit_question( $editing_id ) ) {
		echo '<p>' . esc_attr__( 'You cannot edit this question.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	if ( ! $editing && ! ap_user_can_ask( $editing_id ) ) {
		echo '<p>' . esc_attr__( 'You do not have permission to ask a question.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	anspress()->get_form( 'question' )->generate();
}

/**
 * Remove stop words from post name if option is enabled.
 *
 * @param  string $str Post name to filter.
 * @return string
 * @since  3.0.0
 */
function ap_remove_stop_words_post_name( $str ) {
	$str = sanitize_title( $str );

	if ( ap_opt( 'keep_stop_words' ) ) {
		return $str;
	}

	$post_name = ap_remove_stop_words( $str );

	// Check if post name is not empty.
	if ( ! empty( $post_name ) ) {
		return $post_name;
	}

	// If empty then return original without stripping stop words.
	return sanitize_title( $str );
}

/**
 * TinyMCE editor setting
 *
 * @return array
 * @since  3.0.0
 */
function ap_tinymce_editor_settings( $type = 'question' ) {
	$setting = array(
		'textarea_rows' => 8,
		'tinymce'       => ap_opt( $type . '_text_editor' ) ? false: true,
		'quicktags'     => ap_opt( $type . '_text_editor' ) ? true:  false,
		'media_buttons' => false,
	);

	if ( ap_opt( $type . '_text_editor' )  ) {
		$settings['tinymce'] = array(
			'content_css'      => ap_get_theme_url( 'css/editor.css' ),
			'wp_autoresize_on' => true,
			'statusbar'        => false
		);
	}

	return apply_filters( 'ap_tinymce_editor_settings', $setting, $type );
}

/**
 * Sanitize AnsPress question and answer description field for database.
 *
 * @param  string $content Post content.
 * @return string          Sanitised post content
 * @since  3.0.0
 */
function ap_sanitize_description_field( $content ) {
	$content = str_replace( '<!--more-->', '', $content );
	$content = preg_replace_callback( '/<pre.*?>(.*?)<\/pre>/imsu', 'ap_sanitize_description_field_pre_content', $content );
	$content = preg_replace_callback( '/<code.*?>(.*?)<\/code>/imsu', 'ap_sanitize_description_field_code_content', $content );
	$content = wp_kses( $content, ap_form_allowed_tags() );
	$content = sanitize_post_field( 'post_content', $content, 0, 'db' );
	return $content;
}

function ap_sanitize_description_field_pre_content( $matches ) {
	return '<pre>' . esc_html( $matches[1] ) . '</pre>';
}

function ap_sanitize_description_field_code_content( $matches ) {
	return '<code>' . esc_html( $matches[1] ) . '</code>';
}
