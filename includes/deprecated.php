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

	/** This filter is documented in includes\class-form-hooks.php */
	$args['post_content'] = apply_filters( 'ap_form_contents_filter', $args['post_content'] );

	$args['post_name'] 	  = ap_remove_stop_words_post_name( $args['post_name'] );
	$args['post_type'] 	  = 'question';

	if ( isset( $args['ID'] ) ) {
		/** This filter is documented in includes\class-form-hooks.php */
		$args = apply_filters( 'ap_pre_update_question', $args );
	} else {
		/** This filter is documented in includes\class-form-hooks.php */
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

/**
 * Get all answer form fields.
 *
 * @param  integer|boolean $question_id Post ID.
 * @param  integer|boolean $answer_id   Answer ID.
 * @return array
 *
 * @since  3.0.0
 * @deprecated 4.1.0
 */
function ap_get_answer_form_fields( $question_id = false, $answer_id = false ) {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	global $editing_post;
	$editing = false;

	if ( $answer_id && ap_user_can_edit_answer( (int) $answer_id ) ) {
		$editing = true;
		$editing_post = ap_get_post( (int) $answer_id, 'OBJECT', 'edit' );
	}

	$is_private = false;

	if ( $editing ) {
		$is_private = $editing_post->post_status == 'private_post' ? true : false;
	}

	$fields = array(
		array(
			'name'          => 'description',
			'type'          => is_question() ? 'textarea' : 'editor',
			'value'         => ( $editing ? $editing_post->post_content : wp_kses_post( ap_isset_post_value('description', '' ) ) ),
			'placeholder'  => __( 'Your answer..', 'anspress-question-answer' ),
			'settings' => ap_tinymce_editor_settings('answer'),
			'sanitize' => array( 'sanitize_description' ),
			'validate' => array( 'required' => true, 'length_check' => ap_opt( 'minimum_ans_length' ) ),
		),
		array(
			'name' => 'form_question_id',
			'type'  => 'hidden',
			'value' => ( $editing ? $editing_post->post_parent : $question_id  ),
			'order' => 20,
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

	$fields[] = array(
		'name'  => 'ap_upload',
		'type'  => 'custom',
		'html' => ap_post_upload_form( $editing? $editing_post->ID : false ),
		'order' => 11,
	);

	$fields[] = array(
		'name'  => 'action',
		'type'  => 'hidden',
		'value' => 'ap_ajax',
		'order' => 20,
	);

	$fields[] = array(
		'name'  => 'ap_ajax_action',
		'type'  => 'hidden',
		'value' => 'answer_form',
		'order' => 20,
	);

	if ( $editing ) {
		$fields[] = array(
			'name'  => 'edit_post_id',
			'type'  => 'hidden',
			'value' => $editing_post->ID,
			'order' => 20,
			'sanitize' => array( 'only_int' ),
		);
	}

	/** This filter is documented in includes/class-form-hooks.php */
	$fields = apply_filters( 'ap_answer_form_fields', array( 'fields' => $fields ), $editing );
	return $fields['fields'];
}

/**
 * Generate edit question form, this is a wrapper of ap_answer_form().
 *
 * @param integer $question_id Id of question.
 * @return void
 * @since 2.0.1
 * @deprecated 4.1.0
 */
function ap_edit_answer_form( $question_id ) {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	ap_answer_form( $question_id, true );
}

/**
 * Return or echo hovercard data attribute.
 *
 * @param  integer $user_id User id.
 * @param  boolean $echo    Echo or return? default is true.
 * @return string
 *
 * @deprecated 4.1.0
 */
function ap_hover_card_attributes( $user_id, $echo = true ) {
	if ( $user_id > 0 ) {
		$attr = ' data-userid="' . $user_id . '"';

		if ( true !== $echo ) {
			return $attr;
		}

		echo $attr; // xss okay.
	}
}

/**
 * TinyMCE editor setting
 *
 * @return array
 * @since  3.0.0
 * @deprecated 4.1.0 This is no longer required as of upload field introduction.
 */
function ap_tinymce_editor_settings( $type = 'question' ) {
	_deprecated_function( __FUNCTION__, '4.1.0', 'This is no longer required as of upload field introduction.' );

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
 * Read env file of AnsPress.
 *
 * @return string
 * @deprecated 4.1.0
 */
function ap_read_env() {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	$file = ANSPRESS_DIR . '/env';
	$cache = wp_cache_get( 'ap_env', 'ap' );
	if ( false !== $cache ) {
		return $cache;
	}

	if ( file_exists( $file ) ) {
		// Get the contents of env file.
		$content = file_get_contents( $file ); // @codingStandardsIgnoreLine.
		wp_cache_set( 'ap_env', $content, 'ap' );
		return $content;
	}

}

/**
 * Check if anspress environment is development.
 *
 * @return boolean
 * @deprecated 4.1.0
 */
function ap_env_dev() {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	if ( 'development' === ap_read_env() ) {
		return true;
	}

	return false;
}

/**
 * Insert and update answer.
 *
 * @param  array $question_id Question ID.
 * @param  array $args     Answer arguments.
 * @param  bool  $wp_error Return wp error.
 * @return bool|object|int
 *
 * @deprecated 4.1.0
 */
function ap_save_answer( $question_id, $args, $wp_error = false) {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	$question = ap_get_post( $question_id );

	if ( isset( $args['is_private'] ) && $args['is_private'] ) {
		$args['post_status'] = 'private_post';
	}

	$args = wp_parse_args( $args, array(
		'post_title' 		  => $question->post_title,
		'post_author' 	  => get_current_user_id(),
		'post_status' 	  => 'publish',
		'post_name' 		  => '',
		'comment_status'  => 'open',
	) );

	$args['post_content'] = apply_filters( 'ap_form_contents_filter', $args['post_content'] );
	$args['post_type'] 	  = 'answer';
	$args['post_parent']  = $question_id;

	if ( isset( $args['ID'] ) ) {
		/**
		 * Can be used to modify `$args` before updating answer
		 * @param array $args Answer arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_pre_update_answer', $args );
	} else {
		/**
		 * Can be used to modify args before inserting answer.
		 * @param array $args Answer arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_pre_insert_answer', $args );
	}

	$post_id = wp_insert_post( $args, true );

	if ( true === $wp_error && is_wp_error( $post_id ) ) {
		return $post_id;
	}

	if ( $post_id ) {
		$qameta_args = [ 'last_updated' => current_time( 'mysql' ) ];

		if ( isset( $args['anonymous_name'] ) ) {
			$qameta_args['fields'] = [ 'anonymous_name' => $args['anonymous_name'] ];
		}

		ap_insert_qameta( $post_id, $qameta_args );
		$activity_type = isset( $args['ID'] ) ? 'edit_answer' : 'new_answer';

		// Add answer activity meta.
		ap_update_post_activity_meta( $post_id, $activity_type, get_current_user_id() );
		ap_update_post_activity_meta( $question->ID, $activity_type, get_current_user_id() );

		if ( ap_isset_post_value( 'ap-medias' ) ) {
			$ids = ap_sanitize_unslash( 'ap-medias', 'r' );
			ap_set_media_post_parent( $ids, $post_id );
		}
	}
	return $post_id;
}

/**
 * Display fields group options. Uses AnsPress_Form to renders fields.
 *
 * @since 2.0.0
 * @deprecated 4.1.0 Replaced by new form class.
 */
function ap_option_group_fields() {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	$groups = ap_get_option_groups();
	$active = ap_sanitize_unslash( 'option_page', 'request', 'general' );

	if ( empty( $groups ) && is_array( $groups ) ) {
		return;
	}

	$group = $groups[ $active ];

	foreach ( (array) $group['sections'] as $section_slug => $section ) {
		$fields = $section['fields'];

		if ( is_array( $fields ) ) {
			$fields[] = array(
				'name' => 'action',
				'type' => 'hidden',
				'value' => 'anspress_options',
			);

			$fields[] = array(
				'name' => 'fields_group',
				'type' => 'hidden',
				'value' => $active,
			);

			$fields[] = array(
				'name' => 'ap_active_section',
				'type' => 'hidden',
				'value' => $section_slug,
			);

			$args = array(
				'name'              => 'options_form',
				'is_ajaxified'      => false,
				'submit_button'     => __( 'Save options', 'anspress-question-answer' ),
				'nonce_name'        => 'nonce_option_form',
				'fields'            => $fields,
				'action'            => admin_url( 'admin-post.php' ),

			);

			$form = new AnsPress_Form( $args );
			echo '<div class="postbox ' . esc_attr( $section_slug ) . '">';
			echo '<h3 data-index="' . esc_attr( $section_slug ) . '"><span>' . esc_html( $section['title'] ) . '</span></h3>';
			echo '<div class="inside">';
			echo $form->get_form(); // xss okay.
			echo '</div>';
			echo '</div>';

		} elseif ( function_exists( $fields ) ) {
			echo '<div class="postbox ' . esc_attr( $section_slug ) . '">';
			echo '<h3 data-index="' . esc_attr( $section_slug ) . '"><span>' . esc_html( $section['title'] ) . '</span></h3>';
			echo '<div class="inside">';
			call_user_func( $fields );
			echo '</div>';
			echo '</div>';
		}
	}
}

/**
 * Output option tab nav.
 *
 * @return void
 * @since 2.0.0
 * @deprecated 4.1.0
 */
function ap_options_nav() {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	$groups = ap_get_option_groups();
	$active = ap_sanitize_unslash( 'option_page', 'p' ) ? ap_sanitize_unslash( 'option_page', 'p' ) : 'general' ;
	$menus = array();
	$icons = array(
		'general'    => 'apicon-home',
		'layout'     => 'apicon-eye',
		'pages'      => 'apicon-pin',
		'question'   => 'apicon-question',
		'users'      => 'apicon-users',
		'permission' => 'apicon-lock',
		'moderate'   => 'apicon-flag',
		'roles'      => 'apicon-user',
		'categories' => 'apicon-category',
		'tags'       => 'apicon-tag',
		'labels'     => 'apicon-tag',
	);

	foreach ( (array) $groups as $k => $args ) {
		$link 		= admin_url( "admin.php?page=anspress_options&option_page={$k}" );
		$icon 		= isset( $icons[ $k ] ) ? esc_attr( $icons[ $k ] ) : 'apicon-gear';
		$menus[ $k ] 	= array( 'title' => $args['title'], 'link' => $link, 'icon' => $icon );
	}

	/**
	 * Filter is applied before showing option tab navigation.
	 *
	 * @var array
	 * @since  2.0.0
	 */
	$menus = apply_filters( 'ap_option_tab_nav', $menus );

	$o = '<h2 class="nav-tab-wrapper">';

	foreach ( (array) $menus as $k => $m ) {
		$class = ! empty( $m['class'] ) ? ' ' . $m['class'] : '';
		$o .= '<a href="' . esc_url( $m['link'] ) . '" class="nav-tab ap-user-menu-' . esc_attr( $k . $class ) . ( $active === $k ? '  nav-tab-active' : '' ) . '"><i class="' . $m['icon'] . '"></i>' . esc_attr( $m['title'] ) . '</a>';
	}

	$o .= '</h2>';

	echo $o; // xss okay.
}

/**
 * Register anspress option tab and fields.
 *
 * @param  string  $group_slug     slug for links.
 * @param  string  $group_title    Page title.
 * @return void
 * @since 2.0.0
 * @deprecated 4.1.0
 */
function ap_register_option_section( $group, $slug, $title, $fields ) {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	global $ap_option_tabs;
	$ap_option_tabs[ $group ]['sections'][ $slug ] = array( 'title' => $title, 'fields' => $fields );
}

/**
 * Register anspress option tab and fields.
 *
 * @param  string  $group_slug     slug for links.
 * @param  string  $group_title    Page title.
 * @return void
 * @since 2.0.0
 * @deprecated 4.1.0
 */
function ap_register_option_group( $group_slug, $group_title ) {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	global $ap_option_tabs;
	$ap_option_tabs[ $group_slug ] = array( 'title' => $group_title, 'sections' => [] );
}

/**
 * Return all option groups.
 *
 * @return array
 * @since 3.0.0
 * @deprecated 4.1.0
 */
function ap_get_option_groups() {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	global $ap_option_tabs;
	do_action( 'ap_option_groups' );

	return apply_filters( 'ap_get_option_groups', $ap_option_tabs );
}


/**
 * Callback for @uses ap_sort_array_by_order.
 *
 * @param  array $a Array.
 * @param  array $b Array.
 * @return integer
 * @deprecated 4.1.0 Using `WP_List_Util::sort`, hence this callback is not required anymore.
 */
function ap_sort_order_callback( $a, $b ) {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	if ( $a['order'] == $b['order'] ) {
		return 0;
	}

	return ( $a['order'] < $b['order'] ) ? -1 : 1;
}

/**
 * Output tags order tabs.
 *
 * @deprecated 4.1.0
 */
function ap_tags_tab() {
	_deprecated_function( __FUNCTION__, '4.1.0', 'ap_list_filters' );

	$active = isset( $_GET['ap_sort'] ) ? $_GET['ap_sort'] : 'popular';

	$link = ap_get_link_to( 'tags' ).'?ap_sort=';

	?>
    <ul class="ap-questions-tab ap-ul-inline clearfix" role="tablist">
        <li class="<?php echo $active == 'popular' ? ' active' : ''; ?>"><a href="<?php echo $link.'popular'; ?>"><?php _e( 'Popular', 'anspress-question-answer' ); ?></a></li>
        <li class="<?php echo $active == 'new' ? ' active' : ''; ?>"><a href="<?php echo $link.'new'; ?>"><?php _e( 'New', 'anspress-question-answer' ); ?></a></li>
        <li class="<?php echo $active == 'name' ? ' active' : ''; ?>"><a href="<?php echo $link.'name'; ?>"><?php _e( 'Name', 'anspress-question-answer' ); ?></a></li>
        <?php
			/**
			 * ACTION: ap_tags_tab
			 * Used to hook into tags page tab
			 */
			do_action( 'ap_tags_tab', $active );
		?>
    </ul>
  <?php
}

/**
 * Return current AnsPress page
 *
 * @return string|false
 * @deprecated 4.1.0
 */
function ap_current_page_is() {
	_deprecated_function( __FUNCTION__, '4.1.0' );

	if ( is_anspress() ) {
		if ( is_question() ) {
			$template = 'question';
		} elseif ( is_ask() ) {
			$template = 'ask';
		} elseif ( is_question_categories() ) {
			$template = 'categories';
		} elseif ( is_question_category() ) {
			$template = 'category';
		} elseif ( is_ap_search() ) {
			$template = 'search';
		} elseif ( get_query_var( 'ap_page' ) == '' ) {
			$template = 'base';
		} else {
			$template = 'not-found';
		}

		return apply_filters( 'ap_current_page_is', $template );
	}

	return false;
}

/**
 * Get current user page template file
 *
 * @return string template file name.
 * @deprecated 4.1.0
 */
function ap_get_current_page_template() {
	_deprecated_function( __FUNCTION__, '4.1.0' );
	if ( is_anspress() ) {
		$template = ap_current_page_is();

		return apply_filters( 'ap_current_page_template', $template . '.php' );
	}

	return 'content-none.php';
}

/**
 * Return ajax comment data.
 *
 * @param object $c Comment object.
 * @return array
 * @since 4.0.0
 * @deprecated 4.1.5
 */
function ap_comment_ajax_data( $c, $actions = true ) {
	return array(
		'ID'        => $c->comment_ID,
		'post_id'   => $c->comment_post_ID,
		'link'      => get_comment_link( $c ),
		'avatar'    => get_avatar( $c->user_id, 30 ),
		'user_link' => ap_user_link( $c->user_id ),
		'user_name' => ap_user_display_name( $c->user_id ),
		'iso_date'  => date( 'c', strtotime( $c->comment_date ) ),
		'time'      => ap_human_time( $c->comment_date_gmt, false ),
		'content'   => $c->comment_content,
		'approved'  => $c->comment_approved,
		'class'     => implode( ' ', get_comment_class( 'ap-comment', $c->comment_ID, null, false ) ),
		'actions' 	 => $actions ? ap_comment_actions( $c ) : [],
	);
}

/**
 * Get all theme names from AnsPress themes directory.
 *
 * @return array
 * @deprecated 4.1.6
 */
function ap_theme_list() {
	$themes = array();
	$dirs = array_filter( glob( ANSPRESS_THEME_DIR . '/*' ), 'is_dir' );
	foreach ( $dirs as $dir ) {
		$themes[ basename( $dir ) ] = basename( $dir );
	}

	return $themes;
}