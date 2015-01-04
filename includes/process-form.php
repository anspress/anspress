<?php
/**
 * AnsPress process form
 * @link http://wp3.in
 * @since 2.0
 * @license GPL 2+
 * @package AnsPress
 */

class AnsPress_Process_Form
{
	private $fields;

	private $result;

	private $redirect ;
	/**
	 * Initialize the class
	 */
	public function __construct()
	{
		add_action('init', array($this, 'non_ajax_form'));
		add_action( 'save_post', array($this, 'action_on_new_post'), 0, 2 );
	}

	/**
	 * for non ajax form
	 * @return void
	 */
	public function non_ajax_form()
	{
		//return if ap_form_action is not set, probably its not our form
		if(!isset($_POST['ap_form_action']))
			return;

		$this->process_form();

		if(!empty($this->redirect)){
			wp_redirect( $this->redirect );
			exit;
		}
	}

	/**
	 * for ajax form
	 * @return string
	 */
	public function ajax_form()
	{
		$this->process_form();
		wp_send_json( $this->result );
	}

	/**
	 * Process form based on action value
	 * @return void
	 * @since 2.0
	 */
	public function process_form()
	{
		$action = sanitize_text_field($_POST['ap_form_action']);
		switch ($action) {
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
				 * @since 2.0
				 */
				do_action('ap_process_form_'.$action);
				break;
		}

	}

	

	/**
	 * Process ask form
	 * @return void
	 * @since 2.0
	 */
	public function process_ask_form()
	{
		global $ap_errors, $validate;

		// Do security check, if fails then return
		if(!ap_user_can_ask() || !isset($_POST['__nonce']) || !wp_verify_nonce($_POST['__nonce'], 'ask_form'))
			return;

		$args = array(
			'title' => array(
				'sanitize' => array('sanitize_text_field'),
				'validate' => array('required' => true, 'length_check' => ap_opt('minimum_qtitle_length'))
			),
			'description' => array(
				'sanitize' => array('remove_more', 'strip_shortcodes', 'encode_pre_code', 'wp_kses'),
				'validate' => array('required' => true, 'length_check' => ap_opt('minimum_question_length'))
			),
			'is_private' => array(
				'sanitize' => array('only_boolean')
			),
			'name' => array(
				'sanitize' => array('strip_tags', 'sanitize_text_field')
			),
			'parent_id' => array(
				'sanitize' => array('only_int')
			),
			'edit_post_id' => array(
				'sanitize' => array('only_int')
			),
		);

		/**
		 * FILTER: ap_ask_fields_validation
		 * Filter can be used to modify ask question fields.
		 * @var void
		 * @since 2.0
		 */
		$args = apply_filters( 'ap_ask_fields_validation', $args );

		$validate = new AnsPress_Validation($args);

		$ap_errors = $validate->get_errors();
		
		// if error in form then return
		if($validate->have_error()){
			$this->result = $ap_errors;
			return;
		}

		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if(!empty($fields['edit_post_id'])){
			$this->edit_question();
			return;
		}


		$user_id = get_current_user_id();

		$status = 'publish';
		
		if(ap_opt('moderate_new_question') == 'pending' || (ap_opt('moderate_new_question') == 'point' && ap_get_points($user_id) < ap_opt('mod_question_point')))
			$status = 'moderate';
		
		if(isset($fields['is_private']) && $fields['is_private'])
			$status = 'private_post';
			
		$question_array = array(
			'post_title'	=> $fields['title'],
			'post_author'	=> $user_id,
			'post_content' 	=>  wp_kses($fields['description'], ap_form_allowed_tags()),
			'post_type' 	=> 'question',
			'post_status' 	=> $status,
			'comment_status' => 'open',
		);
		
		if(isset($fields['parent_id']))
			$question_array['post_parent'] = (int)$fields['parent_id'];

		/**
		 * FILTER: ap_pre_insert_question
		 * Can be used to modify args before inserting question
		 * @var array
		 * @since 2.0
		 */
		$question_array = apply_filters('ap_pre_insert_question', $question_array );

		$post_id = wp_insert_post($question_array);

		if($post_id){
			
			// Update Custom Meta
			
			/**
			 * TODO: EXTENSTION - move to tags extension
			 */
			if(isset($fields['tags']))
				wp_set_post_terms( $post_id, $fields['tags'], 'question_tags' );
				
			if (!is_user_logged_in() && ap_opt('allow_anonymous') && !empty($fields['name']))
				update_post_meta($post_id, 'anonymous_name', $fields['name']);
			
			
			$this->redirect =  get_permalink($post_id);

			$this->result = apply_filters('ap_ajax_question_submit_result', 
				array(
					'action' 		=> 'new_question',
					'message'		=> __('Question submitted successfully', 'ap'),
					'redirect_to'	=> get_permalink($post_id)
				)
			);
		}


	}

	/**
	 * Process edit question form
	 * @return void
	 * @since 2.0
	 */
	public function edit_question()
	{
		global $ap_errors, $validate;
		
		// return if user do not have permission to edit this question
		if( !ap_user_can_edit_question($this->fields['edit_post_id']))
			return;

		$post = get_post($this->fields['edit_post_id']);
		$user_id = get_current_user_id();

		$status = 'publish';
		
		if(ap_opt('moderate_new_question') == 'pending' || (ap_opt('moderate_new_question') == 'point' && ap_get_points($user_id) < ap_opt('mod_question_point')))
			$status = 'moderate';
		
		if(isset($this->fields['is_private']) && $this->fields['is_private'])
			$status = 'private_post';

		$question_array = array(
			'ID'			=> $post->ID,
			'post_title'	=> $this->fields['title'],
			'post_name'		=> sanitize_title($this->fields['title']),
			'post_content' 	=> $this->fields['description'],
			'post_status' 	=> $status,
		);

		/**
		 * FILTER: ap_pre_update_question
		 * Can be used to modify $args before updating question
		 * @var array
		 * @since 2.0
		 */
		$question_array = apply_filters('ap_pre_update_question', $question_array );

		$post_id = wp_update_post($question_array);

		if($post_id){				

			/**
			 * TODO: EXTENSION - move this to tags
			 */
			wp_set_post_terms( $post_id, $this->fields['tags'], 'question_tags' );

			
			
			$this->redirect = get_permalink($post_id);

			$this->result = apply_filters('ap_ajax_edit_question_submit_result', 
				array(
					'action' 		=> 'edited_question',
					'message'		=> __('Question updated successfully', 'ap'),
					'redirect_to'	=> $this->redirect
				)
			);
		}
	}

	/**
	 * add _o actions after inserting question and answer
	 * @param  int $post_id
	 * @param  object $post    
	 * @return void          
	 * @since  2.0
	 */
	public function action_on_new_post( $post_id, $post ) {

		// return on autosave
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
		
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		if ( $post->post_type == 'question' ) {
			//check if post have updated meta, if not this is a new post :D
			$updated = get_post_meta($post_id, ANSPRESS_UPDATED_META, true);
			if($updated == ''){
				/**
				 * ACTION: ap_after_new_question
				 * action triggered after inserting a question
				 * @since 0.9
				 */
				do_action('ap_after_new_question', $post_id, $post);
			}else{
				/**
				 * ACTION: ap_after_update_question
				 * action triggered after updating a question
				 * @since 0.9
				 */
				do_action('ap_after_update_question', $post_id, $post);
			}
		}elseif ( $post->post_type == 'answer' ) {
			$updated = get_post_meta($post_id, ANSPRESS_UPDATED_META, true);
			if($updated == ''){
				/**
				 * ACTION: ap_after_new_answer
				 * action triggered after inserting an answer
				 * @since 0.9
				 */
				do_action('ap_after_new_answer', $post_id, $post);
			}else{
				/**
				 * ACTION: ap_after_update_answer
				 * action triggered after updating an answer
				 * @since 0.9
				 */
				do_action('ap_after_update_answer', $post_id, $post);
			}
		}
	}

	public function process_answer_form()
	{
		global $ap_errors, $validate;

		$question = get_post((int)$_POST['question_id']);
		// Do security check, if fails then return
		if(!ap_user_can_answer($question->ID) || !isset($_POST['__nonce']) || !wp_verify_nonce($_POST['__nonce'], 'nonce_answer_'.$question->ID))
			return;

		$args = array(
			'description' => array(
				'sanitize' => array('remove_more', 'strip_shortcodes', 'encode_pre_code', 'wp_kses'),
				'validate' => array('required' => true, 'length_check' => ap_opt('minimum_question_length'))
			),
			'is_private' => array(
				'sanitize' => array('only_boolean')
			),
			'name' => array(
				'sanitize' => array('strip_tags', 'sanitize_text_field')
			),
			'question_id' => array(
				'sanitize' => array('only_int')
			),
			'edit_post_id' => array(
				'sanitize' => array('only_int')
			),
		);

		/**
		 * FILTER: ap_answer_fields_validation
		 * Filter can be used to modify answer form fields.
		 * @var void
		 * @since 2.0
		 */
		$args = apply_filters( 'ap_answer_fields_validation', $args );

		$validate = new AnsPress_Validation($args);

		$ap_errors = $validate->get_errors();
		
		// if error in form then return
		if($validate->have_error()){
			$this->result = $ap_errors;
			return;
		}

		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if(!empty($fields['edit_post_id'])){
			//$this->edit_question();
			return;
		}


		$user_id = get_current_user_id();

		$status = 'publish';
		
		if(isset($fields['is_private']) && $fields['is_private'])
			$status = 'private_answer';
			
		$question_array = array(
			'post_title'	=> $fields['title'],
			'post_author'	=> $user_id,
			'post_content' 	=>  wp_kses($fields['description'], ap_form_allowed_tags()),
			'post_type' 	=> 'question',
			'post_status' 	=> $status,
			'comment_status' => 'open',
		);
		
		if(isset($fields['parent_id']))
			$question_array['post_parent'] = (int)$fields['parent_id'];

		/**
		 * FILTER: ap_pre_insert_question
		 * Can be used to modify args before inserting question
		 * @var array
		 * @since 2.0
		 */
		$question_array = apply_filters('ap_pre_insert_question', $question_array );

		$post_id = wp_insert_post($question_array);

		if($post_id){
			
			// Update Custom Meta
			
			/**
			 * TODO: EXTENSTION - move to tags extension
			 */
			if(isset($fields['tags']))
				wp_set_post_terms( $post_id, $fields['tags'], 'question_tags' );
				
			if (!is_user_logged_in() && ap_opt('allow_anonymous') && !empty($fields['name']))
				update_post_meta($post_id, 'anonymous_name', $fields['name']);
			
			
			$this->redirect =  get_permalink($post_id);

			$this->result = apply_filters('ap_ajax_question_submit_result', 
				array(
					'action' 		=> 'new_question',
					'message'		=> __('Question submitted successfully', 'ap'),
					'redirect_to'	=> get_permalink($post_id)
				)
			);
		}
	}
}
    
?>