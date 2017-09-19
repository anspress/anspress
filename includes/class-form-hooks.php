<?php
/**
 * Holds all hooks related to AnsPress forms.
 *
 * @link         https://anspress.io
 * @since        4.1.0
 * @license      GPL-3.0+
 * @package      AnsPress
 * @subpackage   Form Hooks
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The form hooks.
 *
 * @since 4.1.0
 */
class AP_Form_Hooks {
	private static $form;

	public static function question_form() {
		$editing_id = ap_sanitize_unslash( 'id', 'r' );

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

		// Add private field checkbox if enabled.
		if ( ap_opt( 'allow_private_posts' ) ) {
			$form['fields']['is_private'] = array(
				'type'  => 'checkbox',
				'label'  => __( 'Is private?', 'anspress-question-answer' ),
				'desc'  => __( 'Only visible to admin and moderator.', 'anspress-question-answer' ),
			);
		}

		// Add name fields if anonymous is allowed.
		if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) ) {
			$form['fields']['anonymous_name'] = array(
				'label' => __( 'Your Name', 'anspress-question-answer' ),
				'attr'  => array(
					'placeholder' => __( 'Enter your name to display', 'anspress-question-answer' ),
				),
				'order' => 20,
			);
		}

		$form['fields']['post_id'] = array(
			'type'     => 'input',
			'subtype'  => 'hidden',
			'value'    => $editing_id,
			'sanitize' => 'absint',
		);

		// Add value when editing post.
		if ( ! empty( $editing_id ) ) {
			$question = ap_get_post( $editing_id );
			$form['fields']['post_title']['value']   = $question->post_title;
			$form['fields']['post_content']['value'] = $question->post_content;
			$form['fields']['is_private']['value']   = 'private_post' === $question->post_status ? true : false;

			if ( isset( $form['fields']['anonymous_name'] ) ) {
				$form['fields']['anonymous_name'] = ap_get_post_field( 'anonymous_name', $question );
			}
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

	/**
	 * Process question form submission.
	 *
	 * @return void
	 * @since 4.1.0
	 */
	public static function submit_question_form() {
		$editing = false;
		$form = anspress()->get_form( 'question' );

		/**
		 * Action triggered before processing question form.
		 *
		 * @since 4.1.0
		 */
		do_action( 'ap_submit_question_form' );

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
			'post_content' 		 => $values['post_content']['value'],
		);

		if ( ! empty( $values['post_id']['value'] ) ) {
			$question_args['ID'] = $values['post_id']['value'];
			$editing = true;
			$_post = ap_get_post( $question_args['ID'] );

			// Check if valid post type and user can edit.
			if ( 'question' !== $_post->post_type || ! ap_user_can_edit_question( $_post ) ) {
				ap_ajax_json( 'something_went_wrong' );
			}
		}

		// Add default arguments if not editing.
		if ( ! $editing ) {
			$question_args = wp_parse_args( $question_args, array(
				'post_author' 		 => get_current_user_id(),
				'post_status' 		 => ap_new_edit_post_status( false, 'question', false ),
				'post_name' 		   => '',
				'comment_status' 	 => 'open',
			) );
		}

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

		// Check if duplicate.
		if ( ! $editing && ap_opt( 'duplicate_check' ) && false !== ap_find_duplicate_post( $question_args['post_content'], 'question' ) ) {
			$form->add_error( 'duplicate-question', __( 'You are trying to post a duplicate question. Please search existing questions before posting a new one.', 'anspress-question-answer' ) );

			ap_ajax_json([
				'success'       => false,
				'snackbar'      => [ 'message' => __( 'Unable to post question.', 'anspress-question-answer' ) ],
				'form_errors'   => $form->errors,
				'fields_errors' => $form->get_fields_errors(),
			] );
		}

		/**
		 * Filter question description before saving.
		 *
		 * @param string $content Post content.
		 * @since unknown
		 * @since @3.0.0 Moved from process-form.php
		 */
		$question_args['post_content'] = apply_filters( 'ap_form_contents_filter', $question_args['post_content'] );

		$question_args['post_name'] = ap_remove_stop_words_post_name( $question_args['post_title'] );

		if ( $editing ) {
			/**
			 * Can be used to modify `$args` before updating question
			 *
			 * @param array $question_args Question arguments.
			 * @since 2.0.1
			 * @since 4.1.0 Moved from includes/ask-form.php.
			 */
			$question_args = apply_filters( 'ap_pre_update_question', $question_args );
		} else {
			/**
			 * Can be used to modify args before inserting question
			 *
			 * @param array $question_args Question arguments.
			 * @since 2.0.1
			 * @since 4.1.0 Moved from includes/ask-form.php.
			 */
			$question_args = apply_filters( 'ap_pre_insert_question', $question_args );
		}

		if ( ! $editing ) {
			$question_args['post_type'] = 'question';
			$post_id = wp_insert_post( $question_args, true );
		} else {
			$post_id = wp_update_post( $question_args, true );
		}

		// If error return and send error message.
		if ( is_wp_error( $post_id ) ) {
			ap_ajax_json([
				'success'       => false,
				'snackbar'      => array(
					'message' => sprintf(
						// Translators: placeholder contain error message.
						__( 'Unable to post question. Error: %s', 'anspress-question-answer' ),
						$post_id->get_error_message()
					),
				),
			] );
		}

		$form->after_save( false, array(
			'post_id' => $post_id,
		) );

		// Clear temporary images.
		if ( $post_id ) {
			ap_clear_unattached_media();
		}

		if ( isset( $question_args['ID'] ) ) {
			$message = __( 'Question updated successfully, you\'ll be redirected in a moment.', 'anspress-question-answer' );
		} else {
			$message = __( 'Your question is posted successfully, you\'ll be redirected in a moment.', 'anspress-question-answer' );
		}

		ap_ajax_json( array(
			'success'  => true,
			'snackbar' => [
				'message' => $message,
			],
			'redirect' => get_permalink( $post_id ),
			'post_id'  => $post_id,
		) );
	}
}
