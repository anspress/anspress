<?php
/**
 * AnsPress ajax requests
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_Ajax
{

	private $request;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {

		add_action('ap_ajax_suggest_similar_questions', array($this, 'suggest_similar_questions'));
		add_action('ap_ajax_load_comment_form', array($this, 'load_comment_form'));
		add_action('ap_ajax_delete_comment', array($this, 'delete_comment'));
		add_action('ap_ajax_select_best_answer', array($this, 'select_best_answer'));
		add_action('ap_ajax_delete_post', array($this, 'delete_post'));
		add_action('ap_ajax_change_post_status', array($this, 'change_post_status'));
		add_action('ap_ajax_load_user_field_form', array($this, 'load_user_field_form'));
		
		add_action('wp_ajax_ap_suggest_tags', array($this, 'ap_suggest_tags'));
		add_action('wp_ajax_nopriv_ap_suggest_tags', array($this, 'ap_suggest_tags'));


    }

    

    /**
     * Show similar questions when asking a question
     * @return void
     * @since 2.0.1
     */
    public function suggest_similar_questions(){

    	if(empty($_POST['value'] ))
    		return;

    	$keyword = sanitize_text_field($_POST['value']);
		$questions = get_posts(array(
			'post_type'   	=> 'question',
			'showposts'   	=> 10,
			's' 			=> $keyword,
		));
		
		
		if($questions){
			$items = '<div class="ap-similar-questions-head">';
			$items .= '<h3>'.ap_icon('check', true). sprintf(__('%d similar questions found', 'ap'), count($questions)) .'</h3>';
			$items .= '<p>'.__('We found similar questions that have already been asked, click to read them. Avoid creating duplicate questions, it will be deleted.').'</p>';
			$items .= '</div>';
			$items .= '<div class="ap-similar-questions">';
			foreach ($questions as $p){
				$count = ap_count_answer_meta($p->ID);
				$p->post_title = ap_highlight_words($p->post_title, $keyword);
				
				if(!isset($_POST['is_admin']))
					$items .= '<a class="ap-sqitem clearfix" href="'.get_permalink($p->ID).'"><span class="acount">'. sprintf(_n('1 Answer', '%d Answers', $count, 'ap' ), $count) .'</span><span class="ap-title">'.$p->post_title.'</span></a>';

				else
					$items .= '<div class="ap-q-suggestion-item clearfix"><a class="select-question-button button button-primary button-small" href="'.add_query_arg(array('post_type' => 'answer', 'post_parent' => $p->ID), admin_url( 'post-new.php' )).'">'.__('Select', 'ap').'</a><span class="question-title">'.$p->post_title.'</span><span class="acount">'. sprintf(_n('1 Answer', '%d Answers', $count, 'ap' ), $count) .'</span></div>';
			}
			$items .= '</div>';
			$result = array('status' => true, 'html' => $items);
		}else{
			$result = array('status' => false, 'message' => __('No related questions found', 'ap'));
		}
		
		ap_send_json($result);
    }

    /**
     * Return comment form
     * @return void
     * @since 2.0.1
     */
    public function load_comment_form(){
    	$result = array(
    		'ap_responce' 	=> true,
    		'action' 		=> 'load_comment_form',
    	);

		if((wp_verify_nonce( $_REQUEST['__nonce'], 'comment_form_nonce' )) || (wp_verify_nonce( $_REQUEST['__nonce'], 'edit_comment_'.(int)$_REQUEST['comment_ID'] ))){
			
			$comment_args  = array();
			$content = '';
			$commentid = '';
			if(isset($_REQUEST['comment_ID'])){
				$comment = get_comment($_REQUEST['comment_ID']);
				$comment_post_ID = $comment->comment_post_ID;
				$nonce = wp_create_nonce( 'comment_'.$comment->comment_ID );
				$comment_args['label_submit'] = __('Update comment', 'ap');

				$content = $comment->comment_content;
				$commentid = '<input type="hidden" name="comment_ID" value="'.$comment->comment_ID.'"/>';
			}else{
				$comment_post_ID = (int)$_REQUEST['post'];
				$nonce = wp_create_nonce( 'comment_'.(int)$_REQUEST['post'] );
			}

			$comment_args = array(
				'id_form' => 'ap-commentform',
				'title_reply' => '',
				'logged_in_as' => '',
				'comment_field' => '<div class="ap-comment-submit"><input type="submit" value="'.__('Comment', 'ap').'" name="submit"></div><div class="ap-comment-textarea"><textarea name="comment" rows="3" aria-required="true" id="ap-comment-textarea" class="ap-form-control autogrow" placeholder="'.__('Respond to the post.', 'ap').'">'.$content.'</textarea></div><input type="hidden" name="ap_form_action" value="comment_form"/><input type="hidden" name="ap_ajax_action" value="comment_form"/><input type="hidden" name="__nonce" value="'.$nonce.'"/>'.$commentid,
				'comment_notes_after' => ''
			);
			
			if(isset($_REQUEST['comment_ID']))
				$comment_args['label_submit'] = __('Update comment', 'ap');

			//$current_user = get_userdata( get_current_user_id() );
			global $withcomments;
			$withcomments = true;

			$post = new WP_Query(array('p' => $comment_post_ID, 'post_type' => array('question', 'answer')));
			$count = get_comment_count( $comment_post_ID );
			ob_start();
				echo '<div class="ap-comment-block clearfix">';
					//if(ap_user_can_comment() || (isset($_REQUEST['comment_ID']) && ap_user_can_edit_comment((int)$_REQUEST['comment_ID'] ))){
						echo '<div class="ap-comment-form clearfix">';
							echo '<div class="ap-comment-inner">';
								comment_form($comment_args, $comment_post_ID );
							echo '</div>';
						echo '</div>';
					//}
					while( $post->have_posts() ) : $post->the_post();					
					comments_template();
					endwhile;
					wp_reset_postdata();
				echo '</div>';
			$result['html'] = ob_get_clean();
			$result['container'] = '#comments-'.$comment_post_ID;
			$result['message'] = 'success';
			$result['view'] = array('comments_count_'.$comment_post_ID => $count['approved'], 'comment_count_label_'.$comment_post_ID => sprintf(_n('One comment', '%d comments', $count['approved'], 'ap'), $count['approved']) );

		}else{
			$result['message'] = 'no_permission';
		}

		ap_send_json(ap_ajax_responce($result));
    }

    /**
     * Ajax action for deleting comment
     * @return void
     * @since 2.0.0
     */
    public function delete_comment(){
    	if(isset($_POST['comment_ID']) && ap_user_can_delete_comment((int)$_POST['comment_ID'] ) && wp_verify_nonce( $_POST['__nonce'], 'delete_comment' )){

    		$comment = get_comment( $_POST['comment_ID'] );
    		if (time() > (get_comment_date( 'U', (int)$_POST['comment_ID'] ) + (int)ap_opt('disable_delete_after')) && !is_super_admin()) {
				ap_send_json( ap_ajax_responce(array('message_type' => 'warning', 'message' => sprintf(__('This post was created %s ago, its locked hence you cannot delete it.', 'ap'), ap_human_time( get_comment_date( 'U', (int)$_POST['comment_ID'] )) ))));
				return;
			}

    		$delete = wp_delete_comment( (int)$_POST['comment_ID'], true );
    		
    		if($delete){
    			do_action( 'ap_after_deleting_comment', $comment );
    			$count = get_comment_count( $comment->comment_post_ID );
    			ap_send_json(ap_ajax_responce(  array( 'action' => 'delete_comment', 'comment_ID' => (int)$_POST['comment_ID'], 'message' => 'comment_delete_success', 'view' => array('comments_count_'.$comment->comment_post_ID => $count['approved'], 'comment_count_label_'.$comment->comment_post_ID => sprintf(_n('One comment', '%d comments', $count['approved'], 'ap'), $count['approved']) ))));
    		}else{
    			ap_send_json( ap_ajax_responce('something_wrong'));
    		}
    		return;
    	}
    	ap_send_json( ap_ajax_responce('no_permission'));
    }

    /**
     * Ajax action for selecting a best answer
     * @return void
     * @since 2.0.0
     */
    public function select_best_answer(){
		$answer_id = (int)$_POST['answer_id'];
		
		if(!is_user_logged_in()){
			ap_send_json( ap_ajax_responce('no_permission'));
			return;
		}

		if(!wp_verify_nonce( $_POST['__nonce'], 'answer-'.$answer_id )){
			ap_send_json( ap_ajax_responce('something_wrong'));
			return;
		}

		$post = get_post($answer_id);
		$user_id = get_current_user_id();
		
		if(ap_question_best_answer_selected($post->post_parent)){
			do_action('ap_unselect_answer', $user_id, $post->post_parent, $post->ID);
			update_post_meta($post->ID, ANSPRESS_BEST_META, 0);
			update_post_meta($post->post_parent, ANSPRESS_SELECTED_META, false);
			update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

			if(ap_opt('close_after_selecting'))
				wp_update_post( array('ID' => $post->post_parent, 'post_status' => 'publish') );

			ap_send_json( ap_ajax_responce(array('message' => 'unselected_the_answer', 'action' => 'unselected_answer', 'do' => 'reload')));



		}else{
			do_action('ap_select_answer', $user_id, $post->post_parent, $post->ID);
			update_post_meta($post->ID, ANSPRESS_BEST_META, 1);
			update_post_meta($post->post_parent, ANSPRESS_SELECTED_META, $post->ID);
			update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

			if(ap_opt('close_after_selecting'))
				wp_update_post( array('ID' => $post->post_parent, 'post_status' => 'closed') );

			$html = ap_select_answer_btn_html($answer_id);
			ap_send_json( ap_ajax_responce(array('message' => 'selected_the_answer', 'action' => 'selected_answer', 'do' => 'reload', 'html' => $html)));
		}
	}

	public function delete_post()
	{
		$post_id = (int) $_POST['post_id'];

		$action = 'delete_post_'.$post_id;	
		
		if(!wp_verify_nonce( $_POST['__nonce'], $action ) || !ap_user_can_delete($post_id)){
			ap_send_json( ap_ajax_responce('something_wrong'));
			return;
		}
		
		$post = get_post( $post_id );

		if( (time() > (get_the_time('U', $post->ID) + (int)ap_opt('disable_delete_after'))) && !is_super_admin( ) ){
			ap_send_json( ap_ajax_responce(array('message_type' => 'warning', 'message' => sprintf(__('This post was created %s ago, its locked hence you cannot delete it.', 'ap'), ap_human_time( get_the_time('U', $post->ID)) ))));
			return;
		}

		wp_trash_post($post_id);
		if($post->post_type == 'question'){
			do_action('ap_wp_trash_question', $post_id);
			ap_send_json( ap_ajax_responce( array('action' => 'delete_question', 'do' => 'redirect', 'redirect_to' => ap_base_page_link(), 'message' => 'question_moved_to_trash')));
		}else{
			do_action('ap_wp_trash_answer', $post_id);
			$current_ans = ap_count_published_answers($post->post_parent);
			$count_label = sprintf( _n('1 Answer', '%d Answers', $current_ans, 'ap'), $current_ans);
			$remove = (!$current_ans ? true : false);
			ap_send_json( ap_ajax_responce(array(
				'action' 		=> 'delete_answer', 
				'div_id' 		=> '#answer_'.$post_id,
				'count' 		=> $current_ans,
				'count_label' 	=> $count_label,
				'remove' 		=> $remove,
				'message' 		=> 'answer_moved_to_trash',
				'view'			=> array('answer_count' => $current_ans, 'answer_count_label' => $count_label))));
		}
		
	}

	/**
	 * Handle change post status request
	 * @return void
	 * @since 2.1
	 */
	public function change_post_status(){
		$post_id = (int) $_POST['post_id'];
		$status = $_POST['status'];

		if(!is_user_logged_in() || !wp_verify_nonce( $_POST['__nonce'], 'change_post_status_'.$post_id ) || !ap_user_can_change_status($post_id)){
			ap_send_json( ap_ajax_responce('no_permission'));
			die();
		}else{		
			$post = get_post($post_id);
			if(($post->post_type == 'question' || $post->post_type == 'answer') && $post->post_status != $status){
				$update_data = array();
				if($status == 'publish')
					$update_data['post_status'] = 'publish';

				elseif($status == 'moderate')
					$update_data['post_status'] = 'moderate';

				elseif($status == 'private_post')
					$update_data['post_status'] = 'private_post';

				elseif($status == 'closed')
					$update_data['post_status'] = 'closed';

				// unregister history action for edit
				remove_action('ap_after_new_answer', array('AP_History', 'new_answer'));
				remove_action('ap_after_new_question', array('AP_History', 'new_question'));

				$update_data['ID'] = $post->ID;
				wp_update_post( $update_data );
				
				ap_add_history(get_current_user_id(), $post_id, '', 'status_updated');

				add_action('ap_post_status_updated', $post->ID);

				ob_start();
					ap_post_status_description($post->ID);
				$html = ob_get_clean();

				ap_send_json( ap_ajax_responce(array(
					'action' 		=> 'status_updated',
					'message' 		=> 'status_updated',
					'do'			=> array('remove_if_exists', 'toggle_active_class', 'append_before'),
					'append_before_container'		=> '#ap_post_actions_'.$post->ID,
					'toggle_active_class_container'	=> '#ap_post_status_toggle_'.$post->ID,
					'remove_if_exists_container'	=> '#ap_post_status_desc_'.$post->ID,
					'active'		=> '.'.$status,
					'html'			=> $html,
				)));
				die();
			}			
		}
		ap_send_json( ap_ajax_responce('something_wrong'));
		die();
	}

	public function load_user_field_form(){
		$user_id 		= get_current_user_id();
		$field_name 	= sanitize_text_field($_POST['field']);

		if(!is_user_logged_in() || !wp_verify_nonce( $_POST['__nonce'], 'user_field_form_'.$field_name.'_'.$user_id )){
			ap_send_json( ap_ajax_responce('no_permission'));
		}else{
			if(ap_has_users(array('ID' => $user_id ) )){
				while ( ap_users() ) : ap_the_user(); 
					$form = ap_user_get_fields(array('show_only' => $field_name, 'form' => array('field_hidden' => false, 'hide_footer' => false, 'show_cancel' => true, 'is_ajaxified' => true, 'submit_button' => __('Update', 'ap'))));
					ap_send_json( ap_ajax_responce(array(
						'action' 		=> 'user_field_form_loaded',
						'do'			=> 'updateHtml',
						'container'		=> '#user_field_form_'.$field_name,
						'html'			=> $form->get_form()
					)));
				endwhile;
			}
		}
		ap_send_json( ap_ajax_responce('something_wrong'));
		die();
	}

	
	public function check_email(){
	   $email = sanitize_text_field($_POST['email']);

	   /* use the email as the username */
       if ( email_exists( $email ) || username_exists($email) )
           echo 'false' ;
		else
			echo 'true';
		
		die();
	}
	
	public function ap_suggest_tags(){
		$keyword = sanitize_text_field($_POST['q']);
		$tags = get_terms( 'question_tag', array(
			'orderby'   	=> 'count',
			'order' 		=> 'DESC',
			'hide_empty' 	=> false,
			'search' 		=> $keyword,
			'number' 		=> 8
		));
		
		//$new_tag_html = '';
		//if(ap_user_can_create_tag())
			//$new_tag_html = '<div class="ap-cntlabel"><a href="#" id="ap-load-new-tag-form" data-args="'.wp_create_nonce('new_tag_form').'">'.__('Create new tag', 'ap').'</a></div>';

		if($tags){
			$items = array();
			foreach ($tags as $k => $t){
				$items[$k]		= $t->name;
			}

			$result = array('status' => true, 'items' => $items);
			die(json_encode($result));
		}
		
		die(json_encode(array('status' => false)));
	}

}
