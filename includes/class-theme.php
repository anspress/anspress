<?php

/**
 * Class for anspress theme
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if (!defined('WPINC')) 
{
    die;
}

class AnsPress_Theme
{
    
    /**
     * Initial call
     */
    public function __construct() 
    {
        AnsPress_Common_Pages::get_instance();
        
        add_action('init', array($this, 'init_actions'));
        add_filter('post_class', array( $this, 'question_answer_post_class' ));
        add_filter('body_class', array( $this, 'body_class' ));        
        add_filter('comments_template', array( $this, 'comment_template' ));
        add_action('after_setup_theme', array( $this, 'includes' ));
        add_filter('wp_title', array( $this, 'ap_title' ) , 1, 2);
        add_filter('the_title', array( $this, 'the_title' ) , 1, 2);
        add_filter('wp_head', array( $this, 'feed_link' ) , 9);
        add_action('ap_before', array( $this, 'ap_before_html_body' ));
    }

    public function init_actions()
    {
        add_shortcode( 'anspress', array( AnsPress_BasePage_Shortcode::get_instance(), 'anspress_sc' ) );
    }
    
    /**
     * AnsPress theme function as like WordPress theme function
     * @return void
     */
    public function includes() 
    {
        require_once ap_get_theme_location('functions.php');
    }
        
    /**
     * Add answer-seleted class in post_class
     * @param  array $classes
     * @return array
     * @since 2.0.1
     *
     */
    public function question_answer_post_class($classes) 
    {
        global $post;
        if ($post->post_type == 'question') 
        {
            if (ap_question_best_answer_selected($post->post_id)) $classes[] = 'answer-selected';
            
            $classes[] = 'answer-count-' . ap_count_answer_meta();
        }
        if ($post->post_type == 'answer') 
        {
            if (ap_answer_is_best($post->post_id)) $classes[] = 'best-answer';
        }
        
        return $classes;
    }
    
    /**
     * Add anspress classess to body
     * @param  array $classes
     * @return array
     * @since 2.0.1
     */
    public function body_class($classes) 
    {
        
        // add anspress class to body
        if (get_the_ID() == ap_opt('questions_page_id') || get_the_ID() == ap_opt('question_page_id') || is_singular('question')) $classes[] = 'anspress';
        
        // return the $classes array
        return $classes;
    }
    
    // register comment template
    public function comment_template($comment_template) 
    {
        global $post;
        if ($post->post_type == 'question' || $post->post_type == 'answer') 
        {
            return ap_get_theme_location('comments.php');
        } 
        else
        {
            return $comment_template;
        }
    }
    
    public function disable_comment_form($open, $post_id) 
    {
        if (ap_opt('base_page') == $post_id || ap_opt('ask_page') == $post_id || ap_opt('edit_page') == $post_id || ap_opt('a_edit_page') == $post_id || ap_opt('categories_page') == $post_id) 
        {
            return false;
        }
        return $open;
    }
    
    /**
     * @param string $title
     * @return void
     */
    public function ap_title($title) 
    {
        if (is_anspress()) 
        {
            remove_filter('wp_title', array(
                $this,
                'ap_title'
            ));

            $new_title = ap_page_title();
            
            $new_title = str_replace('ANSPRESS_TITLE', $new_title, $title);
            $new_title = apply_filters('ap_title', $new_title);
            
            return $new_title;
        }
        
        return $title;
    }
    
    public function the_title($title, $id = null) 
    {
        
        if ($id == ap_opt('base_page')) 
        {
            remove_filter('the_title', array(
                $this,
                'the_title'
            ));
            return ap_page_title();
        }
        return $title;
    }
    
    public function menu($atts, $item, $args) 
    {
        return $atts;
    }
    
    public function feed_link() 
    {
        if (is_anspress()) 
        {
            echo '<link href="' . esc_url(home_url('/feed/question-feed')) . '" title="' . __('Question >> Feed', 'ap') . '" type="application/rss+xml" rel="alternate">';
        }
    }
    
    public function ap_before_html_body() 
    {
        dynamic_sidebar('ap-before');
    }

}
