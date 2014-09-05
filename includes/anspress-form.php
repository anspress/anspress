<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

class anspress_form
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;
    /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
		add_action( 'init', array($this, 'process_forms') );
		//add_action('comment_form', array($this, 'comment_button') );
		add_action( 'wp_ajax_ap_load_comment_form', array($this, 'load_ajax_commentform') ); 
		add_action( 'wp_ajax_nopriv_ap_load_comment_form', array($this, 'load_ajax_commentform') );
		
		// Send all comment submissions through my "ajaxComment" method
		add_action('comment_post', array($this, 'save_comment'), 20, 2);
	
		add_action( 'wp_ajax_ap_update_comment', array($this, 'update_comment_form') ); 
		add_action( 'wp_ajax_ap_delete_comment', array($this, 'delete_comment') ); 
		
		add_action( 'wp_ajax_nopriv_ap_not_logged_in_messgae', array($this, 'ap_not_logged_in_messgae') ); 
		add_action( 'wp_ajax_ap_edit_comment_form', array($this, 'edit_comment_form') ); 
		
		add_action( 'wp_ajax_ap_submit_question', array($this, 'ajax_question_submit') ); 
		add_action( 'wp_ajax_nopriv_ap_submit_question', array($this, 'ajax_question_submit') ); 
		
		add_action( 'wp_ajax_ap_submit_answer', array($this, 'ajax_answer_submit') ); 
		add_action( 'wp_ajax_nopriv_ap_submit_answer', array($this, 'ajax_answer_submit') ); 
				
		add_action('wp_insert_comment', array($this, 'comment_inserted'), 99, 2);
		add_action('pre_comment_approved', array($this, 'pre_comment_approved'), 99, 2);
		
		// add ask form fields
		add_action('ap_ask_form_fields', array($this, 'ask_from_title_field'));
		add_action('ap_ask_form_fields', array($this, 'ask_from_content_field'));
		add_action('ap_ask_form_fields', array($this, 'ask_from_category_field'));
		add_action('ap_ask_form_fields', array($this, 'ask_from_tags_field'));
		
		// edit question form fields
		add_action('ap_edit_question_form_fields', array($this, 'edit_question_from_title_field'), 10, 2);
		add_action('ap_edit_question_form_fields', array($this, 'edit_question_from_content_field'), 10, 2);
		add_action('ap_edit_question_form_fields', array($this, 'edit_question_from_category_field'), 10, 2);
		add_action('ap_edit_question_form_fields', array($this, 'edit_question_from_tags_field'), 10, 2);
		
		add_action('ap_answer_fields', array($this, 'answer_from_content_field'), 10, 2);
		add_action('ap_edit_answer_fields', array($this, 'edit_answer_from_content_field'), 10, 2);
		
		add_filter('ap_save_question_filds', array($this, 'signup_fields'));
		add_filter('ap_save_answer_filds', array($this, 'signup_fields'));		
		
		//add_action( 'wp_ajax_ap_toggle_login_signup', array($this, 'ap_toggle_login_signup') ); 
		add_action( 'wp_ajax_nopriv_ap_toggle_login_signup', array($this, 'ap_toggle_login_signup') ); 
		
		//add_filter( 'ap_question_form_validation', array($this, 'ap_signup_login_validation') ); 
		 
		//add_filter( 'ap_answer_form_validation', array($this, 'ap_signup_login_validation') );
		
		add_action( 'wp_ajax_ap_delete_post', array($this, 'ap_delete_post') ); 
		
		add_action( 'wp_ajax_ap_load_edit_form', array($this, 'ap_load_edit_form') );
		add_action('ap_after_ask_form', array($this, 'login_signup_modal'));
		add_action('ap_after_answer_form', array($this, 'login_signup_modal'));
		add_action('ap_ask_form_bottom', array($this, 'login_bottom'));
		add_action('ap_answer_form_bottom', array($this, 'login_bottom'));
		
		add_action( 'wp_ajax_nopriv_ap_ajax_login', array($this, 'ap_ajax_login') );
		add_action( 'wp_ajax_nopriv_ap_ajax_signup', array($this, 'ap_ajax_signup') );
    }
	
	public function process_forms(){
		/* 
		*	check if its a ajax posting, if yes do not do from here. 
		*	else it will create multiple posts 
		*/
		if(isset($_POST['action']) && $_POST['action'] != 'ap_submit_question'){
			$this->process_ask_form();
			$this->process_edit_question_form();
		}
		
		if(isset($_POST['action']) && $_POST['action'] != 'ap_submit_answer'){
			$this->process_answer_form();
			$this->process_edit_answer_form();
		}	

	}
	
	public function get_question_fields_to_process(){
		$fields = array(
			'post_title' 	=> sanitize_text_field($_POST['post_title']),
			'post_content' 	=>  $_POST['post_content'],
		);
		
		//remove <!--more-->
		
		$fields['post_content'] = str_replace('<!--more-->', '', $fields['post_content']);
		$fields['post_content'] = str_replace('<!-- more -->', '', $fields['post_content']);
		
		if(isset($_POST['category']))
			$fields['category']	= sanitize_text_field($_POST['category']);
		
		if(isset($_POST['tags']))
			$fields['tags']	= sanitize_text_field($_POST['tags']);
		
		return apply_filters('ap_save_question_filds', $fields);
		
	}
	
	public function validate_question_form(){
		global $ap_question_form_validation;
		$error = array();
		$error['has_error'] 	= false;

		if(strlen(utf8_decode($_POST['post_title'])) < ap_opt('minimum_qtitle_length')){
			$error['post_title'] 	= sprintf(__('Question title must have %d letters or more.', 'ap'), ap_opt('minimum_qtitle_length'));
			$error['has_error'] 	= true;
		}
		if(strlen(utf8_decode($_POST['post_content'])) < ap_opt('minimum_question_length')){
			$error['post_content'] 	= sprintf(__('Question content must have %d letters or more.', 'ap'), ap_opt('minimum_question_length'));
			$error['has_error'] 	= true;
		}
		
		if(ap_opt('enable_categories') && $_POST['category'] === ''){
			$error['category'] 	= __('You must select a category.', 'ap');
			$error['has_error'] 	= true;
		}
		if(ap_opt('enable_tags') && $_POST['tags'] === ''){
			$error['tags'] 	= __('You must add at least one tag.', 'ap');
			$error['has_error'] 	= true;
		}
		
		if(ap_opt('enable_tags') && $_POST['tags'] !=='' ){
			$tags = explode(',', $_POST['tags']);
			if(count($tags) > ap_opt('max_tags')){
				$error['tags'] 	= sprintf(__('Maximum tags limit for a question is %d', 'ap'), ap_opt('max_tags'));
				$error['has_error'] 	= true;
			}
		}
		
		$ap_question_form_validation = $error;
		
		$ap_question_form_validation = apply_filters('ap_question_form_validation', $ap_question_form_validation);
		
		return $ap_question_form_validation;
	}
	
	public function process_ask_form(){
		if(!is_user_logged_in())
			return false;
		
		if(isset($_POST['is_question']) && isset($_POST['submitted']) && isset($_POST['ask_form']) && wp_verify_nonce($_POST['ask_form'], 'post_nonce')) {
		
			$fields = $this->get_question_fields_to_process();
			
			if(!ap_user_can_ask())
				return;
			
			$validate = $this->validate_question_form();
			if($validate['has_error']){
				if($_POST['action'] == 'ap_submit_question'){
					$result = array(
								'action' 		=> 'validation_falied',								
								'message' 		=> __('Question not submitted, please check the form fields.','ap'),
								'error' => $validate
							);
					return json_encode($result) ;
				}
				
				return;
			}

			do_action('process_ask_form');			
			$user_id = get_current_user_id();			
			$status = 'publish';
			
			if(ap_opt('moderate_new_question') == 'pending' || (ap_opt('moderate_new_question') == 'point' && ap_get_points($user_id) < ap_opt('mod_question_point')))
				$status = 'moderate';
				
			$question_array = array(
				'post_title'	=> $fields['post_title'],
				'post_author'	=> $user_id,
				'post_content' 	=>  wp_kses($fields['post_content'], ap_form_allowed_tags()),
				'post_type' 	=> 'question',
				'post_status' 	=> $status
			);

			$post_id = wp_insert_post($question_array);
			
			if($post_id){
				
				// Update Custom Meta
				if(isset($fields['category']))
					wp_set_post_terms( $post_id, $fields['category'], 'question_category' );
				if(isset($fields['tags']))
					wp_set_post_terms( $post_id, $fields['tags'], 'question_tags' );
					
				update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
				update_post_meta($post_id, ANSPRESS_FAV_META, '0');
				update_post_meta($post_id, ANSPRESS_CLOSE_META, '0');
				update_post_meta($post_id, ANSPRESS_FLAG_META, '0');
				update_post_meta($post_id, ANSPRESS_VIEW_META, '0');
				update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				update_post_meta($post_id, ANSPRESS_SELECTED_META, false);
				
				//ap_add_history($user_id, $post_id, 'asked');
				ap_add_parti($post_id, $user_id, 'question');
				
				//update answer count
				update_post_meta($post_id, ANSPRESS_ANS_META, '0');

				do_action('ap_after_inserting_question', $post_id);
				ap_do_event('new_question', $post_id, $user_id);
				
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
	
	public function get_answer_fields_to_process(){
		$fields = array(			
			'is_answer' 	=> sanitize_text_field($_POST['is_answer']),
			'submitted' 	=> sanitize_text_field($_POST['submitted']),
			'nonce' 		=> $_POST['nonce'],
			'post_content' 		=> $_POST['post_content']
		);
		$fields['post_content'] = str_replace('<!--more-->', '', $fields['post_content']);
		$fields['post_content'] = str_replace('<!-- more -->', '', $fields['post_content']);
		
		if(isset($_POST['form_question_id']))
			$fields['question_id'] 	= sanitize_text_field($_POST['form_question_id']);
		
		return apply_filters('ap_save_answer_filds', $fields);
		
	}
	
	public function validate_ans_form(){
		global $ap_answer_form_validation;
		$error = array();
		$error['has_error'] 	= false;

		if(strlen(utf8_decode($_POST['post_content'])) < ap_opt('minimum_ans_length')){
			$error['post_content'] 	= sprintf(__('Your answer must have %d letters or more.', 'ap'), ap_opt('minimum_ans_length'));
			$error['has_error'] 	= true;
		}
		$ap_answer_form_validation = $error;
		
		$ap_answer_form_validation = apply_filters('ap_answer_form_validation', $ap_answer_form_validation);
		
		return $ap_answer_form_validation;
	}
	
	public function process_answer_form( ){	
		if(!is_user_logged_in())
			return false;
			
		if(isset($_POST['is_answer']) && isset($_POST['submitted']) && isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'post_nonce_'.$_POST['form_question_id'])) {
			$fields = $this->get_answer_fields_to_process();	
			
			$validate = $this->validate_ans_form();
			
			if($validate['has_error']){
				if($_POST['action'] == 'ap_submit_answer'){
					$result = array(
								'action' 		=> 'validation_falied',								
								'message' 		=> __('Answer not submitted, please check the form fields.','ap'),
								'error' => $validate
							);
					return json_encode($result) ;
				}
				
				return;
			}
			
			if(!isset($fields['question_id']) && (!is_int($fields['question_id'])) && ('question' !== get_post_type( $fields['question_id'] )))
				return;
			
			$question = get_post( $fields['question_id'] );
			
			if(!ap_user_can_answer($question->ID) )
				return;
			
			do_action('process_answer_form');
			
			$logged_in = false;
			
			$user_id = get_current_user_id();			
			$ans_array = array(
				'post_author'	=> $user_id,
				'post_content' 	=> wp_kses($fields['post_content'], ap_form_allowed_tags()),
				'post_type' 	=> 'answer',
				'post_status' 	=> 'publish',
				'post_parent' 	=> $question->ID
			);

			$post_id = wp_insert_post($ans_array);
			
			if($post_id){				
				
				// set default value for meta
				update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
				
				// set updated meta for sorting purpose
				update_post_meta($question->ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				
				ap_add_parti($question->ID, $user_id, 'answer');			
				
				// get existing answer count
				$current_ans = ap_count_ans($question->ID);
				
				//update answer count
				update_post_meta($question->ID, ANSPRESS_ANS_META, $current_ans);
				
				update_post_meta($post_id, ANSPRESS_BEST_META, 0);
				
				// redirect if just logged in
				if($logged_in && $_POST['action'] != 'ap_submit_answer'){
					wp_redirect( get_permalink($question->ID) ); exit;
				}
				
				$result = array();
				
				if($_POST['action'] == 'ap_submit_answer'){

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
						ap_answers_list($post->ID, 'voted');
					else
						include(ap_get_theme_location('answer.php'));
					
					$html = ob_get_clean();
					
					$count_label = sprintf( _n('1 Answer', '%d Answers', $current_ans, 'ap'), $current_ans);
					
					$result = apply_filters('ap_ajax_answer_submit_result', 
						array(
							'postid' 		=> $post_id, 
							'action' 		=> 'new_answer',
							'div_id' 		=> '#answer_'.get_the_ID(),
							'count' 		=> $current_ans,
							'count_label' 	=> $count_label,
							'can_answer' 	=> ap_user_can_answer($post->ID),
							'html' 			=> $html,
							'message' 		=> __('Answer submitted successfully!','ap')
						)
					);
					
					if($logged_in)
						$result['redirect_to'] = get_permalink($post->ID);
				}
				do_action('ap_after_inserting_answer', $post_id);
				ap_do_event('new_answer', $post_id, $user_id, $question->ID, $result);
				
				if($_POST['action'] == 'ap_submit_answer')
					return json_encode($result) ;
			}
		}elseif($_POST['action'] == 'ap_submit_answer'){
			$result = array('postid' => $post_id, 'action' => false, 'message' => __('Please try again, answer submission failed!','ap'));
			return json_encode($result) ;
		}
		
	}
	
	public function process_edit_question_form(){
		
		if(isset($_POST['is_question']) && isset($_POST['submitted']) && isset($_POST['edited']) && wp_verify_nonce($_POST['edit_question'], 'post_nonce-'.$_POST['question_id'])) {
			
			$post_id = sanitize_text_field($_POST['question_id']);
			$fields = $this->get_question_fields_to_process();
			
			$post = get_post($post_id);
			
			if( !ap_user_can_edit_question($post->ID))
				return;
			
			$validate = $this->validate_question_form();
			if($validate['has_error']){
				if($_POST['action'] == 'ap_submit_question'){
					$result = array(
								'action' 		=> 'validation_falied',								
								'message' 		=> __('Question not updated, please check the form fields.','ap'),
								'error' => $validate
							);
					return json_encode($result) ;
				}
				
				return;
			}
			
			do_action('process_ask_form');
			
			$user_id = get_current_user_id();
			$question_array = array(
				'ID'			=> $post_id,
				'post_title'	=> sanitize_text_field($fields['post_title']),
				'post_name'		=> sanitize_title($fields['post_title']),
				'post_content' 	=> wp_kses($fields['post_content'], ap_form_allowed_tags()),
				'post_status' 	=> 'publish'
			);

			$post_id = wp_update_post($question_array);
			
			if($post_id){				
				// Update Custom Meta
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['category']), 'question_category' );
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['tags']), 'question_tags' );
				
				// set updated meta for sorting purpose
				update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				
				do_action('ap_after_editing_question', $post_id);
				ap_do_event('edit_question', $post_id, $user_id);
				
				if($_POST['action'] == 'ap_submit_question'){
					$result = apply_filters('ap_ajax_edit_question_submit_result', 
						array(
							'action' 		=> 'edited_question',
							'message'		=> __('Question updated successfully', 'ap'),
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
	
	public function process_edit_answer_form(){		
		if(isset($_POST['is_answer']) && isset($_POST['submitted']) && isset($_POST['edited']) && wp_verify_nonce($_POST['nonce'], 'post_nonce-'.$_POST['answer_id'])) {
			$fields = $this->get_answer_fields_to_process();	

			$validate = $this->validate_ans_form();
			
			if($validate['has_error']){
				if($_POST['action'] == 'ap_submit_answer'){
					$result = array(
								'action' 		=> 'validation_falied',								
								'message' 		=> __('Answer not updated, please check the form fields.','ap'),
								'error' => $validate
							);
					return json_encode($result) ;
				}
				
				return;
			}
			
			$post_id = sanitize_text_field($_POST['answer_id']);
			
			$post = get_post($post_id);
			
			if( !ap_user_can_edit_ans($post->ID)){
				if($_POST['action'] == 'ap_submit_answer'){
					$result = array(
								'action' 		=> 'false',								
								'message' 		=> __('You do not have permission to edit this answer.','ap')
							);
					return json_encode($result) ;
				}
				return;
			}
			
			global $current_user;
			$user_id		= $current_user->ID;
			
			$answer_array = array(
				'ID'			=> $post_id,
				//'post_author'	=> $user_id,
				'post_content' 	=>  wp_kses($fields['post_content'], ap_form_allowed_tags()),
				'post_status' 	=> 'publish'
			);

			$post_id = wp_update_post($answer_array);
			
			if($post_id){					
				// set updated meta for sorting purpose
				update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

				do_action('ap_after_editing_answer', $post_id);
				ap_do_event('edit_answer', $post_id, $user_id, $post->post_parent);
				if($_POST['action'] == 'ap_submit_answer'){
					$result = apply_filters('ap_ajax_answer_edit_result', 
						array(
							'action' 		=> 'answer_edited',
							'message'		=> __('Answer updated successfully', 'ap'),
							'redirect_to'	=> get_permalink($post->post_parent)
						)
					);
					
					return json_encode($result) ;
				}else{
					// Redirect
					wp_redirect( get_permalink($post->post_parent) ); exit;
				}
			}
		}
	}
	
    public function comment_button($post_id) {
		$post_type = get_post_type( $post_id );
		if($post_type == 'question' || $post_type =='answer')
			echo '<div class="ap-comment-sc"><button class="ap-btn-comment ap-btn ap-btn-small" type="submit">' . __( 'Submit' ) . '</button></div>';
    }
	public function load_ajax_commentform(){
		if(!ap_user_can_comment()){
			_e('No Permission', 'ap');
			die();
		}
		
		$args = explode('-', sanitize_text_field($_REQUEST['args']));
		$action = get_post_type($args[0]).'-'.$args[0];	
		if(wp_verify_nonce( $args[1], $action )){						
			$comment_args = array(
				'title_reply' => '',
				'logged_in_as' => '',
				'comment_field' => '<div class="ap-comment-ta"><textarea name="comment" rows="3" aria-required="true" class="form-control autogrow" placeholder="'.__('Respond to the post.', 'ap').'"></textarea></div><input type="hidden" name="ap_comment_form" value="true"/>',
				'comment_notes_after' => ''
			);
			$current_user = get_userdata( get_current_user_id() );
			echo '<div class="comment-form-c clearfix">';
				echo '<div class="ap-content-inner">';
					comment_form($comment_args, $args[0] );
				echo '</div>';
			echo '</div>';

		}
		die();
	}
	
	public function save_comment($comment_ID, $comment_status){
		// If it's an AJAX-submitted comment
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $_REQUEST['ap_comment_form']){
			// Get the comment data
			$comment = get_comment($comment_ID);
			// Allow the email to the author to be sent
			wp_notify_postauthor($comment_ID, $comment->comment_type);
			// Get the comment HTML from my custom comment HTML function

			ob_start();
			ap_comment($comment);		
			$html = ob_get_clean();
			
			$result = json_encode(array('status' => true, 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => __('Comment submitted successfully', 'ap')));
			
			echo $result;
			die();
		}
	}
	
	public function edit_comment_form(){
		$args = explode('-', sanitize_text_field($_REQUEST['args']));
		
		if(!ap_user_can_edit_comment($args[0])){
			_e('No Permission', 'ap');
			die();
		}		
		
		$action = 'comment-'.$args[0];	
		if(wp_verify_nonce( $args[1], $action )){
			$comment = get_comment( $args[0] );
			echo '<form id="edit-comment-'. $args[0].'" class="inline-edit-comment" data-action="ap-edit-comment">';
			echo '<textarea class="form-control" name="content">'.$comment->comment_content.'</textarea>';
			echo '<button class="btn btn-default" data-action="save-inline-comment" data-elem="#edit-comment-'. $args[0].'">'.__('Save', 'ap').'</button>';
			echo '<input type="hidden" name="comment_id" value="'.$args[0].'"/>';
			echo '<input type="hidden" name="ap_comment_form" value="true"/>';
			wp_nonce_field('save-comment-'.$args[0], 'nonce');
			echo '</form>';
		}
		die();
	}
	
	public function update_comment_form(){
		$args = wp_parse_args($_REQUEST['args']);
		$comment_id = sanitize_text_field($args['comment_id']);
		if(!ap_user_can_edit_comment($comment_id)){
			$result = json_encode(array('status' => false, 'message' => __('You do not ahve permission to edit this comment.', 'ap')));
			die($result);
		}		
		$action = 'save-comment-'.$comment_id;	
		
		if(wp_verify_nonce( $args['nonce'], $action )){
			$comment_data = array(
				'comment_ID' => $comment_id,
				'comment_content' => wp_kses($args['content'], ap_form_allowed_tags()),
			);
			$comment_saved = wp_update_comment( $comment_data );
			if($comment_saved){
				$comment = get_comment( $args['comment_id'] );
				ob_start();
				ap_comment($comment);
				$html = ob_get_clean();
				
				$result = json_encode(array('status' => true, 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => __('Comment updated successfully', 'ap')));
			}else{
				$result = json_encode(array('status' => false, 'message' => __('Comment not updated, please retry', 'ap')));
			}
		}else{
			$result = json_encode(array('status' => false, 'message' => __('Comment not updated, please retry', 'ap')));
		}
		die( $result);
	}
	
	public function delete_comment(){
		$args = $args = explode('-', sanitize_text_field($_REQUEST['args']));
		if(!ap_user_can_delete_comment($args[0])){
			$result = array('status' => false, 'message' => __('You do not have permission to delete this comment', 'ap'));
			
			die(json_encode($result));
		}		
		$action = 'delete-comment-'.$args[0];		
		if(wp_verify_nonce( $args[1], $action )){
			wp_delete_comment( $args[0], true );
			$result = array('status' => true, 'message' => __('Comment deleted successfully', 'ap'));
		}
		die(json_encode($result));
	}
	
	public function ap_not_logged_in_messgae(){
		ap_please_login();
		die();
	}
	
	public function ajax_question_submit(){
		if(isset($_POST['is_question']) && isset($_POST['edited']))
			echo $this->process_edit_question_form( true );
		elseif(isset($_POST['is_question']))
			echo $this->process_ask_form( true );
		die();
	}
	
	public function ajax_answer_submit(){
		if(isset($_POST['is_answer']) && isset($_POST['edited']))
			echo $this->process_edit_answer_form( true );
		elseif(isset($_POST['is_answer']))
			echo $this->process_answer_form( true );
		die();
	}
	
	public function comment_inserted($comment_id, $comment_object) {
		if($comment_object->comment_approved =='1' ){
			$post = get_post( $comment_object->comment_post_ID );			
			
			if ($post->post_type == 'question') {
				ap_do_event('new_comment', $comment_object, 'question', '');
				// set updated meta for sorting purpose
				update_post_meta($comment_object->comment_post_ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				
				// add participant	
				ap_add_parti($comment_object->comment_post_ID, $comment_object->user_id, 'comment', $comment_id);
			}elseif($post->post_type == 'answer'){
				ap_do_event('new_comment', $comment_object, 'answer', $post->post_parent);
				$post_id = wp_get_post_parent_id($comment_object->comment_post_ID);
				// set updated meta for sorting purpose
				update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				
				// add participant only			
				ap_add_parti($post_id, $comment_object->user_id, 'comment', $comment_id);
			}
		}
	}
	
	public function pre_comment_approved($approved , $commentdata){
		if($approved =='1' ){
			$post_type = get_post_type( $commentdata->comment_post_ID );
			
			if ($post_type == 'question') {
				// set updated meta for sorting purpose
				update_post_meta($commentdata->comment_post_ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				
				// add participant	
				//ap_add_parti($commentdata->comment_post_ID, $commentdata->user_ID, 'comment');
					
			}elseif($post_type == 'answer'){
				$post_id = wp_get_post_parent_id($commentdata->comment_post_ID);
				// set updated meta for sorting purpose
				update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				// add participant only			
				//ap_add_parti($post_id, $commentdata->user_ID, 'comment');
			}
		}
	}
	
	public function ask_from_title_field($validate){
		?>
			<div class="form-group<?php echo isset($validate['post_title']) ? ' has-error' : ''; ?>">
				<label for="post_title" class="ap-form-label"><?php _e('Title', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="post_title" id="post_title" value="<?php echo sanitize_text_field(@$_POST['title']); ?>" class="form-control" placeholder="<?php _e('Question in one sentence', 'ap'); ?>" />
					<?php echo isset($validate['post_title']) ? '<span class="help-block">'. $validate['post_title'] .'</span>' : ''; ?>
				</div>
			</div>
		<?php
	}	
	
	public function ask_from_content_field($validate){
		?>
			<div class="form-group<?php echo isset($validate['post_content']) ? ' has-error' : ''; ?>">
				<?php 
					wp_editor( '', 'post_content', array('tinymce' => false, 'textarea_rows' => 7, 'media_buttons' => false, 'quicktags'=> array('buttons'=>'strong,em,link,blockquote,del,ul,li,ol,img,code,close'))); 
				?>
				<?php echo isset($validate['post_content']) ? '<span class="help-block">'. $validate['post_content'] .'</span>' : ''; ?>
			</div>
		<?php
	}	
	
	public function ask_from_category_field($validate){
		if(ap_opt('enable_categories')):
			?>
				<div class="form-group<?php echo isset($validate['category']) ? ' has-error' : ''; ?>">
					<label for="category"><?php _e('Category', 'ap') ?></label>
					<select class="form-control" name="category" id="category">
						<option value=""></option>
						<?php 
						$taxonomies = get_terms( 'question_category', 'orderby=count&hide_empty=0' );
						foreach($taxonomies as $cat)
								echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
						?>
					</select>
					<?php echo isset($validate['category']) ? '<span class="help-block">'. $validate['category'] .'</span>' : ''; ?>
				</div>
			<?php
		endif;
	}	
	
	public function ask_from_tags_field($validate){
		if(ap_opt('enable_tags')):
		?>
			<div class="form-group<?php echo isset($validate['tags']) ? ' has-error' : ''; ?>">
				<label for="tags"><?php _e('Tags', 'ap') ?></label>
				<input data-role="ap-tagsinput" type="text" value="" tabindex="5" name="tags" id="tags" class="form-control" />
				<?php echo isset($validate['tags']) ? '<span class="help-block">'. $validate['tags'] .'</span>' : ''; ?>
			</div>
		<?php
		endif;
	}	
	
	public function answer_from_content_field($question_id, $validate){
		?>
			<div class="form-group">
				<?php ap_editor_content(''); ?>
			</div>
		<?php
	}
	
	public function edit_answer_from_content_field($answer, $validate){
		?>
			<div class="form-group">
				<?php ap_editor_content($answer->post_content); ?>
			</div>
		<?php
	}
	
	public function edit_question_from_title_field($question, $validate){
		?>
			<div class="form-group<?php echo isset($validate['post_title']) ? ' has-error' : ''; ?>">
				<label for="post_title" class="ap-form-label"><?php _e('Title', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="post_title" id="post_title" value="<?php echo $question->post_title; ?>" class="form-control" placeholder="<?php _e('Question in one sentence', 'ap'); ?>" />
					<?php echo isset($validate['post_title']) ? '<span class="help-block">'. $validate['post_title'] .'</span>' : ''; ?>
				</div>
			</div>
		<?php
	}
	
	public function edit_question_from_content_field($question, $validate){
		?>
			<div class="form-group<?php echo isset($validate['post_content']) ? ' has-error' : ''; ?>">
				<?php
					ap_editor_content($question->post_content); 
				?>
				<?php echo isset($validate['post_content']) ? '<span class="help-block">'. $validate['post_content'] .'</span>' : ''; ?>
			</div>
		<?php
	}
	
	public function edit_question_from_category_field($question, $validate){
		if(ap_opt('enable_categories')):
			$cats_t = get_the_terms( $question->ID, 'question_category' );

			if(isset($cats_t) && is_array($cats_t)){
				foreach($cats_t as $c)
					$category = $c->term_id;
			}

			?>
				<div class="form-group<?php echo isset($validate['category']) ? ' has-error' : ''; ?>">
					<label for="category"><?php _e('Category', 'ap') ?></label>
					<select class="form-control" name="category" id="category" autocomplete="off">
						<option value=""></option>
						<?php 
						$taxonomies = get_terms( 'question_category', 'orderby=count&hide_empty=0' );
						foreach($taxonomies as $cat)
							echo '<option value="'.$cat->term_id.'"'.(( $category == $cat->term_id ) ? ' selected="selected"' : '').'>'.$cat->name.'</option>';
						?>
					</select>
					<?php echo isset($validate['category']) ? '<span class="help-block">'. $validate['category'] .'</span>' : ''; ?>
				</div>
			<?php
		endif;
	}
	
	public function edit_question_from_tags_field($question, $validate){
		if(ap_opt('enable_tags')):
			$tags_t = get_the_terms( $question->ID, 'question_tags' );
			$tags ='';
			
			if($tags_t){
				foreach($tags_t as $t){
					$tags .= $t->name.', ';
				}
			}
			
			?>
				<div class="form-group<?php echo isset($validate['tags']) ? ' has-error' : ''; ?>">
					<label for="tags"><?php _e('Tags', 'ap') ?></label>
					<input type="text" data-role="ap-tagsinput" value="<?php echo $tags; ?>" tabindex="5" name="tags" id="tags" class="form-control" />
					<?php echo isset($validate['tags']) ? '<span class="help-block">'. $validate['tags'] .'</span>' : ''; ?>
				</div>
			<?php
		endif;
	}
	
	public function signup_fields($fields){
		if(isset($_POST['username']))
			$fields['username'] = sanitize_user($_POST['username']);
		
		if(isset($_POST['email']))
			$fields['email'] =  sanitize_email($_POST['email']);
		
		return $fields;
	}
	
	public function ap_toggle_login_signup(){

		if($_POST['args'] == 'signup')
			ap_login_form();
		else
			ap_signup_form();
			
		die();
	}
	
	public function ap_delete_post(){
		$args = explode('-', sanitize_text_field($_REQUEST['args']));
		$action = 'delete_post_'.$args[0];	
		
		if(!ap_user_can_delete($args[0])){
			$result = array('action' => false, 'message' => __('No Permission', 'ap'));			
		}elseif(wp_verify_nonce( $args[1], $action )){
			$post = get_post( $args[0] );
			wp_trash_post($args[0]);
			if($post->post_type == 'question'){
				$result = array('action' => 'question', 'redirect_to' => get_permalink(ap_opt('base_page')), 'message' => __('Question deleted successfully.', 'ap'));
			}else{
				$current_ans = ap_count_ans($post->post_parent);
				$count_label = sprintf( _n('1 Answer', '%d Answers', $current_ans, 'ap'), $current_ans);
				$remove = (!$current_ans ? true : false);
				$result = array(
					'action' 		=> 'answer', 
					'div' 			=> '#answer_'.$args[0],
					'count' 		=> $current_ans,
					'count_label' 	=> $count_label,
					'remove' 		=> $remove, 
					'message' 		=> __('Answer deleted successfully.', 'ap'));
			}
		}
		die(json_encode($result));
	}
	
	public function ap_load_edit_form(){
		$nonce 			= sanitize_text_field($_POST['nonce']);
		$post_id 	= sanitize_text_field($_POST['id']);
		$type 			= sanitize_text_field($_POST['type']);		
		
		if(wp_verify_nonce( $nonce, $type.'-'.$post_id )){
			$post = get_post($post_id);

			if( ap_user_can_edit_question($post_id) && $post->post_type == 'question'){
				ob_start();
				ap_edit_question_form($post_id);
				$html = ob_get_clean();
				
				$result = array('action' => true, 'type' => 'question', 'message' => __('Form loaded.', 'ap'), 'html' => $html);
			}elseif( ap_user_can_edit_answer($post_id) && $post->post_type == 'answer'){
				ob_start();
				ap_edit_answer_form($post_id);
				$html = ob_get_clean();
				
				$result = array('action' => true, 'type' => 'answer', 'message' => __('Form loaded.', 'ap'), 'html' => $html);
			}else{
				$result = array('action' => false, 'message' => __('You do not have permission to edit this question.', 'ap'));
			}
		}else{
			$result = array('action' => false, 'message' => __('Something went wrong, please try again.', 'ap'));
		}
		
		die(json_encode($result));
	}
	
	public function login_signup_modal(){
		if(!is_user_logged_in()){
		?>
		<div class="ap-modal flag-note" id="ap_login_modal" tabindex="-1" role="dialog">
			<div class="ap-modal-bg"></div>
			<div class="ap-modal-content">
				<div class="ap-modal-header">					
					<h4 class="ap-modal-title"><?php _e('Login', 'ap'); ?><span class="ap-modal-close">&times;</span></h4>					
				</div>
				<div class="ap-modal-body">				
					<?php ap_login_form(); ?>
				</div>
			</div>		  
		</div>
		<div class="ap-modal flag-note" id="ap_signup_modal" tabindex="-1" role="dialog">
			<div class="ap-modal-bg"></div>
			<div class="ap-modal-content">
				<div class="ap-modal-header">					
					<h4 class="ap-modal-title"><?php _e('Sign up', 'ap'); ?><span class="ap-modal-close">&times;</span></h4>
				</div>
				<div class="ap-modal-body">				
					<?php ap_signup_form(); ?>
				</div>
			</div>		  
		</div>
		<?php
		}
	}
	
	public function login_bottom(){
		if(!is_user_logged_in()){
		?>
			<div class="ap-nli-backdrop"></div>
			<div class="ap-account-button clearfix">
				<h3><?php _e('Quickly login or sign up to continue', 'ap'); ?></h3>
				<div class="ap-site-ac">
					<h3><?php _e('Sign up or login', 'ap'); ?></h3>
					<a href="#ap_login_modal" class="ap-open-modal ap-btn" title="<?php _e('Click here to login if you already have an account on this site.', 'ap'); ?>"><?php _e('Login', 'ap'); ?></a>
					<a href="#ap_signup_modal" class="ap-open-modal ap-btn" title="<?php _e('Click here to signup if you do not have an account on this site.', 'ap'); ?>"><?php _e('Sign Up', 'ap'); ?></a>
				</div>
				<div class="ap-social-ac">
					<?php do_action( 'wordpress_social_login' ); ?>
				</div>
			</div>		
		<?php
		}
	}
	
	public function ap_ajax_login(){
		$creds 					= array();
		$creds['user_login'] 	= $_POST['username'];
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] 		= true;
		$user = wp_signon( $creds, false );
		
		if ( is_wp_error($user) )
			$result = array('status' => false, 'message' => __('Unable to login, please try again.', 'ap'));
		
		else
			$result = array('status' => true, 'message' => __('Successfully logged in.', 'ap'));
			
		die(json_encode($result));
	}
	
	public function ap_ajax_signup(){
		if(is_user_logged_in())
			return;
		
		// create user
		$user_id = register_new_user($_POST['username'], $_POST['email']);

		// return if there is any error
		if(is_wp_error($user_id))
			return;		
			
		if ( is_wp_error($user_id) ){
			$result = array('status' => false, 'message' => __('Unable to create account, please try again.', 'ap'));
		
		}else{
			$result = array('status' => true, 'message' => __('Successfully created your account, please check your email for password.', 'ap'));
		}
		
		die(json_encode($result));
	}
}

function ap_form_allowed_tags(){
	$allowed_tags = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
		'pre' => array(),
		'code' => array(),
	);
	
	return apply_filters( 'ap_allowed_tags', $allowed_tags);
}

function ap_ask_form(){

	$validate = ap_validate_form();
	
	if(!empty($validate['has_error'])){
		echo '<div class="alert alert-danger" data-dismiss="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'. __('Problem submitting form, please recheck form', 'ap') .'</div>';
	}
	
	if( ap_user_can_ask()){
		?>
		<form action="" id="ask_question_form" method="POST" data-action="ap-submit-question">			
			<?php do_action('ap_ask_form_fields', $validate); ?>			
			<?php 
				if(!is_user_logged_in()){
					echo '<div id="ap-login-signup">';
					echo do_action('ap_form_login');
					echo '</div>';
				}
			?>
			
			<?php do_action('ap_ask_form_bottom'); ?>
			<?php ap_ask_form_hidden_input(); ?>
			
		</form>
		<?php
		do_action('ap_after_ask_form');
	}
}
function ap_answer_form($question_id){
	global $ap_answer_form_validation;
	$validate = $ap_answer_form_validation;
	
	if(!empty($validate['has_error'])){
		echo '<div class="alert alert-danger" data-dismiss="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'. __('Problem submitting form, please recheck form', 'ap') .'</div>';
	}
	
	if(ap_user_can_answer($question_id) ){
		echo '<form action="" id="answer_form" class="ap-content-inner no-overflow" method="POST" data-action="ap-submit-answer">';
		
		echo '<div class="form-groups">';
		do_action('ap_answer_fields', $question_id, $validate);
		echo '</div>';		
		do_action('ap_answer_form_bottom');	
		ap_answer_form_hidden_input($question_id);
		echo '</form>';

		echo do_action('ap_after_answer_form');

	}
}

function ap_validate_form(){
	if(isset($_POST['is_question']) && isset($_POST['submitted'])) {
		$error 		= array();
		$has_error 	= false;
		if(trim($_POST['post_title']) === '') {
			$error['post_title'] = __('Please enter a title', 'ap');
			$error['has_error'] = true;
		}	
		
		if(trim($_POST['post_content']) === '') {
			$error['post_content'] = __('Please enter content of question', 'ap');
			$error['has_error'] = true;
		}
		
		return apply_filters('ap_validate_ask_form', $error);		
		
	}elseif(isset($_POST['is_answer']) && isset($_POST['submitted'])) {
		$error 		= array();
		$has_error 	= false;
		
		if(trim($_POST['post_content']) === '') {
			$error[] = __('Please enter some content', 'ap');
			$has_error = true;
		}
		
		do_action('ap_validate_answer_form');
		
		return array('has_error' => $has_error, 'message' => $error);	
	}
	return false;
}


function ap_ask_form_hidden_input(){	
	wp_nonce_field('post_nonce', 'ask_form');
	echo '<input type="hidden" name="is_question" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<button class="btn ap-btn ap-success btn-submit-ask" type="submit">'. __('Ask Question', 'ap'). '</button>';
}

function ap_answer_form_hidden_input($question_id){	
	wp_nonce_field('post_nonce_'.$question_id, 'nonce');
	echo '<input type="hidden" name="is_answer" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<input type="hidden" name="form_question_id" value="'.$question_id.'" />';
	echo '<button type="submit" class="btn-submit-ans btn ap-btn ap-success">'. __('Submit Answer', 'ap'). '</button>';
}


function ap_edit_question_form_hidden_input($post_id){
	wp_nonce_field('post_nonce-'.$post_id, 'edit_question');
	echo '<input type="hidden" name="is_question" value="true" />';
	echo '<input type="hidden" name="question_id" value="'.$post_id.'" />';
	echo '<input type="hidden" name="edited" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<button type="submit" class="btn-submit-ans btn ap-btn ap-success">'. __('Update question', 'ap'). '</button>';
}



function ap_edit_answer_form($post_id){
	global $ap_answer_form_validation;
	$validate = $ap_answer_form_validation;
	
	$post = get_post($post_id);
	
	if( !ap_user_can_edit_ans($post_id)){
		echo '<p>'.__('You don\'t have permission to edit this answer.', 'ap').'</p>';
		return;
	}
	
	if(!empty($validate['has_error'])){
		echo '<div class="alert alert-danger" data-dismiss="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'. __('Problem submitting form, please recheck form', 'ap') .'</div>';
	}
	
	if(ap_user_can_edit_ans($post->ID) ){
		echo '<form action="" id="answer_form" class="ap-content-inner" method="POST" data-action="ap-submit-answer">';
		
		echo '<div class="form-groups">';
		echo '<div class="ap-fom-group-label">'.__('Edit answer', 'ap').'</div>';
		do_action('ap_edit_answer_fields', $post, $validate);
		echo '</div>';
			
		ap_edit_answer_form_hidden_input($post->ID);
		echo '</form>';
	}
}
function ap_edit_answer_form_hidden_input($post_id){
	wp_nonce_field('post_nonce-'.$post_id, 'nonce');
	echo '<input type="hidden" name="is_answer" value="true" />';
	echo '<input type="hidden" name="answer_id" value="'.$post_id.'" />';
	echo '<input type="hidden" name="edited" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<button type="submit" class="btn btn-primary">'. __('Update Answer', 'ap'). '</button>';
}

function ap_signup_form(){
	if(!is_user_logged_in()):
	?>
		<form method="POST" id="ap-signup-form">
			<div class="form-group">
				<label for="username" class="ap-form-label"><?php _e('Username', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" value="" tabindex="5" name="username" id="username" class="form-control" placeholder="<?php _e('Username', 'ap') ?>" />
				</div>
			</div>	
			<div class="form-group">
				<label for="email" class="ap-form-label"><?php _e('Email', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" value="" tabindex="5" name="email" id="email" class="form-control" placeholder="<?php _e('name@domain.com', 'ap') ?>" />
				</div>
			</div>

			<button type="submit" class="ap-btn" ><?php _e('Login', 'ap') ?></button>
			<input type="hidden" name="action" value="ap_ajax_signup" />
		</form>
	<?php
	endif;
}

function ap_login_form(){
	if(!is_user_logged_in()){
	?>
		<form method="POST" id="ap-login-form">
			<div class="for-non-logged-in ap-login-fields">
				<div class="form-group">
					<label for="username" class="ap-form-label"><?php _e('Username', 'ap') ?></label>
					<div class="no-overflow">
						<input type="text" value="" tabindex="5" name="username" id="username" class="form-control" placeholder="<?php _e('Enter your username', 'ap') ?>" />
					</div>
				</div>	
				<div class="form-group">
					<label for="password" class="ap-form-label"><?php _e('Password', 'ap') ?></label>
					<div class="no-overflow">
						<input type="password" value="" tabindex="5" name="password" id="password" class="form-control" placeholder="<?php _e('Enter your password', 'ap') ?>" />
					</div>
				</div>
			</div>
			<button type="submit" class="ap-btn" ><?php _e('Login', 'ap') ?></button>
			<input type="hidden" name="action" value="ap_ajax_login" />
		</form>
	<?php
	}
}

function ap_edit_question_form($question_id = false){
	if(!$question_id)
		$question_id = get_edit_question_id();
		
	if( !ap_user_can_edit_question($question_id)){
		echo '<p>'.__('You don\'t have permission to access this page.', 'ap').'</p>';
		return;
	}
	$validate = ap_validate_form();
	
	$question 	= get_post($question_id);
	
	if(!$question)
		return;
		
	$action 	= $question->post_type.'-'.$question_id;	

	if(!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], $action)){
		echo '<p>'.__('Trying to cheat? huh!.', 'ap').'</p>';
		return;
	}
	

	?>
	<form action="" id="ask_question_form" method="POST" data-action="ap-submit-question">			
		<div class="form-groups">
			<div class="ap-fom-group-label"><?php _e('Edit question', 'ap'); ?></div>
			<?php do_action('ap_edit_question_form_fields', $question, $validate); ?>
		</div>
		
		<?php do_action('ap_ask_form_bottom'); ?>
		<?php ap_edit_question_form_hidden_input($question->ID); ?>
		
	</form>
	<?php

}
