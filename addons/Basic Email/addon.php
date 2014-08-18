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
			$subject = 'AnsPress: '.__('New Question: ', 'ap'). $post->post_title;		
			$message = sprintf(__('Hello! Admin, <br /><br /> A new question is posted by %s <br />', 'ap'), ap_user_display_name($post->post_author, true));
			$message .= ap_truncate_chars($post->post_content, 100);
			$message .= "<br /><br /><a href='". $post_url. "'>".__('View question', 'ap')."</a><br />";
			//sends email
			wp_mail(get_option( 'admin_email' ), $subject, $message );
		}
	}
	
	public function new_answer_email( $post_id ) {
		// lets check if post is not revision
		if ( !wp_is_post_revision( $post_id ) ) {

			$post = get_post($post_id); 
			$parent = get_post($post->post_parent);
			$subject = 'AnsPress: '.__('New Answer: ', 'ap'). $parent->post_title;	
			$message = sprintf(__('Hello!,<br /><br /> A new answer is posted by %s <br />', 'ap'), ap_user_display_name($post->post_author, true));			
			$message .= ap_truncate_chars($post->post_content, 100);
			$message .= "<br /><br /><a href='". get_permalink($parent->ID)."#".$post->ID. "'>".__('View Answer', 'ap')."</a><br />";
			
			//sends email
			
			if($post->post_author != $parent->post_author){
				$email = get_the_author_meta( 'user_email', $parent->post_author);
				wp_mail($email, $subject, $message );
			}
			
			if(!in_array($post->post_author, get_super_admins()))
				wp_mail(get_option( 'admin_email' ), $subject, $message );
		}
	}

}


AP_Basic_Email_Addon::get_instance();
