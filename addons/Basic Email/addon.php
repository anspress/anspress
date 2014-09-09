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
    }
	
	/* Notify admin about new questions */
	public function new_question_email( $post_id ) {
		// lets check if post is not revision
		if ( !wp_is_post_revision( $post_id ) ) {
			$post_url = get_permalink( $post_id );
			$post = get_post($post_id); 
			$subject = __('New Question: ', 'ap'). $post->post_title;		
			$message = sprintf(__('Hello! Admin, <br /><br /> A new question is posted by %s <br />', 'ap'), ap_user_display_name($post->post_author, true));
			$message .= ap_truncate_chars($post->post_content, 100);
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
			$parti_emails = ap_get_parti_emails($post->post_parent);
			
			if(isset($parti_emails[$post->post_author]))
				unset($parti_emails[$post->post_author]);
			
			// return if no email found
			if(empty($parti_emails))
				return;			
			
			$parent = get_post($post->post_parent);
			
			$subject = __('New Answer on: ', 'ap'). $parent->post_title;
			
			$message = sprintf(__('Hello!,<br /><br /> A new answer is posted by %s <br />', 'ap'), ap_user_display_name($post->post_author, true));
			
			$message .= ap_truncate_chars($post->post_content, 100);
			$message .= "<br /><br /><a href='". get_permalink($parent->ID)."#".$post->ID. "'>".__('View Answer', 'ap')."</a><br />";
			
			$message .= '<p style="color:#777; font-size:11px">'.__('Powered by', 'ap').'<a href="http://open-wp.com">AnsPress</a></p>';
			
			//sends email
			
			foreach($parti_emails as $email){
				wp_mail($email, $subject, $message );
			}
			
			$admins = get_option( 'admin_email' );
			
			$current_user_email = get_the_author_meta( 'user_email', $post->post_author);
			
			foreach($admins as $k => $admin_email){
				if($admin_email == $current_user_email)
					unset($admins[$k]);
			}
			
			if(!empty($admins))
				foreach($admins as $email)
					wp_mail($email, $subject, $message );
		}
	}

}


AP_Basic_Email_Addon::get_instance();
