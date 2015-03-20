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
		
	}

	public function content_setup_nav()
	{
		global $bp;

		bp_core_new_nav_item( array(
		    'name'                  => __('Questions', 'ap'),
		    'slug'                  => 'questions',
		    'screen_function'       => array($this, 'questions_screen_link'),
		    'position'              => 40,//weight on menu, change it to whatever you want
		    'default_subnav_slug'   => 'my-posts-subnav'
		) );
		bp_core_new_nav_item( array(
		    'name'                  => __('Answers', 'ap'),
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
}