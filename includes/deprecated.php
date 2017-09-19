<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}


/**
 * Send ajax response if there is error in validation class.
 * @param  object $validate Validation class.
 * @since  3.0.0
 * @deprecated 4.1.0
 */
function ap_form_validation_error_response( $validate ) {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	// If error in form then return.
	if ( $validate->have_error() ) {
		ap_ajax_json( array(
			'success' => false,
			'form' 			=> $_POST['ap_form_action'],
			'snackbar' => [
				'message' => __( 'Check missing fields and then re-submit.', 'anspress-question-answer' ),
			],
			'errors'		=> $validate->get_errors(),
		) );
	}
}

/**
 * Upload form.
 *
 * @param  boolean|integer $post_id Post ID.
 * @return string
 *
 * @deprecated 4.1.0 This function was replaced by new form class.
 */
function ap_post_upload_form( $post_id = false ) {
	_deprecated_function( __FUNCTION__, '4.1.0', 'AnsPress\Form\Upload' );

	if ( ! ap_user_can_upload( ) ) {
		return;
	}

	if ( false === $post_id ) {
		$media = get_posts( [
			'post_type'   => 'attachment',
			'title'       => '_ap_temp_media',
			'post_author' => get_current_user_id(),
		]);
	} else {
		$media = get_attached_media( '', $post_id );
	}


	$label = sprintf( __( 'Insert images and attach media by %1$sselecting them%2$s', 'anspress-question-answer' ), '<a id="pickfiles" href="javascript:;">', '</a>' );
	$html = '<div id="ap-upload" class="ap-upload"><div class="ap-upload-anchor">' . $label . '</div>';

	$uploads = [];
	foreach ( (array) $media as $m ) {
		$uploads[] = [
			'id'       => $m->ID,
			'fileName' => basename( $m->guid ),
			'fileSize' => size_format( filesize( get_attached_file( $m->ID ) ), 2 ),
			'isImage'  => wp_attachment_is_image( $m->ID ),
			'nonce'    => wp_create_nonce( 'delete-attachment-' . $m->ID ),
			'url'      => $m->guid,
		];
	}

	$html .= '<script type="application/json" id="ap-uploads-data">' . wp_json_encode( $uploads ) . '</script>';
	$html .= '</div>';
	return $html;
}

/**
 * Initialize AnsPress uploader settings.
 *
 * @deprecated 4.1.0
 */
function ap_upload_js_init() {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	if ( ap_user_can_upload( ) ) {
		$mimes = [];

		foreach ( ap_allowed_mimes() as $ext => $mime ) {
			$mimes[] = [ 'title' => $mime, 'extensions' => str_replace( '|', ',', $ext ) ];
		}

		$plupload_init = array(
			'runtimes'            => 'html5,flash,silverlight,html4',
			'browse_button'       => 'plupload-browse-button',
			'container'           => 'plupload-upload-ui',
			'drop_element'        => 'ap-drop-area',
			'file_data_name'      => 'async-upload',
			'url'                 => admin_url( 'admin-ajax.php' ),
			'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters'             => array(
				'mime_types'         => $mimes,
				'max_file_size'      => (int) ap_opt( 'max_upload_size' ) . 'b',
				'prevent_duplicates' => true,
			),
			//'maxfiles'            => ap_opt( 'uploads_per_post' ),
			'multipart_params'    => [
				'_wpnonce' => wp_create_nonce( 'media-upload' ),
				'action'   => 'ap_image_submission',
			],
		);

		echo '<script type="text/javascript"> wpUploaderInit =' . wp_json_encode( $plupload_init ) . ';</script>';
		echo '<script type="text/html" id="ap-upload-template">
				<span class="apicon-check"> ' . esc_attr__( 'Uploaded', 'anspress-question-answer' ) . '</span>
				<span class="apicon-stop"> ' . esc_attr__( 'Failed', 'anspress-question-answer' ) . '</span>
				<span class="ap-upload-name"></span>
				<a href="#" class="insert-to-post">' . esc_attr__( 'Insert to post', 'anspress-question-answer' ) . '</a>
				<a href="#" class="apicon-trashcan"></a>
				<div class="ap-progress"></div>
		</script>';
	}
}

/**
 * Insert and update question.
 *
 * @param  array $args     Question arguments.
 * @param  bool  $wp_error Return wp error.
 * @return bool|object|int
 *
 * @deprecated 4.1.0 This function is replaced by a new method @see `AP_Form_Hooks::submit_question_form()`.
 */
function ap_save_question( $args, $wp_error = false ) {
	_deprecated_function( __FUNCTION__, '4.1.0', 'AP_Form_Hooks::submit_question_form()' );

	if ( isset( $args['is_private'] ) && $args['is_private'] ) {
		$args['post_status'] = 'private_post';
	}

	$args = wp_parse_args( $args, array(
		'post_author' 		 => -1,
		'post_status' 		 => 'publish',
		'post_name' 		   => '',
		'comment_status' 	 => 'open',
	) );

	// Check if question title is empty.
	if ( empty( $args['post_title'] ) ) {
		if ( true === $wp_error ) {
			return new WP_Error( 'question_title_empty', __( 'Question title cannot be blank', 'anspress-question-answer' ) );
		}
		return false;
	}

	/**
		 * This filter is documented in includes\class-form-hooks.php.
		 */
	$args['post_content'] = apply_filters( 'ap_form_contents_filter', $args['post_content'] );

	$args['post_name'] 	  = ap_remove_stop_words_post_name( $args['post_name'] );
	$args['post_type'] 	  = 'question';

	if ( isset( $args['ID'] ) ) {
		/**
		 * This filter is documented in includes\class-form-hooks.php.
		 */
		$args = apply_filters( 'ap_pre_update_question', $args );
	} else {
		/**
		 * This filter is documented in includes\class-form-hooks.php.
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
 * Generate edit question form, this is a wrapper of ap_ask_form()
 *
 * @return void
 * @since 2.0.1
 * @deprecated 4.1.0 Function was replaced by @see `ap_ask_form()`.
 */
function ap_edit_question_form() {
	_deprecated_function( __FUNCTION__, '4.1.0', 'ap_ask_form' );

	ap_ask_form( true );
}

/**
 * Get all ask form fields.
 *
 * @param  integer|boolean $post_id Post ID.
 * @return array
 * @since  3.0.0
 * @deprecated 4.1.0 This function is replaced by @see `AnsPress::get_form()`.
 */
function ap_get_ask_form_fields( $post_id = false ) {
	_deprecated_function( __FUNCTION__, '4.1.0', 'AnsPress::get_form()' );

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
	 * Filter for modifying ask form `$args`.
	 *
	 * @param array $fields 	Ask form fields.
	 * @param bool 	$editing 	Currently editing form.
	 * @since 2.0
	 * @deprecated 4.1.0
	 */
	$fields = apply_filters( 'ap_ask_form_fields', [ 'fields' => $fields ], $editing );

	return $fields['fields'];
}

/**
 * Sanitize AnsPress question and answer description field for database.
 *
 * @param  string $content Post content.
 * @return string Sanitized post content
 *
 * @since  3.0.0
 * @deprecated 4.1.0 This function was replaced by @see `AnsPress\Form\Validate::sanitize_description()`.
 */
function ap_sanitize_description_field( $content ) {
	_deprecated_function( __FUNCTION__, '4.1.0', 'AnsPress\Form\Validate::sanitize_description()' );

	$content = str_replace( '<!--more-->', '', $content );
	$content = preg_replace_callback( '/<pre.*?>(.*?)<\/pre>/imsu', 'ap_sanitize_description_field_pre_content', $content );
	$content = preg_replace_callback( '/<code.*?>(.*?)<\/code>/imsu', 'ap_sanitize_description_field_code_content', $content );
	$content = wp_kses( $content, ap_form_allowed_tags() );
	$content = sanitize_post_field( 'post_content', $content, 0, 'db' );
	return $content;
}

/**
 * Callback used in @see `ap_sanitize_description_field()`.
 *
 * @param array $matches Matched strings.
 * @return string
 * @deprecated 4.1.0
 */
function ap_sanitize_description_field_pre_content( $matches ) {
	return '<pre>' . esc_html( $matches[1] ) . '</pre>';
}

/**
 * Callback used in @see `ap_sanitize_description_field()`.
 *
 * @param array $matches Matched strings.
 * @return string
 * @deprecated 4.1.0
 */
function ap_sanitize_description_field_code_content( $matches ) {
	return '<code>' . esc_html( $matches[1] ) . '</code>';
}
