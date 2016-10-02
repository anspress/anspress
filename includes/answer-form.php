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
 * Get all answer form fields.
 * @param  integer|boolean $question_id Post ID.
 * @return array
 * @since  3.0.0
 */
function ap_get_answer_form_fields( $question_id = false, $answer_id = false ) {
	global $editing_post;
	$editing = false;

	if ( $answer_id && ap_user_can_edit_answer( (int) $answer_id ) ) {
		$editing = true;
		$editing_post = get_post( (int) $answer_id, 'OBJECT', 'edit' );
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

	$fields[] = array(
		'name'  => 'ap_upload',
		'type'  => 'custom',
		'html' => ap_post_upload_form( $editing? $editing_post->ID : false ),
		'order' => 11,
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

	/**
	 * Modify answer form fields.
	 * @param 	array 	$fields 	Answer form fields.
	 * @param 	bool 	$editing 	Currently editing form.
	 * @since  	2.0
	 */
	$fields = apply_filters( 'ap_answer_form_fields', array( 'fields' => $fields ), $editing );
	return $fields['fields'];
}

/**
 * Generate answer form
 * @param  integer $question_id  Question iD.
 * @param  boolean $editing      true if post is being edited.
 * @return void
 */
function ap_answer_form($question_id, $editing = false) {
	if ( ! ap_user_can_answer( $question_id ) && ! $editing ) {
		return;
	}

	global $editing_post;
	$answer_id = $editing ? (int) $_REQUEST['edit_post_id'] : false;

	$args = array(
		'name'              => 'answer_form',
		'is_ajaxified'      => true,
		'submit_button'     => ($editing ? __( 'Update answer', 'anspress-question-answer' ) : __( 'Post answer', 'anspress-question-answer' )),
		'nonce_name'        => 'nonce_answer_'.$question_id,
		'fields'            => ap_get_answer_form_fields( $question_id, $answer_id ),
	);

	anspress()->form = new AnsPress_Form( $args );

	echo anspress()->form->get_form();

	// Post image upload form.
	echo ap_post_upload_hidden_form();
}

/**
 * Generate edit question form, this is a wrapper of ap_answer_form()
 * @param integer $question_id Id of question.
 * @return void
 * @since 2.0.1
 */
function ap_edit_answer_form($question_id) {
	ap_answer_form( $question_id, true );
}

/**
 * Insert and update answer.
 * @param  array $args     Answer arguments.
 * @param  bool  $wp_error Return wp error.
 * @return bool|object|int
 */
function ap_save_answer($question_id, $args, $wp_error = false) {
	$question = get_post( $question_id );
	$status = 'publish';
	if ( isset( $args['is_private'] ) && $args['is_private'] ) {
		$status = 'private_post';
	}

	$args = wp_parse_args( $args, array(
				'post_title' 		=> $question->post_title,
				'post_author' 		=> get_current_user_id(),
				'post_status' 		=> $status,
				'post_name' 		=> '',
				'comment_status' 	=> 'open',
				'attach_uploads' 	=> false,
			) );

	/**
	 * Filter question description before saving.
	 * @param string $content Post content.
	 * @since unknown
	 * @since 3.0.0 Moved from process-form.php
	 */
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
		// Check if attachment ids exists.
		if ( true === $args['attach_uploads'] ) {
			$attachment_ids = $_POST['attachment_ids'];
			ap_attach_post_uploads( $post_id, $attachment_ids, $args['post_author'] );
		}

		// Update Custom Meta.
		if ( ! empty( $args['anonymous_name'] ) ) {
			update_post_meta( $post_id, 'anonymous_name', $args['anonymous_name'] );
		}
	}
	return $post_id;
}

function ap_answer_post_ajax_response( $question_id, $answer_id ){
	$question = get_post( $question_id );
	// Get existing answer count.
	$current_ans = ap_count_published_answers( $question_id );

	if ( $current_ans == 1 ) {
		global $post;
		$post = $question;
		setup_postdata( $post );
	} else {
		global $post;
		$post = get_post( $answer_id );
		setup_postdata( $post );
	}

	ob_start();
	global $answers;
	global $withcomments;
	$withcomments = true;

	if ( $current_ans == 1 ) {
		$answers = ap_get_answers( array( 'question_id' => $question_id ) );
		ap_get_template_part( 'answers' );
	} else {
		$answers = ap_get_answers( array( 'p' => $answer_id ) );
		while ( ap_have_answers() ) : ap_the_answer();
			ap_get_template_part( 'answer' );
		endwhile;
	}

	$html = ob_get_clean();

	$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );

	$result = array(
		'postid' 		=> $answer_id,
		'action' 		=> 'new_answer',
		'div_id' 		=> '#answer_'.get_the_ID(),
		'can_answer' 	=> ap_user_can_answer( $post->ID ),
		'html' 			=> $html,
		'message' 		=> 'answer_submitted',
		'do' 			=> 'clearForm',
		'view'			=> array( 'answer_count' => $current_ans, 'answer_count_label' => $count_label ),
	);

	ap_ajax_json( $result );
}
