<?php
/**
 * All actions of AnsPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_BP
{
	/**
	 * Initialize the class
	 * @since 2.0.1
	 */
	public function __construct()
	{
		add_action( 'bp_setup_nav',  array( $this, 'content_setup_nav') );
		add_post_type_support( 'question', 'buddypress-activity' );
		add_post_type_support( 'answer', 'buddypress-activity' );
		add_action( 'init', array($this, 'question_answer_tracking') );
		add_action( 'bp_activity_entry_meta', array($this, 'activity_buttons') );
		add_filter( 'bp_activity_custom_post_type_post_action', array($this, 'activity_action'), 10, 2 );
		
	}

	public function content_setup_nav()
	{
		global $bp;

		bp_core_new_nav_item( array(
		    'name'                  => sprintf(__('Questions %s', 'ap'), '<span class="count">'.count_user_posts( bp_displayed_user_id() , 'question' ).'</span>'),
		    'slug'                  => 'questions',
		    'screen_function'       => array($this, 'questions_screen_link'),
		    'position'              => 40,//weight on menu, change it to whatever you want
		    'default_subnav_slug'   => 'my-posts-subnav'
		) );
		bp_core_new_nav_item( array(
		    'name'                  => sprintf(__('Answers %s', 'ap'), '<span class="count">'.count_user_posts( bp_displayed_user_id() , 'answer' ).'</span>'),
		    'slug'                  => 'answers',
		    'screen_function'       => array($this, 'answers_screen_link'),
		    'position'              => 40,//weight on menu, change it to whatever you want
		    'default_subnav_slug'   => 'my-posts-subnav'
		) );
	}

	public function questions_screen_link() {
	    add_action( 'bp_template_title', array($this, 'questions_screen_title') );
	    add_action( 'bp_template_content', array($this, 'questions_screen_content') );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	public function questions_screen_title() {
	    _e('Questions', 'ap');
	}

	public function questions_screen_content() {
		global $questions;

    	$questions 		 = new Question_Query(array('author' => bp_displayed_user_id()));
    	echo '<div class="anspress-container">';
	    include ap_get_theme_location('user-questions.php');
	    echo '</div>';
	    wp_reset_postdata();
	}

	public function answers_screen_link() {
	    add_action( 'bp_template_title', array($this, 'answers_screen_title') );
	    add_action( 'bp_template_content', array($this, 'answers_screen_content') );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	public function answers_screen_title() {
	    _e('Answers', 'ap');
	}

	public function answers_screen_content() {
		global $answers;

    	$answers 		 = new Answers_Query(array('author' => bp_displayed_user_id()));
    	echo '<div class="anspress-container">';
	    include ap_get_theme_location('user-answers.php');
	    echo '</div>';
	    wp_reset_postdata();
	}

	public function question_answer_tracking(){
		// Check if the Activity component is active before using it.
	    if ( !function_exists('bp_is_active') || ! bp_is_active( 'activity' ) ) {
	        return;
	    }
	 
	    bp_activity_set_post_type_tracking_args( 'question', array(
	        'component_id'             => 'activity',
	        'action_id'                => 'new_question',
	        'contexts'                 => array( 'activity', 'member' ),
	        'bp_activity_admin_filter' => __( 'Asked a new question', 'ap' ),
            'bp_activity_front_filter' => __( 'Question', 'ap' ),
            'bp_activity_new_post'     => __( '%1$s asked a new <a href="AP_CPT_LINK">question</a>', 'ap' ),
            'bp_activity_new_post_ms'  => __( '%1$s asked a new <a href="AP_CPT_LINK">question</a>, on the site %3$s', 'ap' ),
	    ) );

	    bp_activity_set_post_type_tracking_args( 'answer', array(
	        'component_id'             => 'activity',
	        'action_id'                => 'new_answer',
	        'contexts'                 => array( 'activity', 'member' ),
	        'bp_activity_admin_filter' => __( 'Answered a question', 'ap' ),
            'bp_activity_front_filter' => __( 'Answered', 'ap' ),
            'bp_activity_new_post'     => __( '%1$s <a href="AP_CPT_LINK">answered</a> a question', 'ap' ),
            'bp_activity_new_post_ms'  => __( '%1$s <a href="AP_CPT_LINK">answered</a> a question, on the site %3$s', 'ap' ),
	    ) );
	}

	public function activity_buttons()
	{
		if('new_question' == bp_get_activity_type())
			echo '<a class="button answer bp-secondary-action" title="'.__('Answer this question', 'ap').'" href="'.ap_answers_link(bp_get_activity_secondary_item_id()).'">'.__('Answer', 'ap').'</a>';
	}

	public function activity_action($action, $activity)
	{	
		if($activity->type == 'new_question' || $activity->type == 'new_answer')
			return str_replace('AP_CPT_LINK', get_permalink( $activity->secondary_item_id ), $action);

		return $action;
	}
}