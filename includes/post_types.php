<?php
/**
 * AnsPress post types
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class AnsPress_PostTypes
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
		//Register Custom Post types and taxonomy
        add_action('init', array($this, 'register_question_cpt'), 0);
        add_action('init', array($this, 'register_answer_cpt'), 0);

    }

    /**
     * Register question CPT
     * @return void
     * @since 2.0
     */
    public function register_question_cpt(){
        
        // question CPT labels
        $labels = array(
            'name'              => _x('Questions', 'Post Type General Name', 'ap'),
            'singular_name'     => _x('Question', 'Post Type Singular Name', 'ap'),
            'menu_name'         => __('Questions', 'ap'),
            'parent_item_colon' => __('Parent Question:', 'ap'),
            'all_items'         => __('All Questions', 'ap'),
            'view_item'         => __('View Question', 'ap'),
            'add_new_item'      => __('Add New Question', 'ap'),
            'add_new'           => __('New Question', 'ap'),
            'edit_item'         => __('Edit Question', 'ap'),
            'update_item'       => __('Update Question', 'ap'),
            'search_items'      => __('Search question', 'ap'),
            'not_found'         => __('No question found', 'ap'),
            'not_found_in_trash' => __('No questions found in Trash', 'ap')
        );
        
        /**
         * FILTER: ap_question_cpt_labels
         * filter is called before registering question CPT
         */
        $labels = apply_filters('ap_question_cpt_labels', $labels);

        // question CPT arguments
        $args   = array(
            'label' => __('question', 'ap'),
            'description' => __('Question', 'ap'),
            'labels' => $labels,
            'supports' => array(
                'title',
                'editor',
                'author',
                'comments',
                'trackbacks',
                'revisions',
                'custom-fields'
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_icon' => ANSPRESS_URL . '/assets/question.png',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'query_var' => 'question',
            'capability_type' => 'post',
            'rewrite' => false
        );

        /**
         * FILTER: ap_question_cpt_args 
         * filter is called before registering question CPT
         */
        $args = apply_filters('ap_question_cpt_args', $args);

        // register CPT question
        register_post_type('question', $args);
    }

    /**
     * Register answer custom post type
     * @return void
     * @since  2.0
     */
    public function register_answer_cpt(){
        // Answer CPT labels
        $labels = array(
            'name'          => _x('Answers', 'Post Type General Name', 'ap'),
            'singular_name' => _x('Answer', 'Post Type Singular Name', 'ap'),
            'menu_name' => __('Answers', 'ap'),
            'parent_item_colon' => __('Parent Answer:', 'ap'),
            'all_items' => __('All Answers', 'ap'),
            'view_item' => __('View Answer', 'ap'),
            'add_new_item' => __('Add New Answer', 'ap'),
            'add_new' => __('New answer', 'ap'),
            'edit_item' => __('Edit answer', 'ap'),
            'update_item' => __('Update answer', 'ap'),
            'search_items' => __('Search answer', 'ap'),
            'not_found' => __('No answer found', 'ap'),
            'not_found_in_trash' => __('No answer found in Trash', 'ap')
        );

        /**
         * FILTER: ap_answer_cpt_label 
         * filter is called before registering answer CPT
         */
        $labels = apply_filters('ap_answer_cpt_label', $labels);
        
        // Answers CPT arguments
        $args   = array(
            'label' => __('answer', 'ap'),
            'description' => __('Answer', 'ap'),
            'labels' => $labels,
            'supports' => array(
                'editor',
                'author',
                'comments',
                'revisions',
                'custom-fields'
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'menu_icon' => ANSPRESS_URL . '/assets/answer.png',
            //'show_in_menu' => 'anspress',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post',
            'rewrite' => false
        );
        
        /**
         * FILTER: ap_answer_cpt_args 
         * filter is called before registering answer CPT
         */
        $args = apply_filters('ap_answer_cpt_args', $args);

        // register CPT answer
        register_post_type('answer', $args);
    }

}
