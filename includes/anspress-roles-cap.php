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
class AP_Roles_Permission
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;

    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }

    public function __construct()
    {
		//add_filter( 'user_has_cap', array($this, 'author_cap_filter'), 10, 3 );
	}
	
	function author_cap_filter( $allcaps, $cap, $args ) {

		// Bail out if we're not asking about a post:
		if ( 'edit_post' != $args[0] )
			return $allcaps;

		// Bail out for users who can already edit others posts:
		if ( $allcaps['edit_others_posts'] )
			return $allcaps;

		// Bail out for users who can't publish posts:
		if ( !isset( $allcaps['publish_posts'] ) or !$allcaps['publish_posts'] )
			return $allcaps;

		// Load the post data:
		$post = get_post( $args[2] );

		// Bail out if the user is the post author:
		if ( $args[1] == $post->post_author )
			return $allcaps;

		// Bail out if the post isn't pending or published:
		if ( ( 'pending' != $post->post_status ) and ( 'publish' != $post->post_status ) )
			return $allcaps;

		// Load the author data:
		$author = new WP_User( $post->post_author );

		// Bail out if post author can edit others posts:
		if ( $author->has_cap( 'edit_others_posts' ) )
			return $allcaps;

		$allcaps[$cap[0]] = true;

		return $allcaps;

	}
}

function ap_show_form_to_guest(){
	return ap_opt('show_login_signup');
}

/* Check if a user can ask a question */
function ap_user_can_ask(){
	if(current_user_can('ap_new_question') || is_super_admin() || ap_show_form_to_guest() || ap_allow_anonymous())
		return true;
	
	return false;
}

/* Check if a user can answer on a question */
function ap_user_can_answer($question_id){
	if(is_super_admin())
		return true;
	
	if(ap_opt('close_after_selecting') && ap_is_answer_selected($question_id) )
		return false;
		
	if((current_user_can('ap_new_answer') || ap_show_form_to_guest())){
		if(!ap_opt('multiple_answers') && ap_is_user_answered($question_id, get_current_user_id()) && get_current_user_id() != '0')
			return false;
		else
			return true;
	}
	return false;
}

/* Check if a user can answer on a question */
function ap_user_can_see_answers(){
	if(is_super_admin())
		return true;
	
	if(ap_opt('logged_in_can_see_ans') && !is_user_logged_in() )
		return false;

	return true;
}

function ap_user_can_select_answer($post_id){
	if(is_super_admin())
		return true;
	
	$post 		= get_post($post_id);
	$question 	= get_post($post->post_parent);
	
	global $current_user;
	
	$user_id		= $current_user->ID;
	
	if($post->post_type == 'answer' && $question->post_author ==  $user_id){
		return true;
	}
	
	return false;
}


/* Check if a user can edit answer on a question */
function ap_user_can_edit_ans($post_id){
	if(current_user_can('ap_edit_answer') || current_user_can('ap_edit_others_answer') || is_super_admin()){
		$post = get_post($post_id);
		global $current_user;
		$user_id		= $current_user->ID;
		if(($post->post_author ==  $user_id) || current_user_can('ap_edit_others_answer') || is_super_admin())
			return true;
		else
			return false;
	}
	return false;
}

function ap_user_can_edit_question($post_id = false){
	if(is_super_admin() || current_user_can('ap_edit_others_question') )
		return true;
		
	if(current_user_can('ap_edit_question') || current_user_can('ap_edit_others_question') || is_super_admin()){
		global $current_user;
		if($post_id )
			$post = get_post($post_id);
		else
			global $post;
			
		if(($current_user->ID == $post->post_author) && current_user_can('ap_edit_question'))
			return true;
	}
	return false;
}

function ap_user_can_change_label(){
	if(is_super_admin() || current_user_can('ap_change_label'))
		return true;

	return false;
}

function ap_user_can_comment(){
	if(is_super_admin() || current_user_can('ap_new_comment') || ap_opt('anonymous_comment'))
		return true;

	return false;
}
function ap_user_can_edit_comment($comment_id){
	if(is_super_admin() || current_user_can('ap_mod_comment'))
		return true;
	
	global $current_user;	
	if( current_user_can('ap_edit_comment') && ($current_user->ID == get_comment($comment_id)->user_id))
		return true;

	return false;
}

function ap_user_can_delete_comment($comment_id){
	if(is_super_admin() || current_user_can('ap_mod_comment'))
		return true;
	
	global $current_user;	
	if( current_user_can('ap_delete_comment') && ($current_user->ID == get_comment($comment_id)->user_id))
		return true;

	return false;
}

function ap_user_can_delete($postid){
	if(is_super_admin())
		return true;
	
	$post = get_post($postid);
	global $current_user;
	
	if($current_user->ID == $post->post_author){
		if( ($post->post_type == 'question' || $post->post_type == 'answer') && current_user_can('ap_delete_question'))
			return true;
	}else{
		if( $post->post_type == 'question' && current_user_can('ap_delete_others_question'))
			return true;
		elseif( $post->post_type == 'answer' && current_user_can('ap_delete_others_answer'))
			return true;	
	}

	return false;
}

function ap_user_can_upload_cover(){
	if(is_super_admin() || current_user_can('ap_upload_cover'))
		return true;
	
	return false;
}

function ap_user_can_message(){
	if(is_super_admin() || current_user_can('ap_message'))
		return true;
	
	return false;
}

function ap_user_can_create_tag(){
	if(is_super_admin() || (current_user_can('ap_new_tag') && ap_get_points() >= ap_opt('min_point_new_tag') ))
		return true;
	
	return false;
}

function ap_user_can_view_private_question($question_id){
	if(is_super_admin() || current_user_can('ap_view_private'))
		return true;
	
	$post = get_post( $question_id );
	
	if($post->post_author == get_current_user_id())
		return true;
	
	return false;
}

function ap_user_can_view_question($question_id = false){
	if(is_super_admin())
		return true;
		
	if(!$question_id)
		$question_id = get_the_ID();
	
	$post = get_post( $question_id );
	
	if( $post->post_status == 'publish' || ($post->post_status == 'private_question' && ap_user_can_view_private_question($question_id)))
		return true;
	
	return false;
	
}

function ap_allow_anonymous(){
	return ap_opt('allow_anonymous');
}