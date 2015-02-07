<?php
/**
 * Class for base page
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_Common_Pages
{
    private $template;

    protected static $instance = NULL;

    public static function get_instance()
    {
        // create an object
        NULL === self::$instance and self::$instance = new self;

        return self::$instance; // return the object
    }

    public function __construct()
    {
        global $questions;

        ap_register_page('base', __('Questions', 'ap'), array($this, 'base_page'));
        ap_register_page('question', __('Question', 'ap'), array($this, 'question_page'));
        ap_register_page('ask', __('Ask', 'ap'), array($this, 'ask_page'));
        ap_register_page('edit', __('Edit', 'ap'), array($this, 'edit_page'));
    }


    public function base_page()
    {
    	global $questions;

    	$questions 		 = new Question_Query();

		include(ap_get_theme_location('base.php'));
    }

    public function question_page()
    {
        global $questions;

        $questions          = new WP_Query(array('p' => get_question_id(), 'post_type' => 'question'));
        
        // Set current question as a global $post
        while ( $questions->have_posts() ) : $questions->the_post();
            global $post;
            setup_postdata( $post ); 
        endwhile;

        include(ap_get_theme_location('question.php'));
    }

    public function ask_page()
    {
         include ap_get_theme_location('ask.php');
    }

    public function edit_page()
    {
        $post_id = (int) sanitize_text_field( get_query_var( 'edit_post_id' ));
        if( !ap_user_can_edit_question($post_id)){
                echo '<p>'.__('You don\'t have permission to access this page.', 'ap').'</p>';
                return;
        }else{
            global $editing_post;
            $editing_post = get_post($post_id);
            
            // include theme file
            include ap_get_theme_location('edit.php');
        }
    }

}

