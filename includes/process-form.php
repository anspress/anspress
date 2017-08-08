<?php
/**
 * AnsPress process form.
 *
 * @link     https://anspress.io
 * @since    2.0.1
 * @license  GPL 3+
 * @package  AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Process_Form {
	private $fields;

	private $result;

	private $request;

	private $redirect ;

	private $is_ajax = false;
	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'wp_ajax_ap_ajax', array( $this, 'ap_ajax' ) );
		add_action( 'wp_ajax_nopriv_ap_ajax', array( $this, 'ap_ajax' ) );
	}

	/**
	 * for non ajax form
	 * @return void
	 */
	public function non_ajax_form() {
		// return if ap_form_action is not set, probably its not our form
		if ( ! isset( $_REQUEST['ap_form_action'] ) || isset( $_REQUEST['ap_ajax_action'] ) ) {
			return;
		}

		$this->request = $_REQUEST;
		$this->process_form();

		if ( ! empty( $this->redirect ) ) {
			wp_redirect( $this->redirect );
			wp_die();
		}
	}

	/**
	 * Handle all anspress ajax requests.
	 *
	 * @return void
	 * @since 2.0.1
	 */
	public function ap_ajax() {
		if ( ! isset( $_REQUEST[ 'ap_ajax_action'] ) ) {
			wp_die();
		}

		$this->request = $_REQUEST;

		if ( isset( $_POST['ap_form_action'] ) ) {
	    	$this->is_ajax = true;
	    	$this->process_form();
			ap_ajax_json( $this->result );
		} else {
			$action = ap_sanitize_unslash( 'ap_ajax_action', 'r' );

			/**
				* ACTION: ap_ajax_[$action]
				* Action for processing Ajax requests
				* @since 2.0.1
				*/
			do_action( 'ap_ajax_' . $action );
		}

		// If reached to this point then there is something wrong.
		ap_ajax_json( 'something_wrong' );
	}


	/**
	 * Process form based on action value
	 * @return void
	 * @since 2.0.1
	 */
	public function process_form() {
		$action = sanitize_text_field( $_POST['ap_form_action'] );
		switch ( $action ) {
			case 'ask_form':
				$this->process_ask_form();
				break;

			case 'answer_form':
				$this->process_answer_form();
				break;

			default:
				/**
				 * ACTION: ap_process_form_[action]
				 * process form
				 * @since 2.0.1
				 */
				do_action( 'ap_process_form_' . $action );
				break;
		}
	}

	/**
	 * Process ask form.
	 *
	 * @since 2.0.1
	 */
	public function process_ask_form() {
		// Do security check, if fails then return.
		if ( check_ajax_referer( 'ask_form', false, false ) || ! ap_user_can_ask() ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to post new question', 'anspress-question-answer' ) ],
			) );
		}

		global $ap_errors, $validate;
		$editing_post_id = ap_isset_post_value( 'edit_post_id', false );

		/**
		 * Filter to modify ask question fields validation.
		 *
		 * @param array $args Ask form validation arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_ask_fields_validation', ap_get_ask_form_fields( $editing_post_id ) );

		$validate = new AnsPress_Validation( $args );

		/**
		 * Action triggered after ask form fields validation.
		 *
		 * @since 4.0.0
		 */
		do_action( 'ap_process_ask_form', $validate );

		// If error in form then bail.
		ap_form_validation_error_response( $validate );
		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if ( ! empty( $fields['edit_post_id'] ) ) {
			$this->edit_question();
			return;
		}

		if ( ! ap_user_can_ask() ) {
			ap_ajax_json( array(
				'success' 	=> false,
				'form' 	 	  => $_POST['ap_form_action'],
				'snackbar' 	=> [ 'message' => __( 'You are not allowed to post question.', 'anspress-question-answer' ) ],
			) );
		}

		// Check if duplicate.
		if ( false !== ap_find_duplicate_post( $fields['description'], 'question' ) ) {
			ap_ajax_json( array(
				'success' 	=> false,
				'form' 	 	  => $_POST['ap_form_action'],
				'snackbar' 	=> [ 'message' => __( 'This seems to be a duplicate question. A question with same content already exists.', 'anspress-question-answer' ) ],
			) );
		}

		$filter = apply_filters( 'ap_before_inserting_question', false, $fields['description'] );
		if ( true === $filter || is_array( $filter ) ) {
			if ( is_array( $filter ) ) {
				$this->result = $filter;
			}
			return;
		}

		$question_array = array(
			'post_title'		   => $fields['title'],
			'post_content' 		 => $fields['description'],
			'attach_uploads' 	 => true,
			'is_private' 	     => isset( $this->fields['is_private'] ) && $this->fields['is_private'],
			'anonymous_name' 	 => ! empty( $fields['anonymous_name'] ) ? $fields['anonymous_name'] : '',
		);

		if ( isset( $fields['parent_id'] ) ) {
			$question_array['post_parent'] = (int) $fields['parent_id'];
		}

		$post_id = ap_save_question( $question_array );

		if ( is_wp_error( $post_id ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'action'   => 'new_question',
				'snackbar' => [
					'message' => $post_id->get_error_messages(),
				],
			) );
		}

		if ( $post_id ) {

			ap_ajax_json( array(
				'success'  => true,
				'action'   => 'new_question',
				'redirect' => get_permalink( $post_id ),
				'snackbar' => [
					'message' => __( 'Question posted successfully. Redirecting to question', 'anspress-question-answer' ),
				],
			) );
		}
	}

	/**
	 * Process edit question form.
	 *
	 * @return void
	 * @since 2.0.1
	 */
	public function edit_question() {
		global $ap_errors, $validate;

		// Return if user do not have permission to edit this question.
		if ( ! ap_user_can_edit_question( $this->fields['edit_post_id'] ) ) {
			ap_ajax_json( array(
				'success' 	=> false,
				'form' 	 	  => $_POST['ap_form_action'],
				'snackbar' 	=> [ 'message' => __( 'You are not allowed to edit this question.', 'anspress-question-answer' ) ],
			) );
		}

		$filter = apply_filters( 'ap_before_updating_question', false, $this->fields['description'] );
		if ( true === $filter || is_array( $filter ) ) {
			if ( is_array( $filter ) ) {
				$this->result = $filter;
			}
			return;
		}

		$post = ap_get_post( $this->fields['edit_post_id'] );
		$user_id = get_current_user_id();

		$question_array = array(
			'ID'				       => $post->ID,
			'post_title'		   => $this->fields['title'],
			'post_content' 		 => $this->fields['description'],
			'attach_uploads' 	 => true,
			'post_author' 		 => $post->post_author,
			'is_private' 	     => isset( $this->fields['is_private'] ) && $this->fields['is_private'],
			'anonymous_name' 	 => ! empty( $fields['anonymous_name'] ) ? $fields['anonymous_name'] : '',
		);

		$post_id = ap_save_question( $question_array );

		if ( is_wp_error( $post_id ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'action'   => 'edited_question',
				'snackbar' => [
					'message' => $post_id->get_error_messages(),
				],
			) );
		}

		if ( $post_id ) {

			$this->redirect = get_permalink( $post_id );

			// Trigger update hook.
			ap_trigger_qa_update_hook( $post_id, 'edited' );

			ap_ajax_json( array(
				'success' => true,
				'action' 		=> 'edited_question',
				'redirect' => get_permalink( $post_id ),
				'snackbar' => [
					'message' => __( 'Question updated successfully', 'anspress-question-answer' ),
				],
			) );
		}
	}

	/**
	 * Process answer form
	 */
	public function process_answer_form() {
		$question_id = (int) ap_sanitize_unslash( 'form_question_id', 'r' );

		// Do security check, if fails then return.
		if ( ! ap_user_can_answer( $question_id ) || ! ap_verify_nonce( 'nonce_answer_' . $question_id ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Sorry, you cannot asnwer on this question', 'anspress-question-answer' ) ],
			) );
		}

		global $ap_errors, $validate;
		$question = ap_get_post( $question_id );

		$editing_post_id = ap_isset_post_value( 'edit_post_id', false );

		/**
		 * Filter used to modify answer form fields validation.
		 *
		 * @param array $fields Answer form fields.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_answer_fields_validation', ap_get_answer_form_fields( $question->ID, $editing_post_id ) );

		$validate = new AnsPress_Validation( $args );

		/**
		 * Action triggered after answer form fields validation.
		 *
		 * @param array $validate field validations.
		 * @since 4.0.0
		 */
		do_action( 'ap_process_answer_form', $validate );

		// Bail if there is error in validating form.
		ap_form_validation_error_response( $validate );

		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if ( ! empty( $fields['edit_post_id'] ) ) {
			$this->edit_answer( $question );
			return;
		}

		// Check if duplicate.
		if ( false !== ap_find_duplicate_post( $fields['description'], 'answer', $question->ID ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'form'     => ap_sanitize_unslash( 'ap_form_action' ),
				'snackbar' => [ 'message' => __( 'This seems to be a duplicate answer. An answer with same content already exists.', 'anspress-question-answer' ) ],
			) );
		}

		$filter = apply_filters( 'ap_before_inserting_answer', false, $fields['description'] );
		if ( true === $filter || is_array( $filter ) ) {
			if ( is_array( $filter ) ) {
				$this->result = $filter;
			}
			return;
		}

		$user_id = get_current_user_id();

		$answer_array = array(
			'post_author'		=> $user_id,
			'post_content' 		=> $fields['description'],
			'attach_uploads' 	=> true,
		);

		$answer_array['post_status'] = ap_new_edit_post_status( $user_id, 'answer', false );

		if ( isset( $this->fields['is_private'] ) && $this->fields['is_private'] ) {
			$answer_array['is_private'] = true;
		}

		// Check if anonymous post and have name.
		if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) && ! empty( $fields['anonymous_name'] ) ) {
			$answer_array['anonymous_name'] = $fields['anonymous_name'];
		}

		$answer_id = ap_save_answer( $question->ID, $answer_array );

		if ( $answer_id ) {
			ap_clear_unattached_media();
			ap_answer_post_ajax_response( $question->ID, $answer_id );
		}

	}

	/**
	 * Process edit answer form.
	 * @param  object $question Parent question object.
	 * @return mixed
	 */
	public function edit_answer( $question ) {
		global $ap_errors, $validate;

		// Return if user do not have permission to edit this answer.
		if ( ! ap_user_can_edit_answer( $this->fields['edit_post_id'] ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Sorry, you cannot edit this answer', 'anspress-question-answer' ) ],
			) );
		}

		$filter = apply_filters( 'ap_before_updating_answer', false, $this->fields['description'] );
		if ( true === $filter || is_array( $filter ) ) {
			if ( is_array( $filter ) ) {
				$this->result = $filter;
			}
			return;
		}

		$answer = ap_get_post( $this->fields['edit_post_id'] );
		$answer_array = array(
			'ID'				        => $this->fields['edit_post_id'],
			'post_author'		    => $answer->post_author,
			'post_content' 		  => $this->fields['description'],
			'attach_uploads' 	  => true,
		);

		$answer_array['post_status'] = ap_new_edit_post_status( get_current_user_id(), 'answer', true );

		if ( isset( $this->fields['is_private'] ) && $this->fields['is_private'] ) {
			$answer_array['is_private'] = true;
		}

		$answer_id = ap_save_answer( $question->ID, $answer_array );

		if ( $answer_id ) {
			ap_clear_unattached_media();

			// Trigger update hook.
			ap_trigger_qa_update_hook( $answer_id, 'edited' );

			ap_ajax_json( array(
				'success'  => true,
				'action'   => 'answer_edited',
				'snackbar' => [ 'message' => __( 'Answer updated successfully', 'anspress-question-answer' ) ],
				'redirect' => get_permalink( $answer->post_parent ),
			));
		}
	}
}

/**
 * Send ajax response if there is error in validation class.
 * @param  object $validate Validation class.
 * @since  3.0.0
 */
function ap_form_validation_error_response( $validate ) {
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

