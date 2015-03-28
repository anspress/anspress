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
        NULL === self::$instance && self::$instance = new self;

        return self::$instance; // return the object
    }

    public function __construct()
    {
        global $questions;

        ap_register_page('base', __('Questions', 'ap'), array($this, 'base_page'));
        ap_register_page('question', __('Question', 'ap'), array($this, 'question_page'));
        ap_register_page('ask', __('Ask', 'ap'), array($this, 'ask_page'));
        ap_register_page('edit', __('Edit', 'ap'), array($this, 'edit_page'));
        ap_register_page('search', __('Search', 'ap'), array($this, 'search_page'));
    }


    public function base_page()
    {
    	global $questions, $wp;
        
        $tags = @$wp->query_vars['ap_sc_atts_tags'];
        $categories = @$wp->query_vars['ap_sc_atts_categories'];
        $tax_relation = @$wp->query_vars['ap_sc_atts_tax_relation'];
        $tax_relation = !empty($tax_relation) ? $tax_relation : 'OR';

        $tags_operator = @$wp->query_vars['ap_sc_atts_tags_operator'];
        $tags_operator = !empty($tags_operator) ? $tags_operator : 'IN';

        $categories_operator = @$wp->query_vars['ap_sc_atts_categories_operator'];
        $categories_operator = !empty($categories_operator) ? $categories_operator : 'IN';

        $args = array();
        $args['tax_query'] = array('relation' => $tax_relation);

        if(!empty($tags) && is_array($tags)){
            $args['tax_query'][] = array(
                'taxonomy' => 'question_tag',
                'field'    => 'slug',
                'terms'    => $tags,
                'operator' => $tags_operator,
            );
        }

        if(!empty($categories) && is_array($categories)){
            $args['tax_query'][] = array(
                'taxonomy' => 'question_category',
                'field'    => 'slug',
                'terms'    => $categories,
                'operator' => $categories_operator,
            );
        }

    	$questions 		 = new Question_Query($args);

		include(ap_get_theme_location('base.php'));
    }

    public function question_page()
    {
        remove_filter( 'the_content', array($this, 'question_page') );
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

    public function search_page()
    {
        global $questions;
        $keywords   = sanitize_text_field( get_query_var( 'ap_s' ));
        $questions  = new Question_Query(array('s' => $keywords));

        if($questions->have_posts())
            include(ap_get_theme_location('base.php'));
        else
            _e('No posts found based on your criteria.', 'ap');
    }

}

