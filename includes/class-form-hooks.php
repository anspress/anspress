<?php

class AP_Form_Hooks {
	private static $form;

	public static function question_form(){
		$form = array(
			'fields' => array(
				'title' => array(
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
				'description' => array(
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

	public static function save_is_private_post( $field, $qp_qa ) {
		if ( true === $field->value() ) {
			$qp_qa->set( 'post_status', 'private_post' );
		}
	}

	public static function init() {
		self::$form = ltrim( ap_isset_post_value( 'ap_form_name', '' ), 'form_' );

		$form_method = 'process_' . self::$form;

		if ( method_exists( __CLASS__, $form_method ) ) {
			self::$form_method();
		}
	}

	public static function process_question() {
		$form = anspress()->get_form( 'question' );

		if ( ! $form->is_submitted() ) {
			wp_die( 'Trying to cheat!', 'anspress-question-answer' );
		}

		$form->sanitize_validate();
		$post_id = $form->save_post();

		var_dump( $post_id );
		PC::debug( $form );
	}
}