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
		
		add_action('wp_ajax_ap_suggest_tags', array($this, 'ap_suggest_tags'));
		add_action('wp_ajax_nopriv_ap_suggest_tags', array($this, 'ap_suggest_tags'));


    }

    

    /**
     * Show similar questions when asking a question
     * @return void
     * @since 2.0.1
     */
    public function suggest_similar_questions(){
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
			foreach ($questions as $k => $p){
				$count = ap_count_answer_meta($p->ID);
				$p->post_title = ap_highlight_words($p->post_title, $keyword);
				$items .= '<a class="ap-sqitem" href="'.get_permalink($p->ID).'">'.$p->post_title.'<span class="acount">'. sprintf(_n('1 Answer', '%d Answers', $count, 'ap' ), $count) .'</span></a>';
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
    		'do' 			=> 'append'
    	);

		if((wp_verify_nonce( $_REQUEST['__nonce'], 'comment_form_nonce' ) && ap_user_can_comment()) || (isset($_REQUEST['comment_ID']) && ap_user_can_edit_comment((int)$_REQUEST['comment_ID'] ) && wp_verify_nonce( $_REQUEST['__nonce'], 'edit_comment_'.(int)$_REQUEST['comment_ID'] ))){

			

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
				'comment_field' => '<textarea name="comment" rows="3" aria-required="true" id="ap-comment-textarea" class="form-control autogrow" placeholder="'.__('Respond to the post.', 'ap').'">'.@$content.'</textarea><input type="hidden" name="ap_form_action" value="comment_form"/><input type="hidden" name="ap_ajax_action" value="comment_form"/><input type="hidden" name="__nonce" value="'.$nonce.'"/>'.@$commentid,
				'comment_notes_after' => ''
			);
			
			if(isset($_REQUEST['comment_ID']))
				$comment_args['label_submit'] = __('Update comment', 'ap');

			$current_user = get_userdata( get_current_user_id() );

			ob_start();
				echo '<div class="ap-comment-form clearfix">';
					echo '<div class="ap-content-inner">';
						comment_form($comment_args, $comment_post_ID );
					echo '</div>';
				echo '</div>';
			$result['html'] = ob_get_clean();
			$result['container'] = '#comments-'.$comment_post_ID;

		}else{
			$result['message'] = __('You do not have permission to comment', 'ap');
			$result['message_type'] = 'warning';
		}

		ap_send_json($result);
    }

    /**
     * Ajax action for deleting comment
     * @return void
     * @since 2.0.0
     */
    public function delete_comment(){
    	if(isset($_POST['comment_ID']) && ap_user_can_delete_comment((int)$_POST['comment_ID'] ) && wp_verify_nonce( $_POST['__nonce'], 'delete_comment' )){
    		$delete = wp_delete_comment( (int)$_POST['comment_ID'], true );
    		
    		if($delete){
    			ap_send_json(ap_ajax_responce(  array( 'action' => 'delete_comment', 'comment_ID' => (int)$_POST['comment_ID'], 'message' => 'comment_delete_success')));
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
		
		if(ap_is_answer_selected($post->post_parent)){
			ap_do_event('unselect_answer', $user_id, $post->post_parent, $post->ID);
			update_post_meta($post->ID, ANSPRESS_BEST_META, 0);
			update_post_meta($post->post_parent, ANSPRESS_SELECTED_META, false);
			update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
			ap_send_json( ap_ajax_responce(array('message' => 'unselected_the_answer', 'action' => 'unselected_answer', 'do' => 'redirect', 'redirect_to' => get_permalink($post->ID))));

		}else{
			ap_do_event('select_answer', $user_id, $post->post_parent, $post->ID);
			update_post_meta($post->ID, ANSPRESS_BEST_META, 1);
			update_post_meta($post->post_parent, ANSPRESS_SELECTED_META, $post->ID);
			update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
			$html = ap_select_answer_btn_html($answer_id);
			ap_send_json( ap_ajax_responce(array('message' => 'selected_the_answer', 'action' => 'selected_answer', 'do' => 'redirect', 'redirect_to' => get_permalink($post->ID))));
		}
	}

	public function delete_post()
	{
		$post_id = (int) $_POST['post_id'];

		$action = 'delete_post_'.$post_id;	
		
		if(!wp_verify_nonce( $_POST['__nonce'], $action )){
			ap_send_json( ap_ajax_responce('something_wrong'));
			return;
		}

		if(!ap_user_can_delete($post_id)){
			ap_send_json( ap_ajax_responce('no_permission'));
			return;
		}
		
		$post = get_post( $post_id );
		wp_trash_post($post_id);
		if($post->post_type == 'question'){
			ap_send_json( ap_ajax_responce( array('action' => 'delete_question', 'do' => 'redirect', 'redirect_to' => get_permalink(ap_opt('anspress_questions')), 'message' => 'question_moved_to_trash')));
		}else{
			$current_ans = ap_count_published_answers($post->post_parent);
			$count_label = sprintf( _n('1 Answer', '%d Answers', $current_ans, 'ap'), $current_ans);
			$remove = (!$current_ans ? true : false);
			ap_send_json( ap_ajax_responce(array(
				'action' 		=> 'delete_answer', 
				'div_id' 			=> '#answer_'.$post_id,
				'count' 		=> $current_ans,
				'count_label' 	=> $count_label,
				'message' 		=> 'answer_moved_to_trash',
				'view'			=> array('answer_count' => $current_ans, 'answer_count_label' => $count_label))));
		}
		
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
		$tags = get_terms( 'question_tags', array(
			'orderby'   	=> 'count',
			'order' 		=> 'DESC',
			'hide_empty' 	=> false,
			'search' 		=> $keyword,
			'number' 		=> 8
		));
		
		$new_tag_html = '';
		if(ap_user_can_create_tag())
			$new_tag_html = '<div class="ap-cntlabel"><a href="#" id="ap-load-new-tag-form" data-args="'.wp_create_nonce('new_tag_form').'">'.__('Create new tag', 'ap').'</a></div>';
		
		if($tags){
			$items = array();
			foreach ($tags as $k => $t){
				$items[$k]['id'] 		= $t->slug;
				$items[$k]['name'] 		= $t->name;
				$items[$k]['count'] 	= $t->count;
				$items[$k]['description'] = ap_truncate_chars($t->description, 80);
			}
			$result = array('status' => true, 'items' => $items, 'form' => '<div class="clearfix"></div>'.$new_tag_html);
		}else{
			$form = '';
			if(ap_user_can_create_tag())
				$form = '<div class="ap-esw warning">'.__('No tags found', 'ap').'</div>'.ap_tag_form();
				
			$result = array('status' => false, 'message' => __('No related tags found', 'ap'), 'form' => $form);
		}
		
		die(json_encode($result));
	}

}
