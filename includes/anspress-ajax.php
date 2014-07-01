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

class anspress_ajax
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
		add_action('wp_ajax_ap_set_status', array($this, 'ap_set_status'));
		add_action('wp_ajax_nopriv_ap_check_username', array($this, 'check_username'));
		add_action('wp_ajax_nopriv_ap_check_email', array($this, 'check_email'));
		add_action('wp_ajax_recount_votes', array($this, 'recount_votes'));
		add_action('wp_ajax_recount_views', array($this, 'recount_views'));
		add_action('wp_ajax_recount_fav', array($this, 'recount_fav'));
		add_action('wp_ajax_recount_flag', array($this, 'recount_flag'));
		add_action('wp_ajax_recount_close', array($this, 'recount_close'));
    }
	
	public function ap_set_status(){
		if(!is_user_logged_in())
			die('not_logged_in');
		
		if(ap_user_can_change_status()){
			$args = explode('-', sanitize_text_field($_REQUEST['args']));
			$action = 'question-'.$args[0];	
			if(wp_verify_nonce( $args[1], $action )){		
				ap_set_question_status($args[0], $args[2]);
			}
		}
		//ap_change_status_btn_html($args[0]);
		die();		
	}
	
	public function check_username(){
	   $username = sanitize_text_field($_POST['username']);
       if ( username_exists( $username ) )
           echo 'false' ;
		else
			echo 'true';
		
		die();
	}
	public function check_email(){
	   $email = sanitize_text_field($_POST['email']);

       if ( email_exists( $email ) )
           echo 'false' ;
		else
			echo 'true';
		
		die();
	}
	
	public function recount_votes(){
		$args=array(
			'post_type' => 'question',
			'showposts' => 40,
			'orderby' => 'meta_value_num',
			'meta_query' => array(
				array(
				 'key' => ANSPRESS_VOTE_META,
				 'compare' => 'NOT EXISTS'
				),
			)
		);
		$questions = new WP_Query($args);

		if($questions->have_posts()){
			$count = $questions->found_posts;
			while ( $questions->have_posts() ) : $questions->the_post(); 
				add_post_meta(get_the_ID(), ANSPRESS_VOTE_META, '0', true);
			endwhile ;
			wp_reset_query();

			printf( __( 'Checked %s questions.', 'ap' ), $count);
		}else{
			 _e( 'All questions looks fine.', 'ap' );
		}
		
		$args=array(
			'post_type' => 'answer',
			'showposts' => 40,
			'orderby' => 'meta_value_num',
			'meta_query' => array(
				array(
				 'key' => ANSPRESS_VOTE_META,
				 'compare' => 'NOT EXISTS'
				),
			)
		);
		$questions = new WP_Query($args);

		if($questions->have_posts()){
			$count = $questions->found_posts;
			while ( $questions->have_posts() ) : $questions->the_post(); 
				add_post_meta(get_the_ID(), ANSPRESS_VOTE_META, '0', true);
			endwhile ;
			wp_reset_query();

			printf( __( 'Checked %s answers.', 'ap' ), $count);
		}else{
			 _e( 'All answers looks fine.', 'ap' );
		}
		
		die();
	}
	public function recount_views(){
		$args=array(
			'post_type' => 'question',
			'showposts' => 40,
			'orderby' => 'meta_value_num',
			'meta_query' => array(
				array(
				 'key' => ANSPRESS_VIEW_META,
				 'compare' => 'NOT EXISTS'
				),
			)
		);
		$questions = new WP_Query($args);

		if($questions->have_posts()){
			$count = $questions->found_posts;
			while ( $questions->have_posts() ) : $questions->the_post(); 
				add_post_meta(get_the_ID(), ANSPRESS_VIEW_META, '0', true);
			endwhile ;
			wp_reset_query();

			printf( __( 'Checked %s questions.', 'ap' ), $count);
		}else{
			 _e( 'All questions looks fine.', 'ap' );
		}
		
		die();
	}	
	public function recount_fav(){
		$args=array(
			'post_type' => 'question',
			'showposts' => 40,
			'orderby' => 'meta_value_num',
			'meta_query' => array(
				array(
				 'key' => ANSPRESS_FAV_META,
				 'compare' => 'NOT EXISTS'
				),
			)
		);
		$questions = new WP_Query($args);

		if($questions->have_posts()){
			$count = $questions->found_posts;
			while ( $questions->have_posts() ) : $questions->the_post(); 
				add_post_meta(get_the_ID(), ANSPRESS_FAV_META, '0', true);
			endwhile ;
			wp_reset_query();

			printf( __( 'Checked %s questions.', 'ap' ), $count);
		}else{
			 _e( 'All questions looks fine.', 'ap' );
		}
		
		die();
	}	
	public function recount_flag(){
		$args=array(
			'post_type' => 'question',
			'showposts' => 40,
			'orderby' => 'meta_value_num',
			'meta_query' => array(
				array(
				 'key' => ANSPRESS_FLAG_META,
				 'compare' => 'NOT EXISTS'
				),
			)
		);
		$questions = new WP_Query($args);

		if($questions->have_posts()){
			$count = $questions->found_posts;
			while ( $questions->have_posts() ) : $questions->the_post(); 
				add_post_meta(get_the_ID(), ANSPRESS_FLAG_META, '0', true);
			endwhile ;
			wp_reset_query();

			printf( __( 'Checked %s questions.', 'ap' ), $count);
		}else{
			 _e( 'All questions looks fine.', 'ap' );
		}
		
		die();
	}
	public function recount_close(){
		$args=array(
			'post_type' => 'question',
			'showposts' => 40,
			'orderby' => 'meta_value_num',
			'meta_query' => array(
				array(
				 'key' => ANSPRESS_CLOSE_META,
				 'compare' => 'NOT EXISTS'
				),
			)
		);
		$questions = new WP_Query($args);

		if($questions->have_posts()){
			$count = $questions->found_posts;
			while ( $questions->have_posts() ) : $questions->the_post(); 
				add_post_meta(get_the_ID(), ANSPRESS_CLOSE_META, '0', true);
			endwhile ;
			wp_reset_query();

			printf( __( 'Checked %s questions.', 'ap' ), $count);
		}else{
			 _e( 'All questions looks fine.', 'ap' );
		}
		
		die();
	}
	
}
