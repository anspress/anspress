<?php
/**
 * Question class
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

if(!class_exists('Question_Query')):

/**
 * Question
 *
 * This class is for retriving questions based on $args
 */
class Question_Query {

    public $questions;
    public $question;
    public $max_num_pages;
    public $args = array();

    /**
     * Initialize class
     * @param array $args
     * @access public
     * @since  2.0
     */
    public function __construct( $args = array() ) {

        global $questions;

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        if(isset($args['post_parent']))
            $post_parent = $args['post_parent'];
        else
            $post_parent = (get_query_var('parent')) ? get_query_var('parent') : false;

        if(isset($args['orderby']))
            $orderby = $args['orderby'];
        else
            $orderby = (isset($_GET['ap_sort'])) ? $_GET['ap_sort'] : 'active';

        $defaults = array(
            'post_status'   => array('publish', 'moderate', 'private_post', 'closed'),
            'showposts'     => ap_opt('question_per_page'),
            'orderby'       => $orderby,
            'paged'         => $paged,
        );

        if($post_parent)
            $this->args['post_parent'] = $post_parent;

        $this->args = wp_parse_args( $args, $defaults );

        if(get_query_var('ap_s') != '')
            $this->args['s'] = sanitize_text_field(get_query_var('ap_s'));

        $this->sortby_questions();

        do_action('ap_pre_get_questions', $this);

        $this->args['post_type'] = 'question';        

        $questions = new WP_Query($this->args);

        $this->questions    = $questions;
        $this->question     = $questions->post;
        $this->max_num_pages= $questions->max_num_pages;
    }

    public function get_questions(){
        return $this->questions;
    }

    /**
     * Modify orderby args
     * @return void
     */
    public function sortby_questions(){
        switch ( $this->args[ 'sortby' ] ) {
            case 'answers' :
                $this->args[ 'orderby' ] = 'meta_value_num';
                $this->args[ 'meta_key' ] = ANSPRESS_ANS_META;
            break;
            case 'unanswered' :
                $this->args[ 'orderby' ] = 'meta_value_num';
                $this->args[ 'meta_key' ] = ANSPRESS_ANS_META ;
                $this->args[ 'meta_value' ] = 0 ;
            break;
            case 'voted' :
                $this->args['orderby'] = 'meta_value_num';
                $this->args['meta_key'] = ANSPRESS_VOTE_META;
            break;
            case 'unsolved' :
                $this->args['orderby'] = 'meta_value_num';
                $this->args['meta_key'] = ANSPRESS_SELECTED_META;
                $this->args['meta_compare'] = '!=';
                $this->args['meta_value'] = 1;

 
            break;
            case 'oldest' :
                $this->args['orderby'] = 'date';
                $this->args['order'] = 'ASC';
            break;
            case 'active' :
                $this->args['orderby'] = 'meta_value';
                $this->args['meta_key'] = ANSPRESS_UPDATED_META;
                $this->args['meta_query']  = array(
                    'relation' => 'OR',
                    array(
                        'key' => ANSPRESS_UPDATED_META
                    ),
                );
            break;

            //TOOD: Add more orderby like most viewed, and user order like 'answered by user_id', 'asked_by_user_id'
        }
         
    }

    public function have_posts(){
        return $this->questions->have_posts();
    }

    public function the_question(){
        return $this->questions->the_post();
    }

}

endif;

function ap_get_questions($args = ''){
    global $questions;
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    if(isset($args['post_parent']))
        $post_parent = $args['post_parent'];
    else
        $post_parent = (get_query_var('parent')) ? get_query_var('parent') : false;

    if(isset($args['sortby']))
        $sortby = $args['sortby'];
    else
        $sortby = (isset($_GET['ap_sort'])) ? $_GET['ap_sort'] : 'active';

    $args = wp_parse_args( $args, array(
        'post_status'   => array('publish', 'moderate', 'private_post', 'closed'),
        'showposts'     => ap_opt('question_per_page'),
        'sortby'        => $sortby,
        'paged'         => $paged,
        'post_parent'   => $post_parent
    ) );

    $questions = new Question_Query($args);

    return $questions;
}

function ap_have_questions(){
    global $questions;
    return $questions->have_posts();
}

function ap_questions(){
    global $questions;    
    return $questions->have_posts();
}

function ap_the_question(){
    global $questions;     
    return $questions->the_question(); 
}

function ap_question_the_object(){
    global $questions;
    return $questions->question;
}

/**
 * Echo active question ID
 * @since 2.1
 */
function ap_question_the_ID(){
    echo ap_question_get_the_ID();
}

    /**
     * Return question ID active in loop
     * @return integer
     * @since 2.1
     */
    function ap_question_get_the_ID(){
        $question = ap_question_the_object();

        return $question->ID;
    }

function ap_question_get_author_id(){
    $question = ap_question_the_object();
    return $question->post_author;
}

/**
 * Check if active post is private post
 * @return boolean
 * @since  2.1
 */
function ap_question_is_private(){
    return is_private_post();
}

/**
 * echo user profile link
 * @return 2.1
 */
function ap_question_the_author_link(){
    echo ap_user_link();
}
    /**
     * Return the author profile link
     * @return string
     * @since 2.1
     */
    function ap_question_get_the_author_link(){
        return ap_user_link();
    }

function ap_question_the_author_avatar($size = 45){
    echo ap_question_get_the_author_avatar( $size );
}
    /**
     * Return question author avatar
     * @param  integer $size
     * @return string
     * @since 2.1
     */
    function ap_question_get_the_author_avatar($size = 45){
        return get_avatar( ap_question_get_author_id(), $size );
    }

function ap_question_the_answer_count(){
    echo '<a class="ap-questions-count ap-questions-acount" href="'.ap_answers_link().'"><span>'. ap_question_get_the_answer_count().'</span>'.__('ans', 'ap').'</a>';
}
    /**
     * Return active question answer count
     * @return integer
     * @since 2.1
     */
    function ap_question_get_the_answer_count(){
        return ap_count_answer_meta(ap_question_get_the_ID());
    }

/**
 * Echo active question voting button
 * @return void
 * @since 2.1
 */
function ap_question_the_vote_button(){
    if(!ap_opt('disable_voting_on_question')){
        ?>
            <span class="ap-questions-count ap-questions-vcount">
                <span><?php echo ap_net_vote() ?></span>
                <?php  _e('votes', 'ap'); ?>
            </span>
        <?php 
    }
}

/**
 * Echo active question permalink
 * @return void
 * @since 2.1
 */
function ap_question_the_permalink(){
    echo ap_question_get_the_permalink();
}
    
    /**
     * Return active question permalink
     * @return string
     * @since 2.1
     */
    function ap_question_get_the_permalink(){
        return the_permalink(ap_question_get_the_ID());
    }

/**
 * output questions page pagination
 * @return string pagination html tag
 */
function ap_questions_the_pagination(){
    global $questions;
    ap_pagination(false, $questions->max_num_pages);
}