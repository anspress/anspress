<?php
/**
 * AnsPress process form
 * @link http://anspress.io
 * @since 2.0.1
 * @license GPL 2+
 * @package AnsPress
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Process_Form
{
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
		$this->mark_notification_as_read();

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

	public function mark_notification_as_read() {
		if ( isset( $_GET['ap_notification_read'] ) ) {
			$id = (int) $_GET['ap_notification_read'];

			$notification = ap_get_notification_by_id( $id );

			if ( $notification && ($notification['apmeta_actionid'] == get_current_user_id() ) ) {
				ap_notification_mark_as_read( $id );
			}
		}
	}

	/**
	 * Handle all anspress ajax requests
	 * @return void
	 * @since 2.0.1
	 */
	public function ap_ajax() {
		if ( ! isset( $_REQUEST['ap_ajax_action'] ) ) {
			return;
		}

		$this->request = $_REQUEST;

		if ( isset( $_POST['ap_form_action'] ) ) {
	    	$this->is_ajax = true;
	    	$this->process_form();
			ap_ajax_json( $this->result );
		} else {
			$action = sanitize_text_field( $this->request['ap_ajax_action'] );

	    	/**
	    	 * ACTION: ap_ajax_[$action]
	    	 * Action for processing Ajax requests
	    	 * @since 2.0.1
	    	 */
	    	do_action( 'ap_ajax_'.$action );
		}

		// If reached to this point then there is something wrong.
		ap_send_json( ap_ajax_responce( 'something_wrong' ) );
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

			case 'ap_user_profile_form':
				$this->ap_user_profile_form();
				break;

			case 'upload_post_image':
				$this->upload_post_image();
				break;

			default:
				/**
				 * ACTION: ap_process_form_[action]
				 * process form
				 * @since 2.0.1
				 */
				do_action( 'ap_process_form_'.$action );
				break;
		}
	}

	/**
	 * Process ask form
	 * @return void
	 * @since 2.0.1
	 */
	public function process_ask_form() {
		// Do security check, if fails then return.
		if ( ! ap_user_can_ask() || ! isset( $_POST['__nonce'] ) || ! wp_verify_nonce( $_POST['__nonce'], 'ask_form' ) ) {
			ap_ajax_json('no_permission');
		}

		// Bail if capatcha verification fails.
		ap_captcha_verification_response();

		global $ap_errors, $validate;
		$editing_post_id = ap_isset_post_value( 'edit_post_id', false );

		/**
		 * FILTER: ap_ask_fields_validation
		 * Filter can be used to modify ask question fields.
		 * @param array $args Ask form validation arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_ask_fields_validation', ap_get_ask_form_fields( $editing_post_id ) );

		$validate = new AnsPress_Validation( $args );		

		// If error in form then bail.
		ap_form_validation_error_response( $validate );

		$fields = $validate->get_sanitized_fields();

		$this->fields = $fields;

		if ( ! empty( $fields['edit_post_id'] ) ) {
			$this->edit_question();
			return;
		}

		$filter = apply_filters( 'ap_before_inserting_question', false, $fields['description'] );
		if ( true === $filter || is_array( $filter ) ) {
			if ( is_array( $filter ) ) {
				$this->result = $filter;
			}
			return;
		}

		$user_id = get_current_user_id();

		$question_array = array(
			'post_title'		=> $fields['title'],
			'post_author'		=> $user_id,
			'post_content' 		=> $fields['description'],
			'attach_uploads' 	=> true,
		);

		if ( ap_opt( 'new_question_status' ) == 'moderate' || (ap_opt( 'new_question_status' ) == 'reputation' && ap_get_points( $user_id ) < ap_opt( 'mod_question_point' )) ) {
			$question_array['post_status'] = 'moderate';
		}

		// Check if anonymous post and have name.
		if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) && ! empty( $fields['anonymous_name'] ) ) {
			$question_array['anonymous_name'] = $fields['name'];
		}

		if ( isset( $fields['parent_id'] ) ) {
			$question_array['post_parent'] = (int) $fields['parent_id'];
		}

		$post_id = ap_save_question( $question_array, true );

		if ( $post_id ) {
			ap_ajax_json( array(
				'action' 		=> 'new_question',
				'message'		=> 'question_submitted',
				'do'			=> array( 'redirect' => get_permalink( $post_id ) ),
			) );
		}

		// Remove all unused atthements by user.
		ap_clear_unused_attachments( $user_id );
	}

	/**
	 * Process edit question form
	 * @return void
	 * @since 2.0.1
	 */
	public function edit_question() {
		global $ap_errors, $validate;

		// Return if user do not have permission to edit this question.
		if ( ! ap_user_can_edit_question( $this->fields['edit_post_id'] ) ) {
			return;
		}

		$filter = apply_filters( 'ap_before_updating_question', false, $this->fields['description'] );
		if ( true === $filter || is_array( $filter ) ) {
			if ( is_array( $filter ) ) {
				$this->result = $filter;
			}
			return;
		}

		$post = get_post( $this->fields['edit_post_id'] );
		$user_id = get_current_user_id();

		$question_array = array(
			'ID'				=> $post->ID,
			'post_title'		=> $this->fields['title'],
			'post_content' 		=> $this->fields['description'],
			'attach_uploads' 	=> true,
		);

		if ( ap_opt( 'edit_question_status' ) == 'moderate' || (ap_opt( 'edit_question_status' ) == 'point' && ap_get_points( $user_id ) < ap_opt( 'mod_answer_point' )) ) {
			$question_array['post_status'] = 'moderate';
		}

		// Check if anonymous post and have name.
		if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) && ! empty( $this->fields['anonymous_name'] ) ) {
			$question_array['anonymous_name'] = $this->fields['name'];
		}

		$post_id = ap_save_question( $question_array );

		if ( $post_id ) {
			$this->redirect = get_permalink( $post_id );

			ap_ajax_json( array(
				'action' 		=> 'edited_question',
				'message'		=> 'question_updated',
				'do'			=> array( 'redirect' => $this->redirect ),
			) );
		}

		// Remove all unused atthements by user.
		ap_clear_unused_attachments( $user_id );
	}

	/**
	 * Process answer form
	 */
	public function process_answer_form() {
		// Do security check, if fails then return.
		if ( ! ap_user_can_answer( $_POST['form_question_id'] ) || ! isset( $_POST['__nonce'] ) || ! ap_verify_nonce( 'nonce_answer_'.$_POST['form_question_id'] ) ) {
			ap_ajax_json( 'no_permission' );
		}

		// Bail if capatcha verification fails.
		ap_captcha_verification_response();

		global $ap_errors, $validate;
		$question = get_post( (int) $_POST['form_question_id'] );

		// Check if user have permission to answer a question.
		if ( ! ap_user_can_answer( $question->ID ) ) {
			ap_ajax_json('no_permission');
		}

		$editing_post_id = ap_isset_post_value( 'edit_post_id', false );

		/**
		 * FILTER: ap_answer_fields_validation
		 * Filter can be used to modify answer form fields.
		 * @var void
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_answer_fields_validation', ap_get_answer_form_fields( $question->ID, $editing_post_id ) );

		$validate = new AnsPress_Validation( $args );

		// Bail if there is error in validating form.
		ap_form_validation_error_response( $validate );

		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if ( ! empty( $fields['edit_post_id'] ) ) {
			$this->edit_answer( $question );
			return;
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

		if ( ap_opt( 'new_question_status' ) == 'moderate' || (ap_opt( 'new_question_status' ) == 'reputation' && ap_get_points( $user_id ) < ap_opt( 'mod_question_point' )) ) {
			$answer_array['post_status'] = 'moderate';
		}

		// Check if anonymous post and have name.
		if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) && ! empty( $fields['anonymous_name'] ) ) {
			$answer_array['anonymous_name'] = $fields['name'];
		}

		$answer_id = ap_save_answer( $question->ID, $answer_array );

		if ( $answer_id ) {
			ap_answer_post_ajax_response( $question->ID, $answer_id );
		}
		ap_clear_unused_attachments( $user_id );
	}

	/**
	 * Process edit answer form.
	 * @param  object $question Parent question object.
	 * @return mixed
	 */
	public function edit_answer( $question) {

		global $ap_errors, $validate;

		// Return if user do not have permission to edit this answer.
		if ( ! ap_user_can_edit_answer( $this->fields['edit_post_id'] ) ) {
			$this->result = ap_ajax_responce( 'no_permission' );
			return;
		}

		$filter = apply_filters( 'ap_before_updating_answer', false, $this->fields['description'] );

		if ( true === $filter || is_array( $filter ) ) {
			if ( is_array( $filter ) ) {
				$this->result = $filter;
			}
			return;
		}

		$answer = get_post( $this->fields['edit_post_id'] );

		$status = 'publish';

		if ( ap_opt( 'edit_answer_status' ) == 'moderate' || (ap_opt( 'edit_answer_status' ) == 'point' && ap_get_points( get_current_user_id() ) < ap_opt( 'new_answer_status' )) ) {
			$status = 'moderate';
		}

		if ( isset( $this->fields['is_private'] ) && $this->fields['is_private'] ) {
			$status = 'private_post';
		}

		$answer_array = array(
			'ID'			=> $this->fields['edit_post_id'],
			'post_author'	=> $answer->post_author,
			'post_content' 	=> apply_filters( 'ap_form_contents_filter', $this->fields['description'] ),
			'post_status' 	=> $status,
		);

		$answer_array = apply_filters( 'ap_pre_update_answer', $answer_array );

		$post_id = wp_update_post( $answer_array );

		if ( $post_id ) {
			if ( $this->is_ajax ) {
				$this->result = array(
					'action' 		=> 'answer_edited',
					'message'		=> 'answer_updated',
					'do'			=> array( 'redirect' => get_permalink( $answer->post_parent ) ),
				);
			}

			$this->redirect = get_permalink( $post_id );
		}

		$this->process_image_uploads( $post_id, $answer->post_author );

		// Check for spam in question.
		if ( ap_opt('akismet_validation' ) && ! current_user_can( 'ap_edit_others_answer' ) ) {
			ap_check_spam( $post_id );
		}
	}

	/**
	 * Process user profile and account fields
	 */
	public function ap_user_profile_form() {
		$user_id = get_current_user_id();
		$group = sanitize_text_field( $_POST['group'] );

		if ( ! ap_user_can_edit_profile() ) {
			$this->result  = array( 'message' => 'no_permission' );
			return;
		}

		if ( ! ap_verify_nonce( 'nonce_user_profile_'.$user_id.'_'.$group ) ) {
			ap_send_json( ap_ajax_responce( 'something_wrong' ) );
		}

		$user_fields = ap_get_user_fields( $group, $user_id );

		$validate_fields = array();

		foreach ( $user_fields as $field ) {
			if ( isset( $field['sanitize'] ) ) {
				$validate_fields[$field['name']]['sanitize'] = $field['sanitize'];
			}

			if ( $field['validate'] ) {
				$validate_fields[$field['name']]['validate'] = $field['validate'];
			}
		}

		$validate = new AnsPress_Validation( $validate_fields );

		$ap_errors = $validate->get_errors();

		// If error in form then return.
		if ( $validate->have_error() ) {
			ap_send_json( ap_ajax_responce(array(
				'form' 			=> $_POST['ap_form_action'],
				'message_type' 	=> 'error',
				'message'		=> __( 'Check missing fields and then re-submit.', 'anspress-question-answer' ),
				'errors'		=> $ap_errors,
			)));
			return;
		}

		$fields = $validate->get_sanitized_fields();

		$default_fields = array( 'name', 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'description' );

		if ( is_array( $user_fields ) && ! empty( $user_fields ) ) {
			foreach ( $user_fields as $field ) {

				if ( isset( $fields[$field['name']] ) && ( in_array( $field['name'], $default_fields ) ) ) {

					wp_update_user( array( 'ID' => $user_id, $field['name'] => $fields[$field['name']] ) );

					// If email is updated then send verification email.
					if ( $field['name'] == 'user_email' ) {
						wp_new_user_notification( $user_id, null, 'both' );
					}
				} elseif ( $field['name'] == 'password' && $_POST['password'] == $_POST['password-1'] ) {
					wp_set_password( $_POST['password'], $user_id );
				} elseif ( isset( $fields[$field['name']] ) ) {
					update_user_meta( $user_id, $field['name'], $fields[$field['name']] );
				}
			}
		}

		$this->result  = array(
			'message' 		=> 'profile_updated_successfully',
			'action' 		=> 'updated_user_field',
			'do'			=> array( 'updateHtml' => '#ap_user_profile_form' ),
			'html'			=> ap_user_get_fields( '', $group ),
		);
	}

	public function upload_post_image() {

		if ( ! ap_user_can_upload_image() ) {
			$this->result  = array( 'message' => 'no_permission' );
			return;
		}

		$user_id = get_current_user_id();

		$file = $_FILES['post_upload_image'];

		if ( $file['size'] > ap_opt( 'max_upload_size' ) ) {
			$this->result  = array( 'message_type' => 'error', 'message' => sprintf( __( 'File cannot be uploaded, size is bigger then %d Byte', 'anspress-question-answer' ), ap_opt( 'max_upload_size' ) ) );
			return;
		}

		if ( ap_user_upload_limit_crossed( $user_id ) ) {
			$this->result  = array( 'message' => 'upload_limit_crossed' );
			return;
		}

		if ( ! is_user_logged_in() ) {
			$this->result  = array( 'message' => 'no_permission' );
			return;
		}

		if ( ! isset( $_POST['__nonce'] ) || ! wp_verify_nonce( $_POST['__nonce'], 'upload_image_'.$user_id ) ) {
			ap_send_json( ap_ajax_responce( 'something_wrong' ) ); }

		if ( ! empty( $file ) && is_array( $file ) && $file['error'] == 0 ) {

			$attachment_id = ap_upload_user_file( $file );

			if ( $attachment_id !== false ) {
				ap_send_json( ap_ajax_responce( array( 'action' => 'upload_post_image', 'html' => wp_get_attachment_image( $attachment_id, 'full' ), 'message' => 'post_image_uploaded', 'attachment_id' => $attachment_id ) ) ); }
		}

		ap_send_json( ap_ajax_responce( 'something_wrong' ) );
	}

	public function process_image_uploads($post_id, $user_id) {

		$attachment_ids = $_POST['attachment_ids'];

		// If attchment ids present then user have uploaded images.
		if ( is_array( $attachment_ids ) && count( $attachment_ids ) > 0 ) {
			foreach ( $attachment_ids as $id ) {
				$attach = get_post( $id );

				if ( $attach && 'attachment' == $attach->post_type && $user_id == $attach->post_author ) {
					ap_set_attachment_post_parent( $attach->ID, $post_id ); }
			}
		}

		// Remove all unused atthements by user.
		ap_clear_unused_attachments( $user_id );
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
			'form' 			=> $_POST['ap_form_action'],
			'message_type' 	=> 'error',
			'message'		=> __( 'Check missing fields and then re-submit.', 'anspress-question-answer' ),
			'errors'		=> $validate->get_errors(),
		) );
	}
}

/**
 * Send ajax response if capatcha verification fails.
 * @since 3.0.0
 */
function ap_captcha_verification_response(){
	if ( ap_show_captcha_to_user() && false === ap_check_recaptcha() ) {
		ap_ajax_json( array(
			'form' 			=> $_POST['ap_form_action'],
			'message'		=> 'captcha_error',
			'errors'		=> array( 'captcha' => __( 'Bot verification failed.', 'anspress-question-answer' ) ),
		) );
	}
}