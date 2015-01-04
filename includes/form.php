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

class AnsPress_Form_Helper
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

		
		//add_action('comment_form', array($this, 'comment_button') );
		add_action( 'wp_ajax_ap_load_comment_form', array($this, 'load_ajax_commentform') ); 
		add_action( 'wp_ajax_nopriv_ap_load_comment_form', array($this, 'load_ajax_commentform') );
		
		/*TODO: remove this, only anspress comment from ajax*/
		add_action('comment_post', array($this, 'save_comment'), 20, 2);
	
		add_action( 'wp_ajax_ap_update_comment', array($this, 'update_comment_form') ); 
		add_action( 'wp_ajax_ap_delete_comment', array($this, 'delete_comment') ); 
		add_action( 'ap_after_delete_comment', array($this, 'after_deleting_comment'), 10, 2 ); 
		
		add_action( 'wp_ajax_nopriv_ap_not_logged_in_messgae', array($this, 'ap_not_logged_in_messgae') ); 
		add_action( 'wp_ajax_ap_edit_comment_form', array($this, 'edit_comment_form') ); 
		
		add_action( 'wp_ajax_ap_submit_question', array($this, 'ajax_question_submit') ); 
		add_action( 'wp_ajax_nopriv_ap_submit_question', array($this, 'ajax_question_submit') ); 
		
		add_action( 'wp_ajax_ap_submit_answer', array($this, 'ajax_answer_submit') ); 
		add_action( 'wp_ajax_nopriv_ap_submit_answer', array($this, 'ajax_answer_submit') ); 
				
		add_action('wp_insert_comment', array($this, 'comment_inserted'), 99, 2);
		add_action('pre_comment_approved', array($this, 'pre_comment_approved'), 99, 2);
		
		add_action('ap_answer_fields', array($this, 'answer_from_content_field'), 10, 2);
		add_action('ap_edit_answer_fields', array($this, 'edit_answer_from_content_field'), 10, 2);

		//add_action( 'wp_ajax_ap_toggle_login_signup', array($this, 'ap_toggle_login_signup') ); 
		add_action( 'wp_ajax_nopriv_ap_toggle_login_signup', array($this, 'ap_toggle_login_signup') ); 
		
		//add_filter( 'ap_question_form_validation', array($this, 'ap_signup_login_validation') ); 
		 
		//add_filter( 'ap_answer_form_validation', array($this, 'ap_signup_login_validation') );
		
		add_action( 'wp_ajax_ap_delete_post', array($this, 'ap_delete_post') ); 
		
		add_action( 'wp_ajax_ap_load_edit_form', array($this, 'ap_load_edit_form') );

		add_action( 'wp_ajax_ap_new_tag', array($this, 'ap_new_tag') );
		add_action( 'wp_ajax_ap_load_new_tag_form', array($this, 'ap_load_new_tag_form') );
    }

	
	
	
	public function get_question_fields_to_process(){
		$fields = array(
			'post_title' 	=> sanitize_text_field($_POST['post_title']),
			'post_content' 	=>  strip_shortcodes($_POST['post_content']),
		);
		
		//remove <!--more-->
		
		$fields['post_content'] = str_replace('<!--more-->', '', $fields['post_content']);
		//convert to entity
		$fields['post_content'] = preg_replace_callback('/<pre.*?>(.*?)<\/pre>/imsu', 'ap_convert_pre_char', $fields['post_content']);
		
		if(isset($_POST['tags']))
			$fields['tags']	= sanitize_text_field($_POST['tags']);
		
		if(isset($_POST['private_question']))
			$fields['private_question']	= sanitize_text_field($_POST['private_question']);
		
		if(isset($_POST['parent_id']))
			$fields['parent_id']	= sanitize_text_field($_POST['parent_id']);
		
		if(isset($_POST['name']))
			$fields['name']	= sanitize_text_field($_POST['name']);
		
		return apply_filters('ap_question_fields_to_process', $fields);
		
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
		
		if(ap_opt('enable_tags') && $_POST['tags'] !=='' ){
			$tags = explode(',', $_POST['tags']);
			if(ap_opt('min_tags') > count($tags)){
				$error['tags'] 	= sprintf(__('You must add at least %d', 'ap'), ap_opt('min_tags'));
				$error['has_error'] 	= true;
			}
		}
		
		$ap_question_form_validation = $error;
		
		$ap_question_form_validation = apply_filters('ap_question_form_validation', $ap_question_form_validation);
		
		return $ap_question_form_validation;
	}
	

	
	public function get_answer_fields_to_process(){
		$fields = array(			
			'is_answer' 	=> sanitize_text_field($_POST['is_answer']),
			'submitted' 	=> sanitize_text_field($_POST['submitted']),
			'nonce' 		=> $_POST['nonce'],
			'post_content' 		=> strip_shortcodes($_POST['post_content'])
		);
		$fields['post_content'] = str_replace('<!--more-->', '', $fields['post_content']);
		
		//convert to entity
		$fields['post_content'] = preg_replace_callback('/<pre.*?>(.*?)<\/pre>/imsu', 'ap_convert_pre_char', $fields['post_content']);
		
		if(isset($_POST['form_question_id']))
			$fields['question_id'] 	= sanitize_text_field($_POST['form_question_id']);
		
		if(isset($_POST['name']))
			$fields['name'] 	= sanitize_text_field($_POST['name']);
		
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
		if(!is_user_logged_in() && !ap_opt('allow_anonymous'))
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
			
			if(!ap_user_can_answer($question->ID) && !ap_opt('allow_anonymous'))
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
				// get existing answer count
				$current_ans = ap_count_ans($question->ID);
				
				// redirect if just logged in
				if($logged_in && $_POST['action'] != 'ap_submit_answer'){
					wp_redirect( get_permalink($question->ID) ); exit;
				}
				if (ap_opt('allow_anonymous') && isset($fields['name']))
					update_post_meta($post_id, 'anonymous_name', $fields['name']);
					
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
					
					ap_do_event('new_answer', $post_id, $user_id, $question->ID, $result);
				}				
				
				if($_POST['action'] == 'ap_submit_answer')
					return json_encode($result) ;
			}
		}elseif($_POST['action'] == 'ap_submit_answer'){
			$result = array('postid' => $post_id, 'action' => false, 'message' => __('Please try again, answer submission failed!','ap'));
			return json_encode($result) ;
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
								'message' 		=> __('You don\'t have permission to edit this answer.','ap')
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

    /**
     * Load comment form
     * @return string
     * @since unknown
     */
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
				'comment_field' => '<textarea name="comment" rows="3" aria-required="true" id="ap-comment-textarea" class="form-control autogrow" placeholder="'.__('Respond to the post.', 'ap').'"></textarea><input type="hidden" name="ap_comment_form" value="true"/>',
				'comment_notes_after' => ''
			);
			$current_user = get_userdata( get_current_user_id() );
			echo '<div class="ap-comment-form clearfix">';
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
			$result = json_encode(array('status' => false, 'message' => __('You do not have permission to edit this comment.', 'ap')));
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
			$comment = get_comment($args[0]);
			$delete = wp_delete_comment( $args[0], true );
			if($delete){						
				$post_type = get_post_type( $comment->comment_post_ID );
				do_action('ap_after_delete_comment', $comment, $post_type);
				
				if ($post_type == 'question') 
					ap_do_event('delete_comment', $comment, 'question');
				elseif($post_type == 'answer')
					ap_do_event('delete_comment', $comment, 'answer');
			}
			$result = array('status' => true, 'message' => __('Comment deleted successfully', 'ap'));
		}
		die(json_encode($result));
	}
	
	public function after_deleting_comment($comment, $post_type){
		if ($post_type == 'question') {
			ap_remove_parti($comment->comment_post_ID, $comment->user_id, 'comment', $comment->comment_ID);
		}elseif($post_type == 'answer'){
			$post_id = wp_get_post_parent_id($comment->comment_post_ID);
			ap_remove_parti($post_id, $comment->user_id, 'comment', $comment->comment_ID);
		}
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
		}else{
			return $approved;
		}
	}

	
		
	/**
	 * TODO: EXTENSION - move to tags
	 */
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

	/**
	 * Run on login_form_defaults filter so we can reset a few of the default values
	 * @param  Array $args The login default filter passed in from the wp_login_form() function
	 * @return Array       The modified arguments array
	 */
	public function login_form_defaults($args) {
		$args['label_username'] = 'Username / Email';
		$args['value_remember'] = true;
		return $args;
	}

	/**
	 * Add additional fields to bottom of the login form
	 * @param  String $content The existing content 
	 * @return String
	 */
	public function login_form_bottom_html($content)
	{
		/*
		 * Only add these AJAX login fields if the user wants 
		 * to use an AJAX login form
		 */
		if (ap_opt('ajax_login')) {
			$content .= '<input type="hidden" name="action" value="ap_ajax_login" />';
			$content .= wp_nonce_field( 'ap_login_nonce', '_wpnonce', true, false ); 
		}
		$content .= '<p>' . sprintf(__("Don't have a user account? %sRegister now%s.", 'ap'), '<a class="ap-open-modal" href="#ap_signup_modal">', '</a>') . '</p>';

		return $content;
	}	
	
	
	public function ap_new_tag(){
		if(!wp_verify_nonce( $_POST['_nonce'], 'new_tag' ) && ap_user_can_create_tag())
			die();
			
		$term = wp_insert_term(
		  $_POST['tag_name'],
		  'question_tags', // the taxonomy
		  array(
			'description'=> $_POST['tag_desc']
		  )
		);
		if ( is_wp_error($term) ){
			$result = array('status' => false, 'message' => __('Unable to create tag, please try again.', 'ap'));
		
		}else{
			$result = array('status' => true, 'message' => __('Successfully created a tag.', 'ap'), 'tag' => get_term_by( 'id', $term['term_id'], 'question_tags') );
		}
		die(json_encode($result));
	}
	
	public function ap_load_new_tag_form(){
		if(!wp_verify_nonce( $_REQUEST['args'], 'new_tag_form' ) && ap_user_can_create_tag()){
			$result = array('status' => false, 'message' => __('Unable to load form, please try again.', 'ap'));
		}else{			
			$result = array('status' => true, 'message' => __('Successfully loaded form.', 'ap'), 'html' => ap_tag_form() );
		}
		die(json_encode($result));
	}
}



function ap_answer_form($question_id = false){
	if(!$question_id){
		$question_id = get_the_ID();
	}
	global $ap_answer_form_validation;
	$validate = $ap_answer_form_validation;
	
	if(!empty($validate['has_error'])){
		echo '<div class="alert alert-danger" data-dismiss="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'. __('Problem submitting form, please recheck form', 'ap') .'</div>';
	}
	
	if((!ap_opt('only_admin_can_answer') && ap_user_can_answer($question_id)) || is_super_admin()){
		echo '<form action="" id="answer_form" class="ap-content-inner" method="POST" data-action="ap-submit-answer">';
		
		echo '<div class="form-groups">';
		do_action('ap_answer_fields', $question_id, $validate);
		echo '</div>';		
		do_action('ap_answer_form_bottom');	
		ap_answer_form_hidden_input($question_id);
		echo '</form>';

		echo do_action('ap_after_answer_form');

	}
}



function ap_answer_form_hidden_input($question_id){	
	wp_nonce_field('post_nonce_'.$question_id, 'nonce');
	echo '<input type="hidden" name="is_answer" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<input type="hidden" name="form_question_id" value="'.$question_id.'" />';
	echo '<button type="submit" class="btn-submit-ans btn ap-btn ap-success">'. __('Submit Answer', 'ap'). '</button>';
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
		echo '<form action="" id="edit_form" class="ap-content-inner" method="POST" data-action="ap-submit-answer">';
		
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
	echo '<input type="submit" class="btn btn-primary" value="'. __('Update Answer', 'ap'). '" />';
}


function ap_tag_form(){
	$output = '';	
	$output .= '<form method="POST" id="ap_new_tag_form">';
	$output .= '<strong>'.__('Create new tag', 'ap').'</strong>';
	$output .= '<input type="text" name="tag_name" class="form-control" value="" placeholder="'.__('Enter tag', 'ap').'" />';
	$output .= '<textarea type="text" name="tag_desc" class="form-control" value="" placeholder="'.__('Description of tag.', 'ap').'"></textarea>';
	$output .= '<button type="submit" class="ap-btn">'.__('Create tag', 'ap').'</button>';
	$output .= '<input type="hidden" name="action" value="ap_new_tag" />';
	$output .= '<input type="hidden" name="_nonce" value="'.wp_create_nonce('new_tag').'" />';
	$output .= '</form>';
	
	return $output;
	
}
