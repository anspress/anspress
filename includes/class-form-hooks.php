<?php

class AP_Form_Hooks {
	private static $form;

	public static function question_form(){
		$form = array(
			'fields' => array(
				'post_title' => array(
					'type'  => 'input',
					'label' => __( 'Title', 'anspress-question-answer' ),
					'desc'  => __( 'Question in one sentence', 'anspress-question-answer' ),
					'attr'  => array(
						'autocomplete'   => 'false',
						'placeholder'    => __( 'Question title', 'anspress-question-answer' ),
						'data-action'    => 'suggest_similar_questions',
						'data-loadclass' => 'q-title',
					),
					'min_length' => ap_opt( 'minimum_qtitle_length' ),
					'max_length' => 100,
					'validate'   => 'required,min_string_length,max_string_length',
					'order'      => 2,
				),
				'post_content' => array(
					'type'       => 'editor',
					'label'      => __( 'Description', 'anspress-question-answer' ),
					'min_length' => ap_opt( 'minimum_question_length' ),
					'validate'   => 'required,min_string_length',
				),
			),
		);

		// Add name fields if anonymous is allowed.
		if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) ) {
			$form['fields']['anonymous_name'] = array(
				'label' => __( 'Your Name', 'anspress-question-answer' ),
				'attr'  => array(
					'placeholder' => __( 'Enter your name to display', 'anspress-question-answer' ),
				),
			);
		}

		// Add private field checkbox if enabled.
		if ( ap_opt( 'allow_private_posts' ) ) {
			$form['fields']['is_private'] = array(
				'type'  => 'checkbox',
				'desc'  => __( 'Only visible to admin and moderator.', 'anspress-question-answer' ),
			);
		}

		/**
		 * Filter for modifying question form `$args`.
		 *
		 * @param 	array $fields 	Ask form fields.
		 * @param 	bool 	$editing 	Currently editing form.
		 * @since  	4.1.0
		 */
		$form = apply_filters( 'ap_question_form_fields', $form );

		return $form;
	}

	public static function init() {
		self::$form = ltrim( ap_isset_post_value( 'ap_form_name', '' ), 'form_' );

		$form_method = 'process_' . self::$form;

		if ( method_exists( __CLASS__, $form_method ) ) {
			self::$form_method();
		}
	}

	public static function submit_question_form() {
		$form = anspress()->get_form( 'question' );

		$values = $form->get_values();

		// Check nonce and is valid form.
		if ( ! $form->is_submitted() ) {
			ap_ajax_json([
				'success' => false,
				'snackbar' => [ 'message' => __( 'Trying to cheat!', 'anspress-question-answer' ) ],
			] );
		}

		$question_args = array(
			'post_title'		   => $values['post_title']['value'],
			'post_author'		   => get_current_user_id(),
			'post_content' 		 => $values['post_content']['value'],
			'post_status' 		 => ap_new_edit_post_status( false, 'question', false ),
		);

		if ( $form->have_errors() ) {
			ap_ajax_json([
				'success'       => false,
				'snackbar'      => [ 'message' => __( 'Unable to post question.', 'anspress-question-answer' ) ],
				'form_errors'   => $form->errors,
				'fields_errors' => $form->get_fields_errors(),
			] );
		}

		// Set post parent.
		// @TODO: Check nonce for post parent.
		if ( isset( $values['post_parent'] ) && $values['post_parent']['value'] ) {
			$question_args['post_parent'] = $values['post_parent']['value'];
		}

		// If private override status.
		if ( true === $values['is_private']['value'] ) {
			$question_args['post_status'] = 'private_post';
		}

		// Check if anonymous post and have name.
		if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) && ! empty( $values['anonymous_name']['value'] ) ) {
			$question_args['anonymous_name'] = $values['anonymous_name']['value'];
		}

		// Check if duplicate.
		if ( ap_opt( 'duplicate_check' ) && false !== ap_find_duplicate_post( $question_args['post_content'], 'question' ) ) {
			$form->add_error( 'duplicate-question', __( 'You are trying to post a duplicate question. Please search existing questions before posting a new one.', 'anspress-question-answer' ) );

			ap_ajax_json([
				'success'       => false,
				'snackbar'      => [ 'message' => __( 'Unable to post question.', 'anspress-question-answer' ) ],
				'form_errors'   => $form->errors,
				'fields_errors' => $form->get_fields_errors(),
			] );
		}

		$post_id = ap_save_question( $question_args, true );
		$form->after_save( false, array(
			'post_id' => $post_id,
		) );

		if ( $post_id ) {
			ap_clear_unattached_media();
		}

		ap_ajax_json( array(
			'success'  => true,
			'snackbar' => [
				'message' => __( 'Your question is posted successfully, you\'ll be redirected in a moment.', 'anspress-question-answer' ),
			],
			'redirect' => get_permalink( $post_id ),
			'post_id'  => $post_id,
		) );
	}
}
