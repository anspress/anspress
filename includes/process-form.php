<?php
/**
 * AnsPress process form
 * @link http://anspress.io
 * @since 2.0.1
 * @license GPL 2+
 * @package AnsPress
 */

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

		add_action( 'init', array( $this, 'non_ajax_form' ), 0 );
		add_action( 'save_post', array( $this, 'action_on_new_post' ), 10, 3 );
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
			exit;
		}
	}

	public function mark_notification_as_read() {

		if ( isset( $_GET['ap_notification_read'] ) ) {
			$id = (int) $_GET['ap_notification_read'];

			$notification = ap_get_notification_by_id( $id );

			if ( $notification && ($notification['apmeta_actionid'] == get_current_user_id() ) ) {
				$row = ap_notification_mark_as_read( $id );
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
			return; }

		$this->request = $_REQUEST;

		if ( isset( $_POST['ap_form_action'] ) ) {
	    	$this->is_ajax = true;
	    	$this->process_form();
			ap_send_json( ap_ajax_responce( $this->result ) );
		} else {
			$action = sanitize_text_field( $this->request['ap_ajax_action'] );

	    	/**
	    	 * ACTION: ap_ajax_[$action]
	    	 * Action for processing Ajax requests
	    	 * @since 2.0.1
	    	 */
	    	do_action( 'ap_ajax_'.$action );
		}
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

			case 'comment_form':
				$this->comment_form();

			case 'options_form':
				$this->options_form();
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

	public function check_recaptcha() {

		$recaptcha = new \ReCaptcha\ReCaptcha( ap_opt( 'recaptcha_secret_key' ) );
		$resp = $recaptcha->verify( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] );

		if ( $resp->isSuccess() ) {
			do_action( 'ap_form_captch_verified' );
			return true;
		}

		return false;
	}

	/**
	 * Process ask form
	 * @return void
	 * @since 2.0.1
	 */
	public function process_ask_form() {

		global $ap_errors, $validate;

		if ( ap_show_captcha_to_user() && ! $this->check_recaptcha() ) {
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message'		=> 'captcha_error',
				'errors'		=> array( 'captcha' => __( 'Bot verification failed.', 'ap' ) ),
			);
			return;
		}

		// Do security check, if fails then return
		if ( ! ap_user_can_ask() || ! isset( $_POST['__nonce'] ) || ! wp_verify_nonce( $_POST['__nonce'], 'ask_form' ) ) {
			return; }

		$args = array(
			'title' => array(
				'sanitize' => array( 'sanitize_text_field' ),
				'validate' => array( 'required' => true, 'length_check' => ap_opt( 'minimum_qtitle_length' ) ),
			),
			'description' => array(
				'sanitize' => array( 'remove_more', 'encode_pre_code', 'wp_kses' ),
				'validate' => array( 'length_check' => ap_opt( 'minimum_question_length' ) ),
			),
			'is_private' => array(
				'sanitize' => array( 'only_boolean' ),
			),
			'name' => array(
				'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
			),
			'parent_id' => array(
				'sanitize' => array( 'only_int' ),
			),
			'edit_post_id' => array(
				'sanitize' => array( 'only_int' ),
			),
		);

		/**
		 * FILTER: ap_ask_fields_validation
		 * Filter can be used to modify ask question fields.
		 * @var void
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_ask_fields_validation', $args );

		$validate = new AnsPress_Validation( $args );

		$ap_errors = $validate->get_errors();

		// if error in form then return
		if ( $validate->have_error() ) {
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message_type' 	=> 'error',
				'message'		=> __( 'Check missing fields and then re-submit.', 'ap' ),
				'errors'		=> $ap_errors,
			);
			return;
		}

		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if ( ! empty( $fields['edit_post_id'] ) ) {
			$this->edit_question();
			return;
		}

		$user_id = get_current_user_id();

		$status = 'publish';

		if ( ap_opt( 'new_question_status' ) == 'moderate' || (ap_opt( 'new_question_status' ) == 'reputation' && ap_get_points( $user_id ) < ap_opt( 'mod_question_point' )) ) {
			$status = 'moderate'; }

		if ( isset( $fields['is_private'] ) && $fields['is_private'] ) {
			$status = 'private_post'; }

		$question_array = array(
			'post_title'		=> $fields['title'],
			'post_author'		=> $user_id,
			'post_content' 		=> apply_filters( 'ap_form_contents_filter', $fields['description'] ),
			'post_type' 		=> 'question',
			'post_status' 		=> $status,
			'comment_status' 	=> 'open',
		);

		if ( isset( $fields['parent_id'] ) ) {
			$question_array['post_parent'] = (int) $fields['parent_id'];
		}

		/**
		 * FILTER: ap_pre_insert_question
		 * Can be used to modify args before inserting question
		 * @var array
		 * @since 2.0.1
		 */
		$question_array = apply_filters( 'ap_pre_insert_question', $question_array );

		$post_id = wp_insert_post( $question_array );

		if ( $post_id ) {

			// Update Custom Meta
			if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) && ! empty( $fields['name'] ) ) {
				update_post_meta( $post_id, 'anonymous_name', $fields['name'] ); }

			$this->redirect = get_permalink( $post_id );

			$this->result = array(
				'action' 		=> 'new_question',
				'message'		=> 'question_submitted',
				'do'			=> array('redirect' => get_permalink( $post_id )),
			);
		}

		$this->process_image_uploads( $post_id, $user_id );

	}

	/**
	 * Process edit question form
	 * @return void
	 * @since 2.0.1
	 */
	public function edit_question() {

		global $ap_errors, $validate;

		// return if user do not have permission to edit this question
		if ( ! ap_user_can_edit_question( $this->fields['edit_post_id'] ) ) {
			return; }

		$post = get_post( $this->fields['edit_post_id'] );
		$user_id = get_current_user_id();

		$status = 'publish';

		if ( ap_opt( 'edit_question_status' ) == 'moderate' || (ap_opt( 'edit_question_status' ) == 'point' && ap_get_points( $user_id ) < ap_opt( 'mod_answer_point' )) ) {
			$status = 'moderate'; }

		if ( isset( $this->fields['is_private'] ) && $this->fields['is_private'] ) {
			$status = 'private_post'; }

		$question_array = array(
			'ID'			=> $post->ID,
			'post_author'	=> $post->post_author,
			'post_title'	=> $this->fields['title'],
			'post_name'		=> sanitize_title( $this->fields['title'] ),
			'post_content' 	=> apply_filters( 'ap_form_contents_filter', $this->fields['description'] ),
			'post_status' 	=> $status,
		);

		/**
		 * FILTER: ap_pre_update_question
		 * Can be used to modify $args before updating question
		 * @var array
		 * @since 2.0.1
		 */
		$question_array = apply_filters( 'ap_pre_update_question', $question_array );

		$post_id = wp_update_post( $question_array );

		if ( $post_id ) {

			$this->redirect = get_permalink( $post_id );

			$this->result = array(
				'action' 		=> 'edited_question',
				'message'		=> 'question_updated',
				'do'			=> array( 'redirect' => $this->redirect ),
			);
		}

		$this->process_image_uploads( $post->ID, $post->post_author );
	}

	/**
	 * add _o actions after inserting question and answer
	 * @param  int    $post_id
	 * @param  object $post
	 * @return void
	 * @since  2.0
	 */
	public function action_on_new_post( $post_id, $post, $update ) {

		// return on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

		if ( wp_is_post_revision( $post_id ) || $post->post_status == 'trash'|| $post->post_status == 'auto-draft' ) {
			return; }

		$updated = get_post_meta( $post_id, ANSPRESS_UPDATED_META, true );

		if ( $post->post_type == 'question' ) {
			// check if post have updated meta, if not this is a new post :D
			if ( $updated == '' ) {
				/**
				 * ACTION: ap_after_new_question
				 * action triggered after inserting a question
				 * @since 0.9
				 */
				do_action( 'ap_processed_new_question', $post_id, $post );
			} else {

				do_action( 'ap_processed_update_question', $post_id, $post );
			}
		} elseif ( $post->post_type == 'answer' ) {

			if ( $updated == '' ) {

				do_action( 'ap_processed_new_answer', $post_id, $post );
			} else {
				/**
				 * ACTION: ap_after_update_answer
				 * action triggered after updating an answer
				 * @since 0.9
				 */
				do_action( 'ap_processed_update_answer', $post_id, $post );

			}
		}
	}

	/**
	 * Process answer form
	 */
	public function process_answer_form() {

		global $ap_errors, $validate;

		if ( ap_show_captcha_to_user() && ! $this->check_recaptcha() ) {
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message'		=> 'captcha_error',
				'errors'		=> array( 'captcha' => __( 'Bot verification failed.', 'ap' ) ),
			);
			return;
		}

		$question = get_post( (int) $_POST['form_question_id'] );

		$args = array(
			'description' => array(
				'sanitize' => array( 'remove_more', 'encode_pre_code', 'wp_kses' ),
				'validate' => array( 'required' => true, 'length_check' => ap_opt( 'minimum_question_length' ) ),
			),
			'is_private' => array(
				'sanitize' => array( 'only_boolean' ),
			),
			'name' => array(
				'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
			),
			'form_question_id' => array(
				'sanitize' => array( 'only_int' ),
			),
			'edit_post_id' => array(
				'sanitize' => array( 'only_int' ),
			),
		);

		/**
		 * FILTER: ap_answer_fields_validation
		 * Filter can be used to modify answer form fields.
		 * @var void
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_answer_fields_validation', $args );

		$validate = new AnsPress_Validation( $args );

		$ap_errors = $validate->get_errors();

		// if error in form then return
		if ( $validate->have_error() ) {
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message_type' 	=> 'error',
				'message'		=> __( 'Check missing fields and then re-submit.', 'ap' ),
				'errors'		=> $ap_errors,
			);
			return;
		}

		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if ( ! empty( $fields['edit_post_id'] ) ) {
			$this->edit_answer( $question );
			return;
		}

		// Do security check, if fails then return
		if ( ! ap_user_can_answer( $question->ID ) || ! isset( $_POST['__nonce'] ) || ! wp_verify_nonce( $_POST['__nonce'], 'nonce_answer_'.$question->ID ) ) {
			$this->result = ap_ajax_responce( 'no_permission' );
			return;
		}

		$user_id = get_current_user_id();

		$status = 'publish';

		if ( ap_opt( 'new_answer_status' ) == 'moderate' || (ap_opt( 'new_answer_status' ) == 'point' && ap_get_points( $user_id ) < ap_opt( 'new_answer_status' )) ) {
			$status = 'moderate'; }

		if ( isset( $this->fields['is_private'] ) && $this->fields['is_private'] ) {
			$status = 'private_post';
		}

		$answer_array = array(
			'post_title'	=> $question->post_title,
			'post_author'	=> $user_id,
			'post_content' 	=> apply_filters( 'ap_form_contents_filter', $fields['description'] ),
			'post_parent' 	=> $question->ID,
			'post_type' 	=> 'answer',
			'post_status' 	=> $status,
			'comment_status' => 'open',
		);

		/**
		 * FILTER: ap_pre_insert_answer
		 * Can be used to modify args before inserting answer
		 * @var array
		 * @since 2.0.1
		 */
		$answer_array = apply_filters( 'ap_pre_insert_answer', $answer_array );

		$post_id = wp_insert_post( $answer_array );

		if ( $post_id ) {
			// get existing answer count
			$current_ans = ap_count_published_answers( $question->ID );

			if ( ! is_user_logged_in() && ap_opt( 'allow_anonymous' ) && isset( $fields['name'] ) ) {
				update_post_meta( $post_id, 'anonymous_name', $fields['name'] );
			}

			if ( $this->is_ajax ) {

				if ( $current_ans == 1 ) {
					global $post;
					$post = $question;
					setup_postdata( $post );
				} else {
					global $post;
					$post = get_post( $post_id );
					setup_postdata( $post );
				}

				ob_start();
				global $answers;
				if ( $current_ans == 1 ) {
					$answers = ap_get_answers( array( 'question_id' => $question->ID ) );
					ap_get_template_part( 'answers' );
				} else {
					$answers = ap_get_answers( array( 'p' => $post_id ) );
					while ( ap_have_answers() ) : ap_the_answer();
						ap_get_template_part( 'answer' );
					endwhile;
				}

				$html = ob_get_clean();

				$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'ap' ), $current_ans );

				$result = array(
					'postid' 		=> $post_id,
					'action' 		=> 'new_answer',
					'div_id' 		=> '#answer_'.get_the_ID(),
					'can_answer' 	=> ap_user_can_answer( $post->ID ),
					'html' 			=> $html,
					'message' 		=> 'answer_submitted',
					'do' 			=> 'clearForm',
					'view'			=> array( 'answer_count' => $current_ans, 'answer_count_label' => $count_label ),
				);

				$this->result = $result;

			}
		}

		$this->process_image_uploads( $post_id, $user_id );
	}

	public function edit_answer($question) {

		global $ap_errors, $validate;

		// return if user do not have permission to edit this answer
		if ( ! ap_user_can_edit_ans( $this->fields['edit_post_id'] ) ) {
			$this->result = ap_ajax_responce( 'no_permission' );
			return;
		}

		$answer = get_post( $this->fields['edit_post_id'] );

		$status = 'publish';

		if ( ap_opt( 'edit_answer_status' ) == 'moderate' || (ap_opt( 'edit_answer_status' ) == 'point' && ap_get_points( get_current_user_id() ) < ap_opt( 'new_answer_status' )) ) {
			$status = 'moderate'; }

		if ( isset( $this->fields['is_private'] ) && $this->fields['is_private'] ) {
			$status = 'private_post'; }

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
					'do'			=> array('redirect' => get_permalink( $answer->post_parent )),
				);
			}

			$this->redirect = get_permalink( $post_id );
		}

		$this->process_image_uploads( $post_id, $answer->post_author );
	}

	public function comment_form() {

		if ( empty( $_POST['comment'] ) ) {
			$this->result = ap_ajax_responce( 'comment_content_empty' );
			return;
		}

		if ( ! isset( $_REQUEST['comment_ID'] ) ) {
			// Do security check
			if ( ! ap_user_can_comment() || ! isset( $_POST['__nonce'] ) || ! wp_verify_nonce( $_POST['__nonce'], 'comment_' . (int) $_POST['comment_post_ID'] ) ) {
				$this->result = ap_ajax_responce( 'no_permission' );
				return;
			}
		} else {
			if ( ! ap_user_can_edit_comment( (int) $_REQUEST['comment_ID'] ) || ! wp_verify_nonce( $_REQUEST['__nonce'], 'comment_'.(int) $_REQUEST['comment_ID'] ) ) {
				$this->result = ap_ajax_responce( 'no_permission' );
				return;
			}
		}

		$comment_post_ID = (int) $_POST['comment_post_ID'];
		$post = get_post( $comment_post_ID );

		if ( ! $post || empty( $post->post_status ) ) {
			return; }

		if ( in_array( $post->post_status, array( 'draft', 'pending', 'trash' ) ) ) {
			$this->result = ap_ajax_responce( 'draft_comment_not_allowed' );

			return;
		}

		if ( isset( $_POST['comment_ID'] ) ) {

			$comment_id = (int) $_POST['comment_ID'];

			$updated = wp_update_comment( array( 'comment_ID' => $comment_id, 'comment_content' => trim( $_POST['comment'] ) ) );

			if ( $updated ) {

				$comment = get_comment( $comment_id );

				ob_start();
				comment_text( $comment_id );
				$html = ob_get_clean();

				$this->result = ap_ajax_responce( array( 'action' => 'edit_comment', 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => 'comment_edit_success' ) );
			}

			return;
		} else {
			$user = wp_get_current_user();
			if ( $user->exists() ) {
				$user_ID = $user->ID;
				$comment_author = wp_slash( $user->display_name );
				$comment_author_email = wp_slash( $user->user_email );
				$comment_author_url = wp_slash( $user->user_url );
				$comment_content = trim( $_POST['comment'] );
				$comment_type = 'anspress';

			} else {
				$this->result = ap_ajax_responce( 'no_permission' );
				return;
			}

			$comment_parent = 0;

			if ( isset( $_POST['comment_ID'] ) ) {
				$comment_parent = absint( $_POST['comment_ID'] ); }

			$commentdata = compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID' );

			// Automatically approve parent comment.
			if ( ! empty( $_POST['approve_parent'] ) ) {
				$parent = get_comment( $comment_parent );
				if ( $parent && $parent->comment_approved === '0' && $parent->comment_post_ID == $comment_post_ID ) {
					if ( wp_set_comment_status( $parent->comment_ID, 'approve' ) ) {
						$comment_auto_approved = true; }
				}
			}

			$comment_id = wp_new_comment( $commentdata );

			if ( $comment_id > 0 ) {
				$comment = get_comment( $comment_id );
				do_action( 'ap_after_new_comment', $comment );
				ob_start();
				ap_comment( $comment );
				$html = ob_get_clean();
				$count = get_comment_count( $comment->comment_post_ID );
				$this->result = ap_ajax_responce( array( 'action' => 'new_comment', 'status' => true, 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => 'comment_success', 'view' => array( 'comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')', 'comment_count_label_'.$comment->comment_post_ID => sprintf( _n( 'One comment', '%d comments', $count['approved'], 'ap' ), $count['approved'] ) ) ) );
			} else {
				$this->result = ap_ajax_responce( 'something_wrong' );
			}
		}
	}

	public function options_form() {
		if ( ! isset( $_POST['__nonce'] ) || ! wp_verify_nonce( $_POST['__nonce'], 'nonce_option_form' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		flush_rewrite_rules();
		$options = $_POST['anspress_opt'];

		if ( ! empty( $options ) && is_array( $options ) ) {
			$old_options = get_option( 'anspress_opt' );

			foreach ( $options as $k => $opt ) {
				$value = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $opt ) ) );
				$old_options[ $k ] = wp_unslash( $value );
			}

			update_option( 'anspress_opt', $old_options );
			wp_cache_delete( 'ap_opt', 'options' );
			$_POST['anspress_opt_updated'] = true;
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
				'message'		=> __( 'Check missing fields and then re-submit.', 'ap' ),
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
			'do'			=> array('updateHtml' => '#ap_user_profile_form'),
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
			$this->result  = array( 'message_type' => 'error', 'message' => sprintf( __( 'File cannot be uploaded, size is bigger then %d Byte' ), ap_opt( 'max_upload_size' ) ) );
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
