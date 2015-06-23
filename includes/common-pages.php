<?php
/**
 * Class for base page
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_Common_Pages
{

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

        add_action('init', array($this, 'register_common_pages'));
        
    }

    public function register_common_pages()
    {
        ap_register_page('base', __('Questions', 'ap'), array($this, 'base_page'));
        ap_register_page(ap_opt('question_page_slug'), __('Question', 'ap'), array($this, 'question_page'), false);
        ap_register_page(ap_opt('ask_page_slug'), __('Ask', 'ap'), array($this, 'ask_page'));
        ap_register_page('edit', __('Edit', 'ap'), array($this, 'edit_page'), false);
        ap_register_page('search', __('Search', 'ap'), array($this, 'search_page'), false);
    }

    public function base_page()
    {
    	global $questions, $wp;
        $query = $wp->query_vars;

        $tax_relation = @$wp->query_vars['ap_sc_atts_tax_relation'];
        $tax_relation = !empty($tax_relation) ? $tax_relation : 'OR';

        $tags_operator = @$wp->query_vars['ap_sc_atts_tags_operator'];
        $tags_operator = !empty($tags_operator) ? $tags_operator : 'IN';

        $categories_operator = @$wp->query_vars['ap_sc_atts_categories_operator'];
        $categories_operator = !empty($categories_operator) ? $categories_operator : 'IN';

        $args = array();
        $args['tax_query'] = array('relation' => $tax_relation);

        if(isset($query['ap_sc_atts_tags']) && is_array($query['ap_sc_atts_tags'])){
            $args['tax_query'][] = array(
                'taxonomy' => 'question_tag',
                'field'    => 'slug',
                'terms'    => $query['ap_sc_atts_tags'],
                'operator' => $tags_operator,
            );
        }elseif(isset($_GET['ap_tag_sort']) && $_GET['ap_tag_sort'] != 0){
            $cat = (int) $_GET['ap_tag_sort'];
            $args['tax_query'][] = array(
                'taxonomy' => 'question_tag',
                'field'    => 'term_id',
                'terms'    => array($cat),
            );
        }

        if(isset($query['ap_sc_atts_categories']) && is_array($query['ap_sc_atts_categories'])){
            $args['tax_query'][] = array(
                'taxonomy' => 'question_category',
                'field'    => 'slug',
                'terms'    => $query['ap_sc_atts_categories'],
                'operator' => $categories_operator,
            );
        }elseif(isset($_GET['ap_cat_sort']) && $_GET['ap_cat_sort'] != 0){
            $cat = (int) $_GET['ap_cat_sort'];
            $args['tax_query'][] = array(
                'taxonomy' => 'question_category',
                'field'    => 'term_id',
                'terms'    => array($cat),
            );
        }
        ap_get_questions($args);
		include(ap_get_theme_location('base.php'));
    }

    /**
     * Output single question page
     * @return void
     */
    public function question_page()
    {
        global $questions;

        ap_get_question(get_question_id());
        
        if(ap_have_questions()){
            while ( anspress()->questions->have_posts() ) : anspress()->questions->the_post();
                global $post;
                setup_postdata( $post );                 
            endwhile;
            include(ap_get_theme_location('question.php'));
            wp_reset_postdata();
        }else{
            include(ap_get_theme_location('not-found.php'));
        }
        
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
        $keywords   = sanitize_text_field( get_query_var( 'ap_s' ));
        $type       = sanitize_text_field( @$_GET['type'] );
        
        if($type == ''){
            ap_get_questions(array('s' => $keywords));
            include(ap_get_theme_location('base.php'));
        }elseif($type == 'user' && ap_opt('enable_users_directory')){
            global $ap_user_query;        
            $ap_user_query = ap_has_users(array('search' => $keywords, 'search_columns' => array('user_login', 'user_email', 'user_nicename')));
            include(ap_get_theme_location('users/users.php'));
        }
    }

}

