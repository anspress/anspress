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
        add_action('post_type_link',array($this, 'ans_post_type_link'),10,2);
        add_filter('manage_edit-question_columns', array( $this, 'cpt_question_columns'));
        add_action('manage_posts_custom_column', array($this, 'custom_columns_value'));

    }

    /**
     * Register question CPT
     * @return void
     * @since 2.0.1
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
            'rewrite' => true
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

    /**
     * Alter answer CPT permalink
     * @param  string $link
     * @param  object $post 
     * @return string
     * @since 2.0.0-alpha2
     */
    public function ans_post_type_link($link, $post) {
        if ($post->post_type == 'answer' && $post->post_parent != 0) {
            $link = get_permalink($post->post_parent) ."#answer_{$post->ID}";
        }
        return $link;
    }

    /**
     * Alter columns in question cpt
     * @param  array $columns
     * @return array
     * @since  2.0.0-alpha
     */
    public function cpt_question_columns($columns)
    {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "asker" => __('Asker', 'ap'),
            //"status" => __('Status', 'ap'),
            "title" => __('Title', 'ap'),
            //"question_category" => __('Category', 'ap'),
            //"question_tags" => __('Tags', 'ap'),
            "answers" => __('Ans', 'ap'),
            "comments" => __('Comments', 'ap'),
            //"vote" => __('Vote', 'ap'),
            "status" => __('Status', 'ap'),
            "date" => __('Date', 'ap')
        );
        return $columns;
    }

    public function custom_columns_value($column)
    {
        global $post;

        if($post->post_type != 'question')
            return $column;

        if ('asker' == $column || 'answerer' == $column) {
            echo get_avatar(get_the_author_meta('user_email'), 40);
        }elseif ('status' == $column) {
            $total_flag = ap_post_flag_count();
            echo '<span class="post-status">' . $post->post_status .'</span>';
            echo '<span class="flag-count' . ($total_flag ? ' flagged' : '') . '">' .__('Flag : ').'<i>'. $total_flag . '</i></span>';
        } /*elseif (ANSPRESS_CAT_TAX == $column) {

            $category = get_the_terms($post->ID, ANSPRESS_CAT_TAX);            

            if (!empty($category)) {
                $out = array();

                foreach ($category as $cat) {
                    $out[] = edit_term_link($cat->name, '', '', $cat, false);
                }
                echo join(', ', $out);
            }
            
            else {
                _e('--');
            }
        } elseif (ANSPRESS_TAG_TAX == $column) {
            
            $terms = get_the_terms($post->ID, ANSPRESS_TAG_TAX);
            
            
            if (!empty($terms)) {
                $out = array();
                
                
                foreach ($terms as $term) {
                    $out[] = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array(
                        'post_type' => $post->post_type,
                        ANSPRESS_TAG_TAX => $term->slug
                    ), 'edit.php')), esc_html(sanitize_term_field('name', $term->name, $term->term_id, ANSPRESS_TAG_TAX, 'display')));
                }
                
                echo join(', ', $out);
            }
            
            
            else {
                _e('No Tags');
            }
        }*/ elseif ('answers' == $column) {
            /* Get the genres for the post. */
            $an_count_args = array(
                'post_type' => 'answer',
                'post_status' => 'publish',
                'post_parent' => $post->ID,
                'showposts' => -1,
            );
            
            $a_count = count(get_posts($an_count_args));
            
            /* If terms were found. */
            if (!empty($a_count)) {
                
                echo '<a class="ans-count" title="' . $a_count . __('answers', 'ap') . '" href="' . esc_url(add_query_arg(array(
                    'post_type' => 'answer',
                    'post_parent' => $post->ID
                ), 'edit.php')) . '">' . $a_count . '</a>';
            }
            
            /* If no terms were found, output a default message. */
            else {
                echo '<a class="ans-count" title="0' . __('answers', 'ap') . '">0</a>';
            }
        } elseif ('parent_question' == $column) {
            echo '<a class="parent_question" href="' . esc_url(add_query_arg(array(
                'post' => $post->post_parent,
                'action' => 'edit'
            ), 'post.php')) . '"><strong>' . get_the_title($post->post_parent) . '</strong></a>';
        }  /*elseif ('status' == $column) {
            echo '<span class="question-status">' . ap_get_question_label() . '</span>';
        } elseif ('vote' == $column) {
            echo '<span class="vote-count' . ($post->flag ? ' zero' : '') . '">' . $post->net_vote . '</span>';
        }*/
    }

}
