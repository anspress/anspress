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
	/**
	 * Initialize the class
	 */
	public function __construct()
	{
		add_action('init', array($this, 'process_form'));
	}

	/**
	 * Process form based on action value
	 * @return void
	 * @since 2.0
	 */
	public function process_form()
	{
		//return if ap_form_action is not set, probably its not our form
		if(!isset($_POST['ap_form_action']))
			return;

		$action = sanitize_text_field($_POST['ap_form_action']);
		switch ($action) {
			case 'ask_form':
				$this->process_ask_form();
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
		global $ap_errors;

		// Do security check, if fails then return
		if(!ap_user_can_ask() || !isset($_POST['__nonce']) || !wp_verify_nonce($_POST['__nonce'], 'ask_form'))
			return;

		$args = array(
			'title' => array(
				'sanitize' => array('sanitize_text_field'),
				'validate' => array('required' => true, 'length_check' => ap_opt('minimum_qtitle_length'))
			),
			'description' => array(
				'validate' => array('required' => true, 'length_check' => ap_opt('minimum_question_length'))
			),
			'is_private' => array(
				'sanitize' => array('only_boolean')
			),
			'parent' => array(
				'sanitize' => array('only_int')
			),
		);

		/**
		 * FILTER: ap_ask_from
		 * Filter can be used to modify ask question fields.
		 * @var void
		 * @since 2.0
		 */
		$args = apply_filters( 'ap_ask_from', $args );

		$validate = new AnsPress_Validation($args);

		$ap_errors = $validate->get_errors();
		
		if($validate->have_error()){
			if(ap_is_ajax())
				wp_send_json( $ap_errors );

			return;
		}

		$fields = $validate->get_sanitized_fields();

		$user_id = get_current_user_id();

		$status = 'publish';
		
		if(ap_opt('moderate_new_question') == 'pending' || (ap_opt('moderate_new_question') == 'point' && ap_get_points($user_id) < ap_opt('mod_question_point')))
			$status = 'moderate';
		
		if(isset($fields['private_question']) && $fields['private_question'])
			$status = 'private_question';
			
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
			if(isset($fields['category']))
				wp_set_post_terms( $post_id, $fields['category'], 'question_category' );
				
			if(isset($fields['tags']))
				wp_set_post_terms( $post_id, $fields['tags'], 'question_tags' );
				
			if (ap_opt('allow_anonymous') && isset($fields['name']))
				update_post_meta($post_id, 'anonymous_name', $fields['name']);
			
			if($_POST['action'] == 'ap_submit_question'){
				$result = apply_filters('ap_ajax_question_submit_result', 
					array(
						'action' 		=> 'new_question',
						'message'		=> __('Question submitted successfully', 'ap'),
						'redirect_to'	=> get_permalink($post_id)
					)
				);
				
				return json_encode($result) ;
			}else{
				// Redirect
				wp_redirect( get_permalink($post_id) ); exit;
			}
		}


	}
}
    
?>