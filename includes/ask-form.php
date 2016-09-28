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
 * @param  integer|boolean $post_id Post ID.
 * @return array
 * @since  3.0.0
 */
function ap_get_ask_form_fields( $post_id = false ) {
	global $editing_post;
	$editing = false;

	if ( $post_id && ap_user_can_edit_question( (int) $post_id ) ) {
		$editing = true;
		$editing_post = get_post( (int) $post_id, 'OBJECT', 'edit' );
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
			'desc'  => __( 'Write a meaningful title for the question.', 'anspress-question-answer' ),
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
			'desc'  => __( 'Write description for the question.', 'anspress-question-answer' ),
			'value' => ( $editing ? $editing_post->post_content : ap_isset_post_value( 'description', '' )  ),
			'settings' => ap_tinymce_editor_settings( 'question' ),
			'sanitize' => array( 'sanitize_description' ),
			'validate' => array( 'length_check' => ap_opt( 'minimum_question_length' ) ),
		),
		array(
			'name'  => 'ap_upload',
			'type'  => 'custom',
			'html' => ap_post_upload_form( $editing? $editing_post->ID : false ) ,
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

	if ( ap_show_captcha_to_user() ) {
		// Show recpatcha if key exists and enabled.
		if ( ap_opt( 'recaptcha_site_key' ) == '' ) {
			$reCaptcha_html = '<div class="ap-notice red">'.__( 'reCaptach keys missing, please add keys', 'anspress-question-answer' ).'</div>';
		} else {

			$reCaptcha_html = '<div class="g-recaptcha" id="recaptcha" data-sitekey="'.ap_opt( 'recaptcha_site_key' ).'"></div>';

			$reCaptcha_html .= '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl='.get_locale().'&onload=onloadCallback&render=explicit" async defer></script>';

			$reCaptcha_html .= '<script type="text/javascript">';
			$reCaptcha_html .= 'var onloadCallback = function() {';
			$reCaptcha_html .= 'widgetId1 = grecaptcha.render("recaptcha", {';
			$reCaptcha_html .= '"sitekey" : "'.ap_opt( 'recaptcha_site_key' ).'"';
			$reCaptcha_html .= '});';
			$reCaptcha_html .= '};</script>';
		}

		$fields[] = array(
			'name'  => 'captcha',
			'type'  => 'custom',
			'order' => 100,
			'html' 	=> $reCaptcha_html,
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

	/**
	 * FILTER: ap_ask_form_fields
	 * Filter for modifying $args
	 * @param 	array 	$fields 	Ask form fields.
	 * @param 	bool 	$editing 	Currently editing form.
	 * @since  	2.0
	 */
	$fields = apply_filters( 'ap_ask_form_fields', array( 'fields' => $fields ), $editing );

	return $fields['fields'];
}

/**
 * Generate ask form
 * @param  boolean $editing True if post is being edited.
 * @return void
 */
function ap_ask_form( $editing = false ) {
	$post_id = $editing ? (int) $_REQUEST['edit_post_id'] : false;
	// Ask form arguments.
	$args = array(
		'name'              => 'ask_form',
		'is_ajaxified'      => true,
		'multipart'         => true,
		'submit_button'     => ($editing ? __( 'Update question', 'anspress-question-answer' ) : __( 'Post question', 'anspress-question-answer' )),
		'fields'            => ap_get_ask_form_fields( $post_id ),
	);

	$form = new AnsPress_Form( $args );
	echo $form->get_form();
	echo ap_post_upload_hidden_form();
}

/**
 * Generate edit question form, this is a wrapper of ap_ask_form()
 * @return void
 * @since 2.0.1
 */
function ap_edit_question_form() {
	ap_ask_form( true );
}

/**
 * Check reCaptach verification.
 * @return boolean
 * @since  3.0.0
 */
function ap_check_recaptcha() {
	require_once( ANSPRESS_DIR. 'includes/recaptcha.php' );
	$reCaptcha = new gglcptch_ReCaptcha( ap_opt( 'recaptcha_secret_key' ) );

	$gglcptch_remote_addr = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
	$gglcptch_g_recaptcha_response = stripslashes( esc_html( $_REQUEST['g-recaptcha-response'] ) );

	$resp = $reCaptcha->verifyResponse( $gglcptch_remote_addr, $gglcptch_g_recaptcha_response );

	if ( $resp->success ) {
		do_action( 'ap_form_captcha_verified' );
		return true;
	}

	return false;
}

/**
 * Remove stop words from post name if option is enabled.
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
	if ( ! empty($post_name ) ) {
		return $post_name;
	}

	// If empty then return original without stripping stop words.
	return sanitize_title( $str );
}

/**
 * Attach uploads to post and remove orphan attachments
 * done by user.
 * @param  integer $post_id        Post ID.
 * @param  array   $attachment_ids Attachment IDs.
 * @param  integer $user_id        User ID.
 */
function ap_attach_post_uploads($post_id, $attachment_ids, $user_id) {
	foreach ( (array) $attachment_ids as $id ) {
		$attach = get_post( $id );

		if ( $attach && 'attachment' == $attach->post_type && $user_id == $attach->post_author ) {
			ap_set_attachment_post_parent( $attach->ID, $post_id );
		}
	}
}

/**
 * Insert and update question.
 * @param  array $args     Question arguments.
 * @param  bool  $wp_error Return wp error.
 * @return bool|object|int
 */
function ap_save_question($args, $wp_error = false) {
	$status = 'publish';
	if ( isset( $args['is_private'] ) && $args['is_private'] ) {
		$status = 'private_post';
	}

	$args = wp_parse_args( $args, array(
				'post_author' 		=> get_current_user_id(),
				'post_status' 		=> $status,
				'post_name' 		=> '',
				'comment_status' 	=> 'open',
				'attach_uploads' 	=> false,
			) );

	// Check if question title is empty.
	if ( empty( $args['post_title'] ) ) {
		if ( true === $wp_error ) {
			return new WP_Error('question_title_empty', __('Question title cannot be blank', 'anspress-question-answer' ) );
		}
		return false;
	}

	/**
	 * Filter question description before saving.
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
		 * @param array $args Question arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_pre_update_question', $args );
	} else {
		/**
		 * Can be used to modify args before inserting question
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
		// Check if attachment ids exists.
		if ( true === $args['attach_uploads'] ) {
			$attachment_ids = $_POST['attachment_ids'];
			ap_attach_post_uploads( $post_id, $attachment_ids, $args['post_author'] );
		}
		var_dump($args);
		// Update Custom Meta.
		if ( ! empty( $args['anonymous_name'] ) ) {
			update_post_meta( $post_id, 'anonymous_name', $args['anonymous_name'] );
		}
	}

	return $post_id;
}

/**
 * TinyMCE editor setting
 * @return array
 * @since  3.0.0
 */
function ap_tinymce_editor_settings( $type = 'question' ) {
	$setting = array(
		'textarea_rows' => 8,
		'tinymce'   => ap_opt( $type.'_text_editor' ) ? false : true,
		'quicktags' => ap_opt( $type.'_text_editor' ) ? true : false,
		'media_buttons' => false,
	);

	if ( ap_opt( $type.'_text_editor' )  ) {
		$settings['tinymce'] = array(
			'content_css' => ap_get_theme_url( 'css/editor.css' ),
			'wp_autoresize_on' => true,
		);
	}

	return apply_filters( 'ap_tinymce_editor_settings', $setting, $type );
}

/**
 * Sanitize AnsPress question and answer description field for database.
 * @param  string $content Post content.
 * @return string          Sanitised post content
 * @since  3.0.0
 */
function ap_sanitize_description_field( $content ) {
	$content = str_replace( '<!--more-->', '', $content );
	$content = preg_replace_callback( '/<pre.*?>(.*?)<\/pre>/imsu', 'ap_sanitize_description_field_pre_content', $content );
	$content = preg_replace_callback( '/<code.*?>(.*?)<\/code>/imsu', 'ap_sanitize_description_field_code_content', $content );
	$content = wp_kses( $content, ap_form_allowed_tags() );
	$content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );
	return $content;
}

function ap_sanitize_description_field_pre_content( $matches ) {
	return '<pre>'.esc_html( $matches[1] ).'</pre>';
}

function ap_sanitize_description_field_code_content( $matches ) {
	return '<code>'.esc_html( $matches[1] ).'</code>';
}
