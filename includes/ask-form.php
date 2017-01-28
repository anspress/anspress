<?php
/**
 * Form and controls of ask form
 *
 * @link https://anspress.io
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get all ask form fields.
 *
 * @param  integer|boolean $post_id Post ID.
 * @return array
 * @since  3.0.0
 */
function ap_get_ask_form_fields( $post_id = false ) {
	global $editing_post;
	$editing = false;

	if ( $post_id && ap_user_can_edit_question( (int) $post_id ) ) {
		$editing = true;
		$editing_post = ap_get_post( (int) $post_id, 'OBJECT', 'edit' );
	}

	$is_private = false;

	if ( $editing ) {
		$is_private = $editing_post->post_status == 'private_post' ? true : false;
	}

	$fields = array(
		array(
			'name' => 'title',
			'label' => __( 'Title', 'anspress-question-answer' ),
			'type'  => 'text',
			'placeholder'  => __( 'Question in one sentence', 'anspress-question-answer' ),
			'value' => ( $editing ? $editing_post->post_title : ap_isset_post_value( 'title', '' ) ),
			'order' => 5,
			'attr' => 'data-action="suggest_similar_questions" data-loadclass="q-title"',
			'autocomplete' => false,
			'sanitize' => array( 'sanitize_text_field' ),
			'validate' => array( 'required' => true, 'length_check' => ap_opt( 'minimum_qtitle_length' ) ),
		),
		array(
			'name' => 'suggestion',
			'type'  => 'custom',
			'order' => 5,
			'html' => '<div id="similar_suggestions"></div>',
		),
		array(
			'name' => 'description',
			'label' => __( 'Description', 'anspress-question-answer' ),
			'type'  => 'editor',
			'value' => ( $editing ? $editing_post->post_content : ap_isset_post_value( 'description', '' )  ),
			'settings' => ap_tinymce_editor_settings( 'question' ),
			'sanitize' => array( 'sanitize_description' ),
			'validate' => array( 'length_check' => ap_opt( 'minimum_question_length' ) ),
		),
		array(
			'name'  => 'ap_upload',
			'type'  => 'custom',
			'html' => ap_post_upload_form( $editing? $editing_post->ID : false ),
			'order' => 10,
		),
		array(
			'name' => 'parent_id',
			'type'  => 'hidden',
			'value' => ( $editing ? $editing_post->post_parent : get_query_var( 'parent' )  ),
			'order' => 20,
			'sanitize' => array( 'only_int' ),
		),
	);

	// Add name fields if anonymous is allowed.
	if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) ) {
		$fields[] = array(
			'name'      => 'anonymous_name',
			'label'     => __( 'Name', 'anspress-question-answer' ),
			'type'      => 'text',
			'placeholder'  => __( 'Enter your name to display', 'anspress-question-answer' ),
			'value'     => ap_isset_post_value( 'name', '' ),
			'order'     => 12,
			'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
		);
	}

	// Add private field checkbox if enabled.
	if ( ap_opt( 'allow_private_posts' ) ) {
		$fields[] = array(
			'name' => 'is_private',
			'type'  => 'checkbox',
			'desc'  => __( 'Only visible to admin and moderator.', 'anspress-question-answer' ),
			'value' => $is_private,
			'order' => 12,
			'show_desc_tip' => false,
			'sanitize' => array( 'only_boolean' ),
		);
	}

	if ( $editing ) {
		$fields[] = array(
			'name'  => 'edit_post_id',
			'type'  => 'hidden',
			'value' => $editing_post->ID,
			'order' => 20,
			'sanitize' => array( 'only_int' ),
		);
	}

	$fields[] = array(
		'name'  => 'ap_ajax_action',
		'type'  => 'hidden',
		'value' => 'ask_form',
		'order' => 20,
	);

	/**
	 * FILTER: ap_ask_form_fields
	 * Filter for modifying $args
	 *
	 * @param 	array 	$fields 	Ask form fields.
	 * @param 	bool 	$editing 	Currently editing form.
	 * @since  	2.0
	 */
	$fields = apply_filters( 'ap_ask_form_fields', array( 'fields' => $fields ), $editing );

	return $fields['fields'];
}

/**
 * Output new/edit question form.
 * Pass post_id to edit existing question.
 *
 * @return void
 */
function ap_ask_form( $post_id = false ) {
	$editing = true;

	if ( false === $post_id ) {
		$post_id = ap_sanitize_unslash( 'id', 'r', false );
	}

	// If post_id is empty then its not editing.
	if ( empty( $post_id ) ) {
		$editing = false;
	}

	if ( $editing && ! ap_user_can_edit_question( $post_id ) ) {
		echo '<p>' . esc_attr__( 'You cannot edit this question.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	$_post = ap_get_post( $post_id );

	// Check if valid post type.
	if ( $editing && 'question' !== $_post->post_type ) {
		echo '<p>' . esc_attr__( 'Post you are trying to edit is not a question.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	// Ask form arguments.
	$args = array(
		'name'              => 'ask_form',
		'is_ajaxified'      => true,
		'submit_button'     => ($editing ? __( 'Update question', 'anspress-question-answer' ) : __( 'Post question', 'anspress-question-answer' )),
		'fields'            => ap_get_ask_form_fields( $post_id ),
		'attr'							=> ' ap="questionForm"',
	);

	$form = new AnsPress_Form( $args );
	echo $form->get_form(); // xss okay.
}

/**
 * Generate edit question form, this is a wrapper of ap_ask_form()
 *
 * @return void
 * @since 2.0.1
 */
function ap_edit_question_form() {
	ap_ask_form( true );
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
 * Insert and update question.
 *
 * @param  array $args     Question arguments.
 * @param  bool  $wp_error Return wp error.
 * @return bool|object|int
 */
function ap_save_question( $args, $wp_error = false ) {

	if ( isset( $args['is_private'] ) && $args['is_private'] ) {
		$args['post_status'] = 'private_post';
	}

	$args = wp_parse_args( $args, array(
		'post_author' 		 => -1,
		'post_status' 		 => 'publish',
		'post_name' 		   => '',
		'comment_status' 	 => 'open',
		'attach_uploads' 	 => false,
	) );

	// Check if question title is empty.
	if ( empty( $args['post_title'] ) ) {
		if ( true === $wp_error ) {
			return new WP_Error( 'question_title_empty', __( 'Question title cannot be blank', 'anspress-question-answer' ) );
		}
		return false;
	}

	/**
	 * Filter question description before saving.
	 *
	 * @param string $content Post content.
	 * @since unknown
	 * @since @3.0.0 Moved from process-form.php
	 */
	$args['post_content'] = apply_filters( 'ap_form_contents_filter', $args['post_content'] );

	$args['post_name'] 	  = ap_remove_stop_words_post_name( $args['post_name'] );
	$args['post_type'] 	  = 'question';

	if ( isset( $args['ID'] ) ) {
		/**
		 * Can be used to modify `$args` before updating question
		 *
		 * @param array $args Question arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_pre_update_question', $args );
	} else {
		/**
		 * Can be used to modify args before inserting question
		 *
		 * @param array $args Question arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_pre_insert_question', $args );
	}

	$post_id = wp_insert_post( $args, true );

	if ( true === $wp_error && is_wp_error( $post_id ) ) {
		return $post_id;
	}

	if ( $post_id ) {
		$qameta_args = [ 'last_updated' => current_time( 'mysql' ) ];

		if ( isset( $args['anonymous_name'] ) && ap_opt( 'allow_anonymous' ) ) {
			$qameta_args['fields'] = [ 'anonymous_name' => $args['anonymous_name'] ];
		}

		ap_insert_qameta( $post_id, $qameta_args );
		$activity_type = isset( $args['ID'] ) ? 'edit_question' : 'new_question';

		// Add question activity meta.
		ap_update_post_activity_meta( $post_id, $activity_type, get_current_user_id() );

		if ( ap_isset_post_value( 'ap-medias' ) ) {
			$ids = ap_sanitize_unslash( 'ap-medias', 'r' );
			ap_set_media_post_parent( $ids, $post_id );
		}
	}

	return $post_id;
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
		'tinymce'   => ap_opt( $type . '_text_editor' ) ? false : true,
		'quicktags' => ap_opt( $type . '_text_editor' ) ? true : false,
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
