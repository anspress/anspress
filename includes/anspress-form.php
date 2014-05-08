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
		add_action( 'init', array($this, 'process_ask_form') );
		add_action( 'init', array($this, 'process_answer_form') );
		add_action( 'init', array($this, 'process_edit_question_form') );
		add_action( 'init', array($this, 'process_edit_answer_form') );
		add_action( 'wp_ajax_ap_load_comment_form', array($this, 'load_ajax_commentform') ); 
		add_action( 'wp_ajax_nopriv_ap_load_comment_form', array($this, 'load_ajax_commentform') ); 
		add_action( 'wp_ajax_nopriv_ap_not_logged_in_messgae', array($this, 'ap_not_logged_in_messgae') ); 
		add_action( 'wp_ajax_ap_edit_comment_form', array($this, 'edit_comment_form') ); 
		add_action( 'wp_ajax_ap_save_comment_form', array($this, 'save_comment_form') ); 
		add_action( 'wp_ajax_ap_delete_comment', array($this, 'delete_comment') ); 
    }
	
	public function process_ask_form(){	
		if(isset($_POST['is_question']) && isset($_POST['submitted']) && isset($_POST['ask_form']) && wp_verify_nonce($_POST['ask_form'], 'post_nonce')) {

			if ( !is_user_logged_in() )
				return;
			
			if(!ap_user_can_ask())
				return;
			
			$validate = ap_validate_form();
			if($validate['has_error'])
				return;

			do_action('process_ask_form');
			
			global $current_user;
			$user_id		= $current_user->ID;
			

			$question_array = array(
				'post_title'	=> sanitize_text_field($_POST['post_title']),
				'post_author'	=> $user_id,
				'post_content' 	=>  wp_kses($_POST['post_content'], ap_form_allowed_tags()),
				'post_type' 	=> 'question',
				'post_status' 	=> 'publish'
			);

			$post_id = wp_insert_post($question_array);
			
			if($post_id){
				// Update Custom Meta
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['category']), 'question_category' );
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['tags']), 'question_tags' );
				ap_set_question_status($post_id);
				// Redirect
				wp_redirect( get_permalink($post_id) ); exit;
			}
		}	
	}	
	
	public function process_answer_form(){

		if(isset($_POST['is_answer']) && isset($_POST['submitted']) && isset($_POST['answer_form']) && wp_verify_nonce($_POST['answer_form'], 'post_nonce')) {
			
			if ( !is_user_logged_in() )
				return;
			
			$validate = ap_validate_form();
			if($validate['has_error'])
				return;
				
			if(!isset($_POST['form_question_id']) && (!is_int($_POST['form_question_id'])) && ('question' !== get_post_type( $_POST['form_question_id'] )))
				return;
			
			$post = get_post($_POST['form_question_id']);
			global $current_user;
			$user_id		= $current_user->ID;
			
			if(!ap_user_can_answer($post->ID) )
				return;
			
			do_action('process_answer_form');
			
			$ans_array = array(
				'post_author'	=> $user_id,
				'post_content' 	=> wp_kses($_POST['post_content'], ap_form_allowed_tags()),
				'post_type' 	=> 'answer',
				'post_status' 	=> 'publish',
				'post_parent' 	=> sanitize_text_field($_POST['form_question_id'])
			);

			$post_id = wp_insert_post($ans_array);
		}
	}
	
	public function process_edit_question_form(){
		
		if(isset($_POST['is_question']) && isset($_POST['submitted']) && isset($_POST['edited']) && wp_verify_nonce($_POST['edit_question'], 'post_nonce-'.$_POST['question_id'])) {
			
			$post_id = $_POST['question_id'];
			
			$post = get_post($post_id);
			
			if( !ap_user_can_edit_question($post->ID))
				return;
			
			if(!ap_user_can_ask())
				return;
			
			$validate = ap_validate_form();
			if($validate['has_error'])
				return;

			do_action('process_ask_form');
			
			global $current_user;
			$user_id		= $current_user->ID;
			
			$question_array = array(
				'ID'			=> $post_id,
				'post_title'	=> sanitize_text_field($_POST['post_title']),
				//'post_author'	=> $user_id,
				'post_content' 	=>  wp_kses($_POST['post_content'], ap_form_allowed_tags()),
				'post_status' 	=> 'publish'
			);

			$post_id = wp_update_post($question_array);
			
			if($post_id){
				// Update Custom Meta
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['category']), 'question_category' );
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['tags']), 'question_tags' );

				// Redirect
				wp_redirect( get_permalink($post_id) ); exit;
			}
		}
	}	
	public function process_edit_answer_form(){
		
		if(isset($_POST['is_answer']) && isset($_POST['submitted']) && isset($_POST['edited']) && wp_verify_nonce($_POST['edit_answer'], 'post_nonce-'.$_POST['answer_id'])) {
			
			$post_id = $_POST['answer_id'];
			
			$post = get_post($post_id);
			
			if( !ap_user_can_edit_ans($post->ID))
				return;
			
			$validate = ap_validate_form();
			if($validate['has_error'])
				return;

			
			global $current_user;
			$user_id		= $current_user->ID;
			

			$answer_array = array(
				'ID'			=> $post_id,
				//'post_author'	=> $user_id,
				'post_content' 	=>  wp_kses($_POST['post_content'], ap_form_allowed_tags()),
				'post_status' 	=> 'publish'
			);

			$post_id = wp_update_post($answer_array);
			
			if($post_id){
				// Update Custom Meta
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['category']), 'question_category' );
				wp_set_post_terms( $post_id, sanitize_text_field($_POST['tags']), 'question_tags' );

				$cur_post = get_post($post_id);
				// Redirect
				wp_redirect( get_permalink($cur_post->post_parent) ); exit;
			}
		}
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
				'comment_field' => '<textarea id="anspress-comment" name="comment" cols="45" rows="2" aria-required="true"></textarea>',
				'comment_notes_after' => ''
			);
			
			comment_form($comment_args, $args[0] );

		}
		die();
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
			echo '<form id="edit-comment-'. $args[0].'" class="inline-edit-comment">';
			echo '<textarea name="content">'.$comment->comment_content.'</textarea>';
			echo '<button class="btn" data-action="save-inline-comment" data-elem="#edit-comment-'. $args[0].'">'.__('Save', 'ap').'</button>';
			echo '<input type="hidden" name="comment_id" value="'.$args[0].'"/>';
			wp_nonce_field('save-comment-'.$args[0], 'nonce');
			echo '</form>';
		}
		die();
	}
	
	public function save_comment_form(){
		$args = wp_parse_args($_REQUEST['args']);
		$comment_id = sanitize_text_field($args['comment_id']);
		if(!ap_user_can_edit_comment($comment_id)){
			_e('No Permission', 'ap');
			die();
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
				printf( '<div class="comment-meta"> %1$s <a href="%2$s"><time datetime="%3$s">%4$s %5$s</time></a> <a href="#" data-action="edit-comment" data-args="%6$s">%7$s</a></div>',
					ap_user_display_name_point($comment->user_id),
					esc_url( get_comment_link( $comment->comment_ID ) ),
					get_comment_date( 'c', $comment->comment_ID ),
					ap_human_time(get_comment_date('U', $comment->comment_ID)),
					__('ago', 'ap'),
					$comment->comment_ID.'-'.wp_create_nonce( 'comment-'.$comment->comment_ID ),
					__('edit', 'ap')
				);
				echo '<p class="comment-texts">'.$comment->comment_content.'</p>';
			}
		}
		die();
	}
	
	public function delete_comment(){
		$args = $args = explode('-', sanitize_text_field($_REQUEST['args']));
		if(!ap_user_can_delete_comment($args[0])){
			_e('No Permission', 'ap');
			die();
		}		
		$action = 'delete-comment-'.$args[0];		
		if(wp_verify_nonce( $args[1], $action )){
			wp_delete_comment( $args[0], true );
		}
		die();
	}
	
	public function ap_not_logged_in_messgae(){
		ap_please_login();
		die();
	}
}

function ap_form_allowed_tags(){
	$allowed_tags = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'p' => array(),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
		'pre' => array(),
	);
	
	return apply_filters( 'ap_allowed_tags', $allowed_tags);
}

function ap_ask_form(){
	global $post;
	global $current_user;
	$validate = ap_validate_form();
	
	if(isset($validate['has_error']) && $validate['has_error']){
		echo '<div class="alert alert-danger">'. implode(', ', $validate['message']) .'</div>';
	}
	
	if( ap_user_can_ask()){
		?>
		<form action="" id="ask_question_form" method="POST">
			<?php do_action('ap_ask_form_top'); ?>
			<div class="ap-form-group">
				<label for="post_title"><?php _e('Title', 'ap') ?></label>				
				<input type="text" name="post_title" id="post_title" value="" class="form-control" placeholder="<?php _e('Question in one sentence', 'ap'); ?>" />
			</div>
			<div class="ap-form-group">						
				<label for="post_content"><?php _e('Content', 'ap') ?></label>
				<?php 
					wp_editor( '', 'post_content', array('media_buttons' => false, 'quicktags' => false, 'textarea_rows' => 7, 'teeny' => true)); 
				?>
			</div>

			<div class="ap-form-group">
				<label for="category"><?php _e('Category', 'ap') ?></label>
				<select class="form-control" name="category" id="industries">
					<?php 
					$taxonomies = get_terms( 'question_category', 'orderby=count&hide_empty=0' );
					foreach($taxonomies as $cat)
							echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
					?>
				</select>
			</div>
			<div class="ap-form-group">
				<label for="tags"><?php _e('Tags', 'ap') ?></label>
				<input type="text" value="" tabindex="5" name="tags" id="tags" class="form-control" />
			</div>
			<?php do_action('ap_ask_form_bottom'); ?>
			<?php ap_ask_form_hidden_input(); ?>
			
		</form>
		<?php
	}
}

function ap_validate_form(){
	if(isset($_POST['is_question']) && isset($_POST['submitted'])) {
		$error 		= array();
		$has_error 	= false;
		if(trim($_POST['post_title']) === '') {
			$error[] = __('Please enter a title', 'ap');
			$has_error = true;
		}	
		
		if(trim($_POST['post_content']) === '') {
			$error[] = __('Please enter content of question', 'ap');
			$has_error = true;
		}
		
		do_action('ap_validate_ask_form');
		
		return array('has_error' => $has_error, 'message' => $error);	
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
	echo '<button type="submit">'. __('Ask Question', 'ap'). '</button>';
}

function ap_answer_form_hidden_input($question_id){
	do_action('ap_answer_form_bottom');
	
	wp_nonce_field('post_nonce', 'answer_form');
	echo '<input type="hidden" name="is_answer" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<input type="hidden" name="form_question_id" value="'.$question_id.'" />';
	echo '<button type="submit">'. __('Submit Answer', 'ap'). '</button>';
}


function ap_edit_question_form_hidden_input($post_id){
	wp_nonce_field('post_nonce-'.$post_id, 'edit_question');
	echo '<input type="hidden" name="is_question" value="true" />';
	echo '<input type="hidden" name="question_id" value="'.$post_id.'" />';
	echo '<input type="hidden" name="edited" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<button type="submit">'. __('Update question', 'ap'). '</button>';
}



function ap_edit_answer_form($post_id){

	if( !ap_user_can_edit_ans($post_id)){
		echo '<p>'.__('You don\'t have permission to access this page.', 'ap').'</p>';
		return;
	}

	$action = get_post_type($post_id).'-'.$post_id;	

	
	if(!isset($_REQUEST['ap_nonce']) || !wp_verify_nonce($_REQUEST['ap_nonce'], $action)){
		echo '<p>'.__('Trying to cheat? huh!.', 'ap').'</p>';
		return;
	}
	
	global $current_user;
	$post = get_post($post_id);
	
	$validate = ap_validate_form();
	
	if(isset($validate['has_error']) && $validate['has_error']){
		echo '<div class="alert alert-danger">'. implode(', ', $validate['message']) .'</div>';
	}	

	?>
	<form action="" id="ask_question_form" method="POST">
		<?php do_action('ap_ask_form_top'); ?>

		<div class="form-group">						
			<label for="post_content"><?php _e('Your Answer', 'ap') ?></label>
			<?php 
				wp_editor( apply_filters('the_content', $post->post_content), 'post_content', array('media_buttons' => false, 'quicktags' => false, 'textarea_rows' => 15, 'teeny' => true)); 
			?>
		</div>

		<?php do_action('ap_edit_question_form_bottom'); ?>
		<?php ap_edit_answer_form_hidden_input($post_id); ?>
		
	</form>
	<?php

}
function ap_edit_answer_form_hidden_input($post_id){
	wp_nonce_field('post_nonce-'.$post_id, 'edit_answer');
	echo '<input type="hidden" name="is_answer" value="true" />';
	echo '<input type="hidden" name="answer_id" value="'.$post_id.'" />';
	echo '<input type="hidden" name="edited" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<button type="submit">'. __('Update Answer', 'ap'). '</button>';
}