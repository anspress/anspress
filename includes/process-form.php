<?php
/**
 * AnsPress process form
 * @link http://wp3.in
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
	public function __construct()
	{

		add_action('init', array($this, 'non_ajax_form'));
		add_action( 'save_post', array($this, 'action_on_new_post'), 10, 3 );
		add_action('wp_ajax_ap_ajax', array($this, 'ap_ajax'));
		add_action('wp_ajax_nopriv_ap_ajax', array($this, 'ap_ajax'));
	}

	/**
	 * for non ajax form
	 * @return void
	 */
	public function non_ajax_form()
	{
		//return if ap_form_action is not set, probably its not our form
		if(!isset($_REQUEST['ap_form_action']) || isset($_REQUEST['ap_ajax_action']))
			return;

		$this->request = $_REQUEST;
		$this->process_form();

		if(!empty($this->redirect)){
			wp_redirect( $this->redirect );
			exit;
		}
	}

	/**
     * Handle all anspress ajax requests 
     * @return void
     * @since 2.0.1
     */
    public function ap_ajax()
    {

    	if(!isset($_REQUEST['ap_ajax_action']))
    		return;
    	
    	$this->request = $_REQUEST;

    	if(isset($_POST['ap_form_action'])){
	    	$this->is_ajax = true;
	    	$this->process_form();
			ap_send_json( ap_ajax_responce( $this->result ) );
		}else{
			$action = sanitize_text_field($this->request['ap_ajax_action']);

	    	/**
	    	 * ACTION: ap_ajax_[$action]
	    	 * Action for processing Ajax requests
	    	 * @since 2.0.1
	    	 */
	    	do_action('ap_ajax_'.$action);
		}
    }


	/**
	 * Process form based on action value
	 * @return void
	 * @since 2.0.1
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

			case 'comment_form':
				$this->comment_form();
				break;
			
			default:
				/**
				 * ACTION: ap_process_form_[action]
				 * process form
				 * @since 2.0.1
				 */
				do_action('ap_process_form_'.$action);
				break;
		}

	}

	public function check_recaptcha()
	{
		$recaptcha = new \ReCaptcha\ReCaptcha(ap_opt('recaptcha_secret_key'));
		$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
		
		if ($resp->isSuccess())
			return true;

		return false;
	}

	/**
	 * Process ask form
	 * @return void
	 * @since 2.0.1
	 */
	public function process_ask_form()
	{
		global $ap_errors, $validate;

		if(ap_opt('enable_recaptcha') && !$this->check_recaptcha()){
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message'		=> 'captcha_error',
				'errors'		=> array('captcha' => __('Bot verification failed.', 'ap'))
			);
			return;
		}

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
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_ask_fields_validation', $args );

		$validate = new AnsPress_Validation($args);

		$ap_errors = $validate->get_errors();
		
		// if error in form then return
		if($validate->have_error()){
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message_type' 	=> 'error',
				'message'		=> __('Check missing fields and then re-submit.', 'ap'),
				'errors'		=> $ap_errors
			);
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
		
		if(ap_opt('moderate_new_question') == 'pending' || (ap_opt('moderate_new_question') == 'reputation' && ap_get_points($user_id) < ap_opt('mod_question_point')))
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
		 * @since 2.0.1
		 */
		$question_array = apply_filters('ap_pre_insert_question', $question_array );

		$post_id = wp_insert_post($question_array);

		if($post_id){
			
			// Update Custom Meta
			
			/**
			 * TODO: EXTENSTION - move to tags extension
			 */
			//if(isset($fields['tags']))
				//wp_set_post_terms( $post_id, $fields['tags'], 'question_tags' );
				
			if (!is_user_logged_in() && ap_opt('allow_anonymous') && !empty($fields['name']))
				update_post_meta($post_id, 'anonymous_name', $fields['name']);
			
			
			$this->redirect =  get_permalink($post_id);

			$this->result = array(
				'action' 		=> 'new_question',
				'message'		=> 'question_submitted',
				'redirect_to'	=> get_permalink($post_id),
				'do'			=> 'redirect'
			);
		}


	}

	/**
	 * Process edit question form
	 * @return void
	 * @since 2.0.1
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
		 * @since 2.0.1
		 */
		$question_array = apply_filters('ap_pre_update_question', $question_array );

		$post_id = wp_update_post($question_array);

		if($post_id){				
			
			$this->redirect = get_permalink($post_id);

			$this->result = array(
				'action' 		=> 'edited_question',
				'message'		=> 'question_updated',
				'do'			=> 'redirect',
				'redirect_to'	=> $this->redirect
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
	public function action_on_new_post( $post_id, $post, $update ) {

		// return on autosave
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
		
		if ( wp_is_post_revision( $post_id ) || $post->post_status == 'trash'|| $post->post_status == 'auto-draft')
			return;
		
		$updated = get_post_meta($post_id, ANSPRESS_UPDATED_META, true);
		
		if ( $post->post_type == 'question' ) {
			//check if post have updated meta, if not this is a new post :D
			
			if($updated == '' ){
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
			
			if( $updated == ''){
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

		if(ap_opt('enable_recaptcha') && !$this->check_recaptcha()){
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message'		=> 'captcha_error',
				'errors'		=> array('captcha' => __('Bot verification failed.', 'ap'))
			);
			return;
		}

		$question = get_post((int)$_POST['form_question_id']);

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
			'form_question_id' => array(
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
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_answer_fields_validation', $args );

		$validate = new AnsPress_Validation($args);

		$ap_errors = $validate->get_errors();
		
		// if error in form then return
		if($validate->have_error()){
			$this->result = array(
				'form' 			=> $_POST['ap_form_action'],
				'message_type' 	=> 'error',
				'message'		=> __('Check missing fields and then re-submit.', 'ap'),
				'errors'		=> $ap_errors
			);
			return;
		}

		$fields = $validate->get_sanitized_fields();
		$this->fields = $fields;

		if(!empty($fields['edit_post_id'])){
			$this->edit_answer($question);
			return;
		}


		$user_id = get_current_user_id();

		/**
		 * TODO: ADD - moderate for answers too
		 */
		$status = 'publish';
		
		if($fields['is_private'])
			$status = 'private_post';
			
		$answer_array = array(
			'post_title'	=> $question->post_title,
			'post_author'	=> $user_id,
			'post_content' 	=>  $fields['description'],
			'post_parent' 	=>  $question->ID,
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
		$answer_array = apply_filters('ap_pre_insert_answer', $answer_array );

		$post_id = wp_insert_post($answer_array);

		if($post_id){				
			// get existing answer count
			$current_ans = ap_count_published_answers($question->ID);

			if (!is_user_logged_in() && ap_opt('allow_anonymous') && isset($fields['name']))
				update_post_meta($post_id, 'anonymous_name', $fields['name']);
			

			if($this->is_ajax){
				

				if($current_ans == 1){
					global $post;
					$post = $question;
					setup_postdata($post);
				}else{
					global $post;
					$post = get_post($post_id);
					setup_postdata($post);
				}
				
				ob_start();
				if($current_ans == 1)								
					ap_get_answers(array('question_id' => $question->ID));
				else
					include(ap_get_theme_location('answer.php'));
				
				$html = ob_get_clean();
				
				$count_label = sprintf( _n('1 Answer', '%d Answers', $current_ans, 'ap'), $current_ans);
				
				$result = array(
					'postid' 		=> $post_id, 
					'action' 		=> 'new_answer',
					'div_id' 		=> '#answer_'.get_the_ID(),
					'can_answer' 	=> ap_user_can_answer($post->ID),
					'html' 			=> $html,
					'message' 		=> 'answer_submitted',
					'do' 			=> 'clearForm',
					'view'			=> array('answer_count' => $current_ans, 'answer_count_label' => $count_label)
				);
				
				$this->result = $result;
				
			}
		}
	}

	public function edit_answer($question)
	{
		global $ap_errors, $validate;
		
		// return if user do not have permission to edit this answer
		if( !ap_user_can_edit_ans($this->fields['edit_post_id']))
			return;
		
		$answer = get_post($this->fields['edit_post_id']);
		
		$status = 'publish';

		if($this->fields['is_private'])
			$status = 'private_post';

		$answer_array = array(
			'ID'			=> $this->fields['edit_post_id'],
			'post_content' 	=> $this->fields['description'],
			'post_status' 	=> $status
		);

		$answer_array = apply_filters( 'ap_pre_update_answer', $answer_array );
		
		$post_id = wp_update_post($answer_array);
		
		if($post_id){					
			if($this->is_ajax){
				$this->result = array(
					'action' 		=> 'answer_edited',
					'message'		=> 'answer_updated',
					'redirect_to'	=> get_permalink($answer->post_parent),
					'do'			=> 'redirect'
				);
			}

			$this->redirect = get_permalink($post_id);
		}
	}

	public function comment_form()
	{
		if(empty($_POST['comment'])){
			$this->result = ap_ajax_responce( 'comment_content_empty');
			return;
		}

		if(!isset($_REQUEST['comment_ID']) ){
			// Do security check
			if(!ap_user_can_comment() || !isset($_POST['__nonce']) || !wp_verify_nonce($_POST['__nonce'], 'comment_' . (int)$_POST['comment_post_ID'])){
				$this->result = ap_ajax_responce( 'no_permission');
				return;
			}
		}else{
			if(!ap_user_can_edit_comment((int)$_REQUEST['comment_ID'] ) || !wp_verify_nonce( $_REQUEST['__nonce'], 'comment_'.(int)$_REQUEST['comment_ID'] )){
				$this->result = ap_ajax_responce( 'no_permission');
				return;
			}			
		}

		$comment_post_ID = (int) $_POST['comment_post_ID'];
		$post = get_post( $comment_post_ID );

		if(!$post || empty( $post->post_status ))
			return;

		if ( in_array($post->post_status, array('draft', 'pending', 'trash') ) ){
			$this->result = ap_ajax_responce('draft_comment_not_allowed');

			return;
		}

		if ( isset( $_POST['comment_ID'] ) ){
			$comment_id = wp_update_comment( array('comment_ID' => (int)$_POST['comment_ID'], 'comment_content' => trim( $_POST['comment'] )) );
			$comment = get_comment($comment_id);
			ob_start();
			ap_comment($comment);		
			$html = ob_get_clean();
			$this->result = ap_ajax_responce(  array( 'action' => 'edit_comment', 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => 'comment_edit_success'));

			return;
		}else{
			$user = wp_get_current_user();
			if ( $user->exists() ) {
				$user_ID = $user->ID;
				$comment_author = wp_slash( $user->display_name );
				$comment_author_email = wp_slash( $user->user_email );
				$comment_author_url = wp_slash( $user->user_url );
				$comment_content = trim( $_POST['comment'] );
				$comment_type = 'anspress';

			} else {
				$this->result = ap_ajax_responce('no_permission');
				return;
			}

			$comment_parent = 0;

			if ( isset( $_POST['comment_ID'] ) )
				$comment_parent = absint( $_POST['comment_ID'] );

			$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

			// Automatically approve parent comment.
			if ( !empty($_POST['approve_parent']) ) {
				$parent = get_comment( $comment_parent );
				if ( $parent && $parent->comment_approved === '0' && $parent->comment_post_ID == $comment_post_ID ) {
					if ( wp_set_comment_status( $parent->comment_ID, 'approve' ) )
						$comment_auto_approved = true;
				}
			}

			$comment_id = wp_new_comment( $commentdata );

			if($comment_id > 0){
				$comment = get_comment($comment_id);
				do_action( 'ap_after_new_comment', $comment );
				ob_start();
				ap_comment($comment);		
				$html = ob_get_clean();
				$count = get_comment_count( $comment->comment_post_ID );
				$this->result = ap_ajax_responce(  array( 'action' => 'new_comment', 'status' => true, 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => 'comment_success', 'view' => array('comments_count_'.$comment->comment_post_ID => $count['approved'], 'comment_count_label_'.$comment->comment_post_ID => sprintf(_n('One comment', '%d comments', $count['approved'], 'ap'), $count['approved']) )));
			}else{
				$this->result = ap_ajax_responce('something_wrong');
			}

		}
	}
}
