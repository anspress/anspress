<?php
/*
	Name:Basic Email
	Description: Basic email notification
	Version:1.0
	Author: Rahul Aryan
	Author URI: http://open-wp.com
	Addon URI: http://open-wp.com/anspress
*/


class AP_Basic_Email_Addon
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
		add_action( 'ap_after_inserting_question', array($this, 'new_question_email') );
		add_action( 'ap_after_inserting_answer', array($this, 'new_answer_email') );
		add_action( 'ap_event_new_comment', array($this, 'new_comment'), 10, 3);
		add_action('ap_event_select_answer', array($this, 'select_answer'), 10, 3);
    }
	
	/* Notify admin about new questions */
	public function new_question_email( $post_id ) {
		// lets check if post is not revision
		if ( !wp_is_post_revision( $post_id ) ) {
			$post_url = get_permalink( $post_id );
			$post = get_post($post_id); 
			$subject = __('New Question: ', 'ap'). $post->post_title;		
			$message = sprintf(__('Hello! Admin, <br /><br /> A new question is posted by %s <br />', 'ap'), ap_user_display_name($post->post_author, true));
			$message .= ap_truncate_chars(strip_tags($post->post_content), 100);
			$message .= "<br /><br /><a href='". $post_url. "'>".__('View question', 'ap')."</a><br />";
			$message .= '<p style="color:#777; font-size:11px">'.__('Powered by', 'ap').' <a href="http://open-wp.com">AnsPress</a></p>';
			
			//sends email
			wp_mail(get_option( 'admin_email' ), $subject, $message );
		}
	}
	
	public function new_answer_email( $post_id ) {

		// lets check if post is not revision
		if ( !wp_is_post_revision( $post_id ) ) {
			$post = get_post($post_id);
			
			$emails = ap_get_email_to_notify($post->post_parent, $post->post_author);			
			$current_user_email = get_the_author_meta( 'user_email', $post->post_author);
			$admin_email = get_option( 'admin_email' );
			
			if($admin_email != $current_user_email)
				$emails['admin'] = $admin_email;	
			
			if(empty($emails) || !$emails)
				return false;
				
			$emails = array_unique ($emails);
	
			$parent = get_post($post->post_parent);
			
			$subject = __('New Answer on: ', 'ap'). $parent->post_title;
			
			$message = sprintf(__('Hello!,<br /><br /> A new answer is posted by %s <br />', 'ap'), ap_user_display_name($post->post_author, true));
			
			$message .= ap_truncate_chars(strip_tags($post->post_content), 100);
			$message .= "<br /><br /><a href='". get_permalink($parent->ID)."#".$post->ID. "'>".__('View Answer', 'ap')."</a><br />";
			
			$message .= '<p style="color:#777; font-size:11px">'.__('Powered by', 'ap').'<a href="http://open-wp.com">AnsPress</a></p>';
			
			if(!empty($emails))
				foreach($emails as $email){
					if(is_email($email))
						wp_mail($email, $subject, $message );
				}

		}
	}
	
	public function new_comment($comment, $post_type, $question_id){
		$emails = ap_get_email_to_notify($comment->comment_post_ID, $comment->user_id);

		$post = get_post($question_id);
		if($post_type == 'question'){
			$subject = __('New comment on: ', 'ap'). $post->post_title;
			
			$message = sprintf(__('Hello!,<br /><br /> A new comment is posted by %s <br />', 'ap'), ap_user_display_name($comment->user_id, true));
			
			$message .= ap_truncate_chars($comment->comment_content , 100);
			$message .= "<br /><br /><a href='". get_comments_link($parent->ID). "'>".__('View comment', 'ap')."</a><br />";
			
			$message .= '<p style="color:#777; font-size:11px">'.__('Powered by', 'ap').'<a href="http://open-wp.com">AnsPress</a></p>';
			
			if(!empty($emails))
				foreach($emails as $email){
					wp_mail($email, $subject, $message );
				}
		}else{
			$subject = __('New comment on answer of: ', 'ap'). $post->post_title;
			
			$message = sprintf(__('Hello!,<br /><br /> A new comment is posted by %s <br />', 'ap'), ap_user_display_name($comment->user_id, true));
			
			$message .= ap_truncate_chars($comment->comment_content , 100);
			$message .= "<br /><br /><a href='". get_comments_link($parent->ID). "'>".__('View comment', 'ap')."</a><br />";
			
			$message .= '<p style="color:#777; font-size:11px">'.__('Powered by', 'ap').'<a href="http://open-wp.com">AnsPress</a></p>';
			
			if(!empty($emails))
				foreach($emails as $email){
					wp_mail($email, $subject, $message );
				}
		}
	}
	
	public function select_answer($user_id, $question_id, $answer_id){
		$post_url = get_permalink( $question_id );
		$post = get_post($post_id); 
		$subject = __('Your answer is selected as a best', 'ap');		
		$message = sprintf(__('Hello!, <br /><br /> You answer on %s is selected as best by %s <br />', 'ap'), $pos->post_title, ap_user_display_name($user_id, true));

		$message .= "<br /><br /><a href='". $post_url. "'>".__('View question', 'ap')."</a><br />";
		$message .= '<p style="color:#777; font-size:11px">'.__('Powered by', 'ap').' <a href="http://open-wp.com">AnsPress</a></p>';

		$emails = ap_get_email_to_notify($post->post_parent, $user_id);
		
		if(!empty($emails))
			foreach($emails as $email){
				wp_mail($email, $subject, $message );
			}
		
		//sends email
		wp_mail(get_option( 'admin_email' ), $subject, $message );

	}

}

function ap_get_email_to_notify($post_id, $current_user){
	$parti_emails = ap_get_parti_emails($post_id);
			
	if(isset($parti_emails[$current_user]))
		unset($parti_emails[$current_user]);
	
	// return if no email found
	if(empty($parti_emails))
		return false;	
	
	return $parti_emails;
}


AP_Basic_Email_Addon::get_instance();
