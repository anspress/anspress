<?php
/**
 * Form and controls of ask form
 *
 * @link http://anspress.io
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

/**
 * Generate ask form
 * @param  boolean $editing True if post is being edited.
 * @return void
 */
function ap_ask_form($editing = false) {
	global $editing_post;

	$is_private = false;

	if ( $editing ) {
		$is_private = $editing_post->post_status == 'private_post' ? true : false;
	}

	// Ask form arguments.
	$args = array(
		'name'              => 'ask_form',
		'is_ajaxified'      => true,
		'multipart'         => true,
		//'attr'         		=> ' data-before="apShowSimilarQuestions"',
		'submit_button'     => ($editing ? __( 'Update question', 'ap' ) : __( 'Post question', 'ap' )),
		'fields'            => array(
			array(
				'name' => 'title',
				'label' => __( 'Title', 'ap' ),
				'type'  => 'text',
				'placeholder'  => __( 'Question in one sentence', 'ap' ),
				'desc'  => __( 'Write a meaningful title for the question.', 'ap' ),
				'value' => ( $editing ? $editing_post->post_title : sanitize_text_field( @$_POST['title'] ) ),
				'order' => 5,
				'attr' => 'data-action="suggest_similar_questions"',
				'autocomplete' => false,
			),
			array(
				'name' => 'title',
				'type'  => 'custom',
				'order' => 5,
				'html' => '<div id="similar_suggestions"></div>',
			),
			array(
				'name' => 'description',
				'label' => __( 'Description', 'ap' ),
				'type'  => 'editor',
				'desc'  => __( 'Write description for the question.', 'ap' ),
				'value' => ( $editing ? apply_filters( 'the_content', $editing_post->post_content ) : @$_POST['description']  ),
				'settings' => apply_filters( 'ap_ask_form_editor_settings', array(
					'textarea_rows'     => 8,
					'tinymce'           => ap_opt( 'question_text_editor' ) ? false : true,
					'quicktags'         => ap_opt( 'question_text_editor' ) ? true : false,
					'media_buttons'     => false,
				)),
			),
			array(
				'name'  => 'ap_upload',
				'type'  => 'custom',
				'html' => ap_post_upload_form(),
				'order' => 10,
			),
			array(
				'name' => 'parent_id',
				'type'  => 'hidden',
				'value' => ( $editing ? $editing_post->post_parent : get_query_var( 'parent' )  ),
				'order' => 20,
			),
		),
	);

	// Add name fields if anonymous is allowed.
	if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) ) {
		$args['fields'][] = array(
			'name'      => 'name',
			'label'     => __( 'Name', 'ap' ),
			'type'      => 'text',
			'placeholder'  => __( 'Enter your name to display', 'ap' ),
			'value'     => sanitize_text_field( @$_POST['name'] ),
			'order'     => 12,
		);
	}

	// Add private field checkbox if enabled.
	if ( ap_opt( 'allow_private_posts' ) ) {
		$args['fields'][] = array(
			'name' => 'is_private',
			'type'  => 'checkbox',
			'desc'  => __( 'Only visible to admin and moderator.', 'ap' ),
			'value' => $is_private,
			'order' => 12,
			'show_desc_tip' => false,
		);
	}

	if ( ap_show_captcha_to_user() ) {
		// Show recpatcha if key exists and enabled.
		if ( ap_opt( 'recaptcha_site_key' ) == '' ) {
			$reCaptcha_html = '<div class="ap-notice red">'.__( 'reCaptach keys missing, please add keys', 'ap' ).'</div>';
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


		$args['fields'][] = array(
			'name'  => 'captcha',
			'type'  => 'custom',
			'order' => 100,
			'html' 	=> $reCaptcha_html,
		);
	}

	/**
	 * FILTER: ap_ask_form_fields
	 * Filter for modifying $args
	 * @var array
	 * @since  2.0
	 */
	$args = apply_filters( 'ap_ask_form_fields', $args, $editing );

	if ( $editing ) {
		$args['fields'][] = array(
		'name'  => 'edit_post_id',
		'type'  => 'hidden',
		'value' => $editing_post->ID,
		'order' => 20,
		);
	}

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
